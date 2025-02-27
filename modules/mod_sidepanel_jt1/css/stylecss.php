<?php
 /**
 * @package SidePanel Module for Joomla 3.0 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
header('content-type: text/css');
$moduleId = $_GET['id'];
?>

#sidepanel_jt<?php echo $moduleId ?> {
	min-height:100px;
	height:auto !important;
	height:100px;
	z-index:2000;
	box-shadow: none;
	-moz-box-shadow:none;
	-webkit-box-shadow:none;
}


#sidepanel_jt<?php echo $moduleId ?> h2.title {
	margin-top:5px;
}
#sidepanel_jt<?php echo $moduleId ?> .content {
	clear:both;
	overflow:auto!important;
}
.ui-slideouttab-panel{padding:0;}
.ui-slideouttab-handle {
    background-color: white;
    padding: 0.6em 0.6em 0.6em 0.6em;
    box-sizing: border-box;
}
.ui-slideouttab-panel {
	display: block;
	position: fixed!important;
    border: 0px;
}
/* This class is added after the tabs are initialised, otherwise the user sees the 
   tabs slide out of the way when the page is initialised. */
.ui-slideouttab-ready {
	transition: transform 0.5s ease 0s;
}

/* Hide tabs and panels when printed. */
@media print {
    .ui-slideouttab-panel {
        display: none;
    }
}

/* Tab handles */
.ui-slideouttab-handle {
    display: block;
    position: absolute;
    cursor: pointer;
    color: white;
	font-size:14px!important;
	text-decoration:none;
   
}
.ui-slideouttab-handle:hover,.ui-slideouttab-handle:focus{text-decoration:none;}
.ui-slideouttab-handle-image {
    transform: rotate(0);
}

/* turn font awesome icon in a tab upright */
.ui-slideouttab-left .ui-slideouttab-handle>.fa, 
.ui-slideouttab-right .ui-slideouttab-handle>.fa { 
    transform: rotate(90deg);
}
.ui-slideouttab-handle>.fa {
    margin-left: 0.5em;
}

/* apply rounded corners if handle has the -rounded class */
.ui-slideouttab-top .ui-slideouttab-handle-rounded,
.ui-slideouttab-left .ui-slideouttab-handle-rounded {
    border-radius: 0 0 4px 4px;
}
.ui-slideouttab-right .ui-slideouttab-handle-rounded, 
.ui-slideouttab-bottom .ui-slideouttab-handle-rounded {
    border-radius: 4px 4px 0 0;
}

#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-ready .handle<?php echo $moduleId ?>.imagebg.ui-slideouttab-handle {padding:0;background-color:transparent!important;}
div.phpdebugbar{z-index:998!important}