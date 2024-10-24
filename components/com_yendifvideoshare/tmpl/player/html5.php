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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

$app = Factory::getApplication();

// Video Sources
$sources = array();

switch ( $this->item->type ) {
    case 'youtube':
        $sources['youtube'] = array(
            'src'  => $this->item->youtube,
            'type' => 'video/youtube',
        );
        break;
    case 'vimeo':
        $sources['vimeo'] = array(
            'src'  => $this->item->vimeo,
            'type' => 'video/vimeo',
        );
        break;
    case 'rtmp':
		if ( ! empty( $this->item->hls ) ) {
			$sources['hls'] = array(
				'src'  => $this->item->hls,
				'type' => 'application/x-mpegurl',
			);
		}

		if ( ! empty( $this->item->dash ) ) {
			$sources['dash'] = array(
				'src'  => $this->item->dash,
				'type' => 'application/dash+xml',
			);
		}
        break;
    default:
		$this->item->type = 'default';

        $sources = array();

        // SD
		$ext = pathinfo( $this->item->mp4, PATHINFO_EXTENSION );
        if ( ! in_array( $ext, array( 'webm', 'ogv' ) ) ) {
			$ext = 'mp4';
		}

        $sources['sd'] = array(
            'src'  => $this->item->mp4,
            'type' => 'video/' . $ext,
        );
        
        // HD
        if ( ! empty( $this->item->mp4_hd ) ) {
			$sources['sd']['label'] = 'SD';

            $sources['hd'] = array(
                'src'   => $this->item->mp4_hd,
                'type'  => 'video/mp4',
                'label' => 'HD'
            );
        }

		// WebM
        if ( ! empty( $this->item->webm ) ) {
            $sources['webm'] = array(
                'src'  => $this->item->webm,
                'type' => 'video/webm',
            );
        }

		// OGV
        if ( ! empty( $this->item->ogv ) ) {
            $sources['ogv'] = array(
                'src'  => $this->item->ogv,
                'type' => 'video/ogv',
            );
        }
} 

// Video Tracks
$tracks = array();

if ( ! empty( $this->item->captions ) ) {
	$tracks[] = $this->item->captions;
}

// Video Attributes
$attributes = array(
    'id'            => 'player',
	'style'         => 'width: 100%; height: 100%;',
	'controls'      => '',
	'playsinline'   => '',
	'controlsList'  => 'nodownload',
	'oncontextmenu' => 'return false;'
);

if ( $this->params->get( 'volume', -1 ) == 0 ) {
	$attributes['muted'] = true;
}

if ( $this->params->get( 'loop' ) ) {
    $attributes['loop'] = true;
}

if ( ! empty( $this->item->image ) ) {
    $attributes['poster'] = $this->item->image;
}

// Player Settings
$settings = array(	
	'autoplay'                  => $this->params->get( 'autoplay' ) ? true : false,
	'controlBar'                => array(),
	'bigPlayButton'             => $this->params->get( 'playbtn' ) ? true : false,
	'playbackRates'             => array( 0.5, 1, 1.5, 2 ),
	'suppressNotSupportedError' => true,
	'custom'                    => array(
		'uid'          => $app->input->getInt( 'uid', 0 ),
		'siteUrl'      => URI::root(),
		'videoId'      => $this->item->id,	
		'videoTitle'   => $this->item->title,	
		'videoExcerpt' => '',
		'ipAddress'    => $this->getIpAddress(),
		'autoAdvance'  => ( $app->input->getInt( 'uid', 0 ) > 0 && $this->params->get( 'autoplaylist' ) ) ? 1 : 0,
		'loop'         => $this->params->get( 'loop' ) ? 1 : 0,
		'volume'       => (int) $this->params->get( 'volume', -1 ),
		'hotkeys'      => $this->params->get( 'hotkeys' ) ? 1 : 0,
		'i18n'         => array(
			'streamNotFound' => Text::_( 'COM_YENDIFVIDEOSHARE_PLAYER_STREAM_NOT_FOUND' )
		)
	)
);

if ( $this->item->id > 0 ) {
	$excerpt = $this->item->meta_description;

	if ( empty( $excerpt ) && ! empty( $this->item->description ) ) {
		$excerpt = YendifVideoShareHelper::Truncate( $this->item->description );
		$excerpt = str_replace( '...', '', $excerpt );
	}

	$settings['custom']['videoExcerpt'] = $excerpt;
}

