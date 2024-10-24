<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Plg_System_YendifVideoShare
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use PluginsWare\Component\YendifVideoShare\Administrator\Helper\YouTubeImport;

/**
 * Adds cron functionality to the component
 *
 * @since  2.0.0
 */
class plgSystemYendifVideoShare extends CMSPlugin {

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  2.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe -- event dispatcher
	 * @param   object  $config    An optional associative array of configuration settings
	 *
	 * @since   2.0.0
	 */
	public function __construct( &$subject, $config ) {
		parent::__construct( $subject, $config );

		// If we are on admin don't process
		if ( ! $this->app->isClient( 'site' ) ) {
			return;
		}

		$this->cron();
	}

	private function cron() {
		$db = Factory::getDbo();
		$date = Factory::getDate();

		$query  = 'SELECT id FROM #__yendifvideoshare_imports';

		$where = array();
		$where[] = 'state = 1';
		$where[] = 'next_import_date IS NOT NULL';
		$where[] = 'next_import_date != ' . $db->quote( $db->getNullDate() );
		$where[] = 'next_import_date <= ' . $db->quote( $date->toSql() );

		$query .= ' WHERE ' . implode( ' AND ', $where );
		$query .= ' LIMIT 1';

    	$db->setQuery( $query );
		$id = $db->loadResult();

		if ( $id > 0 ) {
			$query = 'UPDATE #__yendifvideoshare_imports SET next_import_date=NULL WHERE id=' . (int) $id;
			$db->setQuery( $query );
			$db->execute();

			// Import
			$import = YouTubeImport::getInstance();
			$import->process( $id );
		}
	}

}
