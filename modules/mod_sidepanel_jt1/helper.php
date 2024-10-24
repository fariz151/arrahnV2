<?php
 /**
 * @package SidePanel Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die;
error_reporting(E_ALL & ~E_STRICT &~E_WARNING &~E_DEPRECATED &~E_NOTICE);
if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}
use Joomla\Utilities\ArrayHelper;
Use Joomla\String\StringHelper;
require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'src'.DS.'Helper'.DS.'RouteHelper.php');

class mod_SidePanelHelper{
public function get_articles($params){
				
		$insertarticle = $params->get('ArticleId');

		$db = JFactory::getDBO();

		$sql = "SELECT * FROM #__content WHERE id = '".intval($insertarticle)."' AND state = 1";
		$db->setQuery($sql);
		$article = $db->loadAssoc();
		
		if ($params->def('prepare_content', 1))
		{
			JPluginHelper::importPlugin('content');
			$article['introtext'] = JHtml::_('content.prepare', $article['introtext']);
			$article['fulltext'] = JHtml::_('content.prepare', $article['fulltext']);

		}
		
		return $article;

	}
	
}
?>