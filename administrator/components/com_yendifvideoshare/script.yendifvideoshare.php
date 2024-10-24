<?php
/**
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Updates the database structure of the component
 *
 * @since  1.0.0
 */
class Com_YendifVideoShareInstallerScript extends InstallerScript {

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var  string
	 */
	protected $extension = 'Yendif Video Share';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var  string
	 */
	protected $minimumJoomla = '4.0';

	/**
	 * Method called before install/update the component
	 * 
	 * Note: This method won't be called during uninstall process
	 *
	 * @param   string  $type    Type of process [install | update]
	 * @param   mixed   $parent  Object who called this method
	 *
	 * @return  boolean  True if the process should continue, false otherwise
	 * 
	 * @since   1.0.0
     * @throws  Exception
	 */
	public function preflight( $type, $parent )	{
		$result = parent::preflight( $type, $parent );

		if ( ! $result ) {
			return $result;
		}

		// logic for preflight before install
		return $result;
	}

	/**
	 * Method to install the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function install( $parent ) {	
		$this->installDb( $parent );
		$this->installPlugins( $parent );
		$this->installModules( $parent );
	}

	/**
	 * Method to update the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return  void
	 * 
	 * @since   1.0.0
	 */
	public function update( $parent ) {	
		$this->installDb( $parent );
		$this->installPlugins( $parent );
		$this->installModules( $parent );	
	}	

	/**
	 * Method called after install/update the component.
	 * 
	 * @param   string  $type    type
	 * @param   string  $parent  parent
	 *
	 * @return  boolean
	 * 
	 * @since   1.0.0
	 */
	public function postflight( $type, $parent ) {
		return true;
	}	

	/**
	 * Method to uninstall the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return  void
	 * 
	 * @since   2.0.0
	 */
	public function uninstall( $parent ) {
		$this->uninstallPlugins( $parent );
		$this->uninstallModules( $parent );
	}

