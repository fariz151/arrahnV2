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

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Filesystem\File;
use \Joomla\CMS\Filesystem\Folder;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Registry\Registry;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Database\DatabaseDriver;
use \PluginsWare\Component\YendifVideoShare\Administrator\Helper\YouTubeImport;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class VideoTable.
 *
 * @since  2.0.0
 */
class VideoTable extends Table {	

	protected $upload_dir = 'media/com_yendifvideoshare/uploads/videos';

	/**
	 * Constructor
	 *
	 * @param  JDatabase  &$db  A database connector object
	 * 
	 * @since  2.0.0
	 */
	public function __construct( DatabaseDriver $db ) {
		$this->typeAlias = 'com_yendifvideoshare.video';
		parent::__construct( '#__yendifvideoshare_videos', 'id', $db );
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
	
		if ( $array['id'] == 0 || empty( $array['userid'] ) ) {
			$array['userid'] = Factory::getUser()->id;
		}

		if ( $array['id'] == 0 || empty( $array['created_by'] ) ) {
			$array['created_by'] = Factory::getUser()->id;
		}

		if ( $array['id'] == 0 || empty( $array['modified_by'] ) ) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		if ( $array['id'] == 0 || empty( $array['created_date'] ) ) {
			$array['created_date'] = $date->toSql();
		}

		if ( $task == 'apply' || $task == 'save' ) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		if ( $task == 'apply' || $task == 'save' ) {
			$array['updated_date'] = $date->toSql();
		}		

		// Support for alias field
		if ( empty( $array['alias'] ) ) {
			if ( empty( $array['title'] ) )	{
				$array['alias'] = OutputFilter::stringURLSafe( date( 'Y-m-d H:i:s' ) );
			} else {
				if ( Factory::getConfig()->get( 'unicodeslugs' ) == 1 ) {
					$array['alias'] = OutputFilter::stringURLUnicodeSlug( trim( $array['title'] ) );
				} else {
					$array['alias'] = OutputFilter::stringURLSafe( trim( $array['title'] ) );
				}
			}
		}

		// Support for webm field
		if ( ! isset( $array['webm'] ) ) {
			$array['webm'] = '';
		}

		// Support for ogv field
		if ( ! isset( $array['ogv'] ) ) {
			$array['ogv'] = '';
		}

		// Support for thirdparty field
		if ( ! isset( $array['thirdparty'] ) ) {
			$array['thirdparty'] = '';
		}

		// Support for preroll field
		if ( ! isset( $array['preroll'] ) ) {
			$array['preroll'] = -1;
		}
		
		// Support for postroll field
		if ( ! isset( $array['postroll'] ) ) {
			$array['postroll'] = -1;
		}

		// Support for views field
		if ( isset( $array['views'] ) ) {
			$array['views'] = (int) $array['views'];
		} else {
			$array['views'] = 0;
		}

		// Support for featured field
		if ( ! isset( $array['featured'] ) ) {
			$array['featured'] = 0;
		}

		// Support for rating field
		if ( isset( $array['rating'] ) ) {
			$array['rating'] = (float) $array['rating'];
		} else {
			$array['rating'] = 0;
		}
		
		// Support for multiple field: related
		if ( isset( $array['related'] ) ) {
			if ( is_array( $array['related'] ) )	{
				$array['related'] = implode( ',', $array['related'] );
			} elseif ( strpos( $array['related'], ',' ) != false ) {
				$array['related'] = explode( ',', $array['related'] );
			} elseif ( strlen( $array['related'] ) == 0 ) {
				$array['related'] = '';
			}
		} else {
			$array['related'] = '';
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

		if ( ! Factory::getUser()->authorise( 'core.admin', 'com_yendifvideoshare.video.' . $array['id'] ) ) {
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_yendifvideoshare/access.xml',
				"/access/section[@name='video']/"
			);
			$default_actions = Access::getAssetRules( 'com_yendifvideoshare.video.' . $array['id'] )->getData();
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
		$array['rtmp'] = '';
		$array['flash'] = '';

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

			if ( $jaccess ) {
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
		$app = Factory::getApplication();

		$maxUploadSize = 0;
		if ( $app->isClient( 'site' ) ) {
			$params = ComponentHelper::getParams( 'com_yendifvideoshare' );
			$maxUploadSize = (int) $params->get( 'max_upload_size', 0 );
		}

		// If there is an ordering column and this is a new row then get the next ordering value
		if ( property_exists( $this, 'ordering' ) && $this->id == 0 ) {
			$this->ordering = self::getNextOrder();
		}
		
		// Check if alias is unique
		if ( ! $this->isUnique( 'alias' ) ) {
			$count = 0;
			$currentAlias = $this->alias;

			while ( ! $this->isUnique( 'alias' ) ) {
				$this->alias = $currentAlias . '-' . $count++;
			}
		}
		
		// Support file field: video		
		$files = $app->input->files->get( 'jform', array(), 'raw' );
		$array = $app->input->get( 'jform', array(), 'ARRAY' );
		$date = HTMLHelper::_( 'date', 'now', 'Y-m', false );

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
				$fileSize = $singleFile['size'];
				$fileMime = $singleFile['type'];
				$fileTemp = $singleFile['tmp_name'];				

				if ( $maxUploadSize > 0 && $fileSize > $maxUploadSize ) {
					$app->enqueueMessage( 
						Text::sprintf( 
							'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILESIZE',
							$maxUploadSize
						), 
						'error' 
					);

					return false;
				}

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
				
				$uploadPath = JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' . $filename;		
				
				if ( ! Folder::exists( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' ) ) {
					Folder::create( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' );
				}

				if ( ! File::exists( $uploadPath ) ) {
					if ( ! File::upload( $fileTemp, $uploadPath ) ) {
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

		// Support file field: mp4_hd
		if ( $files['mp4_hd']['size'] > 0 ) {
			// Deleting existing file
			$oldFile = YendifVideoShareHelper::getFile( $this->id, $this->_tbl, 'mp4_hd' );
			YendifVideoShareHelper::deleteFile( $oldFile );

			$this->mp4_hd = '';
			$singleFile = $files['mp4_hd'];

			// Check if the server found any error.
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
				if ( isset( $array['mp4_hd'] ) ) {
					$this->mp4_hd = $array['mp4_hd'];
				}
			} else {
				// Check for filetype
				$okMIMETypes = 'video/mp4,video/webm,video/ogg';
				$validMIMEArray = explode( ',', $okMIMETypes );
				$fileSize = $singleFile['size'];
				$fileMime = $singleFile['type'];
				$fileTemp = $singleFile['tmp_name'];				

				if ( $maxUploadSize > 0 && $fileSize > $maxUploadSize ) {
					$app->enqueueMessage( 
						Text::sprintf( 
							'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILESIZE',
							$maxUploadSize
						), 
						'error' 
					);

					return false;
				}

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

				$uploadPath = JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' . $filename;		
				
				if ( ! Folder::exists( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' ) ) {
					Folder::create( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' );
				}

				if ( ! File::exists( $uploadPath ) ) {
					if ( ! File::upload( $fileTemp, $uploadPath ) ) {
						$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_MOVING_FILE' ), 'error' );
						return false;
					}
				}

				$this->mp4_hd = Uri::root( true ) . '/' . $this->upload_dir . '/' . $date . '/' . $filename;
			}
		} else {
			if ( isset( $array['mp4_hd'] ) ) {
				$this->mp4_hd = $array['mp4_hd'];
			}
		}

		// Support file field: webm
		if ( isset( $files['webm'] ) && $files['webm']['size'] > 0 ) {
			// Deleting existing file
			$oldFile = YendifVideoShareHelper::getFile( $this->id, $this->_tbl, 'webm' );
			YendifVideoShareHelper::deleteFile( $oldFile );

			$this->webm = '';
			$singleFile = $files['webm'];

			// Check if the server found any error.
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
				if ( isset( $array['webm'] ) ) {
					$this->webm = $array['webm'];
				}
			} else {
				// Check for filetype
				$okMIMETypes = 'video/webm';
				$validMIMEArray = explode( ',', $okMIMETypes );
				$fileSize = $singleFile['size'];
				$fileMime = $singleFile['type'];
				$fileTemp = $singleFile['tmp_name'];				

				if ( $maxUploadSize > 0 && $fileSize > $maxUploadSize ) {
					$app->enqueueMessage( 
						Text::sprintf( 
							'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILESIZE',
							$maxUploadSize
						), 
						'error' 
					);

					return false;
				}

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

				$uploadPath = JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' . $filename;		
				
				if ( ! Folder::exists( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' ) ) {
					Folder::create( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' );
				}

				if ( ! File::exists( $uploadPath ) ) {
					if ( ! File::upload( $fileTemp, $uploadPath ) ) {
						$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_MOVING_FILE' ), 'error' );
						return false;
					}
				}

				$this->webm = Uri::root( true ) . '/' . $this->upload_dir . '/' . $date . '/' . $filename;
			}
		} else {
			if ( isset( $array['webm'] ) ) {
				$this->webm = $array['webm'];
			}
		}

		// Support file field: ogv
		if ( isset( $files['ogv'] ) && $files['ogv']['size'] > 0 ) {
			// Deleting existing file
			$oldFile = YendifVideoShareHelper::getFile( $this->id, $this->_tbl, 'ogv' );
			YendifVideoShareHelper::deleteFile( $oldFile );

			$this->ogv = '';
			$singleFile = $files['ogv'];

			// Check if the server found any error.
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
				if ( isset( $array['ogv'] ) ) {
					$this->ogv = $array['ogv'];
				}
			} else {
				// Check for filetype
				$okMIMETypes = 'video/ogg';
				$validMIMEArray = explode( ',', $okMIMETypes );
				$fileSize = $singleFile['size'];
				$fileMime = $singleFile['type'];
				$fileTemp = $singleFile['tmp_name'];				

				if ( $maxUploadSize > 0 && $fileSize > $maxUploadSize ) {
					$app->enqueueMessage( 
						Text::sprintf( 
							'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILESIZE',
							$maxUploadSize
						), 
						'error' 
					);

					return false;
				}

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

				$uploadPath = JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' . $filename;		
				
				if ( ! Folder::exists( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' ) ) {
					Folder::create( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' );
				}

				if ( ! File::exists( $uploadPath ) ) {
					if ( ! File::upload( $fileTemp, $uploadPath ) ) {
						$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_MOVING_FILE' ), 'error' );
						return false;
					}
				}

				$this->ogv = Uri::root( true ) . '/' . $this->upload_dir . '/' . $date . '/' . $filename;
			}
		} else {
			if ( isset( $array['ogv'] ) ) {
				$this->ogv = $array['ogv'];
			}
		}

		// Support file field: image
		if ( $files['image']['size'] > 0 ) {
			// Deleting existing file
			$oldFile = YendifVideoShareHelper::getFile( $this->id, $this->_tbl, 'image' );
			YendifVideoShareHelper::deleteFile( $oldFile );

			$this->image = '';
			$singleFile = $files['image'];

			// Check if the server found any error.
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
				if ( isset( $array['image'] ) )	{
					$this->image = $array['image'];
				}
			} else {
				// Check for filetype
				$okMIMETypes = 'image/jpeg,image/png,image/gif';
				$validMIMEArray = explode( ',', $okMIMETypes );
				$fileSize = $singleFile['size'];
				$fileMime = $singleFile['type'];
				$fileTemp = $singleFile['tmp_name'];				

				if ( $maxUploadSize > 0 && $fileSize > $maxUploadSize ) {
					$app->enqueueMessage( 
						Text::sprintf( 
							'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILESIZE',
							$maxUploadSize
						), 
						'error' 
					);

					return false;
				}

				if ( ! in_array( $fileMime, $validMIMEArray ) )	{
					$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ), 'error' );
					return false;
				}

				if ( getimagesize( $fileTemp ) === FALSE ) {
					$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ), 'error' );
					return false;
				}

				// Replace any special characters in the filename
				$filename = File::stripExt( $singleFile['name'] );
				$extension = File::getExt( $singleFile['name'] );
				$filename = preg_replace( "/[^A-Za-z0-9]/i", "-", $filename );
				$filename = $filename . '.' . $extension;				

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

				$this->image = Uri::root( true ) . '/' . $this->upload_dir . '/' . $date . '/' . $filename;
			}
		} else {
			if ( isset( $array['image'] ) )	{
				$this->image = $array['image'];
			}
		}

		// Auto generate image URL from YouTube video URL
		if ( $this->type == 'youtube' && empty( $this->image ) ) {
			$this->image = YendifVideoShareHelper::getYouTubeVideoImage( $this->youtube );
		}

		// Auto generate image URL from Vimeo video URL
		if ( $this->type == 'vimeo' && empty( $this->image ) ) {
			$this->image = YendifVideoShareHelper::getVimeoVideoImage( $this->vimeo );
		}

		// Auto generate image URL from YouTube/Vimeo embedcode
		if ( $this->type == 'thirdparty' && empty( $this->image ) ) {
			$this->image = YendifVideoShareHelper::getVideoImageFromEmbedCode( $this->thirdparty );
		}

		// Support file field: captions
		if ( $files['captions']['size'] > 0 ) {
			// Deleting existing file
			$oldFile = YendifVideoShareHelper::getFile( $this->id, $this->_tbl, 'captions' );
			YendifVideoShareHelper::deleteFile( $oldFile );

			$this->captions = '';
			$singleFile = $files['captions'];

			// Check if the server found any error.
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
				if ( isset( $array['captions'] ) ) {
					$this->captions = $array['captions'];
				}
			} else {
				// Check for filetype
				$fileSize = $singleFile['size'];
				$fileTemp = $singleFile['tmp_name'];				

				if ( $maxUploadSize > 0 && $fileSize > $maxUploadSize ) {
					$app->enqueueMessage( 
						Text::sprintf( 
							'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILESIZE',
							$maxUploadSize
						), 
						'error' 
					);

					return false;
				}

				if ( YendifVideoShareHelper::isWebVTT( $fileTemp ) === false ) {
					$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ), 'error' );
					return false;
				}

				// Replace any special characters in the filename
				$filename = File::stripExt( $singleFile['name'] );
				$extension = File::getExt( $singleFile['name'] );
				$filename = preg_replace( "/[^A-Za-z0-9]/i", "-", $filename );
				$filename = $filename . '.' . $extension;				

				$uploadPath = JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' . $filename;		
				
				if ( ! Folder::exists( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' ) ) {
					Folder::create( JPATH_ROOT . '/' . $this->upload_dir . '/' . $date . '/' );
				}

				if ( ! File::exists( $uploadPath ) ) {
					if ( ! File::upload( $fileTemp, $uploadPath ) ) {
						$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_MOVING_FILE' ), 'error' );
						return false;
					}
				}

				$this->captions = Uri::root( true ) . '/' . $this->upload_dir . '/' . $date . '/' . $filename;
			}
		} else {
			if ( isset( $array['captions'] ) ) {
				$this->captions = $array['captions'];
			}
		}

		// Set published_up to null if not set
		if ( ! $this->published_up ) {
			$this->published_up = null;
		}

		// Set published_down to null if not set
		if ( ! $this->published_down ) {
			$this->published_down = null;
		}

		return parent::check();
	}

	/**
     * Overrides Table::store to set modified data and user id.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   2.1.1
     */
    public function store( $updateNulls = true ) {
		return parent::store( true );
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return  string  The asset name
	 *
	 * @see    Table::_getAssetName
	 * @since  2.0.0
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
	 * @see    Table::_getAssetParentId
	 * @since  2.0.0
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
			$db = Factory::getDbo();

			// mp4: Is uploaded through our component interface?
			YendifVideoShareHelper::deleteFile( $this->mp4 );

			// mp4_hd: Is uploaded through our component interface?
			YendifVideoShareHelper::deleteFile( $this->mp4_hd );

			// webm: Is uploaded through our component interface?
			YendifVideoShareHelper::deleteFile( $this->webm );

			// ogv: Is uploaded through our component interface?
			YendifVideoShareHelper::deleteFile( $this->ogv );

			// youtube: Update the imports table (only if applicable)
			$import = YouTubeImport::getInstance();
			$import->onVideoDeleted( $this );

			// image: Is uploaded through our component interface?
			YendifVideoShareHelper::deleteFile( $this->image );

			// captions: Is uploaded through our component interface?
			YendifVideoShareHelper::deleteFile( $this->captions );

			// Ratings
			$query = 'DELETE FROM #__yendifvideoshare_ratings WHERE videoid=' . (int) $pk;
			$db->setQuery( $query );
			$db->execute();

			// Likes & Dislikes
			$query = 'DELETE FROM #__yendifvideoshare_likes_dislikes WHERE videoid=' . (int) $pk;
			$db->setQuery( $query );
			$db->execute();
		}

        return $result;
    }

	/**
	 * Check if a field is unique
	 *
	 * @param   string  $field  Name of the field
	 *
	 * @return  bool  True if unique
	 * 
	 * @since   2.0.0
	 */
	private function isUnique( $field ) {
		$db = Factory::getDbo();
		$query = $db->getQuery( true );

		$query
			->select( $db->quoteName( $field ) )
			->from( $db->quoteName( $this->_tbl ) )
			->where( $db->quoteName( $field ) . ' = ' . $db->quote( $this->$field ) )
			->where( $db->quoteName( 'id' ) . ' <> ' . (int) $this->{$this->_tbl_key} );

		$db->setQuery( $query );
		$db->execute();

		return ( $db->getNumRows() == 0 ) ? true : false;
	}

}