// Player Settings: Controlbar
if ( $this->params->get( 'controlbar' ) ) { 
	$controls = array( 
		'playpause'   => 'PlayToggle', 
		'currenttime' => 'CurrentTimeDisplay', 
		'progress'    => 'progressControl', 
		'duration'    => 'durationDisplay',
		'tracks'      => 'SubtitlesButton',
		'audio'       => 'AudioTrackButton',
		'quality'     => 'qualitySelector',
		'speed'       => 'PlaybackRateMenuButton',  
		'volumebtn'   => 'VolumePanel', 
		'fullscreen'  => 'fullscreenToggle'
	);

	foreach ( $controls as $key => $control ) {
		switch ( $key ) {
			case 'quality':
				$enabled = ( isset( $sources['hd'] ) || isset( $sources['hls'] ) || isset( $sources['dash'] ) ) ? 1 : 0;
				break;
			case 'tracks':
			case 'audio':
				$enabled = ! empty( $this->item->captions ) ? 1 : 0;
				break;
			default:
				$enabled = $this->params->get( $key, 0 );
		}

		if ( ! $enabled ) {	
			unset( $controls[ $key ] );	
		}	
	}

	$settings['controlBar']['children'] = array_values( $controls );
}

if ( ! isset( $settings['controlBar']['children'] ) || empty( $settings['controlBar']['children'] ) ) {
	$attributes['class'] = 'vjs-no-control-bar';
}

// Player Settings: YouTube
if ( isset( $sources['youtube'] ) ) {
	$settings['techOrder'] = array( 'youtube' );

	$settings['youtube'] = array( 
		'iv_load_policy' => 3 
	);

	parse_str( $sources['youtube']['src'], $queries );

	if ( isset( $queries['start'] ) ) {
		$settings['custom']['start'] = (int) $queries['start'];
	}

	if ( isset( $queries['t'] ) ) {
		$settings['custom']['start'] = (int) $queries['t'];
	}

	if ( isset( $queries['end'] ) ) {
		$settings['custom']['end'] = (int) $queries['end'];
	}
}

// Player Settings: Vimeo
if ( isset( $sources['vimeo'] ) ) {
	$settings['techOrder'] = array( 'vimeo2' );
}

// Player Settings: Ads
if ( $this->hasAds() ) {
	$settings['custom']['ads'] = array(
		'adTagUrl'      => $this->params->get( 'vasturl' ),
		'showCountdown' => ( $this->params->get( 'ad_engine' ) == 'vast' && ! $this->params->get( 'show_adverts_timeframe' ) ) ? false : true
	);	
}

// Player Settings: Logo
$settings['custom']['license'] = array(	 
    'secretKey'        => trim( $this->params->get( 'license', '' ) ),
    'showLogo'         => $this->params->get( 'displaylogo' ) ? 1 : 0,
    'logoImage'        => $this->params->get( 'logo' ),
	'logoLink'         => $this->params->get( 'logotarget' ),
    'logoPosition'     => $this->params->get( 'logoposition' ),
    'logoOpacity'      => (int) $this->params->get( 'logoalpha' ) / 100,    
	'contextmenuLabel' => $this->canDo ? $app->get( 'sitename' ) : 'Powered by Yendif'
);

if ( ( $this->canDo && $this->item->id > 0 ) ) {
	// Player Settings: Embed
	if ( $this->params->get( 'embed' ) ) {
		$settings['custom']['embed'] = array(
			'code' => '<div style="position:relative;padding-bottom:' . $this->params->get( 'ratio', 56.25 ) . '%;height:0;overflow:hidden;"><iframe src="' . URI::root() . 'index.php?option=com_yendifvideoshare&view=player&id=' . $this->item->id . '&format=raw" style="width:100%;height:100%;position:absolute;left:0px;top:0px;overflow:hidden" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>'
		);
	}
		
	// Player Settings: Share
	if ( $this->params->get( 'share' ) ) {
		$shareUrl   = urlencode( $this->getURL() );
		$shareTitle = urlencode( $this->item->title );
		$shareImage = ! empty( $this->item->image ) ? urlencode( $this->item->image ) : '';

		$settings['custom']['share'] = array(
			'facebookUrl'  => "https://www.facebook.com/sharer/sharer.php?u={$shareUrl}",
			'twitterUrl'   => "https://twitter.com/intent/tweet?text={$shareTitle}&amp;url={$shareUrl}",
			'linkedinUrl'  => "https://www.linkedin.com/shareArticle?url={$shareUrl}&amp;title={$shareTitle}",
			'pinterestUrl' => "https://pinterest.com/pin/create/button/?url={$shareUrl}&amp;media={$shareImage}&amp;description={$shareTitle}",
			'tumblrUrl'    => "https://www.tumblr.com/share/link?url={$shareUrl}&amp;name={$shareTitle}"
		);
	}

	// Player Settings: Download
	if ( $this->params->get( 'download' ) && $this->item->type == 'default' ) {
		$settings['custom']['download'] = array(
			'url' => Uri::root() . 'index.php?option=com_yendifvideoshare&task=video.download&id=' . $this->item->id
		);
	}
}
?>

