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

// Needed classes
JLoader::discover('', JPATH_COMPONENT_ADMINISTRATOR . DS . 'resources');
JLoader::discover('', JPATH_COMPONENT_ADMINISTRATOR . DS . 'resources' . DS . 'exceptions');
JLoader::discover('', JPATH_COMPONENT_ADMINISTRATOR . DS . 'resources' . DS . 'models');
JLoader::register('CMSManagerController', JPATH_COMPONENT . DS . 'controller.php');
JLoader::register('AdmintoolsHelperDownload', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_joomlaupdate' . DS . 'helpers' . DS . 'download.php');

// Enabling the debug mode
if (isset($_GET['debugphp'])) {
    define('CMSMANAGER_DEBUG', 1);
}

// Set error reporting to false
@ini_set('error_reporting', 0);
@error_reporting(0);

// Handling the request
$controller = JControllerLegacy::getInstance('CMSManager');
$controller->execute(JFactory::getApplication()->input->get('task', 'status'));
$controller->redirect();