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
 * Display the CMS Manager operations responses in JSON.
 */
class CMSManagerViewResponse extends JViewLegacy
{
    public $token;
    public $debug;
    public $code = 200;
    public $data;
    private $app;

    /**
     * Display the CMSManager data in JSON.
     *
     * @param string $tpl the name of the template file to parse; automatically searches through the template paths.
     * @return mixed a string if successful, otherwise a JError object.
     */
    public function display($tpl = null)
    {
        $this->app = JFactory::getApplication();

        if (defined('CMSMANAGER_DEBUG')) {
            print_r($this->data);
            $this->app->close();
        }

        return $this->response();
    }

    /**
     * Handle the response
     */
    private function response()
    {
        // Set http_response_code for old PHP Version
        if (!function_exists('http_response_code')) {
            function http_response_code($newcode = NULL)
            {
                static $code = 200;
                if ($newcode !== NULL) {
                    header('X-PHP-Response-Code: ' . $newcode, true, $newcode);
                    if (!headers_sent())
                        $code = $newcode;
                }
                return $code;
            }
        }

        // Set HTTP response code
        http_response_code($this->code);

        // Authentication error
        if ($this->code == 401) {
            header('Content-type: application/json');
            $this->app->close(json_encode($this->data));
        }

        // Debug without JWT
        if ($this->debug) {
            header('Content-type: application/json');
            $this->app->close(json_encode($this->data));
        }

        // Sign with JWT
        header('Content-type: application/json');

        $jwt = JWT::encode($this->data, $this->token);
        $encrypted = ApiCrypter::encrypt($jwt, substr($this->token, 0, 16));
        $this->app->close(json_encode($encrypted));
    }

}
