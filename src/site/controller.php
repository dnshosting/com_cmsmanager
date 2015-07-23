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
 * CMS Manager frontend's main controller.
 * Make sure that the callee is a trusted
 * and performs the requested operations.
 */
class CMSManagerController extends JControllerLegacy
{
    /**
     * @var String The JWT Secret Token.
     */
    private $token;

    /**
     * @var boolean True if debug is enabled.
     */
    private $debug;

    /**
     * @var CMSManager The CMSManager operations handler.
     */
    private $cmsmanager;

    /**
     * @var JViewLegacy The response view.
     */
    private $view;

    /**
     * Map the CMS Manager commands to the corresponding operations.
     *
     * @param bool $cachable
     * @param bool $urlparams
     *
     * @return JControllerLegacy A JControllerLegacy object to support chaining.
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Get default view (folder name = response) with default format (view.json.php)
        $this->view = $this->getView("response", "json");

        $app = JFactory::getApplication();

        $this->debug = $this->view->debug = $app->input->getBool('debug', false);
        $cmd = $app->input->getCmd('cmd', 'status');
        $url = $app->input->getString('url', '');
        $store = $app->input->getBool('store', true);
        $name = $app->input->getString('name', '');
        $apc = $app->input->getBool('clearApc', false);

        // Checking credentials
        $this->checkToken();

        // Clear APC cache
        if($apc) {
            if(function_exists("apc_clear_cache")) {
                @apc_clear_cache();
                @apc_clear_cache('user');
                @apc_clear_cache('opcode');
            }
        }

        $this->cmsmanager = $cmsmanager = new CMSManager($store);

        // Operations dispatching
        if ($cmd == 'status') {
            $this->view->data = new CMSManagerSendData();
        } else if ($cmd == 'installSingleExtension') {
            $cmsmanager->installSingleExtension($url);
        } else if ($cmd == 'getInfo') {
            $this->view->data = new CMSManagerSite();
        } else if ($cmd == 'installExtensions') {
            $cmsmanager->installUpdate($url);
        } else if ($cmd == 'getUpdates') {
            $cmsmanager->discoverExtension();
            $clean = $name == "clean";

            $cmsmanager->getUpdates($clean);
            $this->view->code = 204;
        } else if ($cmd == 'installUpdate') {
            $container = new \stdClass();

            // If anything goes wrong, return an INTERNAL_SERVER_ERROR
            // plus some log messages
            if (@!$cmsmanager->installUpdate($name)) {
                $this->view->code = 500;
                $container->logs = $cmsmanager->getLog()->getLogs();
                $container->status = "KO";
            } else {
                $this->view->code = 200;
                $container->logs = $cmsmanager->getLog()->getLogs();
                $container->status = "OK";
            }

            $this->view->data = $container;
        } else if ($cmd == 'discoverAndInstallExtensions') {
            $cmsmanager->discoverExtension();
            $cmsmanager->installDiscoveredExtension();
        } else if ($cmd == 'fixDb') {
            $cmsmanager->fixDb();
        } else if ($cmd == 'removeExtension') {
            $cmsmanager->removeExtension($name) ? $this->view->code = 204 : $this->view->code = 500;
        }

        $this->view->display();
    }

    /**
     * Check the JWT authentication token.
     */
    private function checkToken()
    {
        // Clear the cache
        $cache = JCache::getInstance('');
        $cache->clean('_system');

        // Get the authentication key
        // or return and error response
        $key = JFactory::getApplication()->input->get('key');
        if (empty($key)) {
            $this->view->code = 401;
            $this->view->data = "ERR_CREDENTIALS_EMPTY";
            $this->view->display();
        }

        // Fetches the JWT secret from the Joomla configuration
        $this->token = JComponentHelper::getParams('com_cmsmanager')->get('secret_key');

        // In debug, the authentication key must be in plaintext
        if ($this->debug) {
            if ($key != $this->token) {
                $this->view->code = 401;
                $this->view->data = "ERR_CREDENTIALS_BADSIGN";
                $this->view->display();
            }
            return;
        }

        // Decoding the request payload with JWT
        try {
            JWT::decode($key, $this->token);
        } catch (Exception $e) {
            $this->view->code = 401;
            $this->view->data = $e->getMessage();

            if ($e->getMessage() == "Expired token") $this->view->data = "ERR_CREDENTIALS_EXPIRED";
            if ($e->getMessage() == "Signature verification failed") $this->view->data = "ERR_CREDENTIALS_BADSIGN";

            $this->view->display();
        }

        // If ok, set the token in view for response
        $this->view->token = $this->token;
    }

}