	/**
	 * Method to update the DB of the component
	 *
	 * @param   mixed  $parent  Object who started the upgrading process
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	private function installDb( $parent ) {
		$db = Factory::getDbo();

		// Migrate from Joomla 3.x
		if ( $this->existsTable( '#__yendifvideoshare_config' ) ) {
			$this->migrateFromJoomla3();
		}		
		
		// Install default data on a fresh installation
		$query = 'SELECT COUNT(id) FROM #__yendifvideoshare_categories';
		$db->setQuery( $query );

		if ( ! $db->loadResult() ) {		
			// Insert our first category
			$item = new stdClass();

			$item->id = 1;
			$item->title = 'Uncategorized';
			$item->alias = 'uncategorized';
			$item->parent = 0;
			$item->access = 1;
			$item->created_by = Factory::getUser()->id;
			$item->modified_by = Factory::getUser()->id;
			$item->state = 1;

			$db->insertObject( '#__yendifvideoshare_categories', $item );

			// Set Component Parameters
			$config = array(
				'image_ratio' => 56.25,
				'no_of_rows' => 3,
				'no_of_cols' => 3,
				'default_image' => Uri::root() . 'media/com_yendifvideoshare/images/placeholder.jpg',
				'title_length' => 0,
				'show_excerpt' => 1,
				'excerpt_length' => 100,
				'show_category' => 1,
				'show_user' => 0,	
				'show_date' => 1,
				'show_views' => 1,				
				'enable_popup' => 0,
				'show_rating' => 0,
				'allow_guest_rating' => 1,
				'show_likes' => 0,
				'allow_guest_like' => 1,
				'show_videos_count' => 1,
				'show_title' => 1,
				'show_description' => 1,
				'show_search' => 1,
				'show_related' => 1,							
				'related_no_of_rows' => '',
				'related_no_of_cols' => '',
				'related_orderby' =>'',
				'comments' => 'none',
				'fb_app_id' => '',
				'fb_post_count' => 5,
				'fb_color_scheme' => 'light',
				'ratio' => 56.25,
				'autoplay' => 0,
				'loop' => 0,
				'volume' => -1,
				'hotkeys' => 0,
				'show_consent' => 0,
				'analytics' => '',
				'controlbar' => 1,
				'playbtn' => 1,
				'playpause' => 1,
				'currenttime' => 1,
				'progress' => 1,
				'duration' => 1,
				'volumebtn' => 1,
				'fullscreen' => 1,
				'embed' => 0,
				'share' => 0,
				'download' => 0,
				'playlist_position' => 'right',
				'playlist_width' => 250,
				'playlist_height' => 150,
				'autoplaylist' => 0,
				'ad_engine' => 'vast',
				'enable_adverts' => 'both',
				'show_adverts_timeframe' => 1,
				'can_skip_adverts' => 1,
				'show_skip_adverts_on' => 3,
				'vasturl' => '',
				'allow_upload' => 1,
				'max_upload_size' => 104857600,
				'allow_youtube' => 1,
				'allow_vimeo' => 1,
				'allow_hls_dash' => 1,
				'allow_subtitle' => 0,
				'schedule_video_publishing' => 0,
				'youtube_api_key' => '',
				'vimeo_access_token' => '',
				'custom_css' => '',
				'show_feed' => 0,
				'feed_icon' => Uri::root() . 'media/com_yendifvideoshare/images/rss.png',
				'feed_limit' => 20,
				'itemid_category' => 0,
				'itemid_video' => 0,
				'license' => '',
				'logo' => '',
				'logoposition' => 'bottomleft',
				'logoalpha' => 50,
				'logotarget' => 'https://yendifplayer.com/',
				'displaylogo' => 1				
			);

			$this->setComponentParams( $config );
		}

		// ...
		$query = 'SELECT id FROM #__yendifvideoshare_options WHERE name=' . $db->quote( 'access' );
		$db->setQuery( $query );
        $count = $db->loadResult();		

		if ( ! $count ) {
			$row = new stdClass();			
   			$row->id = NULL;
			$row->name = 'access';
			$row->value = 1;
			 
   			$db->insertObject( '#__yendifvideoshare_options', $row );		
		}
	}

	/**
	 * Installs plugins for this component
	 *
	 * @param   mixed  $parent  Object who called the install/update method
	 *
	 * @return  void
	 * 
	 * @since  2.0.0
	 */
	private function installPlugins( $parent ) {
		$installation_folder = $parent->getParent()->getPath( 'source' );
		$app = Factory::getApplication();

		/* @var $plugins SimpleXMLElement */
		if ( method_exists( $parent, 'getManifest' ) ) {
			$plugins = $parent->getManifest()->plugins;
		} else {
			$plugins = $parent->get( 'manifest' )->plugins;
		}

		if ( count( $plugins->children() ) ) {
			$db    = Factory::getDbo();
			$query = $db->getQuery( true );

			foreach ( $plugins->children() as $plugin )	{
				$pluginName  = (string) $plugin['plugin'];
				$pluginGroup = (string) $plugin['group'];
				$path        = $installation_folder . '/plugins/' . $pluginGroup . '/' . $pluginName;
				$installer   = new Installer;

				if ( ! $this->isAlreadyInstalled( 'plugin', $pluginName, $pluginGroup ) ) {
					$result = $installer->install( $path );
				} else {
					$result = $installer->update( $path );
				}

				if ( $result ) {
					$app->enqueueMessage( 
						Text::sprintf(
							'Plugin "%s - %s" was installed successfully',
							$pluginGroup, 
							$pluginName
						)
					);
				} else {
					$app->enqueueMessage(
						Text::sprintf(
							'There was an issue installing the plugin "%s - %s"', 
							$pluginGroup,
							$pluginName
						),
						'error'
					);
				}

				$query
					->clear()
					->update( '#__extensions' )
					->set( 'enabled = 1' )
					->where(
						array(
							'type LIKE ' . $db->quote( 'plugin' ),
							'element LIKE ' . $db->quote( $pluginName ),
							'folder LIKE ' . $db->quote( $pluginGroup )
						)
					);
				$db->setQuery( $query );
				$db->execute();
			}
		}
	}	

	/**
	 * Installs modules for this component
	 *
	 * @param   mixed  $parent  Object who called the install/update method
	 *
	 * @return  void
	 * 
	 * @since  2.0.0
	 */
	private function installModules( $parent ) {
		$installation_folder = $parent->getParent()->getPath( 'source' );
		$app = Factory::getApplication();

		if ( method_exists( $parent, 'getManifest' ) ) {
			$modules = $parent->getManifest()->modules;
		} else {
			$modules = $parent->get( 'manifest' )->modules;
		}

		if ( ! empty( $modules ) ) {
			if ( count( $modules->children() ) ) {
				foreach ( $modules->children() as $module ) {
					$moduleName = (string) $module['module'];
					$path       = $installation_folder . '/modules/' . $moduleName;
					$installer  = new Installer;

					if ( ! $this->isAlreadyInstalled( 'module', $moduleName ) )	{
						$result = $installer->install( $path );
					} else {
						$result = $installer->update( $path );
					}

					if ( $result ) {
						$app->enqueueMessage( 
							Text::sprintf(
								'Module "%s" was installed successfully', 
								$moduleName
							)
						);
					} else {
						$app->enqueueMessage(
							Text::sprintf(
								'There was an issue installing the module "%s"', 
								$moduleName
							),
							'error'
						);
					}
				}
			}
		}
	}	

