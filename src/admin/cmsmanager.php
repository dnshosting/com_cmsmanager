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

// Joomla 3.x directory separator
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$lang = JFactory::getLanguage();
$lang->load('com_cmsmanager.sys', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_cmsmanager', "it-IT", false, true);
$lang->load('com_cmsmanager', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_cmsmanager', "it-IT", false, true);

$task = JFactory::getApplication()->input->get('task');

// Checking if the user is an administrator
$canAdmin = JFactory::getUser()->authorise('core.manage', 'com_cmsmanager');
if (!$canAdmin && JFactory::getApplication()->isAdmin()) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Controller instantiation
if (version_compare(JVERSION, '3.0', 'gt')) {
    jimport('legacy.controller.legacy');
    $controller = JControllerLegacy::getInstance('CMSManager');
} else {
    jimport('joomla.application.component.controller');
    $controller = JController::getInstance('CMSManager');
}

$controller->execute($task);
$controller->redirect();
