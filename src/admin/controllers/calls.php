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
 * Proxy the access to the CMS Manager MVC model.
 */
class CMSManagerControllerCalls extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param   string $name The name of the model.
     * @param   string $prefix The prefix for the PHP class name.
     *
     * @return  JModel
     * @since   1.6
     */
    public function getModel($name = 'Calls', $prefix = 'CMSManagerModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

}
