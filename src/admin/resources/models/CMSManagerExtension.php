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
 * Class holding all the informations regarding
 * a single extension needed by the CMS Manager
 * to operate.
 */
class CMSManagerExtension
{
    /**
     * @var string real extension name
     */
    public $realName;

    /**
     * @var string translated extension name
     */
    public $name;

    /**
     * @var string current version
     */
    public $version;

    /**
     * @var string latest version
     */
    public $lastVersion;

    /**
     * @var string typology
     */
    public $typology;

    /**
     * @var bool true if the extension is for admin
     */
    public $admin = false;

    /**
     * @var bool true if the extension is enabled
     */
    public $extEnabled = false;

    /**
     * @var bool true if the extension is protected
     */
    public $extProtected = false;

    /**
     * @var int state code
     */
    public $state = 0;

    /**
     * @var int Internal extension ID
     */
    public $joomlaId = 0;

    /**
     * @var int Internal Joomla! update ID
     */
    public $updateId = 0;

    /**
     * @var String creation date
     */
    public $date;

    /**
     * @var string the author url
     */
    public $url;

    /**
     * @var string the update url
     */
    public $updateSite;

    /**
     * Create a new CMSManagerExtension instance.
     */
    public function __construct($compName, $realName, $compVer, $compType, $compEnable, $extid, $lastVersion, $updateId, $date, $url, $updateSite = '', $protected, $state, $client)
    {
        $this->name = $compName;
        $this->realName = $realName;
        $this->version = $compVer;
        $this->typology = $compType;
        $this->extEnabled = (bool)$compEnable;
        $this->extProtected = (bool)$protected;
        $this->joomlaId = (int)$extid;
        $this->lastVersion = $lastVersion;
        $this->updateId = (int)$updateId;
        $this->date = $date;
        $this->url = $url;
        $this->updateSite = $updateSite;
        $this->state = (int)$state;
        $this->admin = (bool)$client;
    }

    // #############################################################################

    /**
     * @return mixed
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * @param mixed $realName
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getLastVersion()
    {
        return $this->lastVersion;
    }

    /**
     * @param mixed $lastVersion
     */
    public function setLastVersion($lastVersion)
    {
        $this->lastVersion = $lastVersion;
    }

    /**
     * @return mixed
     */
    public function getTypology()
    {
        return $this->typology;
    }

    /**
     * @param mixed $typology
     */
    public function setTypology($typology)
    {
        $this->typology = $typology;
    }

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->admin;
    }

    /**
     * @param boolean $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return boolean
     */
    public function isExtEnabled()
    {
        return $this->extEnabled;
    }

    /**
     * @param boolean $extEnabled
     */
    public function setExtEnabled($extEnabled)
    {
        $this->extEnabled = $extEnabled;
    }

    /**
     * @return boolean
     */
    public function isExtProtected()
    {
        return $this->extProtected;
    }

    /**
     * @param boolean $extProtected
     */
    public function setExtProtected($extProtected)
    {
        $this->extProtected = $extProtected;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getJoomlaId()
    {
        return $this->joomlaId;
    }

    /**
     * @param int $joomlaId
     */
    public function setJoomlaId($joomlaId)
    {
        $this->joomlaId = $joomlaId;
    }

    /**
     * @return int
     */
    public function getUpdateId()
    {
        return $this->updateId;
    }

    /**
     * @param int $updateId
     */
    public function setUpdateId($updateId)
    {
        $this->updateId = $updateId;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUpdateSite()
    {
        return $this->updateSite;
    }

    /**
     * @param string $updateSite
     */
    public function setUpdateSite($updateSite)
    {
        $this->updateSite = $updateSite;
    }

}
