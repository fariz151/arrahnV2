<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Statistics
 *
 * @copyright       Copyright (C) 2008 - 2023 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Icons\KunenaIcons;
use Kunena\Forum\Libraries\Template\KunenaTemplate;

?>

<div class="kfrontend section border">
  <div class="section-header py-2 px-3 border-bottom bg-light">
    <h2 class="my-0">
      <?php if ($this->usersUrl) : ?>
        <a href="<?php echo $this->usersUrl; ?>">
          <?php echo Text::_('COM_KUNENA_MEMBERS'); ?>
        </a>
      <?php else : ?>
        <?php echo Text::_('COM_KUNENA_MEMBERS'); ?>
      <?php endif; ?>
    </h2>
  </div>

  <div id="kwho" class="">
    <div class="container">
      <div class="row">

        <div class="d-none d-lg-flex col-lg-1 p-3 d-flex align-items-center justify-content-center border-end">
          <?php echo KunenaIcons::members(); ?>
        </div>

        <div class="col-md-11 p-3">
          <ul class="list-unstyled mb-0">
            <li>
              <?php echo Text::sprintf('COM_KUNENA_VIEW_COMMON_WHO_TOTAL', $this->membersOnline); ?>
            </li>
            <?php
            $template  = KunenaTemplate::getInstance();
            $direction = $template->params->get('whoisonlineName');

            if ($direction == 'both') :
            ?>
              <li><?php echo $this->setLayout('both'); ?></li>
            <?php
            elseif ($direction == 'avatar') :
            ?>
              <li><?php echo $this->setLayout('avatar'); ?></li>
            <?php else :
            ?>
              <li><?php echo $this->setLayout('name'); ?></li>
            <?php
            endif;
            ?>

            <?php if (!empty($this->onlineList)) :
            ?>
              <li class="d-flex flex-wrap align-items-center gap-2">
                <span><?php echo Text::_('COM_KUNENA_LEGEND'); ?>:</span>
                <span class="kwho-admin d-flex align-items-center gap-1">
                  <?php echo KunenaIcons::user(); ?><?php echo Text::_('COM_KUNENA_COLOR_ADMINISTRATOR'); ?>
                </span>
                <span class="kwho-globalmoderator d-flex align-items-center gap-1">
                  <?php echo KunenaIcons::user(); ?><?php echo Text::_('COM_KUNENA_COLOR_GLOBAL_MODERATOR'); ?>
                </span>
                <span class="kwho-moderator d-flex align-items-center gap-1">
                  <?php echo KunenaIcons::user(); ?><?php echo Text::_('COM_KUNENA_COLOR_MODERATOR'); ?>
                </span>
                <span class="kwho-banned d-flex align-items-center gap-1">
                  <?php echo KunenaIcons::user(); ?><?php echo Text::_('COM_KUNENA_COLOR_BANNED'); ?>
                </span>
                <span class="kwho-user d-flex align-items-center gap-1">
                  <?php echo KunenaIcons::user(); ?><?php echo Text::_('COM_KUNENA_COLOR_USER'); ?>
                </span>
                <span class="kwho-guest d-flex align-items-center gap-1">
                  <?php echo KunenaIcons::user(); ?><?php echo Text::_('COM_KUNENA_COLOR_GUEST'); ?>
                </span>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>