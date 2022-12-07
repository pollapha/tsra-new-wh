<?php
include('../../php/connection.php');

//$mydir = 'D:\\report\\tsra_wh_label\\';
//$mydir = 'C:/report/tsra_wh_label/';
$mydir = 'C:/tsra_wh_label/';
echo realpath($mydir), PHP_EOL.'<br>';
  
$myfiles = array_diff(scandir($mydir), array('.', '..'));
print_r($myfiles); 