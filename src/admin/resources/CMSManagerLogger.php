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
 * Logger class.
 * Every CMSManagerLogger instance is bound to
 * a specific API action and tracks every
 * event in the action scope.
 */
class CMSManagerLogger
{
    /**
     * @var int Log identifier.
     */
    private $id;

    /**
     * @var string API action.
     */
    private $action;

    /**
     * @var string $params API parameters.
     */
    private $params;

    /**
     * @var array $logs list of Log entries.
     */
    private $logs;

    /**
     * @var int the number of errors.
     */
    private $errorCount = 0;

    /**
     * @var bool whether the log has to be persisted into the DB.
     */
    private $store;

    /**
     * Create a new Logger.
     *
     * @param string $action the performed API action.
     * @param string $params the action paramethers.
     * @param bool $store true if the Logger must be persisted into the DB.
     */
    function __construct($action, $params = "", $store = true)
    {
        $this->action = $action;

        if (!$params)
            $params = array();

        if (!is_array($params))
            $params = array($params);

        $this->params = $params;
        $this->logs = array();
        $this->store = $store;

        if ($store)
            $this->storeDb();
    }

    /**
     * Persist the Logger into the DB.
     *
     * @return bool true on success
     */
    public function storeDb()
    {
        // Create and populate the object that must be persisted
        // into the DB.
        $call = $this->buildObjct();

        try {

            JFactory::getDbo()->insertObject('#__cmsmanager_calls', $call, 'id');
            $this->id = $call->id;
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Convert the Logger to its DB representation.
     *
     * @return stdClass the Logger object ready to be persisted into the DB.
     */
    private function buildObjct()
    {
        $call = new stdClass();
        $call->id = $this->id;
        $call->action = $this->action;
        $call->params = json_encode($this->params);
        $call->count_err = $this->errorCount;

        return $call;
    }

    /**
     * Add log entry to the logger.
     *
     * @param CMSManagerLog $log the log that has to be added to the logger.
     */
    public function addLog(CMSManagerLog $log)
    {
        if ($this->store) {
            $log->setReqId($this->id);
            $log->storeDb();
        }

        $date = $log->getAddedAt();
        $log->setAddedAt($date->format("Y-m-d H:i:s T"));


        if ($log->getError())
            $this->errorCount++;

        array_push($this->logs, $log);

        @JFactory::getDbo()->updateObject('#__cmsmanager_calls', $this->buildObjct(), 'id', false);
    }

    /**
     * Return the list of log entries.
     *
     * @return array the list of log entries.
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Return the number of error logs.
     *
     * @return int the number of error logs.
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * Return the string representation of this logger.
     *
     * @return string the string representation of this logger.
     */
    public function __toString()
    {
        $a = array();

        foreach ($this->logs as $log) {
            array_push($a, get_object_vars($log));
        }

        $mio = get_object_vars($this);
        $mio['logs'] = $a;

        return json_encode($mio);
    }

    // #####################################################################################

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

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

}
