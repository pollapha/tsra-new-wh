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
else if($_SESSION['xxxRole']->{'role'}[1] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}
include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);

if($type == 0)
{
	
}
else if($type == 1)
{
	$obj = $mysqli->real_escape_string(trim(strtoupper($obj)));
	if($mysqli->query("SELECT 1 from tbl_rolemaster where role_name='$obj' limit 1")->num_rows == 0)
	{
		$mysqli->query("INSERT into tbl_rolemaster(role_name,role_viwe,role_insert,role_update,role_del,role_creationDate,role_createBy,menu_id)
		select '$obj',0,0,0,0,now(),$cBy,menu_id from tbl_rolemaster where role_name='ADMIN';");
		echo "{ch:1,data:'บันทึกสำเร็จ'}";
	}
	else
	{
		echo "{ch:2,data:'$obj มีในระบบอยู่แล้ว'}";
	}
}

$mysqli->close();
exit();
?>