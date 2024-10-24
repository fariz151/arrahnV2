<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT ^ E_DEPRECATED ^ E_WARNING);
use Joomla\CMS\Router\Route;

jimport('joomla.form.formfield');


$delete = filter_input(INPUT_POST, 'delete', FILTER_SANITIZE_STRING);
if(isset($delete) && !empty($delete)){
    // Your code here

//if(isset($_POST['delete']) && !empty($_POST['delete'])){
    //find the file
	$folder_path = JPATH_SITE .'/cache/mod_jt_contentslider';
   
// List of name of files inside
// specified folder
$files = glob($folder_path.'/*.{jpg,png,gif}', GLOB_BRACE);

	foreach ( $files as  $file){
    if(is_file($file)) 
    {
        // Delete the given file
        unlink($file); 
}
 }
 }

class JFormFieldCache extends JFormField {
	protected $type = 'Cache';
	// getLabel() left out
	public function getInput() {
		return '
<form id="formGoBack" action="" method="post">
    <input type="hidden" name="formGoBack" class="btn btn-primary" ></input>
</form>
 <form method="post">
  <input type="hidden" value="delete" name="delete" />
 <input  class="button-cancel btn btn-danger" type="submit" value="'.JText::_('MOD_JTCS_THUMBNAIL_DELETE_LABEL').'" /></input>
 </form>';
	}
}
      
?>