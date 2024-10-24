<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Table;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Table\Table;
use \Joomla\Database\DatabaseDriver;

/**
 * Class ImportTable.
 *
 * @since  2.0.0
 */
class ImportTable extends Table {
	
	/**
	 * Constructor
	 *
	 * @param  JDatabase  &$db  A database connector object
	 * 
	 * @since  2.0.0
	 */
	public function __construct( DatabaseDriver $db ) {
		$this->typeAlias = 'com_yendifvideoshare.import';
		parent::__construct( '#__yendifvideoshare_imports', 'id', $db );
		$this->setColumnAlias( 'published', 'state' );
	}

	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   2.0.0
	 */
	public function getTypeAlias() {
		return $this->typeAlias;
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     Table:bind
	 * @since   2.0.0
	 * @throws  \InvalidArgumentException
	 */
	public function bind( $array, $ignore = '' ) {
		$date = Factory::getDate();
		$task = Factory::getApplication()->input->get( 'task' );		

		$array['service'] = 'youtube';
		
		if ( $array['id'] == 0 && empty( $array['video_userid'] ) ) {
			$array['video_userid'] = Factory::getUser()->id;
		}

		if ( $array['id'] == 0 && empty( $array['created_by'] ) ) {
			$array['created_by'] = Factory::getUser()->id;
		}

		if ( $array['id'] == 0 && empty( $array['modified_by'] ) ) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		if ( $task == 'apply' || $task == 'save' ) {
			$array['modified_by'] = Factory::getUser()->id;
		}	

		if ( isset( $array['params'] ) && is_array( $array['params'] ) ) {
			$registry = new Registry;
			$registry->loadArray( $array['params'] );
			$array['params'] = (string) $registry;
		}

		if ( isset( $array['metadata'] ) && is_array( $array['metadata'] ) ) {
			$registry = new Registry;
			$registry->loadArray( $array['metadata'] );
			$array['metadata'] = (string) $registry;
		}

		if ( ! Factory::getUser()->authorise( 'core.admin', 'com_yendifvideoshare.import.' . $array['id'] ) ) {
			$actions = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_yendifvideoshare/access.xml',
				"/access/section[@name='import']/"
			);
			$default_actions = Access::getAssetRules( 'com_yendifvideoshare.import.' . $array['id'] )->getData();
			$array_jaccess   = array();

			foreach ( $actions as $action )	{
				if ( key_exists( $action->name, $default_actions ) ) {
					$array_jaccess[ $action->name ] = $default_actions[ $action->name ];
				}
			}

			$array['rules'] = $this->JAccessRulestoArray( $array_jaccess );
		}

		// Bind the rules for ACL where supported.
		if ( isset( $array['rules'] ) && is_array( $array['rules'] ) ) {
			$this->setRules( $array['rules'] );
		}

		return parent::bind( $array, $ignore );
	}

	/**
	 * This function convert an array of Access objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of Access objects.
	 *
	 * @return  array
	 * 
	 * @since   2.0.0
	 */
	private function JAccessRulestoArray( $jaccessrules ) {
		$rules = array();

		foreach ( $jaccessrules as $action => $jaccess ) {
			$actions = array();

			if ( $jaccess )	{
				foreach ( $jaccess->getData() as $group => $allow )	{
					$actions[ $group ] = (bool) $allow;
				}
			}

			$rules[ $action ] = $actions;
		}

		return $rules;
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return  string  The asset name
	 *
	 * @see     Table::_getAssetName
	 * @since   2.0.0
	 */
	protected function _getAssetName() {
		$k = $this->_tbl_key;
		return $this->typeAlias . '.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   Table    $table  Table name
	 * @param   integer  $id     Id
	 *
	 * @return  mixed  The id on success, false on failure.
	 * 
	 * @see     Table::_getAssetParentId
	 * @since   2.0.0
	 */
	protected function _getAssetParentId( $table = null, $id = null ) {
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = Table::getInstance( 'Asset' );

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName( 'com_yendifvideoshare' );

		// Return the found asset-parent-id
		if ( $assetParent->id )	{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}
	
}
