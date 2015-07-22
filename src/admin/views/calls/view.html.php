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
 * Show the CMS Manager secret key and the performed actions log.
 */
class CMSManagerViewCalls extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $style;
    protected $secret_key;
    protected $sitename;

    /**
     * Display the view showing the CMS Manager secret key and the performed actions log.
     *
     * @param string $tpl : The name of the template file to parse; automatically searches through the template paths.
     * @return mixed A string if successful, otherwise a JError object.
     */
    public function display($tpl = null)
    {
        $language = JFactory::getLanguage();
        $language->load('com_installer', JPATH_ADMINISTRATOR, "it-IT", true);

        $app = JFactory::getApplication();
        $document = JFactory::getDocument();

        $document->addStyleSheet(JURI::root(true) . '/media/com_cmsmanager/css/cmsmanager.css');

        // Get data from the model
        $items = $this->get('Items');
        $pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        // Assign data to the view
        $this->items = $items;
        $this->pagination = $pagination;
        $document->addStyleSheet(JURI::root(true) . '/media/com_cmsmanager/css/bootstrap.min.css');

        if (version_compare(JVERSION, '3.0', 'lt')) {

            JHTML::_('behavior.mootools');

            if (!$app->get('jquery')) {
                $app->set('jquery', 1);
                $document->addscript(JURI::root(true) . '/media/com_cmsmanager/js/jquery.min.js');
            }

            $document->addscript(JURI::root(true) . '/media/com_cmsmanager/js/bootstrap.min.js');
            $this->style = "background-color: #1D6CB0;color: white;border-radius: 4px;text-align: center;padding: 4px 12px;font-size: 13px;line-height: 18px;";

        } else {

            JHtml::_('bootstrap.framework');
            //$this->sidebar = JHtmlSidebar::render();
            $this->style = "";

        }

        $this->secret_key = JComponentHelper::getParams('com_cmsmanager')->get('secret_key');
        $this->sitename = JFactory::getConfig()->get('sitename');

        $this->setToolBar();

        return parent::display($tpl);
    }

    /**
     * Set the page title and display the toolbar.
     */
    protected function setToolBar()
    {
        $this->setTitle();

        $user = JFactory::getUser();

        if ($user->authorise('core.admin', 'com_cmsmanager'))
            JToolBarHelper::preferences('com_cmsmanager');
    }

    /**
     * Set the page title.
     */
    protected function setTitle($sub = null, $icon = 'cmsmanager')
    {
        $path = "media/com_cmsmanager/images/header/icon-48-{$icon}.png";
        $img = JHtml::_('image', $path, null, null, false, true);

        if ($img) {
            $doc = JFactory::getDocument();
            $doc->addStyleDeclaration(".icon-48-{$icon} { background-image: url({$img}); }");
        }

        $title = JText::_('COM_CMSMANAGER');
        if ($sub)
            $title .= ': ' . JText::_($sub);

        if (!version_compare(JVERSION, '3.0', 'lt')) {
            $icon = "eye-open";
        }

        JToolbarHelper::title($title, $icon);
    }

}
