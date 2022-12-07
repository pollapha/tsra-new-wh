<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
include('../php/connection.php');
$type  = intval($_REQUEST['type']);
$cBy = $_SESSION['xxxID'];
if($type == 1)
{
	toArrayStringOne($mysqli->query("select docType FROM tbl_header_replenish where cby='$cBy' and status='' group by docType"),1);
}

$mysqli->close();
exit();
?>