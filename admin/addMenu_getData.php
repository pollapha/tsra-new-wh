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
$role = $_SESSION['xxxPermission'];
$type  = intval($_REQUEST['type']);

if($type==1)
{
	echo '{ch:1,data:';
	toArrayStringAddNumberRow($mysqli->query("SELECT menu_group,menu_header,menu_menuId,menu_menuName,menu_menuUse,menu_url,menu_for, REPLACE(menu_menuId, '.','')*1 sort
	from tbl_menu order by menu_group desc,sort desc"),1);
	echo '}';
}

$mysqli->close();
exit();

?>
