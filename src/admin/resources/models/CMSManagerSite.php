<?php

/**
 * @package     CMS Manager
 * @author      COLT Engine S.R.L.
 * @authorUrl   https://www.joomlahost.it
 *
 * @copyright   Copyright (C) 2015 COLT Engine s.r.l
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Collection of informations about the website execution environment.
 */
class CMSManagerSite
{

    /**
     * @var string Joomla version
     */
    public $joomla;

    /**
     * @var string PHP version
     */
    public $php;

    /**
     * @var string MariaDB/MySQL Version
     */
    public $mysql;

    /**
     * @var string Webserver version
     */
    public $webserver;

    /**
     * @var string Private IP Address
     */
    public $ipAddressLocal;

    /**
     * @var string latest akeeba backup
     */
    public $latestBackup;

    /**
     * @var string memory limit
     */
    public $memoryLimit;

    /**
     * @var string OS short
     */
    public $os;

    /**
     * @var string OS long
     */
    public $osExtended;

    /**
     * @var string Akeeba Secret
     */
    public $akeebaSecret;

    /**
     * @var int Akeeba profile for CMS Manager
     */
    public $akeebaProfile = 0;

    /**
     * @var JVersion the installed version of Joomla
     */
    private $jversion;

    /**
     * @var JDatabase connection to the Joomla database
     */
    private $db;

    /**
     * Create a new CMSManagerSite instance.
     */
    function __construct()
    {
        $this->jversion = new JVersion();
        $this->db = JFactory::getDBO();

        $this->loadData();
    }

    /**
     * Load the data into this object.
     */
    private function loadData()
    {
        $this->joomla = $this->jversion->RELEASE;
        $this->ipAddressLocal = $_SERVER['SERVER_ADDR'];
        $this->mysql = $this->db->getVersion();
        $this->php = phpversion();
        $this->webserver = $_SERVER['SERVER_SOFTWARE'];
        $this->memoryLimit = $this->getMemoryLimitInBytes();
        $this->os = PHP_OS;
        $this->osExtended = php_uname();
        $this->latestBackup = $this->getLatestBackupInfo();
        $this->akeebaSecret = $this->getAkeebaSecretKey();
        $this->akeebaProfile = $this->getAkeebaCMSManagerProfile();
    }

    /**
     * Return the memory limit value in bytes.
     *
     * @return int the memory limit value in bytes.
     */
    private function getMemoryLimitInBytes()
    {
        $memory_limit = ini_get('memory_limit');
        switch (substr($memory_limit, -1)) {
        	case 'k':
            case 'K':
                $memory_limit = (int)$memory_limit * 1024;
                break;
                
            case 'm':
            case 'M':
                $memory_limit = (int)$memory_limit * 1024 * 1024;
                break;
                
            case 'g':
            case 'G':
                $memory_limit = (int)$memory_limit * 1024 * 1024 * 1024;
                break;
        }

        return $memory_limit;
    }

    /**
     * Get latest backup info from Akeeba
     *
     * @return string latest backup date
     */
    private function getLatestBackupInfo()
    {
        $app = JFactory::getApplication();
        $prefix = $app->getCfg('dbprefix');

        $tables = JFactory::getDbo()->getTableList();

        // Check if backup table exist
        if (!in_array($prefix . "ak_stats", $tables))
            return null;

        $query = "SELECT `backupend` FROM `#__ak_stats` WHERE `status` = 'complete' ORDER BY `backupend` DESC LIMIT 0,1";
        $this->db->setQuery($query);
        try {
            $result = $this->db->loadResult();
            return $result ? $result : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get first backup profile for CMS Manager from Akeeba
     *
     * @return string latest backup date
     */
    public function getAkeebaCMSManagerProfile()
    {
        $app = JFactory::getApplication();
        $prefix = $app->getCfg('dbprefix');

        $tables = JFactory::getDbo()->getTableList();

        // Check if backup table exist
        if (!in_array($prefix . "ak_profiles", $tables))
            return 0;

        $query = "SELECT `id` FROM `#__ak_profiles` WHERE `description` = 'CMS Manager Backup Profile' ORDER BY `id` ASC LIMIT 0,1";
        $this->db->setQuery($query);

        try {
            $result = $this->db->loadResult();
            return $result ? (int) $result : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get Akeeba Secret Key
     *
     * @return string akeeba key
     */
    private function getAkeebaSecretKey()
    {
        if ( ! file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba/version.php'))
            return null;

        $params = JComponentHelper::getParams('com_akeeba');
        if ( ! $params->get('frontend_enable'))
            return null;

        return $params->get('frontend_secret_word');
    }

    // #############################################################################

    /**
     * @return mixed
     */
    public function getOsExtended()
    {
        return $this->osExtended;
    }

    /**
     * @param mixed $osExtended
     */
    public function setOsExtended($osExtended)
    {
        $this->osExtended = $osExtended;
    }

    /**
     * @return mixed
     */
    public function getJoomla()
    {
        return $this->joomla;
    }

    /**
     * @param mixed $joomla
     */
    public function setJoomla($joomla)
    {
        $this->joomla = $joomla;
    }

    /**
     * @return mixed
     */
    public function getPhp()
    {
        return $this->php;
    }

    /**
     * @param mixed $php
     */
    public function setPhp($php)
    {
        $this->php = $php;
    }

    /**
     * @return mixed
     */
    public function getMysql()
    {
        return $this->mysql;
    }

    /**
     * @param mixed $mysql
     */
    public function setMysql($mysql)
    {
        $this->mysql = $mysql;
    }

    /**
     * @return mixed
     */
    public function getWebserver()
    {
        return $this->webserver;
    }

    /**
     * @param mixed $webserver
     */
    public function setWebserver($webserver)
    {
        $this->webserver = $webserver;
    }

    /**
     * @return mixed
     */
    public function getIpAddressLocal()
    {
        return $this->ipAddressLocal;
    }

    /**
     * @param mixed $ipAddressLocal
     */
    public function setIpAddressLocal($ipAddressLocal)
    {
        $this->ipAddressLocal = $ipAddressLocal;
    }

    /**
     * @return mixed
     */
    public function getLatestBackup()
    {
        return $this->latestBackup;
    }

    /**
     * @param mixed $latestBackup
     */
    public function setLatestBackup($latestBackup)
    {
        $this->latestBackup = $latestBackup;
    }

    /**
     * @return mixed
     */
    public function getMemoryLimit()
    {
        return $this->memoryLimit;
    }

    /**
     * @param mixed $memoryLimit
     */
    public function setMemoryLimit($memoryLimit)
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * @return mixed
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * @param mixed $os
     */
    public function setOs($os)
    {
        $this->os = $os;
    }

    /**
     * @return string
     */
    public function getAkeebaSecret()
    {
        return $this->akeebaSecret;
    }

    /**
     * @param string $akeebaSecret
     */
    public function setAkeebaSecret($akeebaSecret)
    {
        $this->akeebaSecret = $akeebaSecret;
    }

    /**
     * @return int
     */
    public function getAkeebaProfile()
    {
        return $this->akeebaProfile;
    }

    /**
     * @param int $akeebaProfile
     */
    public function setAkeebaProfile($akeebaProfile)
    {
        $this->akeebaProfile = $akeebaProfile;
    }

}
