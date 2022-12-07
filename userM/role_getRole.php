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

if($type == 0)
{
	$obj = $mysqli->real_escape_string(trim(strtoupper($obj)));
	getData($mysqli,$obj);
}
else if($type == 1)
{
	$sql = '';
	if($role == 'ADMIN') $sql ="SELECT role_name from tbl_rolemaster  group by role_name";
	else if($role == 'SUPPORT')	$sql ="SELECT role_name from tbl_rolemaster where role_name not in('ADMIN') group by role_name";
	else $sql ="SELECT role_name from tbl_rolemaster where role_name not in('ADMIN','SUPPORT') group by role_name";
	toArrayStringOne($mysqli->query($sql),1);
}
else if($type == 2)
{
	$obj  = $_REQUEST['obj'];
	$obj = $mysqli->real_escape_string(trim(strtoupper($obj)));
	$sql = '';
	if($role == 'ADMIN')
	{

		$sql = "SELECT group_concat(data separator ',')data from 
		(select t2.forG,concat('{\"id\":\"',t1.role_id,'\",\"value\":\"',concat(t1.menu_menuId,' ',t1.menu_menuName),'\",\"open\":\"1\",\"d1\":',t1.role_viwe,',\"data\":[',t2.data,']}')data from 
		(select t3.menu_group,t2.role_id,t3.menu_menuName,t2.role_viwe,t3.menu_menuId from tbl_rolemaster t2
		left join tbl_menu t3 on t2.menu_id = t3.menu_id 
		where t2.role_name='$obj' and t3.menu_header=1 order by t3.menu_group) t1
		inner join
		(select 1 forG,t3.menu_group,group_concat(concat('{\"id\":\"',t2.role_id,'\",\"value\":\"',concat(t3.menu_menuId,' ',t3.menu_menuName),'\",\"d1\":',t2.role_viwe,',\"d2\":',t2.role_insert,',\"d3\":',t2.role_update,',\"d4\":',t2.role_del,'}') ORDER BY REPLACE(t3.menu_menuId, '.','')*1 separator ',') data  from tbl_rolemaster t2
		left join tbl_menu t3 on t2.menu_id = t3.menu_id 
		where t2.role_name='$obj' and t3.menu_header=0 group by t3.menu_group) t2 
		on t1.menu_group = t2.menu_group) t1 group by t1.forG";
	}
	else if($role == 'SUPPORT')
	{
		$sql = "SELECT group_concat(data separator ',')data from 
		(select t2.forG,concat('{\"id\":\"',t1.role_id,'\",\"value\":\"',concat(t1.menu_menuId,' ',t1.menu_menuName),'\",\"open\":\"1\",\"d1\":',t1.role_viwe,',\"data\":[',t2.data,']}')data from 
		(select t3.menu_group,t2.role_id,t3.menu_menuName,t2.role_viwe,t3.menu_menuId from tbl_rolemaster t2
		left join tbl_menu t3 on t2.menu_id = t3.menu_id 
		where t2.role_name='$obj' and t3.menu_header=1 and t3.menu_for not in('ADMIN') order by t3.menu_group) t1
		inner join
		(select 1 forG,t3.menu_group,group_concat(concat('{\"id\":\"',t2.role_id,'\",\"value\":\"',concat(t3.menu_menuId,' ',t3.menu_menuName),'\",\"d1\":',t2.role_viwe,',\"d2\":',t2.role_insert,',\"d3\":',t2.role_update,',\"d4\":',t2.role_del,'}') ORDER BY REPLACE(t3.menu_menuId, '.','')*1 separator ',') data  from tbl_rolemaster t2
		left join tbl_menu t3 on t2.menu_id = t3.menu_id 
		where t2.role_name='$obj' and t3.menu_header=0 and t3.menu_for not in('ADMIN') group by t3.menu_group) t2 
		on t1.menu_group = t2.menu_group) t1 group by t1.forG";
	}
	else
	{
		$sql = "SELECT group_concat(data separator ',')data from 
		(select t2.forG,concat('{\"id\":\"',t1.role_id,'\",\"value\":\"',concat(t1.menu_menuId,' ',t1.menu_menuName),'\",\"open\":\"1\",\"d1\":',t1.role_viwe,',\"data\":[',t2.data,']}')data from 
		(select t3.menu_group,t2.role_id,t3.menu_menuName,t2.role_viwe,t3.menu_menuId from tbl_rolemaster t2
		left join tbl_menu t3 on t2.menu_id = t3.menu_id 
		where t2.role_name='$obj' and t3.menu_header=1 and t3.menu_for in('ALL') order by t3.menu_group) t1
		inner join
		(select 1 forG,t3.menu_group,group_concat(concat('{\"id\":\"',t2.role_id,'\",\"value\":\"',concat(t3.menu_menuId,' ',t3.menu_menuName),'\",\"d1\":',t2.role_viwe,',\"d2\":',t2.role_insert,',\"d3\":',t2.role_update,',\"d4\":',t2.role_del,'}') ORDER BY REPLACE(t3.menu_menuId, '.','')*1 separator ',') data  from tbl_rolemaster t2
		left join tbl_menu t3 on t2.menu_id = t3.menu_id 
		where t2.role_name='$obj' and t3.menu_header=0 and t3.menu_for in('ALL') group by t3.menu_group) t2 
		on t1.menu_group = t2.menu_group) t1 group by t1.forG";
	}
	$re = $mysqli->query($sql);
	if($re->num_rows>0)
	{
		echo '{ch:1,data:[';
		echo $re->fetch_object()->data;
		echo ']}';
	}
	else echo '{ch:2,data:"ไม่พบข้อมูล"}';
	
}

function getData($mysqli,$part)
{
	echo '{ch:1,data:';
	toArrayStringAddNumberRow($mysqli->query("SELECT partNo,qty,revision,fifo,replace(fifo,'-', '')*1 sortFifo,id FROM tbl_inventory where partNo='$part' and area='STORAGE' and ref=0 and _use=1 order by sortFifo"),1);
	echo '}';
}

$mysqli->close();
exit();

?>