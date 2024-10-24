<?php
/***
 * @package     mod_jt_contentslider
 * @copyright   Copyright (C) 2007 - 2021 http://www.joomlatema.net, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author     	JoomlaTema.Net
 * @link 		http://www.joomlatema.net
 ***/

namespace Joomla\Module\JTContentSlider\Site\helper;

defined('_JEXEC') or die;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\Componenthelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\Arrayhelper;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Image\Image;
use Joomla\CMS\HTML\HTMLhelper;
use Joomla\CMS\Language\Text;


\JLoader::register('ContenthelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

const IMAGE_HANDLERS = [
    IMAGETYPE_JPEG => [
        'load' => 'imagecreatefromjpeg',
        'save' => 'imagejpeg',
        'quality' => 100
    ],
    IMAGETYPE_PNG => [
        'load' => 'imagecreatefrompng',
        'save' => 'imagepng',
        'quality' => 7
    ],
    IMAGETYPE_GIF => [
        'load' => 'imagecreatefromgif',
        'save' => 'imagegif'
    ],
	IMAGETYPE_WEBP => [
        'load' => 'imagecreatefromwebp',
        'save' => 'imagewebp',
        'quality' => 90
    ],
];
 
 class ThumbImage
{
function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];

    switch($mime){
        case 'image/gif':
            $image_create = "imagecreatefromgif";
            $image = "imagegif";
            break;

        case 'image/png':
            $image_create = "imagecreatefrompng";
            $image = "imagepng";
            $quality = 7;
            break;

        case 'image/jpeg':
            $image_create = "imagecreatefromjpeg";
            $image = "imagejpeg";
            $quality = 80;
            break;
		case 'image/webp':
            $image_create = "imagecreatefromwebp";
            $image = "imagewebp";
            $quality =80;
            break;

        default:
            return false;
            break;
    }
/////////////////////////////////*******************///////////////////
if ($max_height == null) {

        // get width to height ratio
        $ratio = $width / $height;

        // if is portrait
        // use ratio to scale height to fit in square
        if ($width > $height) {
            $max_height = floor($max_width / $ratio);
        }
        // if is landscape
        // use ratio to scale width to fit in square
        else {
            $max_height = $max_width;
            $max_width = floor($max_width * $ratio);
        }
    }

////////////////////**************************////////////////////////

    $dst_img = imagecreatetruecolor($max_width, $max_height);
    $src_img = $image_create($source_file);
	

    $width_new = $height * $max_width / $max_height;
    $height_new = $width * $max_height / $max_width;
    //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
    if($width_new > $width){
        //cut point by height
        $h_point = (($height - $height_new) / 2);
        //copy image
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
    }else{
        //cut point by width
        $w_point = (($width - $width_new) / 2);
        imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
    }

if ($image = "imagegif"){
 $image($dst_img, $dst_dir);}
 else{
    $image($dst_img, $dst_dir, $quality);
}
    if($dst_img)imagedestroy($dst_img);
    if($src_img)imagedestroy($src_img);
}


/**
 * @param $src - a valid file location
 * @param $dest - a valid file target
 * @param $targetWidth - desired output width
 * @param $targetHeight - desired output height or null
 */
