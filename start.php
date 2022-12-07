<?php
$path = $_SERVER['PHP_SELF'];
ini_set('session.save_path',$_SERVER['DOCUMENT_ROOT'].  substr($path, 0,strpos($path, '/', 1)) .'/sessions');
$TTV_PROJECT_NAME = 'tsra-wh';
// $TTV_PROJECT_NAME = 'abt_packing';
$TTV_CACHE_PAGE_JS = $TTV_PROJECT_NAME.'pageJS';
$TTV_CACHE_OJBJECT_DATA_PAGE = $TTV_PROJECT_NAME.'checkPageDataObject';
$DEBUG_MODE = 1;//true-false
$APCU_MODE = 0;//true-false
?>