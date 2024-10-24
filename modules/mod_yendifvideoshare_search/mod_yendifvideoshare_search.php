<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Search
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$app = Factory::getApplication();

$config = $app->getParams( 'com_yendifvideoshare' );

$params->def( 'css', $config->get( 'custom_css' ) );

require ModuleHelper::getLayoutPath( 'mod_yendifvideoshare_search' );
