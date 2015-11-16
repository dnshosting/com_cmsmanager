<?php

/**
 * @package     CMS Manager
 * @author      COLT Engine S.R.L. <andrea.sosso@dnshosting.it>
 * @authorUrl   https://www.joomlahost.it
 *
 * @copyright   Copyright (C) 2015 COLT Engine s.r.l, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.installer.helper');
jimport('joomla.installer.installer');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.updater.update');

/**
 * Helper class implementing the core operations
 * of the CMS Manager.
 */
class CMSManager
{
    /**
     * @var CMSManagerLogger the main Logger.
     */
    private $log;

    /**
     * @var bool true if the Log entries must be persisted into the DB.
     */
    private $store;

    /**
     * @var string Temporary installation path.
     */
    private $installPath = '/tmp/cmsmanager_install';

    /**
     * Create a new instance of CMSManager.
     *
     * @param bool|true $store if true, the Log messages are persisted into the DB.
     */
    function __construct($store = true)
    {
        $this->installPath = JPATH_SITE . $this->installPath;
        $this->store = $store;
    }

    /**
     * Return the Logger instance bound to the CMSManager.
     *
     * @return CMSManagerLogger the Logger.
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Enable backup
     *
     * @return bool true if success, false otherwise
     */
    public function enableBackup() {
        $this->log = new CMSManagerLogger(__FUNCTION__, "", $this->store);
        return $this->enableAkeeebaBackup();
    }

    /**
     * Enable Akeeba Remote Backup
     *
     * @return bool true if success, false otherwise
     */
    private function enableAkeeebaBackup() {

        if ( ! file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba/version.php')) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_AKEEBA_NOT_FOUND');
            $log->setError();
            $this->log->addLog($log);

            return false;
        }

        $params = JComponentHelper::getParams('com_akeeba');

        $update = false;

        // Enable frontend if is disabled
        if ( ! $params->get('frontend_enable')) {
            $params->set("frontend_enable", 1);
            $update = true;
        }

        // Generate secret word if is empty
        if(!$params->get('frontend_secret_word')) {
            $params->set('frontend_secret_word', $this->generateRandomString());
            $update = true;
        }

