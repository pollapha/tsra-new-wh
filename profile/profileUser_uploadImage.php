<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
include('../php/connection.php');
// $type  = intval($_REQUEST['type']);
$cBy = $_SESSION['xxxID'];
if(move_uploaded_file($_FILES["upload"]["tmp_name"],"../images/user/".$_FILES["upload"]["name"]))
{
    $extension = explode('.',$_FILES["upload"]["name"]);
    $len = count($extension);
    $extension = $extension[$len-1];
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
    $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'.'.$extension; 
    rename("../images/user/".$_FILES["upload"]["name"],"../images/user/".$fileName);

    $mysqli->query("update tbl_user set user_image='$fileName'  where user_id=$cBy");
    $_SESSION['xxxImage'] = $fileName;
    if($re1 = $mysqli->query("SELECT concat('images/user/',user_image),user_fName,user_lname,concat(user_fName,' ',user_lname)user_fName from tbl_user where user_id=$cBy"))
    {
        if($re1->num_rows>0)
        {
            echo '{"status":"server","sname":';
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