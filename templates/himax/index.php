<?php
/**
 * @package   T4_BLANK
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (class_exists('\T4\T4')) {
	\T4\T4::render($this);
} else {
	include 'error-t4.php';
}
?>
<link rel="stylesheet" id="jssDefault" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/themes/default.css" />
<?php  if($this->direction == 'rtl') {
$css_path='rtl/themes';
}
else if($this->direction == 'ltr')  {
$css_path='themes';
}
 ?>
<script type="text/javascript">
	jQuery(document).ready(function() {
	jQuery('#styleOptions').styleSwitcher({
  	hasPreview: false,
  	defaultThemeId: 'jssDefault',
  	fullPath:'<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/<?php echo $css_path; ?>/',
  	cookie: {
  		expires:60,
  		isManagingLoad: true
  	}
});
});
</script>