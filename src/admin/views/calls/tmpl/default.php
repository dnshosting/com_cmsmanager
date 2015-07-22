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

JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

?>
<h1><?php echo JText::_('COM_CMSMANAGER_AUTHENTICATION'); ?></h1>
<p><?php echo JText::_('COM_CMSMANAGER_ADDSITE_NOTE'); ?><br/>
    <small>(<?php echo JText::_('COM_CMSMANAGER_SECRET_KEY_EDIT'); ?>)</small>
</p>

<br/>

<div id="cmsmanager-bootstrap">
    <div class="cmsmanager-bootstrap">
        <form class="form-horizontal">
            <div class="form-group">
                <label for="secret"
                       class="col-sm-8 col-md-4 control-label"><?php echo JText::_('COM_CMSMANAGER_SECRET_KEY'); ?>
                    &nbsp;</label>

                <div class="col-sm-8 col-md-4">
                    <input type="text" readonly="readonly" class="form-control" name="secret" id="secret" placeholder=""
                           value="<?php echo $this->secret_key ?>">
                </div>
            </div>

            <br/>

            <div class="form-group">
                <label for="secret"
                       class="col-sm-8 col-md-4 control-label"><?php echo JText::_('COM_CMSMANAGER_SITE_NAME'); ?>
                    &nbsp;</label>

                <div class="col-sm-8 col-md-4">
                    <input type="text" readonly="readonly" class="form-control" name="secret" id="secret" placeholder=""
                           value="<?php echo $this->sitename ?>">
                </div>
            </div>

            <br/>

            <div class="form-group">
                <label for="secret"
                       class="col-sm-8 col-md-4 control-label"><?php echo JText::_('COM_CMSMANAGER_URL'); ?>
                    &nbsp;</label>

                <div class="col-sm-8 col-md-4">
                    <input type="text" readonly="readonly" class="form-control" name="secret" id="secret" placeholder=""
                           value="<?php echo JURI::root() ?>">
                </div>
            </div>
        </form>

        <!-- // URL presente anche in install.cmsmanager.php -->
        <form class="form-horizontal" action="https://www.joomlahost.it/dnshst/jm/angiereg/angie_reg.jsp" method="post"
              target="_blank">
            <input type="hidden" name="name" value="<?php echo $this->sitename ?>">
            <input type="hidden" name="domain" value="<?php echo JURI::root() ?>">
            <input type="hidden" name="key" value="<?php echo $this->secret_key ?>">

            <div class="form-group">
                <label for="secret" class="col-sm-8 col-md-4 control-label"></label>
                <input style="<?php echo $this->style; ?>" type="submit"
                       value="<?php echo JText::_('COM_CMSMANAGER_ADDSITE') ?>" class="btn btn-primary">
            </div>
        </form>

    </div>
</div>

<br/>

<h1><?php echo JText::_("COM_CMSMANAGER_ACTION_LIST"); ?></h1>
<p><?php echo JText::_("COM_CMSMANAGER_DETAILED_INFO"); ?> <a href="https://www.joomlahost.it/dnshst/pannello/#joomla"
                                                              target="_blank">CMS Manager</a></p>

<form action="<?php echo JRoute::_('index.php?option=com_cmsmanager'); ?>" method="post" name="adminForm"
      id="adminForm">

    <?php if (!empty($this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span12">
        <?php else : ?>
        <div id="j-main-container">
            <?php endif; ?>

            <?php if (count($this->items) > 0) : ?>

                <table class="adminlist sortable table table-striped">
                    <thead>
                    <tr>
                        <th class="center"
                            width="7%"><?php echo JHtml::_('grid.sort', 'COM_CMSMANAGER_ERROR_COUNT', 'id', $listDirn, $listOrder); ?></th>
                        <th class="center"
                            width="15%"><?php echo JHtml::_('grid.sort', 'COM_CMSMANAGER_DATA', 'added_at', $listDirn, $listOrder); ?></th>
                        <th class="center"><?php echo JHtml::_('grid.sort', 'COM_CMSMANAGER_ACTION', 'action', $listDirn, $listOrder); ?></th>
                    </tr>
                    </thead>

                    <tfoot>
                    <tr>
                        <td colspan="10">
                            <div class="center">
                                <?php echo $this->pagination->getListFooter(); ?>
                            </div>
                        </td>
                    </tr>
                    <?php if (!empty($this->sidebar)) : ?>
                        <tr>
                            <td colspan="10">
                                <div class="btn-group hidden-phone">
                                    <label for="limit"
                                           class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
                                    <?php echo $this->pagination->getLimitBox(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tfoot>

                    <?php foreach ($this->items as $i => $item) : ?>
                        <tr class="row<?php echo $i % 2; ?>">

                            <td class="center">
                                <?php
                                if ($item->count_err) $esito = 0;
                                else $esito = 1;
                                ?>
                                <?php echo JHtml::_('jgrid.published', $esito, $i, '', false); ?>
                            </td>

                            <td class="center">
                                <?php echo $item->added_at; ?>
                            </td>

                            <td class="center">
                                <?php echo JText::_("COM_CMSMANAGER_ACTION_" . strtoupper($item->action)); ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </table>

                <div>
                    <input type="hidden" name="task" value=""/>
                    <input type="hidden" name="boxchecked" value="0"/>
                    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
                    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
                    <?php echo JHtml::_('form.token'); ?>
                </div>
            <?php else : ?>
                <p><strong><?php echo JText::_('COM_CMSMANAGER_ACTION_NOLOGS') ?></strong></p>
            <?php endif; ?>

        </div>
</form>

<div class="jh-footer">
    <p><a href="http://www.joomlahost.it/dnshst/jm/cms-manager.jsp" title="JoomlaHost.it - Il tuo hosting Joomla" target="_blank"><img
                src="../media/com_cmsmanager/images/joomlahost.png" alt="JoomlaHost.it"/></a></p>
</div>
