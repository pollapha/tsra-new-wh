<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ViewAssembly'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ViewAssembly'}[0] == 0) {
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if (!isset($_REQUEST['type'])) {
	echo json_encode(array('ch' => 2, 'data' => 'ข้อมูลไม่ถูกต้อง'));
	exit();
}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/xlsxwriter.class.php');
include('../common/common.php');
include('../php/connection.php');
if ($type <= 10) //data
{
	if ($type == 1) {
		// $re = select_group($mysqli);
		// closeDBT($mysqli, 1, $re);
		$sql = "SELECT 
		BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
		DATE_FORMAT(tap.Assembly_Date, '%d/%m/%y') AS Assembly_Date,
		tap.Serial_ID,
		tap.WorkOrder,
		tap.Part_No,
		tpm.Part_Name,
		tap.Model,
		tap.Part_Type,
		tap.Package_Type,
		tap.Qty_Package,
		SUM(tap.Used) AS Used,
		SUM(tap.Count) AS Count,
		tcm.Customer_Code,
		tap.status,
		tap.Confirm_Assembled_DateTime
	FROM
		tbl_assembly_pre tap
			INNER JOIN
		tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
			LEFT JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
	WHERE
		Assembly_Date IS NOT NULL
			AND Area != 'Received'
		GROUP BY WorkOrder
	ORDER BY tap.Assembly_Pre_ID DESC , Serial_ID ASC;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 5) {
		$obj  = $_POST['obj'];
		$filenameprefix = $mysqli->real_escape_string(trim($obj['filenameprefix']));
		$sql = sqlexport_excel();
		$mysqli->autocommit(FALSE);
		try {
			if ($sql != '') {
				if ($re1 = $mysqli->query($sql)) {
					if ($re1->num_rows > 0) {
						$data = excelRow($re1);
						$writer = new XLSXWriter();
						$writer->writeSheet($data);
						$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
						$filename = $filenameprefix . '-' . $randomString . '.xlsx';
						ob_end_clean();
						header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
						header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
						header('Content-Transfer-Encoding: binary');
						header('Cache-Control: must-revalidate');
						header('Pragma: public');
						$writer->writeToStdOut();
					} else {
						echo json_encode(array('ch' => 2, 'data' => "ไม่พบข้อมูลในระบบ"));
					}
				} else {
					echo json_encode(array('ch' => 2, 'data' => "Error SP"));
				}
			} else {
				echo json_encode(array('ch' => 2, 'data' => "Error SP"));
			}
			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'ViewAssembly'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ViewAssembly'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'ViewAssembly'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'ViewAssembly'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');


function select_group($mysqli)
{

	$sql = "SELECT 
		BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
		DATE_FORMAT(tap.Assembly_Date, '%d/%m/%y') AS Assembly_Date,
		tap.Serial_ID,
		tap.WorkOrder,
		tap.Part_No,
		tpm.Part_Name,
		tap.Model,
		tap.Part_Type,
		tap.Package_Type,
		tap.Qty_Package,
		tap.Used,
		tcm.Customer_Code,
		tap.status
	FROM
		tbl_assembly_pre tap
			INNER JOIN
		tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
			LEFT JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
	WHERE
		Assembly_Date IS NOT NULL
	ORDER BY tap.Assembly_Pre_ID DESC , Serial_ID ASC;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('Assembly_Header_ID', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'Serial_ID',
			'WorkOrder',
			'Part_No',
			'Part_Name',
			'Model',
			'Package_Type',
			'Qty_Package',
			'Used',
			'Customer_Code',
			'status'
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['Assembly_Date'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "Assembly_Date" => $key1,
			"Assembly_Date" => $value1[0]['Assembly_Date'],
			"Status_Assembly" => $value1[0]['Status_Assembly'],
			"Part_Type" => $value1[0]['Part_Type'],
			"Confirm_Assembly_DateTime" => $value1[0]['Confirm_Assembly_DateTime'],
			'Total_Item' => count($value1), "open" => 0, "data" => $sub
		);
		$c++;
	}
	return $dateset;
}

function excelRow($result, $row = true, $seq = 0)
{
	$exceldata = array();
	$headdata = array();
	$data = array();
	$c = 0;
	if ($row) {
		$i = $seq;
		array_push($headdata, 'NO');
		while ($row = $result->fetch_field()) {
			array_push($headdata, $row->name);
		}
		$data[] = $headdata;
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			array_unshift($row, ++$c);
			$data[] = $row;
		}
	}
	return $data;
}

function sqlexport_excel()
{
	$sql = "SELECT 
		GRN_Number,
		DATE_FORMAT(Receive_DateTime, '%d/%m/%y %H:%i') AS Receive_DateTime,
		DN_Number,
		Part_No,
		Qty,
		Package_Number,
		FG_Serial_Number,
		Status_Receiving,
		DATE_FORMAT(Confirm_Receive_DateTime,
				'%d/%m/%y %H:%i') AS Confirm_Receive_DateTime
	FROM
		tbl_receiving_header rh
			INNER JOIN
		tbl_receiving_pre rp ON rp.Receiving_Header_ID = rh.Receiving_Header_ID
	WHERE
		Receive_DateTime IS NOT NULL
			AND status = 'COMPLETE'
	ORDER BY GRN_Number DESC , Part_No DESC , FG_Serial_Number ASC;";

	return $sql;
}


$mysqli->close();
exit();
