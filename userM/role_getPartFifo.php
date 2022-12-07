<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(empty($_SESSION['xxxID']))
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}

include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);

if($type == 0)
{
	$obj = $mysqli->real_escape_string(trim(strtoupper($obj)));
	getData($mysqli,$obj);
}
else if($type == 1)
{

}

function getData($mysqli,$part)
{
	echo '{ch:1,data:';
	toArrayStringAddNumberRow($mysqli->query("select partNo,qty,revision,fifo,replace(fifo,'-', '')*1 sortFifo,id FROM tbl_inventory where partNo='$part' and area='STORAGE' and ref=0 and _use=1 order by sortFifo"),1);
	echo '}';
}

$mysqli->close();
exit();

?>