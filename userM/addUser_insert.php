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
else if($_SESSION['xxxRole']->{'add_user'}[1] == 0 && $_SESSION['xxxRole']->{'add_user'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}
include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$roleCh = $_SESSION['xxxPermission'];
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);

if($type == 1)
{

	$userName = $mysqli->real_escape_string(trim($obj['userName']));
	$fName = 	$mysqli->real_escape_string(trim($obj['fName']));
	$lName = 	$mysqli->real_escape_string(trim($obj['lName']));
	$role = 	$mysqli->real_escape_string(trim($obj['role']));
	if($mysqli->query("SELECT 1 from tbl_user where user_name = BINARY '$userName'")->num_rows == 0)
	{

		$mysqli->query("INSERT INTO tbl_user(user_name,user_pass,user_fName,user_lname,user_image,user_status,user_permission,user_creationDate,user_createBy) 
		values('$userName','d404559f602eab6fd602ac7680dacbfaadd13630335e951f097af3900e9de176b6db28512f2e000b9d04fba5133e8b1c6e8df59db3a8ab9d60be4b97cc9e81db','$fName','$lName','icon_index.jpg','1','$role',now(),'$cBy');");

		if($roleCh == 'ADMIN')
		{
			$sql = "SELECT concat('images/user/',user_image),user_name,user_fName,user_lname,user_permission,user_id,user_status from tbl_user order by user_id desc";
		}
		else if($roleCh == 'SUPPORT')
		{
			$sql = "SELECT concat('images/user/',user_image),user_name,user_fName,user_lname,user_permission,user_id,user_status from tbl_user where user_permission not in('ADMIN') order by user_id desc";
		}
		else
		{
			$sql = "SELECT concat('images/user/',user_image),user_name,user_fName,user_lname,user_permission,user_id,user_status from tbl_user where user_permission not in('ADMIN','SUPPORT') order by user_id desc";
		}
    	if($re1 = $mysqli->query($sql))
    	{
    	    if($re1->num_rows>0)
    	    {
    	        echo '{ch:1,data:';
    	        toArrayStringAddNumberRow($re1,1);
    	        echo '}';
    	    }
    	    else echo "{ch:2,data:'ไม่พบ $obj ในระบบ'}";
    	}
    	else echo "{ch:2,data:'โคิดผิด 1'}";
	}
	else
	{
		echo "{ch:2,data:'มี User นี้อยู่แล้ว'}";
	}
	
}
$mysqli->close();
exit();
?>