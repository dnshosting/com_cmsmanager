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
 * Payload class containing a complete overview on the website status including the list of installed extensions.
 */
class CMSManagerSendData
{
    /**
     * @var CMSManagerStatus
     */
    public $status;

    /**
     * @var CMSManagerSite
     */
    public $site;

    /**
     * @var array list of extension
     */
    public $extensions;

    /**
     * @var CMSManager
     */
    private $cmsmanager;

    function __construct()
    {
        $this->cmsmanager = new CMSManager(true);
        $this->loadData();
    }

    /**
     * Load current data
     */
    private function loadData()
    {
        $this->site = new CMSManagerSite();
        $this->status = new CMSManagerStatus();
        $this->extensions = $this->cmsmanager->listExtensions();
    }

    // #############################################################################

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param mixed $extensions
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @return CMSManager
     */
    public function getCmsmanager()
    {
        return $this->cmsmanager;
    }

    /**
     * @param CMSManager $cmsmanager
     */
    public function setCmsmanager($cmsmanager)
    {
        $this->cmsmanager = $cmsmanager;
    }

}