function createThumbnail($src, $dest, $targetWidth, $targetHeight) {

    // 1. Load the image from the given $src
    // - see if the file actually exists
    // - check if it's of a valid image type
    // - load the image resource

    // get the type of the image
    // we need the type to determine the correct loader
    $type = exif_imagetype($src);

    // if no valid type or no handler found -> exit
    if (!$type || !IMAGE_HANDLERS[$type]) {
        return null;
    }

    // load the image with the correct loader
    $image = call_user_func(IMAGE_HANDLERS[$type]['load'], $src);

    // no image found at supplied location -> exit
    if (!$image) {
        return null;
    }


    // 2. Create a thumbnail and resize the loaded $image
    // - get the image dimensions
    // - define the output size appropriately
    // - create a thumbnail based on that size
    // - set alpha transparency for GIFs and PNGs
    // - draw the final thumbnail

    // get original image width and height
    $width = imagesx($image);
    $height = imagesy($image);

    // maintain aspect ratio when no height set
  if ($targetHeight == null) {

        // get width to height ratio
        $ratio = $width / $height;

        // if is portrait
        // use ratio to scale height to fit in square
        if ($width > $height) {
            $targetHeight = floor($targetWidth / $ratio);
        }
        // if is landscape
        // use ratio to scale width to fit in square
        else {
            $targetHeight = $targetWidth;
            $targetWidth = floor($targetWidth * $ratio);
        }
    }

    // create duplicate image based on calculated target size
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

    // set transparency options for GIFs and PNGs
    if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {

        // make image transparent
        imagecolortransparent(
            $thumbnail,
            imagecolorallocate($thumbnail, 0, 0, 0)
        );

        // additional settings for PNGs
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
    }

    // copy entire source image to duplicate image and resize
    imagecopyresampled(
        $thumbnail,
        $image,
        0, 0, 0, 0,
        $targetWidth, $targetHeight,
        $width, $height
    );


    // 3. Save the $thumbnail to disk
    // - call the correct save method
    // - set the correct quality level

    // save the duplicate version of the image to disk
	
	if ($type == IMAGETYPE_GIF) {
    return call_user_func(
        IMAGE_HANDLERS[$type]['save'],
        $thumbnail,
        $dest
    );
}
else {
    return call_user_func(
        IMAGE_HANDLERS[$type]['save'],
        $thumbnail,
        $dest,
        IMAGE_HANDLERS[$type]['quality']
    );
	}
}
}
abstract class jtcontentsliderhelper
{
	/**
	 * Retrieve a list of article
	 *
	 * @param   Registry       $params  The module parameters.
	 * @param   ArticlesModel  $model   The model.
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	public static function getList(Registry $params, ArticlesModel $model)
	{
		// Get the Dbo and User object
		$db   = Factory::getDbo();
		$user = Factory::getUser();

		// Set application parameters in model
		$app       = Factory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', (int) $params->get('num_intro_skip', 0));
		$model->setState('list.limit', (int) $params->get('count', 5));
		$model->setState('filter.published', 1);

		// This module does not use tags data
		$model->setState('load_tags', false);

		// Access filter
		$access     = !Componenthelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels($user->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', array()));

		// User filter
		$userId = $user->get('id');

		switch ($params->get('user_id'))
		{
			case 'by_me' :
				$model->setState('filter.author_id', (int) $userId);
				break;
			case 'not_me' :
				$model->setState('filter.author_id', $userId);
				$model->setState('filter.author_id.include', false);
				break;

			case 'created_by' :
				$model->setState('filter.author_id', $params->get('author', array()));
				break;

			case '0' :
				break;

			default:
				$model->setState('filter.author_id', (int) $params->get('user_id'));
				break;
		}

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

		// Featured switch
		$featured = $params->get('show_featured', '');

		if ($featured === '')
		{
			$model->setState('filter.featured', 'show');
		}
		elseif ($featured)
		{
			$model->setState('filter.featured', 'only');
		}
		else
		{
			$model->setState('filter.featured', 'hide');
		}

		// Set ordering
		$order_map = array(
			'a.created'  => 'a.created',
			'a.publish_up'  => 'a.publish_up',
			'a.ordering'=> 'a.ordering',
			'fp.ordering'  => 'fp.ordering',
			'a.hits'  => 'a.hits',
			'a.title'  => 'a.title',
			'a.id'  => 'a.id',
			'a.alias'  => 'a.alias',
			'modified'  => 'a.modified',
			'a.publish_down'  => 'a.publish_down',
			'rating_count'  => 'rating_count',
			
			'random' => $db->getQuery(true)->Rand(),
		);

		$ordering = Arrayhelper::getValue($order_map, $params->get('ordering'), 'a.publish_up');
		$dir      = 'DESC';

		$model->setState('list.ordering', $ordering);
		$model->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

		$items = $model->getItems();

		foreach ($items as &$item)
		{
			$item->slug    = $item->id . ':' . $item->alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = Route::_(\ContenthelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
			else
			{
				$item->link = Route::_('index.php?option=com_users&view=login');
			}
		}

		return $items;
	}
	public static function getDate($show_date, $show_date_type, $created_date, $custom_date_format)
	{
		$date = new Date($created_date);
		if($show_date==1)
		{
			switch($show_date_type) {
			case 1:
				echo "<span class=\"jtc_introdate\">";
				echo HTMLhelper::_('date', $date, Text::_('l, d F Y H:i'));
				echo "<br/></span>";
				break;
			case 2:
				echo "<span class=\"jtc_introdate\">";
				echo HTMLhelper::_('date', $date, Text::_('d F Y'));
				echo "<br/></span>";
				break;
			case 3:
				echo "<span class=\"jtc_introdate\">";
				echo HTMLhelper::_('date', $date, Text::_('H:i'));
				echo "<br/></span>";
				break;
			case 4:
				echo "<span class=\"jtc_introdate\">";
				echo HTMLhelper::_('date', $date, Text::_('D, M jS Y'));
				echo "<br/></span>";
				break;
			case 5:
				echo "<span class=\"jtc_introdate\">";
				echo HTMLhelper::_('date', $date, Text::_('l, F jS Y H:i'));
				echo "<br/></span>";
				break;
			case 6:
				echo "<span class=\"jtc_introdate\">";
				echo HTMLhelper::_('date', $date, Text::_($custom_date_format));
				echo "<br/></span>";
				break;
			default:
				echo "<span class=\"jtc_introdate\">";
				echo HTMLhelper::_('date', $date, Text::_('l, d F Y'));
				echo "<br/></span>";
				break;
			}
		}
	}	
	
	public static function getOrgImage($item_id, $article_images,$title,$introtext,$modulebase) {
		$thumb_name = str_replace([':', '\\', '/', '*', '\'', '"'], '', str_replace(' ', '_', strtolower($item_id)) );
		if ($article_images) {
			// Thumbnail is not exist
			$images = json_decode($article_images);
			// Find Article's Image
			if (!empty($images->image_intro) ) { 
				$orig_image = strstr($images->image_intro, '#', true);
				// Added JCE PRO support
				if ($orig_image == NULL) {
					$orig_image = $images->image_intro;
				}
			} elseif (empty($images->image_intro) && !empty($images->image_fulltext) ) { 
				$orig_image = strstr($images->image_fulltext, '#', true);
				// Added JCE PRO support
				if ($orig_image == NULL) {
					$orig_image = $images->image_fulltext;
				}
			} else {
				// Find first image in the article
				$html = $introtext;
				$pattern = '/<img .*?src="([^"]+)"/si';

				if ( preg_match($pattern, $html, $match) ) {
					$orig_image = $match[1];
				} else {
					$orig_image = "";
				}
			}
			
			/// Replace %20 character for image's name with space
			$orig_image = str_replace('%20', ' ', $orig_image);

			if(strpos($orig_image, 'http') !== false) {
				$orig_image = "";
			}

			// If article contains an image then generate a thumbnail
			if($orig_image != ""){
			$orig_image = $orig_image;
			}
			else {
			$orig_image =Uri::root().'/modules/'.$modulebase.'/tmpl/assets/images/default.jpg';
			}
			return $orig_image;
			}
			}
			/////////////////////////////////////////
			
			public static function getCaption($item_id, $article_images,$introtext,$use_caption) {
		
		$caption='';
		if ( $article_images && $use_caption) {
			// Thumbnail is not exist
			$images = json_decode($article_images);
			// Find Article's Image
			if (!empty($images->image_intro) ) { 
				$caption = $images->image_intro_caption;
				if(!$caption){
						$caption = $images->image_fulltext_caption;
				}
				
			} 
			return $caption;
			}
			}
			
			public static function substring($text, $strip_tags, $allowed_tags = '', $replacer = '...', $introtext_truncate = 200)
{
    if ($strip_tags) {
        $text = strip_tags($text, $allowed_tags);
    } else {
        $text = $text;
    }

    if (function_exists('mb_strlen')) {
        if (mb_strlen($text) < $introtext_truncate) return $text;
        $text = mb_substr($text, 0, (int)$introtext_truncate);
    } else {
        if (strlen($text) < $introtext_truncate) return $text;
        $text = substr($text, 0, $introtext_truncate);
    }

    return $text . $replacer;
}


	public static function substrword($text, $strip_tags, $allowed_tags = '', $replacer = '...',  $introtext_truncate = 200) {
		 if ($strip_tags) {
        $text = strip_tags($text, $allowed_tags);
    } else {
        $text = $text;
    }
	
		$tmp = explode(" ", $text);

		if (count($tmp) < $introtext_truncate)
			return $text;

		$text = implode(" ", array_slice($tmp, 0, $introtext_truncate)) . $replacer;

		return $text;
	}
	public static function getThumbnail($item_id, $article_images, $thumb_folder, $show_default_thumb, $thumb_width,$thumb_height,$title,$introtext,$modulebase) {
		$thumb_name = str_replace([':', '\\', '/', '*', '\'', '"'], '', str_replace(' ', '_', strtolower($item_id)) );
		$thumb_name = md5($thumb_width.$thumb_height).'_'. $thumb_name;
		if (!File::exists(JPATH_BASE.$thumb_folder.$thumb_name.'.jpg')) {
			// Thumbnail is not exist
			$images = json_decode($article_images);
			// Find Article's Image
			if (!empty($images->image_intro) ) { 
				$orig_image = strstr($images->image_intro, '#', true);
				// Added JCE PRO support
				if ($orig_image == NULL) {
					$orig_image = $images->image_intro;
				}
			} else if (empty($images->image_intro) && !empty($images->image_fulltext) ) { 
				$orig_image = strstr($images->image_fulltext, '#', true);
				// Added JCE PRO support
				if ($orig_image == NULL) {
					$orig_image = $images->image_fulltext;
				}
			} else {
				// Find first image in the article
				$html = $introtext;
				$pattern = '/<img .*?src="([^"]+)"/si';

				if ( preg_match($pattern, $html, $match) ) {
					$orig_image = $match[1];
				} else {
					$orig_image = "";
				}
			}
			
			// Replace %20 character for image's name with space
			$orig_image = str_replace('%20', ' ', $orig_image);

			if(strpos($orig_image, 'http') !== false) {
				$orig_image = "";
			}

			// If article contains an image then generate a thumbnail
			if($orig_image != ""){				
				$thumb_img=new ThumbImage($orig_image);
				//$org_img = jtcontentsliderhelper::getOrgImage($item->id, $item->images,$item->title,$item->introtext,$modulebase);				
				$imgext=explode('.', $orig_image);
				$ext = end($imgext);
				$thumb_img->createThumbnail($orig_image,JPATH_BASE.$thumb_folder.$thumb_name.'.'.$ext,$thumb_width,$thumb_height);
				$thumb_img = '<img class="jtcs-image" src="'.Uri::root().$thumb_folder.$thumb_name.'.'.$ext.'" alt="'.$title.'"width="'.$thumb_width.'"/>';	
				
			}  else {

			// If article doesn't have any image then use default image or leave it empty
				if($show_default_thumb){$default_thumb=Uri::root().'/modules/'.$modulebase.'/tmpl/assets/images/default.jpg';
				$thumb_img=new ThumbImage($default_thumb);				
				$thumb_img->createThumbnail($default_thumb,JPATH_BASE.$thumb_folder.$thumb_name.'.jpg',$thumb_width,$thumb_height);
				//$thumb_img->resize_crop_image($thumb_width, $thumb_height, $default_thumb, JPATH_BASE.$thumb_folder.$thumb_name, $quality = 80);
				$thumb_img = '<img class="jtcs-image" src="'.Uri::root().$thumb_folder.$thumb_name.'.jpg" alt="'.$title.'"width="'.$thumb_width.'"/>';	
				
				
					//$thumb_img = '<img class="jtcs-image" src="'.Uri::root().'/modules/'.$modulebase.'/tmpl/assets/images/default.jpg" style="width:'.$thumb_width.'px;" alt="'.$title.'"  />';
					
					
				} else {
					$thumb_img="";
				}
			}
		} else {
			// Thumbnail is exists
			$thumb_img = '<img class="jtcs-image" src="'.Uri::root().$thumb_folder.$thumb_name.'.jpg" width="'.$thumb_width.'" alt="'.$title.'" />';
		}
		return $thumb_img;
	}
}