	/**
	 * Uninstalls plugins
	 *
	 * @param   mixed  $parent  Object who called the uninstall method
	 *
	 * @return  void
	 * 
	 * @since   2.0.0
	 */
	private function uninstallPlugins( $parent ) {
		$app = Factory::getApplication();

		if ( method_exists( $parent, 'getManifest' ) ) {
			$plugins = $parent->getManifest()->plugins;
		} else {
			$plugins = $parent->get( 'manifest' )->plugins;
		}

		if ( count( $plugins->children() ) ) {
			$db    = Factory::getDbo();
			$query = $db->getQuery( true );

			foreach ( $plugins->children() as $plugin )	{
				$pluginName  = (string) $plugin['plugin'];
				$pluginGroup = (string) $plugin['group'];
				$query
					->clear()
					->select( 'extension_id' )
					->from( '#__extensions' )
					->where(
						array(
							'type LIKE ' . $db->quote( 'plugin' ),
							'element LIKE ' . $db->quote( $pluginName ),
							'folder LIKE ' . $db->quote( $pluginGroup )
						)
					);
				$db->setQuery( $query );
				$extension = $db->loadResult();

				if ( ! empty( $extension ) ) {
					$installer = new Installer;
					$result    = $installer->uninstall( 'plugin', $extension );

					if ( $result ) {
						$app->enqueueMessage( 
							Text::sprintf(
								'Plugin "%s - %s" was uninstalled successfully', 
								$pluginGroup,
								$pluginName
							)
						 );
					} else {
						$app->enqueueMessage(
							Text::sprintf(
								'There was an issue uninstalling the plugin "%s - %s"', 
								$pluginGroup,
								$pluginName
							),
							'error'
						);
					}
				}
			}
		}
	}

