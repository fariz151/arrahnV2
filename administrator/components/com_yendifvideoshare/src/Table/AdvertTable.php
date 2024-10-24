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

use \Joomla\CMS\Access\Access;
use \Joomla\Database\DatabaseDriver;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Filesystem\File;
use \Joomla\CMS\Filesystem\Folder;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Registry\Registry;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class AdvertTable.
 *
 * @since  2.0.0
 */
class AdvertTable extends Table {
	
	protected $upload_dir = 'media/com_yendifvideoshare/uploads/adverts';

	/**
	 * Constructor
	 *
	 * @param  JDatabase  &$db  A database connector object
	 * 
	 * @since  2.0.0
	 */
	public function __construct( DatabaseDriver $db ) {
		$this->typeAlias = 'com_yendifvideoshare.advert';
		parent::__construct( '#__yendifvideoshare_adverts', 'id', $db );
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

		if ( $array['id'] == 0 && empty( $array['created_by'] ) ) {
			$array['created_by'] = Factory::getUser()->id;
		}

		if ( $array['id'] == 0 && empty( $array['modified_by'] ) ) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		if ( $task == 'apply' || $task == 'save' ) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		// Support for type field
		if ( empty( $array['type'] ) ) {
			$array['type'] = 'both';
		}

		// Support for impressions field
		if ( isset( $array['impressions'] ) ) {
			$array['impressions'] = (int) $array['impressions'];
		} else {
			$array['impressions'] = 0;
		}

		// Support for clicks field
		if ( isset( $array['clicks'] ) ) {
			$array['clicks'] = (int) $array['clicks'];
		} else {
			$array['clicks'] = 0;
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

		if ( ! Factory::getUser()->authorise( 'core.admin', 'com_yendifvideoshare.advert.' . $array['id'] ) ) {
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_yendifvideoshare/access.xml',
				"/access/section[@name='advert']/"
			);
			$default_actions = Access::getAssetRules( 'com_yendifvideoshare.advert.' . $array['id'] )->getData();
			$array_jaccess   = array();

			foreach ( $actions as $action )	{
				if ( key_exists( $action->name, $default_actions ) ) {
					$array_jaccess[ $action->name ] = $default_actions[ $action->name ];
				}
			}

			$array['rules'] = $this->JAccessRulestoArray( $array_jaccess );
		}

		// Bind the rules for ACL where supported
		if ( isset( $array['rules'] ) && is_array( $array['rules'] ) ) {
			$this->setRules( $array['rules'] );
		}				

		// Fallback to the OLD versions		
		$array['cat_ids'] = '';

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
	 * Overloaded check function
	 *
	 * @return  bool
	 * 
	 * @since   2.0.0
	 */
	public function check()	{
		// Support video file field
		$app = Factory::getApplication();
		$files = $app->input->files->get( 'jform', array(), 'raw' );
		$array = $app->input->get( 'jform', array(), 'ARRAY' );

		if ( $files['mp4']['size'] > 0 ) {
			// Deleting existing file
			$oldFile = YendifVideoShareHelper::getFile( $this->id, $this->_tbl, 'mp4' );
			YendifVideoShareHelper::deleteFile( $oldFile );

			$this->mp4 = '';
			$singleFile = $files['mp4'];

			// Check if the server found any error
			$fileError = $singleFile['error'];
			$message = '';

			if ( $fileError > 0 && $fileError != 4 ) {
				switch ( $fileError ) {
					case 1:
						$message = Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_1' );
						break;
					case 2:
						$message = Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_2' );
						break;
					case 3:
						$message = Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_3' );
						break;
				}

				if ( $message != '' ) {
					$app->enqueueMessage( $message, 'error' );
					return false;
				}
			} elseif ( $fileError == 4 ) {
				if ( isset( $array['mp4'] ) )	{
					$this->mp4 = $array['mp4'];
				}
			} else {
				// Check for filetype
				$okMIMETypes = 'video/mp4,video/webm,video/ogg';
				$validMIMEArray = explode( ',', $okMIMETypes );
				$fileMime = $singleFile['type'];
				$fileTemp = $singleFile['tmp_name'];

				if ( ! in_array( $fileMime, $validMIMEArray ) )	{
					$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ), 'error' );
					return false;
				}

				if ( YendifVideoShareHelper::isVideo( $fileTemp ) === false ) {
					$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ), 'error' );
					return false;
				}

				// Replace any special characters in the filename
				$filename = File::stripExt( $singleFile['name'] );
				$extension = File::getExt( $singleFile['name'] );
				$filename = preg_replace( "/[^A-Za-z0-9]/i", "-", $filename );
				$filename = $filename . '.' . $extension;				

				$date = HTMLHelper::_( 'date', 'now', 'Y-m', false );
				$uploadPath = JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' . $filename;		
				
				if ( ! Folder::exists( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' ) ) {
					Folder::create( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' );
				}

				if ( ! File::exists( $uploadPath ) ) {
					if ( ! File::upload( $fileTemp, $uploadPath ) )	{
						$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_MOVING_FILE' ), 'error' );
						return false;
					}
				}

				$this->mp4 = Uri::root( true ) . '/' . $this->upload_dir . '/' . $date . '/' . $filename;
			}
		} else {
			if ( isset( $array['mp4'] ) )	{
				$this->mp4 = $array['mp4'];
			}
		}

		return parent::check();
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
	 * @param   Table   $table  Table name
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
	
    /**
     * Delete a record by id
     *
     * @param   mixed  $pk  Primary key value to delete. Optional
     *
     * @return  bool
	 * 
	 * @since   2.0.0
     */
    public function delete( $pk = null ) {
        $this->load( $pk );
        $result = parent::delete( $pk );
        
		if ( $result ) {
			YendifVideoShareHelper::deleteFile( $this->mp4 );
		}

        return $result;
    }
	
}
