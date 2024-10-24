<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Widget
 *
 * @copyright       Copyright (C) 2008 - 2023 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Icons\KunenaIcons;

?>

<div class="kfrontend section border">
  <div class="section-header bg-light border-bottom py-2 px-3">
    <h2 class="m-0 py-1">
      <?php if ($this->statisticsUrl) : ?>
        <a href="<?php echo $this->statisticsUrl; ?>">
          <?php echo Text::_('COM_KUNENA_STATISTICS'); ?>
        </a>
      <?php else : ?>
        <?php echo Text::_('COM_KUNENA_STATISTICS'); ?>
      <?php endif; ?>
    </h2>
  </div>

  <div id="kstats">
    <div class="container">
      <div class="row">
        <div class="d-none d-lg-flex col-lg-1 border-end d-flex align-items-center justify-content-center">
          <?php echo KunenaIcons::stats(); ?>
        </div>

        <div class="col-md-11 p-3">
          <div class="row">
            <div class="col-md-4">
              <ul class="list-unstyled mb-0">
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_TOTAL_MESSAGES'); ?>:
                  <strong><?php echo (int) $this->messageCount; ?></strong>
                </li>
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_TOTAL_SECTIONS'); ?>:
                  <strong><?php echo (int) $this->sectionCount; ?></strong>
                </li>
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_TODAY_OPEN_THREAD'); ?>:
                  <strong><?php echo (int) $this->todayTopicCount; ?></strong>
                </li>
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_TODAY_TOTAL_ANSWER'); ?>:
                  <strong><?php echo (int) $this->todayReplyCount; ?></strong>
                </li>
              </ul>
            </div>

            <div class="col-md-4">
              <ul class="list-unstyled mb-0">
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_TOTAL_SUBJECTS'); ?>:
                  <strong><?php echo (int) $this->topicCount; ?></strong>
                </li>
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_TOTAL_CATEGORIES'); ?>:
                  <strong><?php echo (int) $this->categoryCount; ?></strong>
                </li>
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_YESTERDAY_OPEN_THREAD'); ?>:
                  <strong><?php echo (int) $this->yesterdayTopicCount; ?></strong>
                </li>
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_YESTERDAY_TOTAL_ANSWER'); ?>:
                  <strong><?php echo (int) $this->yesterdayReplyCount; ?></strong>
                </li>
              </ul>
            </div>

            <div class="col-md-4">
              <ul class="list-unstyled mb-0">
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_TOTAL_USERS'); ?>:
                  <strong><?php echo $this->memberCount; ?></strong>
                </li>
                <li>
                  <?php echo Text::_('COM_KUNENA_STAT_LATEST_MEMBERS'); ?>:
                  <strong><?php echo $this->latestMemberLink; ?></strong>
                </li>
              </ul>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>