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
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);

if($type == 1)
{
    if(!isset($obj['pass1'])) closeDBT($mysqli,2,'กรุณากรอกข้อมูลให้ถูกต้อง');
    if(!isset($obj['pass2'])) closeDBT($mysqli,2,'กรุณากรอกข้อมูลให้ถูกต้อง');
    if(!isset($obj['pass3'])) closeDBT($mysqli,2,'กรุณากรอกข้อมูลให้ถูกต้อง');

    $pass1 =    $mysqli->real_escape_string(trim($obj['pass1']));
    $pass2 =    $mysqli->real_escape_string(trim($obj['pass2']));
    $pass3 =    $mysqli->real_escape_string(trim($obj['pass3']));

    if($pass2 != $pass3) closeDBT($mysqli,2,'passwordใหม่ ไม่ตรงกัน');

    $sql = "SELECT user_id,user_pass from tbl_user where user_id =$cBy and user_status=1 limit 1;";
    if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'Error Code 1');
    if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบ user ในระบบ');
    $row1 = $re1->fetch_array(MYSQLI_ASSOC);
    $user_pass = $row1['user_pass'];

    if($user_pass != hash('sha512',$pass1)) closeDBT($mysqli,2,'password เก่าไม่ถูกต้อง');
    if(hash('sha512',$pass2) == hash('sha512',$pass1)) closeDBT($mysqli,2,'password เก่าและใหม่เหมื่อนกัน');
    $newPass = hash('sha512',$pass2);
    $mysqli->query("UPDATE tbl_user set user_pass='$newPass' where user_id =$cBy limit 1");
    echo json_encode(array('ch'=>1,'data'=>'[]'));
}
$mysqli->close();
exit();
?>