<?php
defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * @package     CMS Manager
 * @author      COLT Engine S.R.L.
 * @authorUrl   https://www.joomlahost.it
 *
 * @copyright   Copyright (C) 2015 COLT Engine s.r.l
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Helper class providing some methods to interact with
 * the Joomla core.
 */
class JInstallerCMS extends JInstallerHelper
{
    /**
     * Download a package given its url.
     *
     * @param   string $url URL of file to download
     * @param   mixed $target Download target filename or false to get the filename from the URL
     *
     * @return  mixed  Path to downloaded package or boolean false on failure
     *
     */
    public static function downloadPackage($url, $target = false)
    {
        $config = JFactory::getConfig();
        $target = $config->get('tmp_path') . '/' . self::getFilenameFromURL($url);

        $downloaded = AdmintoolsHelperDownload::download($url, $target);

        if (!$downloaded)
            return false;

        return basename($target);
    }

}