<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('start.php');
session_start();
include('php/connection.php');

if(!isset($_POST['user'])) closeDBT($mysqli,2,'กรุณากรอกข้อมูลให้ถูกต้อง');
if(!isset($_POST['pass'])) closeDBT($mysqli,2,'กรุณากรอกข้อมูลให้ถูกต้อง');
$user  = $mysqli->real_escape_string($_POST['user']);
$pass  = $mysqli->real_escape_string($_POST['pass']);
$sql = "select user_id,user_pass from tbl_user where user_name='$user' and user_status=1 limit 1;";
if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'Error Code 1');
if($re1->num_rows == 0) closeDBT($mysqli,2,'user หรือ password ไม่ถูกต้อง');
$row1 = $re1->fetch_array(MYSQLI_ASSOC);
$user_id = $row1['user_id'];
$user_pass = $row1['user_pass'];
if($user_pass != hash('sha512',$pass))  closeDBT($mysqli,2,'user หรือ password ไม่ถูกต้อง');
$sql = "select t1.user_id,concat(t1.user_fName,' ',t1.user_lname)user_fName,t1.user_image,t1.user_permission,concat('{',group_concat(concat('\"',t3.menu_menuUse,'\"',':[',t2.role_viwe,',',t2.role_insert,',',t2.role_update,',',t2.role_del,']') separator ','),'}')role
from tbl_user t1 left join tbl_rolemaster t2 on t1.user_permission=t2.role_name 
left join tbl_menu t3 on t2.menu_id = t3.menu_id
where t1.user_id=$user_id group by t1.user_id;";
if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'Error Code 2');
if($re1->num_rows == 0) closeDBT($mysqli,2,'user หรือ password ไม่ถูกต้อง');
$row1 = $re1->fetch_array(MYSQLI_ASSOC);
$_SESSION['xxxID']    		= $row1['user_id'];
$_SESSION['xxxFName'] 		= $row1['user_fName'];
$_SESSION['xxxRole']    	= json_decode($row1['role']);
$_SESSION['xxxPermission']  = $row1['user_permission'];
$_SESSION['xxxImage']    	= $row1['user_image'];
closeDBT($mysqli,1,'[]');
?>