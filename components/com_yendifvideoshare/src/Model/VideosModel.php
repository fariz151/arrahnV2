<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Model;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\ListModel;

/**
 * Class VideosModel.
 *
 * @since  2.0.0
 */
class VideosModel extends ListModel {

	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  2.0.0
	 */
	public function __construct( $config = array() ) {
		if ( empty( $config['filter_fields'] ) ) {
			$config['filter_fields'] = array(
				'id', 'a.id',				
				'title', 'a.title',
				'alias', 'a.alias',
				'catid', 'a.catid',
				'type', 'a.type',
				'mp4', 'a.mp4',
				'mp4_hd', 'a.mp4_hd',
				'webm', 'a.webm',
				'ogv', 'a.ogv',
				'youtube', 'a.youtube',
				'vimeo', 'a.vimeo',
				'hls', 'a.hls',
				'dash', 'a.dash',
				'thirdparty', 'a.thirdparty',
				'image', 'a.image',
				'captions', 'a.captions',
				'duration', 'a.duration',
				'description', 'a.description',	
				'userid', 'a.userid',			
				'access', 'a.access',
				'views', 'a.views',
				'featured', 'a.featured',
				'rating', 'a.rating',
				'preroll', 'a.preroll',
				'postroll', 'a.postroll',				
				'meta_keywords', 'a.meta_keywords',
				'meta_description', 'a.meta_description',
				'state', 'a.state',
				'published_up', 'a.published_up',
				'published_down', 'a.published_down',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'created_date', 'a.created_date',
				'updated_date', 'a.updated_date'
			);
		}

		parent::__construct( $config );
	}	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	protected function populateState( $ordering = null, $direction = null )	{
		$app = Factory::getApplication();
		
		$params = $app->getParams();

		// List state information
		$orderby = $params->get( 'orderby', 'latest' );

		switch ( $orderby ) {
			case 'latest':
				$orderCol  = 'id';
				$orderDirn = 'DESC';
				break;
			case 'date_added':
				$orderCol  = 'created_date';
				$orderDirn = 'DESC';
				break;
			case 'most_viewed':
				$orderCol  = 'views';
				$orderDirn = 'DESC';
				break;
			case 'most_rated':
				$orderCol  = 'rating';
				$orderDirn = 'DESC';
				break;
			case 'a_z':
				$orderCol  = 'title';
				$orderDirn = 'ASC';
				break;
			case 'z_a':
				$orderCol  = 'title';
				$orderDirn = 'DESC';
				break;
			case 'random':
				$orderCol  = 'RAND';
				$orderDirn = '';
				break;
			default:
				$orderCol  = 'ordering';
				$orderDirn = 'ASC';		
		}

		$this->setState( 'list.ordering', $orderCol );
		$this->setState( 'list.direction', $orderDirn );

		$format = $app->input->getWord( 'format' );

		if ( $format === 'feed' ) {
			$limit = (int) $params->get( 'feed_limit', $app->get( 'feed_limit', 20  ) );
		} else {
			$no_of_rows = (int) $params->get( 'no_of_rows', 3 );
			$no_of_cols = (int) $params->get( 'no_of_cols', 3 );

			$limit = $no_of_rows * $no_of_cols;
		}

		$this->setState( 'list.limit', $limit );

		$limitstart = $app->input->get( 'limitstart', 0, 'uint' );
		$this->setState( 'list.start', $limitstart );

		// Filter state information
		$featured = ( $params->get( 'filterby' ) == 'featured' ) ? 1 : 0;
		$this->setState( 'filter.featured', $featured );

		// Load the parameters
		$this->setState( 'params', $params );
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  DatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery() {
		// Create a new query object
		$db    = $this->getDbo();
		$query = $db->getQuery( true );

		// Select the required fields from the table
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);

		$query->from( $db->quoteName( '#__yendifvideoshare_videos', 'a' ) );
			
		// Join over the categories for the category title
		$query->select( "c.title AS category" );
		$query->join( "LEFT", "#__yendifvideoshare_categories AS c ON c.id=a.catid" );
		
		// Filter by start and end dates
		$params = $this->state->get( 'params' );

		if ( $params && $params->get( 'schedule_video_publishing' ) ) {
			$nullDate = $db->getNullDate();
			$nowDate  = Factory::getDate()->toSql();

			$query->where( '(a.published_up IS NULL OR a.published_up = ' . $db->quote( $nullDate ) . ' OR a.published_up <= ' . $db->quote( $nowDate ) . ')' );
			$query->where( '(a.published_down IS NULL OR a.published_down = ' . $db->quote( $nullDate ) . ' OR a.published_down >= ' . $db->quote( $nowDate ) . ')' );
		}

		// Filter by published state
		$query->where( 'a.state = 1' );

		// Filtering featured
		$filter_featured = $this->state->get( 'filter.featured' );

		if ( $filter_featured > 0 ) {
			$query->where( 'a.featured = 1' );
		}		
		
		// Add the list ordering clause
		$orderCol  = $this->state->get( 'list.ordering', 'a.id' );
		$orderDirn = $this->state->get( 'list.direction', 'DESC' );

		if ( $orderCol ) {
			if ( $orderCol == 'RAND' ) {
				$query->order( 'RAND()' );
			} else {
				if ( $orderDirn ) {
					$query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );
				} else {
					$query->order( $db->escape( $orderCol ) );
				}
			}
		}

		return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed  An array of data on success, false on failure.
	 * 
	 * @since   2.0.0
	 */
	public function getItems() {
		$items = parent::getItems();
		return $items;
	}

}
