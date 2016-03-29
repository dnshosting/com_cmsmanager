<?php

/**
 * @package     CMS Manager
 * @author      COLT Engine S.R.L.
 * @authorUrl   https://www.joomlahost.it
 *
 * @copyright   Copyright (C) 2015 COLT Engine s.r.l, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * CMS Manager install script.
 */
class com_cmsmanagerInstallerScript
{
    /**
     * @var JVersion The version of Joomla installed on the website.
     */
    public $version;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->version = new JVersion();
    }

    /**
     * Perform pre-flight checks in order to verify if the target Joomla
     * website is compatible with the com_cmsmanager component.
     *
     * @param   string $type The type of action executed: install, update or discover_install.
     * @param   object $parent
     *
     * @return  boolean True, if the target Joomla website is compatible with the com_cmsmanager component.
     */
    public function preflight($type, $parent)
    {
        // Getting the php_version
        if (defined('PHP_VERSION')) {
            $php_version = PHP_VERSION;
        } elseif (function_exists('phpversion')) {
            $php_version = phpversion();
        } else {
            $php_version = '5.0.0';
        }

        // Loading the language to display the log
        // messages in the correct language
        $language = JFactory::getLanguage();
        $language->load('com_cmsmanager');

        // The com_cmsmanager component requires
        // at lest PHP 5.2.4
        if (!version_compare($php_version, '5.2.4', '>=')) {
            $message = JText::sprintf('COM_CMSMANAGER_INSTALL_PHP_TOO_OLD', '5.2.4');
            $this->addtoLog($message);
            return false;
        }

        return true;
    }

    /**
     * Log debug messages using the proper method according
     * to the installed Joomla Version.
     *
     * @param string $message The log message.
     */
    private function addtoLog($message)
    {
        if (version_compare(JVERSION, '3.0', 'gt')) {
            JLog::add($message, JLog::DEBUG, 'cmsmanager');
        } else {
            JError::raiseWarning(100, $message);
        }
    }

    /**
     * Perform post install, update or discover_install actions in order to
     * generate the CMS Manager secret key and display the proper feedback
     * messages to the user.
     *
     * @param   string $type The type of action executed: install, update or discover_install.
     * @param   object $parent
     */
    public function postflight($type, $parent)
    {
        $version = $this->getVersion();

        // Loading the language to display the log
        // messages in the correct language
        $language = JFactory::getLanguage();
        $language->load('com_cmsmanager');

        $app = JFactory::getApplication();
        $hasfopen = in_array(ini_get('allow_url_fopen'), array('On', '1'));
        $key = $this->getCMSManagerSecretKey($type);

        $style = "";
        if (version_compare($version->getShortVersion(), '3.0.0', '>=')) {
            $sitename = JFactory::getConfig()->get('sitename');
        } else {
            $sitename = JFactory::getConfig()->getValue('config.sitename');
            // Bootstrap included in Joomla 2.5! We need some extra style options.
            $style = "background-color: #1D6CB0;color: white;border-radius: 4px;text-align: center;padding: 4px 12px;font-size: 13px;line-height: 18px;";
        }

        // Clearing the System Cache
        $cache = JCache::getInstance('');
        $cache->clean('_system');

        // TODO: Duplicate of view/calls/tmpl/default.php - to be removed!
        $message = "<br />"
            . JText::_('COM_CMSMANAGER_INSTALL_MESSAGE')
            . "<br />"
            . JText::_('COM_CMSMANAGER_ADDSITE_NOTE')
            . '<form action="https://www.joomlahost.it/dnshst/jm/angiereg/angie_reg.jsp" method="post" target="_blank">'
            . '<input type="hidden" name="name" value="' . $sitename . '">'
            . '<input type="hidden" name="domain" value="' . JURI::root() . '">'
            . '<input type="hidden" name="key" value="' . $key . '">'
            . '<p><input style="' . $style . '" type="submit" value="' . JText::_('COM_CMSMANAGER_ADDSITE') . '" class="btn btn-primary"></p>'
            . '</form>'
            . JText::_('COM_CMSMANAGER_INSTALL_LATER')
            . ' > <strong><a href="index.php?option=com_cmsmanager">' . JText::_('COM_CMSMANAGER') . '</a></strong>'
            . "<br />"
            . "<br />"
            ;

        // If fopen is not enabled
        if (!$hasfopen) {
            $message .= JText::_('COM_CMSMANAGER_INSTALL_NO_FOPEN');
        }

        // Showing the feedback message
        if (version_compare($version->getShortVersion(), '3.0.0', '>=')) {
            $app->enqueueMessage($message);
        } else {
            echo $message;
        }
    }

    /**
     * Return the version of Joomla of the target website.
     *
     * @return JVersion The version of Joomla installed on the target website.
     */
    protected function getVersion()
    {
        if (empty($this->version))
            $this->version = new JVersion;
        return $this->version;
    }

    /**
     * Get the CMS Manager secret key or creates a
     * new one if none.
     *
     * @param string $type "install" or "update"
     *
     * @return string the CMS Manager secret key
     */
    private function getCMSManagerSecretKey($type = 'install')
    {
        jimport('joomla.user.helper');

        $params = JComponentHelper::getParams('com_cmsmanager');
        $current_secret_key = $params->get('secret_key');
        $new_secret_key = md5(JUserHelper::genRandomPassword(32));

        // for a new install
        if ($type === 'install') {
            $this->saveSecret($new_secret_key);
            return $new_secret_key;
        }

        if (empty($current_secret_key)) {
            $this->saveSecret($new_secret_key);
            return $new_secret_key;
        }

        return $current_secret_key;
    }

    /**
     * Save the secret as component parameter.
     *
     * @param string $secret The CMS Manager secret that has to be saved.
     *
     * @return boolean True on success.
     */
    private function saveSecret($secret)
    {
        $db = JFactory::getDbo();

        try {

            // Querying the DB in order to discover if
            // a secret has already been saved into the DB
            $params = $db->setQuery($db->getQuery(true)
                ->select('params')
                ->from('#__extensions')
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_cmsmanager')))
                ->loadResult();

            // If the params JSON string is empty, that means the this is a new installation
            if (empty($params)) {
                $params = '{}';
            }

            // Decoding the json configuration
            $json = json_decode($params);

            // If the secret is already persisted in the DB
            if (isset($json->secret_key) && $secret === $json->secret_key && !empty($secret)) {
                return true;
            }

            // Otherwise we persist the new secret in the DB using the
            // proper query method according to the Joomla version
            $json->secret_key = $secret;
            $setQuery = $db->setQuery($db->getQuery(true)
                ->update('#__extensions')
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($json)))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_cmsmanager')));

            if (version_compare(JVERSION, '3.0', 'gt')) {
                $setQuery->execute();
            } else {
                $setQuery->query();
            }

        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }

        return true;
    }

}
