<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

$app = Factory::getApplication();

// Vars
$player_ratio = $this->params->get( 'ratio', 56.25 );
$image_ratio  = $this->params->get( 'image_ratio', $player_ratio );
$column_class = YendifVideoShareHelper::getCSSClassNames( $this->params, 'grid' );
$popup_class  = YendifVideoShareHelper::getCSSClassNames( $this->params, 'popup' );

// Import CSS
$wa = $app->getDocument()->getWebAssetManager();

if ( $this->params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

if ( $this->params->get( 'enable_popup' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.popup' )
		->useScript( 'com_yendifvideoshare.popup' )
		->useScript( 'com_yendifvideoshare.site' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $this->params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}
?>

<div id="yendif-video-share-category" class="yendif-video-share category himax mb-4">
	<?php if ( $this->params->get( 'show_page_heading' ) ) : ?>
		<div class="page-header">
			<h1>
				<?php if ( $this->escape( $this->params->get( 'page_heading' ) ) ) : ?>
					<?php echo $this->escape( $this->params->get( 'page_heading' ) ); ?>
				<?php else : ?>
					<?php echo $this->escape($this->params->get( 'page_title' ) ); ?>
				<?php endif; ?>

				<?php if ( $this->params->get( 'show_videos_count' ) ) : ?>
            		(<?php echo YendifVideoShareHelper::getVideosCount( $this->category->id, $this->params ); ?>)
            	<?php endif; ?>

				<?php if ( $this->params->get( 'show_feed' ) && $this->params->get( 'feed_icon' ) ) : ?>
					<a href="<?php echo YendifVideoShareRoute::getCategoryRoute( $this->category, $this->params->get( 'itemid_category' ), 0, 'feed' ); ?>" class="yendif-video-share-feed-btn" target="_blank">
						<img src="<?php echo $this->params->get( 'feed_icon' ); ?>" />
					</a>
				<?php endif; ?>
			</h1>
		</div>		
	<?php endif; ?>

	<?php if ( ! empty( $this->category->description ) ) : ?>
		<p class="yendif-video-share-description"><?php echo $this->category->description; ?></p>
	<?php endif; ?>

	<div class="yendif-video-share-grid<?php echo $popup_class; ?> mb-4" data-player_ratio="<?php echo (float) $player_ratio; ?>">
		<div class="yendif-row">
			<?php foreach ( $this->items as $i => $item ) : 
				$route = YendifVideoShareRoute::getVideoRoute( $item, $this->params->get( 'itemid_video' ) );
				$item_link = Route::_( $route );

				if ( $this->params->get( 'enable_popup' ) ) {
					$item_link = 'javascript:void(0)';
				}			

				$iframe_src = URI::root() . 'index.php?option=com_yendifvideoshare&view=player&id=' . $item->id . "&format=raw&autoplay=1";
				?>
				<div class="yendif-video-share-grid-item yendif-video-share-video-<?php echo (int) $item->id; ?> <?php echo $column_class; ?>" data-mfp-src="<?php echo $iframe_src; ?>">
					<div class="card">
						<a href="<?php echo $item_link; ?>" class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo (float) $image_ratio; ?>%">
							<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>
							
							
								<svg class="yendif-video-share-svg-icon yendif-video-share-svg-icon-play" version="1.0" xmlns="http://www.w3.org/2000/svg" width="53.000000pt" height="53.000000pt" viewBox="0 0 53.000000 53.000000" preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,53.000000) scale(0.100000,-0.100000)"  stroke="none">
<path  d="M256,0C114.625,0,0,114.625,0,256c0,141.374,114.625,256,256,256c141.374,0,256-114.626,256-256
		C512,114.625,397.374,0,256,0z M351.062,258.898l-144,85.945c-1.031,0.626-2.344,0.657-3.406,0.031
		c-1.031-0.594-1.687-1.702-1.687-2.937v-85.946v-85.946c0-1.218,0.656-2.343,1.687-2.938c1.062-0.609,2.375-0.578,3.406,0.031
		l144,85.962c1.031,0.586,1.641,1.718,1.641,2.89C352.703,257.187,352.094,258.297,351.062,258.898z"/>
</g>
</svg>
<figure class="play-btn-animation"></figure>

							<?php if ( ! empty( $item->duration ) ) : ?>
								<span class="yendif-video-share-duration small"><?php echo $item->duration; ?></span>
							<?php endif; ?> 
						</a>

						<div class="card-body">
							<div class="yendif-video-share-title">
								<a href="<?php echo $item_link; ?>" class="card-link"><?php echo YendifVideoShareHelper::Truncate( $item->title, $this->params->get( 'title_length', 0 ) ); ?></a>
							</div>							

							<?php
							$meta = array();
								
							// Author Name
							if ( $this->params->get( 'show_user' ) ) {
								$meta[] = sprintf(
									'<span class="yendif-video-share-meta-user"><span class="icon-user icon-fw"></span> %s</span>',
									Factory::getUser( $item->userid )->username
								);
							}

							// Date Added
							if ( $this->params->get( 'show_date' ) ) {
								$date  = ( ! empty( $item->published_up ) && $item->published_up !== '0000-00-00 00:00:00' ) ? $item->published_up : $item->created_date;
								$jdate = new Date( $date );
								$prettyDate = $jdate->format( Text::_( 'DATE_FORMAT_LC3' ) );

								$meta[] = sprintf(
									'<span class="yendif-video-share-meta-date"><span class="icon-calendar icon-fw"></span> %s</span>',
									$prettyDate
								);
							}

							// Views Count
							if ( $this->params->get( 'show_views' ) ) {
								$meta[] = sprintf(
									'<span class="yendif-video-share-meta-views"><span class="icon-eye icon-fw"></span> %s</span>',
									Text::sprintf( 'COM_YENDIFVIDEOSHARE_N_VIEWS_COUNT', $item->views )
								);
							}	
							
							if ( count( $meta ) ) {
								printf( 
									'<div class="yendif-video-share-meta text-muted small mt-1">%s</div>',
									implode( ' &bull; ', $meta )
								);
							}
							?>			
							
							<?php 
							// Categories
							if ( $this->params->get( 'show_category' ) ) : ?>
								<div class="yendif-video-share-category text-muted small mt-1">
									<?php
									printf(
										'<span class="icon-folder-open icon-fw"></span> <a href="%s">%s</a>',
										YendifVideoShareRoute::getCategoryRoute( $this->category, $this->params->get( 'itemid_category' ) ),
										$this->category->title
									);
									?>
								</div>
							<?php endif; ?>

							<?php 
							// Ratings
							if ( $this->params->get( 'show_rating' ) ) : ?>
								<div class="yendif-video-share-ratings-small mt-1">
									<span class="yendif-video-share-ratings-stars">
										<span class="yendif-video-share-ratings-current" style="width: <?php echo (float) $item->rating; ?>%;"></span>
									</span>
								</div>
							<?php endif; ?>

							<?php 
							// Short Description
							if ( $this->params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
								<div class="yendif-video-share-excerpt mt-2">
									<?php echo YendifVideoShareHelper::Truncate( $item->description, $this->params->get( 'excerpt_length' ) ); ?>
								</div>
							<?php endif; ?>							 
						</div>					
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php echo $this->pagination->getListFooter(); ?>
	</div>		

	<?php if ( ! empty( $this->subCategories ) ) : ?>
		<br />

		<?php if ( ! empty( $this->items ) ) : ?>
			<p class="lead"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_TITLE_SUBCATEGORIES' ); ?></p>
		<?php endif; ?>

		<div class="yendif-video-share-grid">
			<div class="yendif-row">
				<?php foreach ( $this->subCategories as $i => $item ) : 
					$route = YendifVideoShareRoute::getCategoryRoute( $item, $this->params->get( 'itemid_category' ) );
					$item_link = Route::_( $route );
					?>
					<div class="<?php echo $column_class; ?>">
						<div class="card">
							<a href="<?php echo $item_link; ?>" class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo $image_ratio; ?>%">
								<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>
							</a>

							<div class="card-body">
								<div class="yendif-video-share-title">
									<a href="<?php echo $item_link; ?>" class="card-link">
										<?php
										echo YendifVideoShareHelper::Truncate( $item->title, $this->params->get( 'title_length', 0 ) );

										if ( $this->params->get( 'show_videos_count' ) ) {
											$count = YendifVideoShareHelper::getVideosCount( $item->id, $this->params );
											echo ' (' . $count . ')';
										}
										?>
									</a>
								</div>

								<?php if ( $this->params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
									<div class="yendif-video-share-excerpt mt-2">
										<?php echo YendifVideoShareHelper::Truncate( $item->description, $this->params->get( 'excerpt_length' ) ); ?>
									</div>
								<?php endif; ?>
							</div>					
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