<!DOCTYPE html>
<html translate="no">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/video-js.min.css?v=7.21.1" />

	<?php if ( isset( $sources['hd'] ) ) : ?>
    	<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/quality-selector/quality-selector.min.css?v=1.2.5" />
	<?php endif; ?>

	<?php if ( isset( $sources['hls'] ) || isset( $sources['dash'] ) ) : ?>
    	<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/quality-menu/videojs-quality-menu.min.css?v=2.0.1" />
	<?php endif; ?>

    <link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/overlay/videojs-overlay.min.css?v=2.1.5" />

	<?php if ( isset( $settings['custom']['ads'] ) ) : ?>
		<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/contrib-ads/videojs-contrib-ads.min.css?v=6.9.0" />
		<link rel="stylesheet" href="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/ima/videojs.ima.min.css?v=2.1.0" /> 
	<?php endif; ?>

	<style type="text/css">
        html, 
        body,
		.video-js {
            width: 100%;
            height: 100%;
            margin: 0; 
            padding: 0; 
            overflow: hidden;
        }

		.video-js.vjs-youtube-mobile .vjs-poster {
			display: none;
		}

		.video-js.vjs-ended .vjs-poster {
			display: block;
		}		

		.video-js:not(.vjs-has-started) .vjs-text-track-display {
			display: none;
		}

		.video-js.vjs-ended .vjs-text-track-display {
			display: none;
		}

		.video-js.vjs-error .vjs-loading-spinner {
			display: none;
		}

		.video-js.vjs-waiting.vjs-paused .vjs-loading-spinner {
			display: none;
		}

		.video-js .vjs-big-play-button {
			width: 1.5em;
			height: 1.5em;
			top: 50%;
			left: 50%;
			margin-top: 0;
			margin-left: 0;
			background-color: rgba( 0, 0, 0, 0.6 );
			border: none;
			border-radius: 50%;
			font-size: 6em;
			line-height: 1.5em;			
			transform: translateX( -50% ) translateY( -50% );
		}

		.video-js:hover .vjs-big-play-button,
		.video-js .vjs-big-play-button:focus {
			background-color: rgba( 0, 0, 0, 0.7 );
		}
				
		.vjs-waiting .video-js .vjs-big-play-button {
			display: none;
		}

		.video-js.vjs-waiting.vjs-paused.vjs-error .vjs-big-play-button {
			display: none;
		}

		.video-js.vjs-waiting.vjs-paused .vjs-big-play-button {
			display: block;
		}

		.video-js.vjs-ended .vjs-big-play-button {
			display: block;
		}

		.video-js.vjs-no-control-bar .vjs-control-bar {
			display: none;
		}		

		.video-js.vjs-ended .vjs-control-bar {
			display: none;
		}

		.video-js .vjs-current-time,
		.video-js .vjs-duration {
			display: block;
		}

		.video-js .vjs-subtitles-button .vjs-icon-placeholder:before {
			content: "\f10d";
		}

		.video-js .vjs-menu li {
			text-transform: Capitalize;
		}

		.video-js .vjs-menu li.vjs-selected:focus,
		.video-js .vjs-menu li.vjs-selected:hover {
			background-color: #fff;
			color: #2b333f;
		}

		.video-js.vjs-quality-menu .vjs-quality-menu-button-4K-flag:after, 
		.video-js.vjs-quality-menu .vjs-quality-menu-button-HD-flag:after {
			background-color: #F00;
		}

		.video-js .vjs-quality-selector .vjs-quality-menu-item-sub-label {			
			position: absolute;
			width: 4em;
			right: 0;
			font-size: 75%;
			font-weight: bold;
			text-align: center;
			text-transform: none;			
		}

		.video-js.vjs-hd .vjs-quality-selector:after {
			position: absolute;
			width: 2.2em;
			height: 2.2em;
			top: 0.5em;
			right: 0;
			padding: 0;
			background-color: #F00;
			border-radius: 2em;						 
			font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
			font-size: 0.7em;
			font-weight: 300;   
			content: "";
			color: inherit;    
			text-align: center;    
			letter-spacing: 0.1em;
			line-height: 2.2em;
			pointer-events: none; 
		}

		.video-js.vjs-hd .vjs-quality-selector:after {
			content: "HD";
		}	
		
		.video-js .vjs-playback-rate .vjs-playback-rate-value {
			font-size: 1.2em;
			line-height: 2.6em;
		}

		.video-js .vjs-share {
			margin: 5px;
			cursor: pointer;
		}	

		.video-js .vjs-share a {
			display: flex;
			margin: 0;
			padding: 10px;
    		background-color: rgba( 0, 0, 0, 0.5 );			
			border-radius: 1px;
			font-size: 15px;
			color: #fff;
		}

		.video-js .vjs-share:hover a {
			background-color: rgba( 0, 0, 0, 0.7 );
		}

		.video-js .vjs-share .vjs-icon-share {
			line-height: 1;
		}

		.video-js.vjs-has-started .vjs-share {
			display: block;
			visibility: visible;
			opacity: 1;
			transition: visibility .1s,opacity .1s;
		}

		.video-js.vjs-has-started.vjs-user-inactive.vjs-playing .vjs-share {
			visibility: visible;
			opacity: 0;
			transition: visibility 1s,opacity 1s;
		}		

		.video-js .vjs-modal-dialog-share-embed {
            background: #111 !important;
        }

		.video-js .vjs-modal-dialog-share-embed .vjs-close-button {
            margin: 7px;
        }

		.video-js .vjs-share-embed {
            display: flex !important;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;   
        }

		.video-js .vjs-share-embed-content {
            width: 100%;
        }

		.video-js .vjs-share-buttons {
            text-align: center;
        }

		.video-js .vjs-share-button {
            display: inline-block;
			margin: 2px;
            width: 40px;
			height: 40px;
            line-height: 1;
			vertical-align: middle;
        }       

        .video-js .vjs-share-button,
        .video-js .vjs-share-button:hover,
        .video-js .vjs-share-button:focus {
            text-decoration: none;
        } 

		.video-js .vjs-share-button:hover {
            opacity: 0.9;
        }

        .video-js .vjs-share-button-facebook {
            background-color: #3B5996;
        }   
		
		.video-js .vjs-share-button-twitter {
            background-color: #55ACEE;
        }

        .video-js .vjs-share-button-linkedin {
            background-color: #006699;
        }

        .video-js .vjs-share-button-pinterest {
            background-color: #C00117;
        }

        .video-js .vjs-share-button-tumblr {
            background-color: #28364B;
        } 
		
		.video-js .vjs-share-button-whatsapp {
            background-color: #25d366;
        }  

        .video-js .vjs-share-button span {
            color: #fff;
            font-size: 24px;
			line-height: 40px;
        }

        .video-js .vjs-embed-code {
            margin: 20px;
        }

        .video-js .vjs-embed-code p {
			margin: 0 0 7px 0;
			font-size: 11px;
            text-align: center;
			text-transform: uppercase;
        }

        .video-js .vjs-embed-code input {
            width: 100%;
            padding: 7px;
            background: #fff;
            border: 1px solid #fff;
            color: #000;
        }

        .video-js .vjs-embed-code input:focus {
            border: 1px solid #fff;
            outline-style: none;
        }

		.video-js .vjs-download {
			margin: 5px;
			cursor: pointer;
		}

		.video-js .vjs-has-share.vjs-download {
			margin-top: 50px;
		}

		.video-js .vjs-download a {
			display: flex;
			margin: 0;
			padding: 10px;
    		background-color: rgba( 0, 0, 0, 0.5 );			
			border-radius: 1px;
			font-size: 15px;
			color: #fff;
		}	
		
		.video-js .vjs-download:hover a {
			background-color: rgba( 0, 0, 0, 0.7 );
		}

		.video-js.vjs-has-started .vjs-download {
			display: block;
			visibility: visible;
			opacity: 1;
			transition: visibility .1s,opacity .1s;
		}

		.video-js.vjs-has-started.vjs-user-inactive.vjs-playing .vjs-download {
			visibility: visible;
			opacity: 0;
			transition: visibility 1s,opacity 1s;
		}

		.video-js .vjs-logo {
			opacity: 0;
			cursor: pointer;
		}

		.video-js.vjs-has-started .vjs-logo {
			opacity: 0.5;
			transition: opacity 0.1s;
		}

		.video-js.vjs-has-started.vjs-user-inactive.vjs-playing .vjs-logo {
			opacity: 0;
			transition: opacity 1s;
		}

		.video-js.vjs-has-started .vjs-logo:hover {
			opacity: 1;
		}

		.video-js .vjs-logo img {
			max-width: 100%;
		}

		.video-js.vjs-ended .vjs-logo {
			display: none;
		}	

		.vjs-contextmenu {
            position: absolute;
            top: 0;
            left: 0;
            margin: 0;
            padding: 0;
            background-color: #2B333F;
  			background-color: rgba( 43, 51, 63, 0.7 );
			border-radius: 2px;
            z-index: 9999999999; /* make sure it shows on fullscreen */
        }
        
       	.vjs-contextmenu-content {
            margin: 0;
            padding: 8px 12px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #FFF;		
            white-space: nowrap;
            cursor: pointer;
        }
    </style>    

    <?php
    if ( ! empty( $this->params->get( 'custom_css' ) ) ) {
        printf( '<style type="text/css">%s</style>', $this->params->get( 'custom_css' ) );
    }
    ?>
