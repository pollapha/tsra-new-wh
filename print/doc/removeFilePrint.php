<?php
$dir = 'C:/tsra_wh_label/';

$myfiles = array_diff(scandir($dir), array('.', '..'));
//print_r($myfiles);
$filename = $myfiles[2];
$file1 = $dir . $filename;

unlink(urldecode($file1));
//echo ('delete');
