<?php

/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage vendor
 * @author Kohl Patrick, Eugen Stranz
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default.php 2701 2011-02-11 15:16:49Z impleri $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>

<div class="vendor-details-view">
  <h1><?php echo $this->vendor->vendor_store_name;
      if (!empty($this->vendor->images[0])) { ?>
      <div class="vendor-image">
        <?php echo $this->vendor->images[0]->displayMediaThumb('', false); ?>
      </div>
    <?php } ?>
  </h1>
  <div class="vendor-description">
    <?php echo $this->vendor->vendor_store_desc . '<br>'; ?>
  </div>

  <div class="vendor-infomation">
    <h5 class="vdr-title"><?php echo vmText::_('COM_VM_VENDOR_INFORMATION') ?></h5>
    <?php echo shopFunctionsF::renderVendorAddress($this->vendor->virtuemart_vendor_id) . '<br>'; ?>
  </div>

  <?php if (!empty($this->vendor->vendor_legal_info)) { ?>
    <div class="legal-infor mt-3">
      <h5 class="vdr-title"><?php echo vmText::_('COM_VM_LEGAL_INFOR') ?></h5>
      <?php echo $this->vendor->vendor_legal_info; ?>
    </div>
  <?php } ?>

  <div class="vendor-detail-view-btn">
    <?php echo $this->linktos ?>
    <?php echo $this->linkcontact ?>
  </div>
</div>