	/**
	 * Uninstalls modules
	 *
	 * @param   mixed  $parent  Object who called the uninstall method
	 *
	 * @return  void
	 * 
	 * @since   2.0.0
	 */
	private function uninstallModules( $parent ) {
		$app = Factory::getApplication();

		if ( method_exists( $parent, 'getManifest' ) ) {
			$modules = $parent->getManifest()->modules;
		} else {
			$modules = $parent->get( 'manifest' )->modules;
		}

		if ( ! empty( $modules ) ) {
			if ( count( $modules->children() ) ) {
				$db    = Factory::getDbo();
				$query = $db->getQuery( true );

				foreach ( $modules->children() as $module ) {
					$moduleName = (string) $module['module'];
					$query
						->clear()
						->select( 'extension_id' )
						->from( '#__extensions' )
						->where(
							array(
								'type LIKE ' . $db->quote( 'module' ),
								'element LIKE ' . $db->quote( $moduleName )
							)
						);
					$db->setQuery( $query );
					$extension = $db->loadResult();

					if ( ! empty( $extension ) ) {
						$installer = new Installer;
						$result    = $installer->uninstall( 'module', $extension );

						if ( $result ) {
							$app->enqueueMessage( 
								Text::sprintf(
									'Module "%s" was uninstalled successfully', 
									$moduleName
								) 
							);
						} else {
							$app->enqueueMessage(
								Text::sprintf(
									'There was an issue uninstalling the module "%s"', 
									$moduleName
								),
								'error'
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Checks if a certain table exists on the current database
	 *
	 * @param   string   $table_name  Name of the table
	 *
	 * @return  boolean  True if it exists, false if it does not
	 * 
	 * @since   2.0.0
	 */
	private function existsTable( $table_name ) {
		$db = Factory::getDbo();
		$table_name = str_replace( '#__', $db->getPrefix(), (string) $table_name );
		return in_array( $table_name, $db->getTableList() );
	}

	/**
	 * Check if an extension is already installed in the system
	 *
	 * @param   string  $type    Extension type
	 * @param   string  $name    Extension name
	 * @param   mixed   $folder  Extension folder(for plugins)
	 *
	 * @return  boolean
	 * 
	 * @since  2.0.0
	 */
	private function isAlreadyInstalled( $type, $name, $folder = null )	{
		$result = false;

		switch ( $type ) {
			case 'plugin':
				$result = file_exists( JPATH_PLUGINS . '/' . $folder . '/' . $name );
				break;
			case 'module':
				$result = file_exists( JPATH_ROOT . '/modules/' . $name );
				break;
		}

		return $result;
	}

	/**
	 * Migrate from Joomla 3.x
	 * 
	 * @since  2.0.0
	 */
	private function migrateFromJoomla3() {
		$app = Factory::getApplication();
		$db  = Factory::getDbo();		

		// Get options
		$query = 'SELECT * FROM #__yendifvideoshare_options';
		$db->setQuery( $query );
		$options = $db->loadObjectList();

		// Set component params
		$params = array();
		$params['itemid_category'] = -1;
		$params['itemid_video'] = -1;

		foreach ( $options as $option ) {
			$name  = $option->name;
			$value = $option->value;

			switch ( $name ) {
				case 'ratio':
					$value = (float) $value * 100;
					break;
				case 'allow_youtube_upload':
					$name = 'allow_youtube';
					break;
				case 'allow_rtmp_upload':
					$name = 'allow_hls_dash';
					break;
				case 'google_api_key':
					$name = 'youtube_api_key';
					break;
				case 'playlist_desc_limit':
					$name = 'excerpt_length';
					break;
				case 'show_media_count':
					$name = 'show_videos_count';
					break;
				case 'show_likes_dislikes':
					$name = 'show_likes';
					break;
				case 'responsive_css':
					$name = 'custom_css';
					break;
			}

			$params[ $name ] = $value;
		}

		$this->setComponentParams( $params );

		// Drop the config table
		$query = 'DROP TABLE IF EXISTS #__yendifvideoshare_config';
		$db->setQuery( $query );
		$db->execute();

		// Clean unwanted directories from the back-end components folder
		$folders = array(
			'controllers',
			'elements',
			'libraries',
			'models',
			'tables',
			'views'
		);

		foreach ( $folders as $folder ) {
			$path = JPATH_ROOT . '/administrator/components/com_yendifvideoshare/' . $folder;

			if ( Folder::exists( $path ) ) {
				Folder::delete( $path );
			}
		}

		$files = array(
			'yendifvideoshare.php',
			'install.mysql.sql',
			'uninstall.mysql.sql'
		);

		foreach ( $files as $file ) {
			$path = JPATH_ROOT . '/administrator/components/com_yendifvideoshare/' . $file;
			
			if ( File::exists( $path ) ) {
				File::delete( $path );
			}
		}

		// Clean unwanted directories from the front-end components folder
		$folders = array(
			'controllers',
			'models',
			'views'
		);

		foreach ( $folders as $folder ) {
			$path = JPATH_ROOT . '/components/com_yendifvideoshare/' . $folder;

			if ( Folder::exists( $path ) ) {
				Folder::delete( $path );
			}
		}

		$files = array(
			'yendifvideoshare.php',
			'komento_plugin.php',
			'router.php'
		);

		foreach ( $files as $file ) {
			$path = JPATH_ROOT . '/components/com_yendifvideoshare/' . $file;
			
			if ( File::exists( $path ) ) {
				File::delete( $path );
			}
		}

		// Uninstall the search plugin
		$query = $db->getQuery( true );

		$query
			->clear()
			->select( 'extension_id' )
			->from( '#__extensions' )
			->where(
				array(
					'type LIKE ' . $db->quote( 'plugin' ),
					'element LIKE ' . $db->quote( 'yendifvideoshare' ),
					'folder LIKE ' . $db->quote( 'search' )
				)
			);
		$db->setQuery( $query );
		$extension = $db->loadResult();

		if ( ! empty( $extension ) ) {
			$installer = new Installer;
			$result    = $installer->uninstall( 'plugin', $extension );

			if ( $result ) {
				$app->enqueueMessage( 'Plugin "yendifvideoshare - search" was uninstalled successfully' );
			} else {
				$app->enqueueMessage( 'There was an issue uninstalling the plugin "yendifvideoshare - search"', 'error' );
			}
		}
	}

	/**
	 * Set component configuration parameters
	 * 
	 * @param  int  $config  Array of default configuration values
	 * 
	 * @since  2.0.0
	 */
	private function setComponentParams( $config ) {
		$db = Factory::getDbo();

		$componentName = 'com_yendifvideoshare';

		$params = array();
		foreach ( $config as $key => $value ) {
			$params[ $key ] = $value;
		}	

		// Save the parameters		
		$query = $db->getQuery( true );

		$query
			->clear()
			->update( '#__extensions' )
			->set( 'params = ' . $db->quote( json_encode( $params ) ) )
			->where(
				array(
					'type LIKE ' . $db->quote( 'component' ),
					'element LIKE ' . $db->quote( $componentName )
				)
			);
		$db->setQuery( $query );
		$db->execute();
	}
	
}
