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
else if($_SESSION['xxxRole']->{'role'}[2] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}
include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);

if($type == 1)
{
	for($i=0,$len=count($obj);$i<$len;$i++)
	{
		$id = $obj[$i]['id'];
		$d1 = $obj[$i]['d1'];
		$d2 = $obj[$i]['d2'];
		$d3 = $obj[$i]['d3'];
		$d4 = $obj[$i]['d4'];
		$mysqli->query("UPDATE tbl_rolemaster set role_viwe=$d1,role_insert=$d2,role_update=$d3,role_del=$d4,role_updateDate=now(),role_updateBy=$cBy where role_id=$id");

	}
	echo "{ch:1,data:'บันทึกสำเร็จ'}";
}
$mysqli->close();
exit();
?>