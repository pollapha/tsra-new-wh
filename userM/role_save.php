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
else if($_SESSION['xxxRole']->{'role'}[1] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}
include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$obj  = $_POST['obj'];
$type  = intval($_POST['type']);
$type = 1;
if($type == 0)
{
	getData($mysqli,$obj);
}
else if($type == 1)
{
	$doctype = $mysqli->real_escape_string(trim(strtoupper($obj['doctype'])));
	if($re1 = $mysqli->query("SELECT group_concat(t1.id separator ',') allId
  	from tbl_inventory t1  left join tbl_header_replenish t3 on t3.id=t1.ref
  	where t3.doctype='$doctype' group by t3.doctype;"))
	{
		if($re1->num_rows>0)
		{
			$mysqli->query("INSERT into tbl_inventory_transac(delivery,partNo,partName,qty,area,fromarea,fifo,vendor,vendorName,revision,tranType,tranName,loc,fromloc,cBy,cDate,docType,dDate,pus,docType_in,remark,boxType)
  			select delivery,partNo,partName,qty,toarea,fromarea,fifo,vendor,vendorName,revision,tranType,tranName,pick,put,cBy,refDate,doctype,dDate,pus,doctype1,remark,boxType from
			(SELECT t2.delivery,t2.partNo,t2.partName,t2.qty,'PICK' toarea,'STORAGE' fromarea,t2.fifo,t2.vendor,t2.vendorName,t2.revision,3 tranType,'REPLENISHMENT' tranName,t3.pick,t3.put,t1.cBy,UNIX_TIMESTAMP(t2.refDate)refDate,t1.doctype,UNIX_TIMESTAMP(t1.dDate)dDate,t2.pus,t2.doctype doctype1,replace(t2.fifo,'-', '')*1 sortFifo,t2.remark,t2.boxType
			from tbl_header_replenish t1 left join tbl_inventory t2 on t1.id=t2.ref left join tbl_partmaster t3 on t2.partNo = t3.partNo
			where t1.doctype='$doctype' order by t2.partNo,sortFifo) t1;");

			if($re2 =$mysqli->query("select boxType,delivery,t1.partNo,t1.partName,t1.qty,'PICK',fifo,t1.vendor,t1.vendorName,revision,t2.pick,t3.cBy,t1.cDate,t3.docType,UNIX_TIMESTAMP(t3.dDate) dDate,pus,remark,t1.docType
  			from tbl_inventory t1 left join tbl_partmaster t2 on t1.partNo = t2.partNo left join tbl_header_replenish t3 on t3.id=t1.ref
  			where t3.doctype='$doctype';"))
			{
				if($re2->num_rows>0)
				{
					$qtyTxt = 'INSERT delayed tbl_inventory(boxType,delivery,partNo,partName,qty,area,fifo,vendor,vendorName,revision,loc,cBy,cDate,docType,dDate,pus,remark,docType_in) values';
					$arTxt = array();
					$begin = 0;
					while($row = $re2->fetch_array(MYSQLI_NUM))
					{
						$c = $row[4]*1;
						$row[4] = 1;
						for($i=0;$i<$c;$i++)
						{
							$begin++;
							$arTxt[] = '("'.join('","',$row).'")';
							if($begin == 1500)
							{
								// echo $qtyTxt.join(',',$arTxt).'<br>';
								$mysqli->query($qtyTxt.join(',',$arTxt));
								$begin = 0;
								$arTxt = array();
							}
						}
					}
					if($begin>0)
					{
						// echo $qtyTxt.join(',',$arTxt).'<br>';
						$mysqli->query($qtyTxt.join(',',$arTxt));
					}
					$allId = $re1->fetch_array(MYSQLI_NUM);
					$allId = $allId[0];
					$mysqli->query("delete from tbl_inventory where id in($allId)");
					$mysqli->query("delete from tbl_header_replenish where doctype='$doctype'");
					echo "{ch:1,data:'$doctype'}";
				}else echo "{ch:2,data:ไม่พบข้อมูล'$doctype'}";
			}
		}
		else echo "{ch:2,data:ไม่พบข้อมูล'$doctype'}";
	}
	else echo "{ch:2,data:'โคดผิด'}";
}
function addData1($mysqli,$row,$qty)
{

}	

function getData($mysqli,$doctype)
{
	echo '{ch:1,data:';
	toArrayStringAddNumberRow($mysqli->query("SELECT partNo,qty,vendorName,vendor,delivery,dDate,revision,remark,pus,doctype,id from tbl_tempin where doctype='$doctype'"),1);
	echo ',data2:"'.$doctype.'"';
	echo '}';
}

$mysqli->close();
exit();
?>