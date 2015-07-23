<?php

/**
 * @package     CMS Manager
 * @author      COLT Engine S.R.L.
 * @authorUrl   https://www.joomlahost.it
 *
 * @copyright   Copyright (C) 2015 COLT Engine s.r.l
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

/**
 * Collection of status informations about the
 * installed CMS Manager component.
 */
class CMSManagerStatus
{

    /**
     * @var bool true if the website is in maintenance mode.
     */
    public $maintenance;

    /**
     * @var bool true if the website support updates (require allow_url_fopen to 1)
     */
    public $updatable;

    /**
     * @var string API Version
     */
    public $apiVersion = "1.0";

    /**
     * @var string Parameters
     */
    private $params;

    /**
     * @var string Current configuration
     */
    private $config;

    /**
     * @var JVersion the installed version of Joomla
     */
    private $jversion;

    /**
     * @var JDatabase connection to the Joomla database
     */
    private $db;

    /**
     * Create a new CMSManagerStatus instance.
     */
    function __construct()
    {
        $this->config = JFactory::getConfig();
        $this->params = JComponentHelper::getParams('com_cmsmanager');
        $this->jversion = new JVersion();
        $this->db = $this->db = JFactory::getDBO();

        $this->loadData();
    }

    /**
     * Load the data into this object.
     */
    private function loadData()
    {
        $this->updatable = $this->isUpdatable();
        $this->maintenance = $this->isMaintenance();
    }

    /**
     * Return true if the website support web updates.
     *
     * @return boolean true if the website support updates.
     */
    private function isUpdatable()
    {
        if (version_compare($this->jversion->getShortVersion(), '3', '<')) {
            return in_array(ini_get('allow_url_fopen'), array('On', 'on', '1', 1));
        }

        return true;
    }

    /**
     * Return true if the website is in maintenance mode.
     *
     * @return boolean true if the website is in maintenance mode.
     */
    private function isMaintenance()
    {
        return
            $this->params->get('maintenance', 0) == 1 ||
            $this->config->get('offline', 0) == 1;
    }

    // #############################################################################

    /**
     * @return mixed
     */
    public function getMaintenance()
    {
        return $this->maintenance;
    }

    /**
     * @param mixed $maintenance
     */
    public function setMaintenance($maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * @return mixed
     */
    public function getUpdatable()
    {
        return $this->updatable;
    }

    /**
     * @param mixed $updatable
     */
    public function setUpdatable($updatable)
    {
        $this->updatable = $updatable;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * @return JRegistry
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param JRegistry $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return JRegistry
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param JRegistry $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return JVersion
     */
    public function getJversion()
    {
        return $this->jversion;
    }

    /**
     * @param JVersion $jversion
     */
    public function setJversion($jversion)
    {
        $this->jversion = $jversion;
    }

    /**
     * @return JDatabase
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param JDatabase $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

}