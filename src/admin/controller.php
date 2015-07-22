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
 * CMS Manager administration controller.
 */
class CMSManagerController extends JControllerLegacy
{

    /**
     * Display the admin view.
     *
     * @param bool|false $cachable If true, the view output will be cached.
     * @param array $urlparams An array of safe url parameters and their variable types.
     *
     * @return JControllerLegacy A JControllerLegacy object to support chaining.
     */
    public function display($cachable = false, $urlparams = array())
    {
        $app = JFactory::getApplication();

        $view = $app->input->get('view', 'calls');
        $app->input->set('view', $view);
        parent::display($cachable);

        return $this;
    }

}
