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
 * Event Log.
 */
class CMSManagerLog
{

    /**
     * @var string the type of logged event.
     */
    public $type;

    /**
     * @var string some additional parameters.
     */
    public $params;

    /**
     * @var string the name of the action.
     */
    public $who;

    /**
     * @var int request id (call id).
     */
    private $req_id;

    /**
     * @var bool whether the Log is an error.
     */
    private $error;

    /**
     * @var \DateTime Log creation date.
     */
    private $added_at;

    /**
     * Create a new Log instance.
     *
     * @param string $who the name of the action.
     * @param string $type the type of logged event.
     * @param string $params some additional parameters.
     */
    function __construct($who, $type, $params = "")
    {

        if (!$params) $params = array();

        if (!is_array($params))
            $params = array($params);

        $this->params = $params;
        $this->type = $type;
        $this->who = $who;
        $this->added_at = new DateTime();
    }

    /**
     * Persist the Log into the DB.
     *
     * @return bool true on success.
     */
    public function storeDb()
    {
        try {

            JFactory::getDbo()->insertObject('#__cmsmanager_logs', $this->getObject(), 'id');
            return true;

        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Convert the Log to its DB representation.
     *
     * @return stdClass the Log object ready to be persisted into the DB.
     */
    public function getObject()
    {
        $store = new stdClass();

        foreach (get_object_vars($this) as $key => $value) $store->$key = $value;
        $store->params = json_encode($store->params);

        return $store;
    }

    // #####################################################################################

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * @param mixed $who
     */
    public function setWho($who)
    {
        $this->who = $who;
    }

    /**
     * @return mixed
     */
    public function getReqId()
    {
        return $this->req_id;
    }

    /**
     * @param mixed $req_id
     */
    public function setReqId($req_id)
    {
        $this->req_id = $req_id;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error = true)
    {

        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getAddedAt()
    {
        return $this->added_at;
    }

    /**
     * @param mixed $added_at
     */
    public function setAddedAt($added_at)
    {
        $this->added_at = $added_at;
    }

}
