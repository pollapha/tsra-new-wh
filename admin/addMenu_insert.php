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
else if($_SESSION['xxxRole']->{'addMenu'}[2] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}
include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$role = $_SESSION['xxxPermission'];
$obj  = $_REQUEST['obj'];
$type  = intval($_REQUEST['type']);

if($type==1)
{
	$menu_group = $mysqli->real_escape_string(trim(strtoupper($obj['menu_group'])));
	$menu_header = $mysqli->real_escape_string(trim(strtoupper($obj['menu_header'])));
	$menu_menuId = $mysqli->real_escape_string(trim(strtoupper($obj['menu_menuId'])));
	$menu_menuName = $mysqli->real_escape_string($obj['menu_menuName']);
	$menu_menuUse = $mysqli->real_escape_string($obj['menu_menuUse']);
	$menu_for = $mysqli->real_escape_string(trim(strtoupper($obj['menu_for'])));
	$menu_url = $mysqli->real_escape_string($obj['menu_menuUrl']);

	$menu_url = $menu_header*1 == 1 ? '': $menu_url;
	$file = "../$menu_url";

	if(1 == 1)
	{
		if(!file_exists($file) && $menu_header*1 == 0)
		{
			$folder = explode('/',$menu_url);
			$folder = $folder[0];
			$template = 'var header_'.$menu_menuUse.' = function()
{
	var menuName="'.$menu_menuUse.'_",fd = "'.$folder.'/"+menuName+"data.php";

    function init()
    {
        
    };

    function ele(name)
    {
        return $$($n(name));
    };

    function $n(name)
    {
        return menuName+name;
    };
    
    function focus(name)
    {
        setTimeout(function(){ele(name).focus();},100);
    };
    
    function setView(target,obj)
    {
        var key = Object.keys(obj);
        for(var i=0,len=key.length;i<len;i++)
        {
            target[key[i]] = obj[key[i]];
        }
        return target;
    };

    function vw1(view,name,label,obj)
    {
        var v = {view:view,required:true,label:label,id:$n(name),name:name,labelPosition:"top"};
        return setView(v,obj);
    };

    function vw2(view,id,name,label,obj)
    {
        var v = {view:view,required:true,label:label,id:$n(id),name:name,labelPosition:"top"};
        return setView(v,obj);
    };

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_'.$menu_menuUse.'",
        body: 
        {
        	id:"'.$menu_menuUse.'_id",
        	type:"clean",
    		rows:
    		[
    		    {

                }
            ],on:
            {
                onHide:function()
                {
                    
                },
                onShow:function()
                {

                },
                onAddView:function()
                {
                	init();
                }
            }
        }
    };
};';
			$fp = fopen($file, 'w');
			fwrite($fp,$template);
			fclose($fp);

			
			$file_data = file_get_contents('data_template.txt');
			$file_data = str_replace("_menu_",$menu_menuUse,$file_data);

			$file = '../'.$folder.'/'.$menu_menuUse.'_data.php';
			$fp = fopen($file, 'w');
			fwrite($fp,$file_data);
			fclose($fp);
		}
		else mkdir("../$menu_menuUse");
		
	
		$mysqli->query("INSERT into tbl_menu (menu_group,menu_header,menu_menuId,menu_menuName,menu_menuUse,menu_for,menu_url)
		values($menu_group,$menu_header,'$menu_menuId','$menu_menuName','$menu_menuUse','$menu_for','$menu_url');");
		$id = $mysqli->query("SELECT LAST_INSERT_ID() id")->fetch_object()->id;
		$re = $mysqli->query("SELECT role_name from tbl_rolemaster group by role_name");
		if($re->num_rows>0)
		{
			while($row = $re->fetch_object())
			{
				if($row->role_name == 'ADMIN')
				{
					$role = $row->role_name;
					$d1 = 1;
					$d2 = 1;
					$d3 = 1;
					$d4 = 1;
				}
				else
				{
					$role = $row->role_name;
					$d1 = 0;
					$d2 = 0;
					$d3 = 0;
					$d4 = 0;
				}
				$mysqli->query("INSERT into tbl_rolemaster(role_name,role_viwe,role_insert,role_update,role_del,role_creationDate,role_createBy,menu_id)
				values('$role',$d1,$d2,$d3,$d4,now(),1,'$id');");
			}
		}
		else
		{
			$mysqli->query("INSERT into tbl_rolemaster(role_name,role_viwe,role_insert,role_update,role_del,role_creationDate,role_createBy,menu_id)
			values('ADMIN',1,1,1,1,now(),1,'$id');");
		}
		echo "{ch:1,data:'บันทึกสำเร็จ'}";
	}
}

$mysqli->close();
exit();

?>
