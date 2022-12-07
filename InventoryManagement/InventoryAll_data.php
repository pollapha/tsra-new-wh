<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'InventoryAll'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'InventoryAll'}[0] == 0) {
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

		$sql = "WITH a AS(
			SELECT 
				trh.GRN_Number,
				tpm.Part_No,
				tpm.Part_Name,
				trh.Serial_Number,
				tiv.Serial_ID,
				tiv.WorkOrder,
				tpm.Color,
				tlm.Location_Code,
				tiv.Area,
				trp.Qty_Unit,
				tiv.Qty,
				tiv.Used_Qty,
				tiv.Assembled_Qty,
				tiv.Pick_Qty,
				tpm.Type,
				trp.Package_Type,
				DATE_FORMAT(trh.Receive_Date, '%Y-%m-%d') AS Receive_Date,
				DATE_FORMAT(trh.Confirm_Receive_DateTime, '%h:%i:%s') AS Receive_Time,
				trh.Confirm_Receive_DateTime,
				CONCAT(FORMAT(Width_Part, 0),
						'x',
						FORMAT(Length_Part, 0),
						'x',
						FORMAT(Height_Part, 0)) AS Dimansion,
				trp.Model AS Model,
				tcm.Customer_Code,
				Mat_SAP1,
				Mat_SAP3 A,
				DN_Number,
				tiv.Status_Working,
				tiv.Created_By_ID,
				tiv.Last_Updated_DateTime,
				tiv.Updated_By_ID,
				tpm.Part_Type
			FROM
				tbl_inventory tiv
					INNER JOIN
				tbl_receiving_header trh ON tiv.Receiving_Header_ID = trh.Receiving_Header_ID
					INNER JOIN
				tbl_receiving_pre trp ON tiv.Serial_ID = trp.Serial_ID
					AND (tiv.WorkOrder = trp.WorkOrder
					OR tiv.WorkOrder IS NULL)
					INNER JOIN
				tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
					LEFT JOIN
				tbl_location_master tlm ON tiv.Location_ID = tlm.Location_ID
					LEFT JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					LEFT JOIN
				tbl_user tuser ON trh.Created_By_ID = tuser.user_id
			GROUP BY trp.Receiving_Pre_ID
			ORDER BY tpm.Part_ID ASC),
			b as (SELECT a.*, 
			(	CASE
				WHEN a.Part_Type = 'Sub material' THEN a.Qty-a.Assembled_Qty 
				WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NOT NULL AND a.Used_Qty-a.Pick_Qty = 0 THEN a.Qty-a.Used_Qty
				WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NOT NULL AND a.Used_Qty-a.Pick_Qty != 0 THEN a.Used_Qty-a.Pick_Qty 
				WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NULL THEN a.Qty-a.Pick_Qty
				WHEN a.Part_Type = 'Assembly part' AND a.Assembled_Qty = 0 AND a.Status_Working != 'Confirm Shipped' THEN a.Qty
				WHEN a.Part_Type = 'Assembly part' AND a.Status_Working = 'Confirm Shipped' THEN a.Assembled_Qty-a.Pick_Qty
				WHEN a.Part_Type = 'Assembly part' AND a.Assembled_Qty-a.Pick_Qty != 0 AND a.Status_Working != 'Confirm Shipped' THEN a.Assembled_Qty
				WHEN a.Part_Type = 'Assembly part' AND a.Assembled_Qty-a.Pick_Qty = 0 AND a.Status_Working != 'Confirm Shipped' THEN a.Assembled_Qty-a.Pick_Qty
			END) AS Total_Qty,
			(
			CASE
				WHEN a.Part_Type = 'Sub material' AND a.Qty-a.Assembled_Qty = 0 THEN 'hide'
				WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NOT NULL AND a.Qty-a.Used_Qty = 0 AND a.Used_Qty-a.Pick_Qty = 0 AND Status_Working = 'Confirm Shipped' THEN 'hide'
				WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NULL AND a.Qty-a.Pick_Qty = 0 AND Status_Working = 'Confirm Shipped' THEN 'hide'
				WHEN a.Part_Type = 'Assembly part' AND a.Qty-a.Pick_Qty = 0 AND Status_Working = 'Confirm Shipped' THEN 'hide'
				ELSE 'show'
			END) AS status_show
			From a
			)
			select b.* 
			from b
			where b.status_show != 'hide'
			ORDER BY Confirm_Receive_DateTime, Serial_ID ASC;";

		//if(a.Part_Type = 'Sub material', a.Qty-a.Assembled_Qty, a.Qty) AS Total_Qty 
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 5) {
		$obj  = $_POST['obj'];
		$filenameprefix = $mysqli->real_escape_string(trim($obj['filenameprefix']));
		$Date = date("Y-m-d");
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
						$filename = $filenameprefix . '-' . $Date . $randomString . '.xlsx';
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
	if ($_SESSION['xxxRole']->{'InventoryAll'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'InventoryAll'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'InventoryAll'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'InventoryAll'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');


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
	$sql = "WITH a AS(
	SELECT 
		trh.GRN_Number,
		tpm.Part_No,
		tpm.Part_Name,
		trh.Serial_Number,
		tiv.Serial_ID,
		tiv.WorkOrder,
		tpm.Color,
		tlm.Location_Code,
		tiv.Area,
		trp.Qty_Unit,
		tiv.Qty,
		tiv.Used_Qty,
		tiv.Assembled_Qty,
		tiv.Pick_Qty,
		tpm.Type,
		trp.Package_Type,
		DATE_FORMAT(trh.Receive_Date, '%Y-%m-%d') AS Receive_Date,
		DATE_FORMAT(trh.Confirm_Receive_DateTime, '%h:%i:%s') AS Receive_Time,
		trh.Confirm_Receive_DateTime,
		CONCAT(FORMAT(Width_Part, 0),
				'x',
				FORMAT(Length_Part, 0),
				'x',
				FORMAT(Height_Part, 0)) AS Dimansion,
		trp.Model AS Model,
		tcm.Customer_Code,
		Mat_SAP1,
		Mat_SAP3,
		DN_Number,
		tiv.Status_Working,
		tiv.Created_By_ID,
		tiv.Last_Updated_DateTime,
		tiv.Updated_By_ID,
		tpm.Part_Type
	FROM
		tbl_inventory tiv
			INNER JOIN
		tbl_receiving_header trh ON tiv.Receiving_Header_ID = trh.Receiving_Header_ID
			INNER JOIN
		tbl_receiving_pre trp ON tiv.Serial_ID = trp.Serial_ID
			AND (tiv.WorkOrder = trp.WorkOrder
			OR tiv.WorkOrder IS NULL)
			INNER JOIN
		tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
			LEFT JOIN
		tbl_location_master tlm ON tiv.Location_ID = tlm.Location_ID
			LEFT JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			LEFT JOIN
		tbl_user tuser ON trh.Created_By_ID = tuser.user_id
	GROUP BY trp.Receiving_Pre_ID
	ORDER BY tpm.Part_ID ASC),
	b as (SELECT a.*, 
	(	CASE
		WHEN a.Part_Type = 'Sub material' THEN a.Qty-a.Assembled_Qty 
		WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NOT NULL AND a.Used_Qty-a.Pick_Qty = 0 THEN a.Qty-a.Used_Qty
		WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NOT NULL AND a.Used_Qty-a.Pick_Qty != 0 THEN a.Used_Qty-a.Pick_Qty 
		WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NULL THEN a.Qty-a.Pick_Qty
		WHEN a.Part_Type = 'Assembly part' AND a.Assembled_Qty = 0 AND a.Status_Working != 'Confirm Shipped' THEN a.Qty
		WHEN a.Part_Type = 'Assembly part' AND a.Status_Working = 'Confirm Shipped' THEN a.Assembled_Qty-a.Pick_Qty
		WHEN a.Part_Type = 'Assembly part' AND a.Assembled_Qty-a.Pick_Qty != 0 AND a.Status_Working != 'Confirm Shipped' THEN a.Assembled_Qty
		WHEN a.Part_Type = 'Assembly part' AND a.Assembled_Qty-a.Pick_Qty = 0 AND a.Status_Working != 'Confirm Shipped' THEN a.Assembled_Qty-a.Pick_Qty
	END) AS Total_Qty,
	(
	CASE
		WHEN a.Part_Type = 'Sub material' AND a.Qty-a.Assembled_Qty = 0 THEN 'hide'
		WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NOT NULL AND a.Qty-a.Used_Qty = 0 AND a.Used_Qty-a.Pick_Qty = 0 AND Status_Working = 'Confirm Shipped' THEN 'hide'
		WHEN a.Part_Type = 'Finish good' AND a.Serial_Number IS NULL AND a.Qty-a.Pick_Qty = 0 AND Status_Working = 'Confirm Shipped' THEN 'hide'
		WHEN a.Part_Type = 'Assembly part' AND a.Qty-a.Pick_Qty = 0 AND Status_Working = 'Confirm Shipped' THEN 'hide'
		ELSE 'show'
	END) AS status_show
	From a
			),
			c AS (
				select b.* 
				from b
				where b.status_show != 'hide'
				ORDER BY Confirm_Receive_DateTime, Serial_ID ASC)
			SELECT 
	c.GRN_Number AS 'GRN Number',
	c.Part_No AS 'Part Number',
	c.Part_Name AS 'Part Name',
	c.Serial_ID AS 'Package Number',
	c.WorkOrder AS 'Work order',
	c.Color,
	c.Location_Code AS 'Location', 
	c.Area,
	c.Qty_Unit AS Unit,
	c.Total_Qty AS 'On hand',
	c.Type AS 'Part Type',
	c.Package_Type AS 'Package Type',
	Receive_Date AS 'Receive Date',
	Receive_Time AS 'Receive Time',
	Dimansion,
	c.Model AS Model,
	c.Mat_SAP1 AS 'Mat SAP 1',
	c.Mat_SAP3 AS 'Mat_SAP 3',
	c.DN_Number AS 'DN Number',
	Status_Working AS Status,
	'' AS Remark
	FROM
	c;";

	return $sql;
}

$mysqli->close();
exit();
