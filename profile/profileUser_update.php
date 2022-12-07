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

    if($re1 = $mysqli->query("SELECT concat('images/user/',user_image),user_fName,user_lname from tbl_user where user_id=$cBy"))
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
else if($type == 2)
{
    $dnote = $mysqli->real_escape_string($_REQUEST['obj']);
    $re1 = getDnote($dnote,$mysqli);
    echo '{ch:1,data:';
    toArrayStringAddNumberRow($re1,1);
    echo '}';
}

function getDnote($dnote,$mysqli)
{

}
$mysqli->close();
exit();
?>