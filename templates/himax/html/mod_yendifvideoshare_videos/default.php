<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Videos
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;
use PluginsWare\Module\YendifVideoShareVideos\Site\Helper\YendifVideoShareVideosHelper;

$items = YendifVideoShareVideosHelper::getItems( $params );

// Vars
$player_ratio = $params->get( 'ratio', 56.25 );
$image_ratio  = $params->get( 'image_ratio', $player_ratio );
$column_class = YendifVideoShareHelper::getCSSClassNames( $params, 'grid' );
$popup_class  = YendifVideoShareHelper::getCSSClassNames( $params, 'popup' );

// Import CSS & JS
$wa = $app->getDocument()->getWebAssetManager();

if ( ! $wa->assetExists( 'style', 'com_yendifvideoshare.site' ) ) {
	$wr = $wa->getRegistry();
	$wr->addRegistryFile( 'media/com_yendifvideoshare/joomla.asset.json' );
}

if ( $params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

if ( $params->get( 'popup' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.popup' )
		->useScript( 'com_yendifvideoshare.popup' )
		->useScript( 'com_yendifvideoshare.site' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}
/////////
$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::base(true).'/templates/himax/html/mod_yendifvideoshare_videos/magnific-popup.css');
$doc->addStyleSheet(JURI::base(true).'/templates/himax/html/mod_yendifvideoshare_videos/site.css');
$doc->addScript(JURI::base(true).'/templates/himax/html/mod_yendifvideoshare_videos/jquery.magnific-popup.js');
$doc->addScript(JURI::base(true).'/templates/himax/html/mod_yendifvideoshare_videos/site.js');
$doc->addScript(JURI::base(true).'/templates/himax/html/mod_yendifvideoshare_videos/modal.js');
?>

<div class="yendif-video-share mod-yendifvideoshare-videos himax">
	<?php if ( empty( $items ) ) : ?>
		<p class="text-muted">
			<?php echo Text::_( 'MOD_YENDIFVIDEOSHARE_VIDEOS_NO_ITEMS_FOUND' ); ?>
		</p>
	<?php else : ?>
		<div class="yendif-video-share-grid<?php echo $popup_class; ?>" data-player_ratio="<?php echo (float) $player_ratio; ?>">
			<div class="yendif-row">
				<?php foreach ( $items as $i => $item ) : 
					$route = YendifVideoShareRoute::getVideoRoute( $item, (int) $params->get( 'itemid', 0 ) );
					$item_link = Route::_( $route );

					if ( $params->get( 'enable_popup' ) ) {
						$item_link = 'javascript:void(0)';
					}			

					$iframe_src = URI::root() . 'index.php?option=com_yendifvideoshare&view=player&id=' . (int) $item->id . "&format=raw&autoplay=1";
					?>
					<div class="yendif-video-share-grid-item yendif-video-share-video-<?php echo (int) $item->id; ?> <?php echo $column_class; ?>" data-mfp-src="<?php echo $iframe_src; ?>">
						<div class="card  wow zoomIn box-shadow rounded " data-wow-duration="1s" data-wow-delay="<?php  $increment = 0.2; echo (0.1 + ($i * $increment)); ?>s">
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
									<a href="<?php echo $item_link; ?>" class="card-link"><?php echo YendifVideoShareHelper::Truncate( $item->title, $params->get( 'title_length', 0 ) ); ?></a>
								</div>								

								<?php
								$meta = array();
										
								// Author Name
								if ( $params->get( 'show_user' ) ) {
									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-user"><span class="icon-user icon-fw"></span> %s</span>',
										Factory::getUser( $item->userid )->username
									);
								}

								// Date Added
								if ( $params->get( 'show_date' ) ) {
									$date  = ( ! empty( $item->published_up ) && $item->published_up !== '0000-00-00 00:00:00' ) ? $item->published_up : $item->created_date;
									$jdate = new Date( $date );
									$prettyDate = $jdate->format( Text::_( 'DATE_FORMAT_LC3' ) );

									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-date"><span class="icon-calendar icon-fw"></span> %s</span>',
										$prettyDate
									);
								}

								// Views Count
								if ( $params->get( 'show_views' ) ) {
									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-views"><span class="icon-eye icon-fw"></span> %s</span>',
										Text::sprintf( 'MOD_YENDIFVIDEOSHARE_VIDEOS_N_VIEWS_COUNT', $item->views )
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
								if ( $params->get( 'show_category' ) && $category = YendifVideoShareHelper::getCategory( $item->catid, array( 'id', 'title', 'alias' ) ) ) : ?>
									<div class="yendif-video-share-category text-muted small mt-1">
										<?php
										printf(
											'<span class="icon-folder-open icon-fw"></span> <a href="%s">%s</a>',
											YendifVideoShareRoute::getCategoryRoute( $category, $params->get( 'itemid_category' ) ),
											$category->title
										);
										?>
									</div>
								<?php endif; ?>

								<?php 
								// Ratings
								if ( $params->get( 'show_rating' ) ) : ?>
									<div class="yendif-video-share-ratings-small mt-1">
										<span class="yendif-video-share-ratings-stars">
											<span class="yendif-video-share-ratings-current" style="width: <?php echo (float) $item->rating; ?>%;"></span>
										</span>
									</div>
								<?php endif; ?>

								<?php 
								// Short Description
								if ( $params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
									<div class="yendif-video-share-excerpt mt-2">
										<?php echo YendifVideoShareHelper::Truncate( $item->description, $params->get( 'excerpt_length' ) ); ?>
									</div>
								<?php endif; ?>
							</div>					
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php 
			// More Button
			if ( $params->get( 'show_more_btn' ) ) {
			 	$more_btn_link = $params->get( 'more_btn_link' );

				if ( empty( $more_btn_link ) ) {
					$itemid = (int) $params->get( 'itemid', 0 );
					$catid = $params->get( 'catid', 0 );						

					if ( is_array( $catid ) ) {
						$catids = array_map( 'intval', $catid );
						$catids = array_filter( $catids );
		
						if ( 1 == count( $catids ) ) {
							$catid = $catids[0];
						} else {
							$catid = 0;
						}
					} else {
						$catid = (int) $catid;
					}

					$route = YendifVideoShareRoute::getVideosRoute( $itemid, $catid );
					if ( $route ) {
						$more_btn_link = Route::_( $route );
					}
				}

				if ( ! empty( $more_btn_link ) ) {
					printf(
						'<a class="btn btn-primary mt-4" href="%s">%s</a>',
						$more_btn_link,
						$params->get( 'more_btn_label', Text::_( 'MOD_YENDIFVIDEOSHARE_VIDEOS_SHOW_MORE_BUTTON_LABEL' ) )
					);
				}
			}
			?>
		</div>		
	<?php endif; ?>
</div>
