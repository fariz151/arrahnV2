<?php
/**
T4 Overide
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div class="com-users-logout logout <?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>
	
	<?php
		$logoutShow = $this->params->get('logoutdescription_show') !== null
			? $this->params->get('logoutdescription_show') : '';
		$logoutDesc = $this->params->get('logout_description') !== null
			? $this->params->get('logout_description') : '';
	?>
	<?php if (($logoutShow == 1 && str_replace(' ', '', $logoutDesc) != '')
	|| $this->params->get('logout_image') != '') : ?>
		<div class="com-users-logout__description logout-description">
	<?php endif; ?>
	
	<?php if ($logoutShow == 1) : ?>
		<?php echo $logoutDesc; ?>
	<?php endif; ?>

	<?php if ($this->params->get('logout_image') != '') : ?>
		<?php $alt = empty($this->params->get('logout_image_alt')) && empty($this->params->get('logout_image_alt_empty'))
			? ''
			: 'alt="' . htmlspecialchars($this->params->get('logout_image_alt'), ENT_COMPAT, 'UTF-8') . '"'; ?>
		<img src="<?php echo $this->escape($this->params->get('logout_image')); ?>" class="com-users-logout__image thumbnail float-right logout-image" <?php echo $alt; ?>>
	<?php endif; ?>

	<?php if (($logoutShow == 1 && str_replace(' ', '', $logoutDesc) != '')
	|| $this->params->get('logout_image') != '') : ?>
		</div>
	<?php endif; ?>

	<form action="<?php echo Route::_('index.php?option=com_users&task=user.logout'); ?>" method="post" class="com-users-logout__form form-horizontal">
		<div class="com-users-logout__submit control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary">
					<span class="fa fa-arrow-left"></span>
					<?php echo Text::_('JLOGOUT'); ?>
				</button>
			</div>
		</div>
		<?php if ($this->params->get('logout_redirect_url')) : ?>
			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('logout_redirect_url', $this->form->getValue('return', null, ''))); ?>">
		<?php else : ?>
			<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('logout_redirect_menuitem', $this->form->getValue('return', null, ''))); ?>">
		<?php endif; ?>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
