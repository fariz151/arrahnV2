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
?>
<div class="">
  <h1 class="mt-0 mb-4"><?php echo $this->header; ?></h1>
  <?php echo $this->subLayout('Widget/Module')->set('position', 'kunena_custom_top'); ?>
  <div class="border p-3 rounded">
    <?php echo $this->body; ?>
  </div>
  <?php echo $this->subLayout('Widget/Module')->set('position', 'kunena_custom_bottom'); ?>
</div>