</head>
<body id="body" class="vjs-waiting">
	<?php
	$_attributes = array();

	foreach ( $attributes as $key => $value ) {
		if ( '' === $value ) {
			$_attributes[] = $key;
		} else {
			$_attributes[] = sprintf( '%s="%s"', $key, $value );
		}
	}
	
	$attributes = implode( ' ', $_attributes );
	?>
    <video-js <?php echo $attributes; ?>>
        <?php 
		// Video Sources
		foreach ( $sources as $source ) {
			printf( 
				'<source type="%s" src="%s" %s/>', 
				$source['type'], 
				$source['src'],
				( isset( $source['label'] ) ? 'label="' . $source['label'] . '"' : '' ) 
			);
		}
		
		// Video Tracks
		foreach ( $tracks as $track ) {
        	printf( 
				'<track src="%s" kind="subtitles" srclang="en" label="subtitles on">', 
				$track
			);
		}
       ?>       
	</video-js>    

	<div id="vjs-share-embed" class="vjs-share-embed" style="display: none;">
        <div class="vjs-share-embed-content">
			<?php if ( isset( $settings['custom']['share'] ) ) : ?>
				<!-- Share Icons -->
				<div class="vjs-share-buttons">
					<a href="<?php echo $settings['custom']['share']['facebookUrl']; ?>" class="vjs-share-button vjs-share-button-facebook" target="_blank">
						<span class="vjs-icon-facebook"></span>
					</a>                
					<a href="<?php echo $settings['custom']['share']['twitterUrl']; ?>" class="vjs-share-button vjs-share-button-twitter" target="_blank">
						<span class="vjs-icon-twitter"></span>
					</a>
					<a href="<?php echo $settings['custom']['share']['linkedinUrl']; ?>" class="vjs-share-button vjs-share-button-linkedin" target="_blank">
						<span class="vjs-icon-linkedin"></span>
					</a>                
					<a href="<?php echo $settings['custom']['share']['pinterestUrl']; ?>" class="vjs-share-button vjs-share-button-pinterest" target="_blank">
						<span class="vjs-icon-pinterest"></span>
					</a>
					<a href="<?php echo $settings['custom']['share']['tumblrUrl']; ?>" class="vjs-share-button vjs-share-button-tumblr" target="_blank">
						<span class="vjs-icon-tumblr"></span>
					</a>                
				</div>
			<?php endif; ?>

			<?php if ( isset( $settings['custom']['embed'] ) ) : ?>
				<!-- Embed URL -->
				<div class="vjs-embed-code">
					<p><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_PLAYER_EMBED_TITLE' ); ?></p>
					<input type="text" id="vjs-copy-embed-url" value="<?php echo htmlspecialchars( $settings['custom']['embed']['code'] ); ?>" readonly />
				</div>
			<?php endif; ?>
        </div>
    </div>

    <div id="vjs-contextmenu" class="vjs-contextmenu" style="display: none;">
        <div class="vjs-contextmenu-content"><?php echo $settings['custom']['license']['contextmenuLabel']; ?></div>
    </div>

    <script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/video.min.js?v=7.21.1" type="text/javascript"></script>

	<?php if ( isset( $sources['hd'] ) ) : ?>
    	<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/quality-selector/silvermine-videojs-quality-selector.min.js?v=1.2.5" type="text/javascript"></script>
    <?php endif; ?>

	<?php if ( isset( $sources['hls'] ) || isset( $sources['dash'] ) ) : ?>
    	<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/quality-menu/videojs-quality-menu.min.js?v=2.0.1" type="text/javascript"></script>
    <?php endif; ?>

    <?php if ( isset( $sources['youtube'] ) ) : ?>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/youtube/Youtube.min.js?v=2.6.1" type="text/javascript"></script>
	<?php endif; ?>

	<?php if ( isset( $settings['custom']['start'] ) || isset( $settings['custom']['end'] ) ) : ?>
    	<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/offset/videojs-offset.min.js?v=2.1.3" type="text/javascript"></script>
	<?php endif; ?>

    <?php if ( isset( $sources['vimeo'] ) ) : ?>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/vimeo/videojs-vimeo2.min.js?v=2.0.0" type="text/javascript"></script>
	<?php endif; ?>

    <script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/overlay/videojs-overlay.min.js?v=2.1.5" type="text/javascript"></script>

	<?php if ( isset( $settings['custom']['ads'] ) ) : ?>
		<script src="https://imasdk.googleapis.com/js/sdkloader/ima3.js" type="text/javascript"></script>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/contrib-ads/videojs-contrib-ads.min.js?v=6.9.0" type="text/javascript"></script>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/ima/videojs.ima.min.js?v=2.1.0" type="text/javascript"></script>
	<?php endif; ?>

	<?php if ( isset( $settings['custom']['hotkeys'] ) ) : ?>
		<script src="<?php echo URI::root(); ?>media/com_yendifvideoshare/player/plugins/hotkeys/videojs.hotkeys.min.js?v=0.2.28" type="text/javascript"></script>
	<?php endif; ?>

	<script type="text/javascript">
		var License = function( player, settings ) {
			function a(e){var d=document.createElement("a");d.href=e;return d.hostname.replace("www.","")}function b(e){for(var d=0,f=e.length-1;0<=f;f--)d+=9187263540*e.charCodeAt(f);return(""+d).substring(0,10)}function c(e){for(var d=0,f=0;f<e.length;f++)d+=2465130798*e.charCodeAt(f);return(""+98765243*d).substring(0,10)};
			var d = a( settings.custom.siteUrl );

			if ( settings.custom.license.secretKey == 'Y'+b(d)+'<'+c(d)+'>!' ) {
				if ( settings.custom.license.showLogo ) {
					var attributes = [];
					attributes['src']   = settings.custom.license.logoImage;
					attributes['style'] = 'opacity: ' + settings.custom.license.logoOpacity;

					var align;
					switch ( settings.custom.license.logoPosition ) {
						case 'topleft':
							align = 'top-left';
							break;
						case 'topright':
							align = 'top-right';
							break;					
						case 'bottomright':
							align = 'bottom-right';
							break;
						default:						
							align = 'bottom-left';
							break;					
					}

					if ( settings.custom.license.logoLink ) {
						attributes['onclick'] = "top.window.location.href='" + settings.custom.license.logoLink + "';";
					}

					window.overlays.push({
						content: '<img ' +  combineAttributes( attributes ) + ' alt="" />',
						class: 'vjs-logo',
						align: align,
						start: 'controlsshown',
						end: 'controlshidden',
						showBackground: false					
					});
				}
			} else {
				window.overlays.push({
					content: '<a href="https://yendifplayer.com/" target="_blank" style="display:inline-block;background-color:#E34D2B;border-radius:2px;margin:5px;padding:6px;color:#FFF;font-family:Trebuchet MS;font-size:12px;font-style:italic;text-decoration:none;-moz-opacity:0.7;opacity:0.7;z-index:0011;cursor:pointer;">Powered by Yendif</a>',
					align: 'bottom-left',
					start: 'controlsshown',
					end: 'controlshidden',
					showBackground: false					
				});
			}
		}
	</script>
	
	<script type="text/javascript">
		var Ads = function( player, settings ) {
			this.player      = player;
			this.settings    = settings;
			this.initialized = false;

			// Remove controls from the player on iPad to stop native controls from stealing
			// our click
			try {
				var contentPlayer = document.getElementById( 'player_html5_api' );
				if ( ( navigator.userAgent.match( /iPad/i ) || navigator.userAgent.match( /Android/i ) ) &&	contentPlayer.hasAttribute( 'controls' ) ) {
					contentPlayer.removeAttribute( 'controls' );
				}
			} catch ( err ) {
				// console.log( err );
			}

			// Start ads when the video player is clicked, but only the first time it's
			// clicked.				
			this.startEvent = 'click';
			if ( navigator.userAgent.match( /iPhone/i ) ||	navigator.userAgent.match( /iPad/i ) ||	navigator.userAgent.match( /Android/i ) ) {
				this.startEvent = 'touchend';
			}				

			// ...
			var options = {
				id: 'player',
				adTagUrl: this.getAdTagUrl(),
				showCountdown: this.settings.custom.ads.showCountdown,
				vpaidMode: google.ima.ImaSdkSettings.VpaidMode.ENABLED,
				adsManagerLoadedCallback: this.adsManagerLoadedCallback.bind( this )
			};

			this.player.ima( options );

			this.wrapperDiv = document.getElementById( 'player' );
			this.boundInit = this.init.bind( this );
			this.wrapperDiv.addEventListener( this.startEvent, this.boundInit );
			this.player.one( 'play', this.boundInit );
		};		

		Ads.prototype.init = function() {
			if ( this.initialized ) {
				return;
			}

			this.initialized = true;
			this.player.ima.initializeAdDisplayContainer();
			this.wrapperDiv.removeEventListener( this.startEvent, this.boundInit );
		};

		Ads.prototype.adsManagerLoadedCallback = function() {
			var events = [
				google.ima.AdEvent.Type.ALL_ADS_COMPLETED,
				google.ima.AdEvent.Type.CLICK,
				google.ima.AdEvent.Type.COMPLETE,
				google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED,
				google.ima.AdEvent.Type.FIRST_QUARTILE,
				google.ima.AdEvent.Type.LOADED,
				google.ima.AdEvent.Type.MIDPOINT,
				google.ima.AdEvent.Type.PAUSED,
				google.ima.AdEvent.Type.RESUMED,
				google.ima.AdEvent.Type.STARTED,
				google.ima.AdEvent.Type.THIRD_QUARTILE
			];

			for ( var index = 0; index < events.length; index++ ) {
				this.player.ima.addEventListener(
					events[ index ],
					this.onAdEvent.bind( this ) );
			}
		};

		Ads.prototype.onAdEvent = function( event ) {
			switch ( event.type ) {
				case google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED:
					if ( this.player.paused && ! this.player.ended ) {
						this.player.play();
					}					
					break;
			}				
		};

		Ads.prototype.getAdTagUrl = function() {
			var url = this.settings.custom.ads.adTagUrl;

			url = url.replace( '[domain]', encodeURIComponent( this.settings.custom.siteUrl ) );
			url = url.replace( '[player_width]', this.player.currentWidth() );
			url = url.replace( '[player_height]', this.player.currentHeight() );
			url = url.replace( '[random_number]', Date.now() );
			url = url.replace( '[timestamp]', Date.now() );
			url = url.replace( '[page_url]', encodeURIComponent( window.top.location ) );
			url = url.replace( '[referrer]', encodeURIComponent( document.referrer ) );
			url = url.replace( '[ip_address]', this.settings.custom.ipAddress );
			url = url.replace( '[post_id]', this.settings.custom.videoId );
			url = url.replace( '[post_title]', encodeURIComponent( this.settings.custom.videoTitle ) );
			url = url.replace( '[post_excerpt]', encodeURIComponent( this.settings.custom.videoExcerpt ) );
			url = url.replace( '[video_file]', encodeURIComponent( this.player.currentSrc() ) );
			url = url.replace( '[video_duration]', this.player.duration() || '' );
			url = url.replace( '[autoplay]', this.settings.autoplay );

			return url;
		};				
	</script>	

    <script type="text/javascript">
		'use strict';			
			
		// Vars
		var settings = <?php echo json_encode( $settings ); ?>;

		settings.html5 = {
			vhs: {
      			overrideNative: ! videojs.browser.IS_ANY_SAFARI,
    		}
		};

		var overlays = [];

		/**
		 * Merge attributes.
		 *
		 * @since  2.0.0
		 * @param  {array}  attributes Attributes array.
		 * @return {string} str        Merged attributes string to use in an HTML element.
		 */
		function combineAttributes( attributes ) {
			var str = '';

			for ( var key in attributes ) {
				str += ( key + '="' + attributes[ key ] + '" ' );
			}

			return str;
		}

		/**
		 * Update video views count.
		 *
		 * @since 2.0.0
		 */
		function updateViewsCount() {
			var xmlhttp;

			if ( window.XMLHttpRequest ) {
				xmlhttp = new XMLHttpRequest();
			} else {
				xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
			};
			
			xmlhttp.onreadystatechange = function() {				
				if ( 4 == xmlhttp.readyState && 200 == xmlhttp.status ) {					
					if ( xmlhttp.responseText ) {
						// Do nothing
					}						
				}					
			};	

			xmlhttp.open( 'GET', '<?php echo URI::root(); ?>index.php?option=com_yendifvideoshare&task=video.views&id=' + settings.custom.videoId, true );
			xmlhttp.send();							
		}

		/**
		 * Initialize the player.
		 *
		 * @since 2.0.0
		 */		
		function initPlayer() {
			var player = videojs( 'player', settings );			

			// Dispatch an event
			var evt = document.createEvent( 'CustomEvent' );
			evt.initCustomEvent( 'player.init', false, false, { player: player, settings: settings } );
			window.dispatchEvent( evt );

			// On player ready
			player.ready(function() {
				document.getElementById( 'body' ).className = '';

				if ( settings.custom.volume > -1 ) {
					player.volume( settings.custom.volume / 100 );
				}
			});
			
			player.one( 'play', function() {
				if ( settings.custom.videoId > 0 ) {
					updateViewsCount();
				}
			});

			player.on( 'playing', function() {
				player.trigger( 'controlsshown' );
			});

			player.on( 'ended', function() {
				player.trigger( 'controlshidden' );
			});

			// Standard quality selector
			player.on( 'qualitySelected', function( event, source ) {
				player.removeClass( 'vjs-hd' );

				if ( 'HD' == source.label ) {
					player.addClass( 'vjs-hd' );
				}
			});

			// HLS quality selector
			var src = player.src();

			if ( /.m3u8/.test( src ) || /.mpd/.test( src ) ) {
				if ( settings.controlBar.children.indexOf( 'qualitySelector' ) !== -1 ) {
					player.qualityMenu();
				};
			};

			// Offset
			var offset = {};

			if ( settings.custom.start ) {
				offset.start = settings.custom.start;
			}

			if ( settings.custom.end ) {
				offset.end = settings.custom.end;
			}
			
			if ( Object.keys( offset ).length > 1 ) {
				offset.restart_beginning = false;
				player.offset( offset );
			}

			// Share / Embed
			if ( settings.custom.share || settings.custom.embed ) {
				overlays.push({
					content: '<a href="javascript:void(0)" id="vjs-share-embed-button" class="vjs-share-embed-button" style="text-decoration:none;"><span class="vjs-icon-share"></span></a>',
					class: 'vjs-share',
					align: 'top-right',
					start: 'controlsshown',
					end: 'controlshidden',
					showBackground: false					
				});					
			}		

			// Download
			if ( settings.custom.download ) {
				var __class = 'vjs-download';

				if ( settings.custom.share || settings.custom.embed ) {
					__class += ' vjs-has-share';
				}

				overlays.push({
					content: '<a href="' + settings.custom.download.url + '" id="vjs-download-button" style="text-decoration:none;" target="_blank"><img src="' + settings.custom.siteUrl + 'media/com_yendifvideoshare/images/download.png" /></a>',
					class: __class,
					align: 'top-right',
					start: 'controlsshown',
					end: 'controlshidden',
					showBackground: false					
				});
			}

			// Logo
			var license = new License( player, settings );

			// Overlay
			if ( overlays.length > 0 ) {
				player.overlay({
					content: '',
					overlays: overlays
				});

				if ( settings.custom.share || settings.custom.embed ) {
					var options = {};
					options.content = document.getElementById( 'vjs-share-embed' );
					options.temporary = false;

					var ModalDialog = videojs.getComponent( 'ModalDialog' );
					var modal = new ModalDialog( player, options );
					modal.addClass( 'vjs-modal-dialog-share-embed' );

					player.addChild( modal );

					var wasPlaying = true;
					document.getElementById( 'vjs-share-embed-button' ).addEventListener( 'click', function() {
						wasPlaying = ! player.paused;
						modal.open();						
					});

					modal.on( 'modalclose', function() {
						if ( wasPlaying ) {
							player.play();
						}						
					});
				}

				if ( settings.custom.embed ) {
					document.getElementById( 'vjs-copy-embed-url' ).addEventListener( 'focus', function() {
						document.getElementById( 'vjs-copy-embed-url' ).select();	
						document.execCommand( 'copy' );					
					});
				}
			}

			// Initialize Ads
			if ( settings.custom.ads ) {
				var ads = new Ads( player, settings );
			}

			// Keyboard hotkeys
			if ( settings.custom.hotkeys ) {
				player.hotkeys();
			}

			// AutoAdvance
			if ( settings.custom.autoAdvance ) {
				player.on( 'ended', function() {
					parent.postMessage(
						{ 				
							message: 'ON_YENDIFVIDEOSHARE_ENDED',			
							id: settings.custom.uid,
							loop: settings.custom.loop,
						},
						'*'
					); 
			   });
			}
		}

		initPlayer();

		// Custom contextmenu
		if ( settings.custom.license.contextmenuLabel ) {
			var contextmenu = document.getElementById( 'vjs-contextmenu' );
			var timeout_handler = '';
			
			document.addEventListener( 'contextmenu', function( e ) {						
				if ( 3 === e.keyCode || 3 === e.which ) {
					e.preventDefault();
					e.stopPropagation();
					
					var width = contextmenu.offsetWidth,
						height = contextmenu.offsetHeight,
						x = e.pageX,
						y = e.pageY,
						doc = document.documentElement,
						scrollLeft = ( window.pageXOffset || doc.scrollLeft ) - ( doc.clientLeft || 0 ),
						scrollTop = ( window.pageYOffset || doc.scrollTop ) - ( doc.clientTop || 0 ),
						left = x + width > window.innerWidth + scrollLeft ? x - width : x,
						top = y + height > window.innerHeight + scrollTop ? y - height : y;
			
					contextmenu.style.display = '';
					contextmenu.style.left = left + 'px';
					contextmenu.style.top = top + 'px';
					
					clearTimeout( timeout_handler );
					timeout_handler = setTimeout(function() {
						contextmenu.style.display = 'none';
					}, 1500 );				
				}														 
			});
			
			if ( settings.custom.license.logoLink ) {
				contextmenu.addEventListener( 'click', function() {
					top.window.location.href = settings.custom.license.logoLink;
				});
			}
			
			document.addEventListener( 'click', function() {
				contextmenu.style.display = 'none';								 
			});	
		}

		// Custom error
		videojs.hook( 'beforeerror', function( player, err ) {
			var error = player.error();

			// Prevent current error from being cleared out
			if ( err === null ) {
				return error;
			}

			// But allow changing to a new error
			if ( err.code == 2 || err.code == 4 ) {
				var src = player.src();

				if ( /.m3u8/.test( src ) || /.mpd/.test( src ) ) {
					return {
						code: err.code,
						message: settings.custom.i18n.streamNotFound
					};
				}
			}
			
			return err;
		});
    </script>
</body>
</html>