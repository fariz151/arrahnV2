<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\View\Adverts;

// No direct access
\defined( '_JEXEC' ) or die;


use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * View class for a list of adverts.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $items;

	protected $pagination;	

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	public function display( $tpl = null ) {
		$this->state = $this->get( 'State' );
		$this->items = $this->get( 'Items' );
		$this->pagination = $this->get( 'Pagination' );
		$this->canDo = YendifVideoShareHelper::canDo();

		if ( $this->canDo ) {
			if ( ! count( $this->items ) && $this->get( 'IsEmptyState' ) ) {
				$this->setLayout( 'emptystate' );
			}
		}

		$this->filterForm = $this->get( 'FilterForm' );
		$this->activeFilters = $this->get( 'ActiveFilters' );

		// Check for errors
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			throw new \Exception( implode( "\n", $errors ) );
		}

		$this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display( $tpl );
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addToolbar()	{
		$state = $this->get( 'State' );
		$canDo = YendifVideoShareHelper::getActions();

		ToolbarHelper::title( Text::_( 'COM_YENDIFVIDEOSHARE_TITLE_ADVERTS' ), 'jcc fa-bullhorn' );

		$toolbar = Toolbar::getInstance( 'toolbar' );

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Advert';

		if ( file_exists( $formPath ) )	{
			if ( $canDo->get( 'core.create' ) )	{
				$toolbar->addNew( 'advert.add' );
			}
		}

		if ( $canDo->get( 'core.edit.state' ) || count( $this->transitions ) ) {
			$dropdown = $toolbar->dropdownButton( 'status-group' )
				->text( 'JTOOLBAR_CHANGE_STATUS' )
				->toggleSplit( false )
				->icon( 'fas fa-ellipsis-h' )
				->buttonClass( 'btn btn-action' )
				->listCheck( true );

			$childBar = $dropdown->getChildToolbar();

			if ( isset( $this->items[0]->state ) ) {
				$childBar->publish( 'adverts.publish' )->listCheck( true );
				$childBar->unpublish( 'adverts.unpublish' )->listCheck( true );
				$childBar->archive( 'adverts.archive' )->listCheck( true );
			} elseif ( isset( $this->items[0] ) ) {
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete( 'adverts.delete' )
					->text( 'JTOOLBAR_EMPTY_TRASH' )
					->message( 'JGLOBAL_CONFIRM_DELETE' )
					->listCheck( true );
			}

			if ( isset( $this->items[0]->checked_out ) ) {
				$childBar->checkin( 'adverts.checkin' )->listCheck( true );
			}

			if ( isset( $this->items[0]->state ) ) {
				$childBar->trash( 'adverts.trash' )->listCheck( true );
			}
		}		

		// Show trash and delete for components that uses the state field
		if ( isset( $this->items[0]->state ) ) {
			if ( $this->state->get( 'filter.state' ) == ContentComponent::CONDITION_TRASHED && $canDo->get( 'core.delete' ) ) {
				$toolbar->delete( 'adverts.delete' )
					->text( 'JTOOLBAR_EMPTY_TRASH' )
					->message( 'JGLOBAL_CONFIRM_DELETE' )
					->listCheck( true );
			}
		}

		if ( $canDo->get( 'core.admin' ) ) {
			$toolbar->preferences( 'com_yendifvideoshare' );
		}

		// Set sidebar action
		Sidebar::setAction( 'index.php?option=com_yendifvideoshare&view=adverts' );
	}
	
	/**
	 * Method to order fields 
	 *
	 * @return  void 
	 * 
	 * @since   2.0.0
	 */
	protected function getSortFields() {
		return array(			
			'a.`id`' => Text::_( 'JGRID_HEADING_ID' ),
			'a.`title`' => Text::_( 'JGLOBAL_TITLE' ),
			'a.`impressions`' => Text::_( 'COM_YENDIFVIDEOSHARE_ADVERTS_IMPRESSIONS' ),
			'a.`clicks`' => Text::_( 'COM_YENDIFVIDEOSHARE_ADVERTS_CLICKS' ),
			'a.`state`' => Text::_( 'JSTATUS' )
		);
	}

	/**
	 * Check if state is set
	 *
	 * @param   mixed  $state  State
	 *
	 * @return  bool
	 * 
	 * @since   2.0.0
	 */
	public function getState( $state ) {
		return isset( $this->state->{$state} ) ? $this->state->{$state} : false;
	}

}