        $site = new CMSManagerSite();
        // Check if CMS Manager Akeeba Profile exist
        if($site->getAkeebaProfile() == 0) {

            $app = JFactory::getApplication();
            $db =  JFactory::getDbo();
            $prefix = $app->getCfg('dbprefix');

            $tables = $db->getTableList();

            // Check if backup table exist
            if (!in_array($prefix . "ak_profiles", $tables))
                return false;

            // Get default Akeeba profile
            $query = "SELECT `configuration` FROM `#__ak_profiles` WHERE `id` = 1";
            JFactory::getDbo()->setQuery($query);

            try {
                $result = $db->loadResult();

                if(!$result) return false;

                $profile = "CMS Manager Backup Profile";
                $query = $db->getQuery(true);

                // Clone default Akeeba profile without filters
                $columns = array('description', 'configuration', 'filters');
                $values = array($db->quote($profile), $db->quote($result), $db->quote(""));

                $query
                    ->insert($db->quoteName('#__ak_profiles'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                $db->execute();

            } catch (Exception $e) {
                return false;
            }

        }

        if($update) {
            // Get a new database query instance
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            // Build the query
            $query->update('#__extensions AS a');
            $query->set('a.params = ' . $db->quote((string)$params));
            $query->where('a.element = "com_akeeba"');

            // Execute the query
            $db->setQuery($query);
            $db->query();

            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_AKEEBA_REMOTE_BACKUP_ENABLED', '');
            $this->log->addLog($log);
        }

        return true;
    }

    /**
     * Generate random string
     * @param int $length
     * @return string
     */
    function generateRandomString($length = 16) {
        return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',$length)),0,$length);
    }

    /**
     * Download and install an extension given the publishing URL.
     *
     * @param $url string the extension publishing url.
     *
     * @return boolean true on success.
     */
    public function installSingleExtension($url)
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, $url, $this->store);
        return $this->downloadExtensions($url);
    }

    /**
     * Download an extension given the publishing URL.
     *
     * @param $url string the extension publishing url.
     * @param $multiple boolean if given URL contains multiple extensions.
     *
     * @return boolean true on success.
     */
    private function downloadExtensions($url, $multiple = false)
    {
        ini_set('allow_url_fopen', 'On');

        $error = "";
        $package = "";

        // Try to download package
        try {
            $package = self::downloadPackage($url);
        } catch (UnexpectedValueException $e) {
            $error = $e->getMessage();
        }

        if (!$package) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_DOWNLOAD_ERR', array($url, $error));
            $log->setError();

            $this->log->addLog($log);

            return false;
        }

        return self::upload($package, $multiple);
    }

    /**
     * Download a package given the publishing URL.
     *
     * @param $url string the extension publishing url.
     * @return mixed path to downloaded package or boolean false on failure
     */
    public function downloadPackage($url)
    {
        return JInstallerCMS::downloadPackage($url);
    }

    /**
     * Load installation file and execute installer
     *
     * @param $package string the name of the package.
     * @param $multiple boolean if it is multiple extensions installation.
     *
     * @return boolean true on success.
     */
    private function upload($package, $multiple)
    {
        // Set time limit to infinite
        set_time_limit(0);

        // Clean the installation directory if exists
        if (JFolder::exists($this->installPath)) {
            if (!JFolder::delete($this->installPath)) {
                $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_FOLDER_DELETE_ERR', $this->installPath);
                $log->setError();

                $this->log->addLog($log);

                return false;
            }
        }

        // Create the installation directory
        if (!JFolder::create($this->installPath)) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_FOLDER_CREATE_ERR', $this->installPath);
            $log->setError();

            $this->log->addLog($log);

            return false;
        }

        // Prepare file for installation
        $src = JPATH_SITE . '/tmp/' . $package;
        $dest = $this->installPath . '/' . $package;

        // Copy to temp folder
        if (!JFile::copy($src, $dest)) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_COPY_ERR', array("src" => $src, "dest" => $dest));
            $log->setError();

            $this->log->addLog($log);

            return false;
        }

        // Clean downloaded file
        if (!JFile::delete($src)) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_SRC_DELETE_ERR', $src);
            $log->setError();

            $this->log->addLog($log);

            return false;
        }

        $r = false;

        // Multiple extensions installation
        if ($multiple) {
            if (class_exists('ZipArchive')) {
                $zip = new ZipArchive;
                if ($zip->open($dest) === TRUE) {
                    $zip->extractTo($this->installPath);
                    $zip->close();

                    // Deleting the root zip file
                    JFile::delete($dest);

                    $pkgs = JFolder::files($this->installPath);

                    foreach ($pkgs as $pkg) {
                        self::installExtension($this->installPath . '/' . $pkg);
                    }

                    $r = true;
                } else {
                    $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_ZIP_ERR');
                    $log->setError();

                    $this->log->addLog($log);
                }
            } else {
                $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_ZIP_NOTFOUND');
                $log->setError();

                $this->log->addLog($log);
            }
        } else {
            // Install extensions
            $r = self::installExtension($dest);
        }

        // Clean temp folder
        $this->cleanTemp();

        return $r;
    }

    /**
     * Install a new extension.
     *
     * @param string $pkg the path of the downloaded package.
     * @return boolean true on success.
     */
    private function installExtension($pkg)
    {
        $installer = new JInstaller();
        $installer->setOverwrite(true);

        // Extract package
        $package = JInstallerHelper::unpack($pkg);

        if(!$package) {
            // Add .zip extension to file
            $zip = $pkg . ".zip";
            JFile::move($pkg, $zip);
            $package = JInstallerHelper::unpack($zip);
        }

        // Install package
        if ($installer->install($package['dir'])) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_INSTALL_OK', $pkg);
            $this->log->addLog($log);
        } else {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_INSTALL_ERR', $pkg);
            $log->setError();
            $this->log->addLog($log);
        }

        if ($this->log->getErrorCount() == 0)
            return true;
        else
            return false;
    }

    /**
     * Remove the temp directory if exists.
     *
     * @return boolean true on success.
     */
    private function cleanTemp()
    {
        if (JFolder::exists($this->installPath)) {

            if (!JFolder::delete($this->installPath)) {
                $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_FOLDER_DELETE_ERR');
                $log->setError();
                $this->log->addLog($log);

                return false;
            }
        }

        return true;
    }

    /**
     * Download azip package containing multiple extensions.
     *
     * @param $url string the extension publishing url.
     *
     * @return boolean true on success.
     */
    public function installExtensions($url)
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, $url, $this->store);
        return $this->downloadExtensions($url, true);
    }

    /**
     * Delete an extension given its identifier.
     *
     * @param $extensionId string the id of the extension that has to be removed.
     *
     * @return boolean true on success.
     */
    public function removeExtension($extensionId)
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, $extensionId, $this->store);
        return $this->uninstallExtension($extensionId);
    }

    /**
     * Delete an extension given its identifier.
     *
     * @param $element string the id of the extension that has to be removed.
     *
     * @return boolean true on success.
     */
    private function uninstallExtension($element)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(array('extension_id', 'element', 'type'));
        $query->from('#__extensions');
        $query->where("extension_id = '$element'");

        $db->setQuery($query);
        $results = $db->loadObjectList();

        if (!$results) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_DELETE_NOTFOUND_ERR', $element);
            $log->setError();

            $this->log->addLog($log);
            return false;
        }

        foreach ($results as $result) {
            $installer = new JInstaller();

            $eid = (int)$result->extension_id;
            $logv = array("element" => $element, "eid" => $eid, "type" => $result->type);

            // Uninstall
            if (!$installer->uninstall($result->type, $eid)) {
                $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_DELETE_GENERIC_ERR', $logv);
                $log->setError();

                $this->log->addLog($log);
            } else {
                $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_DELETE_OK', $logv);
                $this->log->addLog($log);
            }
        }

        if ($this->log->getErrorCount() == 0)
            return true;
        else
            return false;
    }

    /**
     * Fetches the available updates.
     *
     * @param $clean boolean if the cache must be cleared.
     *
     * @return boolean true on success.
     */
    public function getUpdates($clean)
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, "", $this->store);
        if ($clean) {
            $this->cleanUpdate();
            $this->enableSites();
        }

        // Looking for updates...
        /** @var JUpdater $updater */
        $updater = JUpdater::getInstance();

        // Suppress error messages
        @$updater->findUpdates(0, 3600);
    }

    /**
     * Clear the update cache.
     *
     * @return boolean true on success.
     */
    private function cleanUpdate()
    {
        JLoader::register('InstallerModelUpdate', JPATH_ADMINISTRATOR . '/components/com_installer/models/update.php');
        $my = new InstallerModelUpdate();

        if ($my->purge()) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_PURGED_UPDATES');
            $this->log->addLog($log);

            return true;
        } else {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_FAILED_TO_PURGE_UPDATES');
            $log->setError();
            $this->log->addLog($log);

            return false;
        }
    }

    /**
     * Enables any disabled rows in #__update_sites table
     *
     * @return boolean true on success.
     */
    private function enableSites()
    {
        JLoader::register('InstallerModelUpdate', JPATH_ADMINISTRATOR . '/components/com_installer/models/update.php');
        $my = new InstallerModelUpdate();

        if ($my->enableSites()) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_ENABLED_UPDATES');
            $this->log->addLog($log);
            return true;
        } else {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_FAILED_TO_ENABLE_UPDATES');
            $log->setError();
            $this->log->addLog($log);

            return false;
        }
    }

    /**
     * Install multiple updates.
     *
     * @param $uid array list of updates identifiers.
     *
     * @return boolean true on success.
     */
    public function installUpdate($uid)
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, "", $this->store);

        /** @var JUpdate $update */
        $update = new JUpdate();

        $instance = JTable::getInstance('update');
        $instance->load($uid);
        @$update->loadFromXML($instance->detailsurl);

        // install sets state and enqueues messages
        $res = $this->installSingleUpdate($update);

        if ($res) {
            $instance->delete($uid);
        }

        // Insert verbose log
        $app = JFactory::getApplication();
        $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_UPDATE', $app->getMessageQueue());

        $this->log->addLog($log);
        if (!$res)
            $log->setError();

        return $res;
    }

    /**
     * Install a single update.
     *
     * @param $update JUpdate the update that has to be installed.
     *
     * @return boolean true on success.
     */
    private function installSingleUpdate($update)
    {
        // Fetch download url from update site
        if (isset($update->get('downloadurl')->_data)) {
            $url = trim($update->downloadurl->_data);
        } else {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_FETCH_UPDATE_URL_FAILED', $update);
            $log->setError();
            $this->log->addLog($log);
            return false;
        }

        // Download package
        $p_file = self::downloadPackage($url);

        // Was the package downloaded?
        if (!$p_file) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_PACKAGE_DOWNLOAD_FAILED', $url);
            $log->setError();
            $this->log->addLog($log);
            return false;
        }

        // Get configuration tmp
        $config = JFactory::getConfig();
        $tmp_dest = $config->get('tmp_path');

        // Unpack the downloaded package file
        $pkg = $tmp_dest . '/' . $p_file;
        $package = JInstallerHelper::unpack($pkg);

        if(!$package) {
            // Add .zip extension to file
            $zip = $pkg . ".zip";
            JFile::move($pkg, $zip);
            $package = JInstallerHelper::unpack($zip);
        }

        // Unpack package
        if (!$package) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_PACKAGE_UNPACK_FAILED', $url);
            $log->setError();
            $this->log->addLog($log);

            return false;
        }

        // Get an installer instance
        $installer = JInstaller::getInstance();
        $update->set('type', $package['type']);

        // Install the package
        /** @var $installer JInstaller */
        if (!$installer->update($package['dir'])) {
            // There was an error updating the package
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_MSG_UPDATE_ERROR', $package);
            $log->setError();
            $this->log->addLog($log);

            $result = false;
        } else {
            // Package updated successfully
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_MSG_UPDATE_SUCCESS', $package['type']);
            $this->log->addLog($log);

            $result = true;
        }

        // Cleanup the install files
        if (!is_file($package['packagefile'])) {
            $config = JFactory::getConfig();
            $package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
        }

        JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

        return $result;
    }

    /**
     * Discover new extensions.
     */
    public function discoverExtension()
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, "", false);

        $this->purgeDiscoveredExtensions();
        $this->discoverAll();
    }

    /**
     * Purge the discovered extensions cache.
     *
     * @return boolean true on success.
     */
    private function purgeDiscoveredExtensions()
    {
        JLoader::register('InstallerModelDiscover', JPATH_ADMINISTRATOR . '/components/com_installer/models/discover.php');
        $my = new InstallerModelDiscover();

        if ($my->purge()) {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_MSG_DISCOVER_PURGEDDISCOVEREDEXTENSIONS');
            $this->log->addLog($log);

            return true;
        } else {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_MSG_DISCOVER_FAILEDTOPURGEEXTENSIONS');
            $log->setError();
            $this->log->addLog($log);

            return false;
        }

    }

    /**
     * Discover the installed extensions.
     */
    private function discoverAll()
    {
        JLoader::register('InstallerModelDiscover', JPATH_ADMINISTRATOR . '/components/com_installer/models/discover.php');
        $my = new InstallerModelDiscover();
        $my->discover();
    }

    /**
     * Install all the discovered extensions.
     */
    public function installDiscoveredExtension()
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, "", $this->store);

        $installer = JInstaller::getInstance();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions');
        $query->where('state=-1');
        $db->setQuery($query);

        $eid = $db->loadObjectList();

        if ($eid) {
            $failed = false;
            foreach ($eid as $id) {
                $result = $installer->discover_install($id->extension_id);
                if (!$result) {
                    $failed = true;

                    $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_MSG_DISCOVER_INSTALLFAILED', $id->element);
                    $log->setError();
                    $this->log->addLog($log);
                }
            }
            if (!$failed) {
                $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_MSG_DISCOVER_INSTALLSUCCESSFUL');
                $this->log->addLog($log);
            }
        } else {
            $log = new CMSManagerLog(__FUNCTION__, 'COM_CMSMANAGER_MSG_DISCOVER_NOEXTENSIONDETECTED');
            $log->setError();
            $this->log->addLog($log);
        }
    }

    /**
     * Fix database problems.
     */
    public function fixDb()
    {
        $this->log = new CMSManagerLogger(__FUNCTION__, "", $this->store);

        JLoader::register('InstallerModelDatabase', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_installer' . DS . 'models' . DS . 'database.php');
        $my = new InstallerModelDatabase();

        $my->fix();
    }

    /**
     * Return the list of the installed extensions.
     *
     * @return array the list of installed extensions.
     */
    public function listExtensions()
    {
        return $this->getExtensions();
    }

    /**
     * Return the list of installed extensions.
     *
     * @return array the list of installed extensions.
     */
    private function getExtensions()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $lsJCS = array();

        array_push($lsJCS, $this->getJoomlaVer());

        $query->select('e.*, u.update_id, u.version lastVersion');
        $query->from('#__extensions e');
        $query->join('LEFT', '#__updates u ON (u.extension_id = e.extension_id )');
        //$query->where('e.protected != 1');
        $db->setQuery($query);
        $data = $db->loadObjectList();
        $this->translate($data);

        $update_sites = $this->getUpdateSites();

        foreach ($data as $elem) {
            $manifest = json_decode($elem->manifest_cache);

            // Excluding the core extensions.
            // Include weblinks component: https://github.com/joomla-extensions/weblinks
            // In some case, pkg_weblinks has empty manifest.
            if ($manifest != null || $elem->element == "pkg_weblinks") {

                if ($elem->type == 'file') continue;

                if (!property_exists($manifest, "authorEmail")
                    || (property_exists($manifest, "authorEmail") && $manifest->authorEmail != "admin@joomla.org")
                    || $elem->type == "template"
                    || $elem->element == "pkg_weblinks") {

                    $ver = $manifest->version;

                    if (property_exists($manifest, "authorUrl") && $manifest->authorUrl) {
                        $url = $manifest->authorUrl;

                        if (!self::startsWith($url, "http")) {
                            $url = 'http://' . $url;
                        }

                        if (!filter_var($url, FILTER_VALIDATE_URL)) {
                            $url = '';
                        }

                    } else {
                        $url = '';
                    }

                    if (property_exists($manifest, "creationDate") && $manifest->creationDate) {
                        $date = $manifest->creationDate;
                    } else {
                        $date = '';
                    }

                    $type = $elem->type;
                    $realName = $elem->element;

                    if ($type == 'plugin') {
                        $realName = 'plg_' . $elem->folder . '_' . $elem->element;
                    }

                    $name = JText::_($elem->name);

                    // Set update servers
                    $elem->updateServer = '';
                    if (isset($update_sites[$elem->extension_id])) {
                        $elem->updateServer = $update_sites[$elem->extension_id]->location;
                    }

                    $myJCS = new CMSManagerExtension($name, $realName, $ver, $type, $elem->enabled, $elem->extension_id, $elem->lastVersion, $elem->update_id, $date, $url, $elem->updateServer, $elem->protected, $elem->state, $elem->client_id);
                    array_push($lsJCS, $myJCS);
                }
            }
        }

        return $lsJCS;
    }

    /**
     * Return a CMSManagerExtension wrapping informations about
     * the installed version of Joomla.
     *
     * @return CMSManagerExtension an extension wrapping some informations about the installed version of Joomla
     */
    private function getJoomlaVer()
    {
        $instance = new JVersion();
        $method = 'get' . ucfirst("short") . "Version";
        $version = call_user_func(array($instance, $method));

        /** @var JDatabase $db */
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('e.*, u.update_id, u.version lastVersion');
        $query->from('#__extensions e');
        $query->join('LEFT', '#__updates u ON (u.extension_id = e.extension_id )');
        $query->where('e.name = "files_joomla"');

        $db->setQuery($query);
        $data = $db->loadObject();

        //var_dump($data); exit();
        $manifest = json_decode($data->manifest_cache);
        $updateUrl = 'http://update.joomla.org/core/list.xml';

        return new CMSManagerExtension("JOOMLA! CORE", "Joomla!", $version, "core", "1", $data->extension_id, $data->lastVersion, $data->update_id, $manifest->creationDate, 'http://www.joomla.org/', $updateUrl, true, 0, false);
    }


    /**
     * Get translation for component name
     * @param $items
     */
    private function translate(&$items)
    {
        $lang = JFactory::getLanguage();
        foreach ($items as &$item) {
            if (strlen($item->manifest_cache)) {
                $data = json_decode($item->manifest_cache);
                if ($data) {
                    foreach ($data as $key => $value) {
                        if ($key == 'type') {
                            // ignore the type field
                            continue;
                        }
                        $item->$key = $value;
                    }
                }
            }
            $item->author_info = @$item->authorEmail . '<br />' . @$item->authorUrl;
            $item->client = $item->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
            $path = $item->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;
            switch ($item->type) {
                case 'component':
                    $extension = $item->element;
                    $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
                    $lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
                    || $lang->load("$extension.sys", $source, null, false, true);
                    break;
                case 'file':
                    $extension = 'files_' . $item->element;
                    $lang->load("$extension.sys", JPATH_SITE, null, false, true);
                    break;
                case 'library':
                    $extension = 'lib_' . $item->element;
                    $lang->load("$extension.sys", JPATH_SITE, null, false, true);
                    break;
                case 'module':
                    $extension = $item->element;
                    $source = $path . '/modules/' . $extension;
                    $lang->load("$extension.sys", $path, null, false, true)
                    || $lang->load("$extension.sys", $source, null, false, true);
                    break;
                case 'package':
                    $extension = $item->element;
                    $lang->load("$extension.sys", JPATH_SITE, null, false, true);
                    break;
                case 'plugin':
                    $extension = 'plg_' . $item->folder . '_' . $item->element;
                    $source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
                    $lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
                    || $lang->load("$extension.sys", $source, null, false, true);
                    break;
                case 'template':
                    $extension = 'tpl_' . $item->element;
                    $source = $path . '/templates/' . $item->element;
                    $lang->load("$extension.sys", $path, null, false, true)
                    || $lang->load("$extension.sys", $source, null, false, true);
                    break;
            }
            if (!in_array($item->type, array('language', 'template', 'library'))) {
                $item->name = JText::_($item->name);
            }
            settype($item->description, 'string');
            if (!in_array($item->type, array('language'))) {
                $item->description = JText::_($item->description);
            }
        }
    }

    /**
     * Get list of all update sites-
     *
     * @return array the list of updates.
     */
    private function getUpdateSites()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select('us.update_site_id')
            ->select('location')
            ->select('extension_id')
            ->from('#__update_sites_extensions AS ue')
            ->from('#__update_sites AS us')
            ->where('ue.update_site_id = us.update_site_id');
        $db->setQuery($query);
        try {
            $this->update_sites = $db->loadObjectList('extension_id');
        } catch (exception $e) {
            $this->update_sites = array();
        }
        return $this->update_sites;
    }

    /**
     * Check if string start exactly with another string
     *
     * @param $haystack string haystack
     * @param $needle string needle
     * @return bool
     */
    private static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    // #####################################################################################

    /**
     * @return boolean
     */
    public function isStore()
    {
        return $this->store;
    }

    /**
     * @param boolean $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return string Path
     */
    public function getInstallPath()
    {
        return $this->installPath;
    }

    /**
     * @param string $installPath
     */
    public function setInstallPath($installPath)
    {
        $this->installPath = $installPath;
    }

}
