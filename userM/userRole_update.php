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
else if($_SESSION['xxxRole']->{'userRole'}[2] == 0 && $_SESSION['xxxRole']->{'userRole'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}
include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$role = $_SESSION['xxxPermission'];
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);
$param  = $_POST['param'];

if($type == 1)
{

	$mysqli->query("UPDATE tbl_user set user_permission='$param' where user_id in($obj)");
	if($role == 'ADMIN')
	{
		$sql = "SELECT concat('images/user/',user_image),user_fName,user_lname,user_permission,user_id from tbl_user";
	}
	else if($role == 'SUPPORT')
	{
		$sql = "SELECT concat('images/user/',user_image),user_fName,user_lname,user_permission,user_id from tbl_user where user_permission not in('ADMIN')";
	}
	else
	{
		$sql = "SELECT concat('images/user/',user_image),user_fName,user_lname,user_permission,user_id from tbl_user where user_permission not in('ADMIN','SUPPORT')";
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
$mysqli->close();
exit();
?>