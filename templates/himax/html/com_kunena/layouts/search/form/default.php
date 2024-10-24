<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Search
 *
 * @copyright       Copyright (C) 2008 - 2023 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Icons\KunenaIcons;
use Kunena\Forum\Libraries\Route\KunenaRoute;

// FIXME: change into JForm.

// TODO: Add generic form version

$this->ktemplate = KunenaFactory::getTemplate();
$bootstrap = $this->ktemplate->params->get('bootstrap');

HTMLHelper::_('behavior.multiselect');

if ($bootstrap) {
  HTMLHelper::_('dropdown.init');
}

echo $this->subLayout('Widget/Datepicker');

// Load caret.js always before atwho.js script and use it for autocomplete, emojiis...
$this->addScript('jquery.caret.js');
$this->addScript('jquery.atwho.js');
$this->addStyleSheet('jquery.atwho.css');
$this->addScript('assets/js/search.js');

?>

<form action="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=search'); ?>" method="post" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
  <input type="hidden" name="task" value="results" />
  <?php if ($this->me->exists()) : ?>
    <input type="hidden" id="kurl_users" name="kurl_users" value="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=user&layout=listmention&format=raw') ?>" />
  <?php endif; ?>
  <?php echo HTMLHelper::_('form.token'); ?>
  <div id="main-search" class="border rounded">

    <div class="bg-light">
      <h2 id="searchHeader" class="accordion-header mt-0 mb-0 border-bottom py-3 px-3">
        <?php echo Text::_('COM_KUNENA_SEARCH_ADVSEARCH'); ?>
      </h2>
    </div>

    <div class="p-3">
      <div class="row">
        <fieldset class="col-md-6 row mb-0">
          <legend><?php echo Text::_('COM_KUNENA_SEARCH_SEARCHBY_KEYWORD'); ?></legend>
          <div class="col-md-6">
            <div class="form-group">
              <input type="text" name="query" class="form-control" value="<?php echo $this->escape($this->state->get('searchwords')); ?>" placeholder="<?php echo Text::_('COM_KUNENA_SEARCH_KEYWORDS'); ?>" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <?php $this->displayModeList('mode'); ?>
            </div>
          </div>
        </fieldset>

        <?php if (!$this->config->pubProfile && !Factory::getApplication()->getIdentity()->guest || $this->config->pubProfile) : ?>
          <fieldset class="col-md-6 row mb-0">
            <legend><?php echo Text::_('COM_KUNENA_SEARCH_SEARCHBY_USER'); ?></legend>
            <div class="col-md-6">
              <div class="form-group">
                <input id="kusersearch" type="text" name="searchuser" class="form-control" value="<?php echo $this->escape($this->state->get('query.searchuser')); ?>" placeholder="<?php echo Text::_('COM_KUNENA_SEARCH_UNAME'); ?>" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="exactname" value="1" <?php if ($this->state->get('query.exactname')) {
                                                                      echo ' checked="checked" ';
                                                                    } ?> />
                  <?php echo Text::_('COM_KUNENA_SEARCH_EXACT'); ?>
                </label>
              </div>
            </div>
          </fieldset>
        <?php endif; ?>
      </div>
    </div>


    <div class="border-top border-bottom py-2 px-3">
      <h3 class="mt-0 mb-0"><?php echo Text::_('COM_KUNENA_SEARCH_OPTIONS'); ?></h3>
    </div>

    <div class="p-3 bg-light border-bottom">
      <div class="row mb-2">
        <fieldset class="col-md-4">
          <legend>
            <?php echo Text::_('COM_KUNENA_SEARCH_FIND_POSTS'); ?>
          </legend>
          <div class="form-group">
            <?php $this->displayDateList('date'); ?>
            <?php $this->displayBeforeAfterList('beforeafter'); ?>
          </div>
        </fieldset>

        <fieldset class="col-md-4">
          <legend>
            <?php echo Text::_('COM_KUNENA_SEARCH_SORTBY'); ?>
          </legend>
          <div class="form-group">
            <?php $this->displaySortByList('sort'); ?>
            <?php $this->displayOrderList('order'); ?>
          </div>
        </fieldset>

        <fieldset class="col-md-4">
          <legend>
            <?php echo Text::_('COM_KUNENA_SEARCH_AT_A_SPECIFIC_DATE'); ?>
          </legend>
          <div id="searchatdate" class="input-group input-group date" data-date-format="yyyy-dd-mm">
            <input type="text" class="form-control" name="searchatdate" data-date-format="yyyy-dd-mm">
            <button class="btn btn-secondary" type="button" id="button-searchatdate"><?php echo KunenaIcons::calendar(); ?></button>
          </div>
        </fieldset>
      </div>

      <div class="row">
        <div class="col-md-6">
          <fieldset class="form-group row">
            <legend>
              <?php echo Text::_('COM_KUNENA_SEARCH_START'); ?>
            </legend>
            <div class="col-md-6">
              <input type="text" name="limitstart" class="form-control" value="<?php echo $this->escape($this->state->get('list.start')); ?>" size="5" />
            </div>
            <div class="col-md-6">
              <?php $this->displayLimitList('limit'); ?>
            </div>
          </fieldset>

          <?php if ($this->isModerator) :
          ?>
            <fieldset>
              <legend>
                <?php echo Text::_('COM_KUNENA_SEARCH_SHOW'); ?>
              </legend>
              <div class="radio">
                <label>
                  <input class="d-inline-block" type="radio" name="show" value="0" <?php if ($this->state->get('query.show') == 0) {
                                                              echo 'checked="checked"';
                                                            } ?> />
                  <?php echo Text::_('COM_KUNENA_SEARCH_SHOW_NORMAL'); ?>
                </label>
              </div>

              <div class="radio">
                <label>
                  <input class="d-inline-block" type="radio" name="show" value="1" <?php if ($this->state->get('query.show') == 1) {
                                                              echo 'checked="checked"';
                                                            } ?> />
                  <?php echo Text::_('COM_KUNENA_SEARCH_SHOW_UNAPPROVED'); ?>
                </label>
              </div>

              <div class="radio">
                <label>
                  <input class="d-inline-block" type="radio" name="show" value="2" <?php if ($this->state->get('query.show') == 2) {
                                                              echo 'checked="checked"';
                                                            } ?> />
                  <?php echo Text::_('COM_KUNENA_SEARCH_SHOW_TRASHED'); ?>
                </label>
              </div>
            </fieldset>
          <?php endif; ?>
        </div>

        <fieldset class="col-md-6">
          <legend>
            <?php echo Text::_('COM_KUNENA_SEARCH_SEARCHIN'); ?>
          </legend>
          <?php $this->displayCategoryList('categorylist', 'class="form-select" size="10" multiple'); ?>
          <label>
            <input type="checkbox" name="childforums" value="1" <?php if ($this->state->get('query.childforums')) {
                                                                  echo 'checked="checked"';
                                                                } ?> />
            <?php echo Text::_('COM_KUNENA_SEARCH_SEARCHIN_CHILDREN'); ?>
          </label>
        </fieldset>
      </div>
    </div>

    <div class="text-center mt-3 mb-3">
      <button type="submit" class="btn btn-primary">
        <?php echo KunenaIcons::search(); ?><?php echo ' ' . Text::_('COM_KUNENA_SEARCH_SEND') . ' '; ?>
      </button>
      <button type="reset" class="btn btn-outline-secondary" onclick="window.history.back();">
        <?php echo KunenaIcons::cancel(); ?><?php echo ' ' . Text::_('COM_KUNENA_CANCEL') . ' '; ?>
      </button>
    </div>

  </div>
</form>