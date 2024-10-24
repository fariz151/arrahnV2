<?php 
/**
 * @package Mansethaber JT2 Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_mansethaber_JT2.php  2013-07-30  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2013- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die('Restricted access');
$moduleid= $params->get( 'id' );
$module_items    = $params->get('module_items');
?>
<div id="sidepanel_jt<?php echo $module->id; ?>" style="line-height: 1; height: auto; z-index:9999;  background:<?php echo $background;?>;" class="himax">
<div class="sidepanel-styling<?php echo $module->id; ?>" style="border:<?php echo $border_width;?>px solid <?php echo $border_color;?>;">
<?php if ($params->get( 'TabStyle' )=='TextandIcon'){?>
	 <a  style="display: block;  outline: medium none; position: absolute; " class="handle<?php echo $module->id; ?>" href="javascript:void(0)"><?php echo $TabText;?><i class="<?php echo $params->get( 'TabIcon');?>" aria-hidden="true"></i>
</a>
<?php } else { ?>
<a  style="display: block;  outline: medium none; position: absolute; " class="handle<?php echo $module->id; ?> imagebg" href="javascript:void(0)"></a>
<?php } ?>
<?php if ($heading!=null):?><<?php echo $heading_element; ?> class="sidepanel-title"><?php echo $heading;?></<?php echo $heading_element; ?>><?php endif; ?>
	  <div class="content"><?php if ($module_file) { ?>
<div id="modpos" class="module-nostyle">
<div style="clear:both; margin:10px 0;" class="sidepanel_block <?php echo $extClass; ?>" >
<?php
foreach ($module_items as $idx => $item) :
    $substridx = str_replace("module_items", '', $idx);
    ?>
    <div class="modules">
    <?php 
    jimport('joomla.application.module.helper');
    if (!empty($item->id)){
        foreach ($item->id as $module){
            $modules = JModuleHelper::getModuleById($module);
			if ($params->get('ShowModuleTitles'))
			{
			 echo "<h3>".$modules->title."</h3>";
			 }
            echo JModuleHelper::renderModule($modules); 
        }
    };
    ?>
    </div>
<?php endforeach; ?>

</div>
</div>
<?php } else if ($showarticle) { ?>
   <div id="article-content"><?php
   if ($articles==!null){
$itemID = $articles['id'];
			$articleurl = JRoute::_('index.php?option=com_content&view=article&id='.$itemID, false);
			$title = $articles['title'];
			$introtext = $articles['introtext'];
			$fulltext = $articles['fulltext'];
			echo '<div>';
			//echo '<h2>'.$title.'</h2>';
			echo $introtext;
			if($fulltext != ''){
				echo '<a href="'. $articleurl .'">'. JText::_("MOD_SIDEPANEL_READ_MORE") .'</a>';
				}
		
			echo '</div>'; 
		}
		else {
				echo "Article is not published or selected"; 
				}
		?></div>
   <?php } else { ?>
   <div id="htmlcontent"><?php echo $htmlcontent; ?></div>
<?php } ?>
</div>
</div>
</div>
