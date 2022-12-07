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
else if($_SESSION['xxxRole']->{'role'}[3] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}
include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);

if($type ==1)
{
	$partNo = $mysqli->real_escape_string(trim(strtoupper($obj['partNo'])));
	$doctype = $mysqli->real_escape_string(trim(strtoupper($obj['doctype'])));
	
	if($re1 = $mysqli->query("SELECT t2.id delID,t1.id docID,t2.fifo,replace(t2.fifo,'-', '')*1 sortFifo from tbl_header_replenish t1 left join tbl_inventory t2 on t1.id=t2.ref 
	where t1.doctype='$doctype' and t2.partNo='$partNo' and t2.area='REPLENISH' order by sortFifo desc limit 1"))
	{
		$re1 = $re1->fetch_object();
		$delID = $re1->delID;
		$docID = $re1->docID;
		$mysqli->query("UPDATE tbl_inventory set ref=0,area='STORAGE' where id=$delID");

		if($mysqli->query("SELECT 1 from tbl_header_replenish t1 left join tbl_inventory t2 on t1.id=t2.ref 
		where t1.id=$docID and t2.area='REPLENISH' limit 1")->num_rows == 0)
		{
			$mysqli->query("DELETE from tbl_header_replenish where id=$docID");
		}
		getData1($mysqli,$doctype,$docID,$partNo);
	}
}

function getData1($mysqli,$doctype,$headerID,$partNo)
{
	echo '{ch:1,data:';
	toArrayStringAddNumberRow($mysqli->query("SELECT partNo,qty,revision,fifo,t1.doctype from tbl_header_replenish t1 left join tbl_inventory t2 on t1.id=t2.ref where t1.id=$headerID and t2.area='REPLENISH' order by t2.refDate desc"),1);
	echo ',data2:"'.$doctype.'"';
	echo ',data3:';
	toArrayStringAddNumberRow($mysqli->query("select partNo,qty,revision,fifo,replace(fifo,'-', '')*1 sortFifo,id FROM tbl_inventory where partNo='$partNo' and area='STORAGE' and ref=0 and _use=1 order by sortFifo"),1);
	echo '}';
}

$mysqli->close();
exit();
?>