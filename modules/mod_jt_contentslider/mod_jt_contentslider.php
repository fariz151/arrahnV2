<?php
/***
 * @package     mod_jt_contentslider
 * @copyright   Copyright (C) 2007 - 2021 http://www.joomlatema.net, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author     	JoomlaTema.Net
 * @link 		http://www.joomlatema.net
 ***/

defined('_JEXEC') or die;
$modulebase = "mod_jt_contentslider";
		$thumb_folder_0 ="/cache/".$modulebase."/";
		$thumb_folder ="/cache/".$modulebase."/";

		// Create thumbnail folder if not exist
		if (!JFolder::exists(JPATH_BASE.$thumb_folder)) {
			JFolder::create(JPATH_BASE.$thumb_folder);
			JFile::write(JPATH_BASE.$thumb_folder_0.'/index.html', "");
			JFile::write(JPATH_BASE.$thumb_folder.'/index.html', "");
		}
use Joomla\CMS\helper\Modulehelper;
use Joomla\Module\JTContentSlider\Site\helper\jtcontentsliderhelper;
use Joomla\Utilities\Arrayhelper;
Use Joomla\String\Stringhelper;
use Joomla\CMS\HTML\HTMLhelper;
HTMLhelper::_('jquery.framework');	
JHtmlBootstrap::renderModal();


$model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Site', ['ignore_request' => true]);
$list = jtcontentsliderhelper::getList($params, $model);

$show_introtext = $params->get('show_introtext', 1);
$thumb_width = $params->get('thumb_width', 56);

$thumb_loadorder = $params->get('loadorder', 0);
$introtext_truncate = $params->get('limit_intro', 200);
$limit_title=  $params->get('limit_title', 25);
$show_morecat_links = $params->get('show_more_in', 1);
$show_date = $params->get('show_date', 1);
$show_date_type = $params->get('show_date_type', 1);
$custom_date_format = $params->get('custom_date_format', "");
$show_default_thumb = $params->get('show_default_thumb', 0);
$use_caption = $params->get('use_caption',0);
$limit_intro_by=$params->get('limit_intro_by','char');
$limit_title_by=$params->get('limit_title_by','char');
$replacer_text=$params->get('replacer_text','');
$strip_tags=$params->get('strip_tags');
$allowed_tags=$params->get('allowed_tags','');
$replacertitle=$params->get('replacer','');
//$keep_aspect_ratio = $params->get('keep_aspect_ratio',true);


$tmp = $params->get('keep_aspect_ratio','true');
$tmp2 = $params->get('thumb_height',200);

$thumb_height =  ( $tmp=='true' ) ? '' :(int)$tmp2;

//Get Open target
$openTarget 	= $params->get( 'open_target', '_parent' );
$modal = $params->get('modalbox');
// Get timezone 
//$config = JFactory::getConfig();
//$offset = $config->get('offset');

$thumbPath = JPATH_BASE . '/cache/' .$module->module.'/';


$modulebase = "mod_jt_contentslider";
$thumb_folder ="/cache/".$modulebase."/";

$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::base(true).'/modules/'.$modulebase.'/tmpl/assets/css/style.css');
$doc->addStyleSheet(JURI::base(true).'/modules/'.$modulebase.'/tmpl/assets/css/lightbox.css');
$doc->addScript(JURI::base(true).'/modules/'.$modulebase.'/tmpl/assets/js/lightbox-plus-jquery.js');
$doc->addScript(JURI::base(true).'/modules/'.$modulebase.'/tmpl/assets/js/owl.carousel.js');
require Modulehelper::getLayoutPath($modulebase, $params->get('layout', 'default'));
