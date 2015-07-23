<?php

/**
 * @package     CMS Manager
 * @author      COLT Engine S.R.L.
 * @authorUrl   https://www.joomlahost.it
 *
 * @copyright   Copyright (C) 2015 COLT Engine s.r.l
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.application.component.modellist');

/**
 * Perform the calls to interact with the CMS Manager MVC model.
 */
class CMSManagerModelCalls extends JModelList
{
    /**
     * Constructor.
     *
     * @param    array $config An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'action',
                'added_at',
                'id',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string $ordering An optional ordering field.
     * @param   string $direction An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState('added_at', 'DESC');
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('*');
        $query->from($db->quoteName('#__cmsmanager_calls', 'c'));

        $query->order($db->escape($this->getState('list.ordering', 'default_sort_column')) . ' ' .
            $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

}
