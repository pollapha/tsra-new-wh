<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'Transaction'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'Transaction'}[0] == 0) {
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

		$sql = "SELECT 
		BIN_TO_UUID(ts.ID,TRUE) AS ID,
		trh.GRN_Number,
		pickh.TS_Number,
		tsh.GTN_Number,
		tsh.Invoice_Number,
		tpm.Part_No,
		tph.Serial_Number,
		ts.Serial_ID,
		tcm.Customer_Code,
		ts.Qty,
		ts.From_Area,
		ts.To_Area,
		ts.Trans_Type,
		(SELECT 
				Location_Code
			FROM
				tbl_location_master tlm
			WHERE
				ts.From_Loc_ID = tlm.Location_ID) AS From_Location_Code,
		(SELECT 
				Location_Code
			FROM
				tbl_location_master tlm
			WHERE
				ts.To_Loc_ID = tlm.Location_ID) AS To_Location_Code,
		FIFO_No,
		DATE_FORMAT(ts.Creation_DateTime, '%d/%m/%y %H:%i') AS Creation_DateTime,
		(SELECT 
				user_fName
			FROM
				tbl_user tu
			WHERE
				ts.Created_By_ID = tu.user_id) AS Created_By,
		DATE_FORMAT(ts.Last_Updated_DateTime,
				'%d/%m/%y %H:%i') AS Last_Updated_DateTime,
		(SELECT 
				user_fName
			FROM
				tbl_user tu
			WHERE
				ts.Updated_By_ID = tu.user_id) AS Updated_By
	FROM
		tbl_transaction ts
			LEFT JOIN
		tbl_receiving_header trh ON ts.Receiving_Header_ID = trh.Receiving_Header_ID
			LEFT JOIN
		tbl_picking_header pickh ON ts.Picking_Header_ID = pickh.Picking_Header_ID
			LEFT JOIN
		tbl_shipping_header tsh ON ts.Shipping_Header_ID = tsh.Shipping_Header_ID
			LEFT JOIN
		tbl_part_master tpm ON ts.Part_ID = tpm.Part_ID
			LEFT JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			LEFT JOIN
		tbl_palletizing_header tph ON ts.Palletizing_Header_ID = tph.Palletizing_Header_ID
		AND tph.Status != 'CANCEL'
		WHERE Trans_Type != 'CANCEL' AND Trans_Type != 'EDIT'
	ORDER BY ts.Creation_DateTime DESC, ts.ID DESC;";
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
	if ($_SESSION['xxxRole']->{'Transaction'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'Transaction'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'Transaction'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'Transaction'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
			BIN_TO_UUID(ts.ID,TRUE) AS ID,
			trh.GRN_Number,
			pickh.TS_Number,
			tsh.GTN_Number,
			tsh.Invoice_Number,
			tpm.Part_No,
			tph.Serial_Number,
			ts.Serial_ID,
			tcm.Customer_Code,
			ts.Qty,
			ts.From_Area,
			ts.To_Area,
			ts.Trans_Type,
			(SELECT 
					Location_Code
				FROM
					tbl_location_master tlm
				WHERE
					ts.From_Loc_ID = tlm.Location_ID) AS From_Location_Code,
			(SELECT 
					Location_Code
				FROM
					tbl_location_master tlm
				WHERE
					ts.To_Loc_ID = tlm.Location_ID) AS To_Location_Code,
			FIFO_No,
			DATE_FORMAT(ts.Creation_DateTime, '%d/%m/%y %H:%i') AS Creation_DateTime,
			(SELECT 
					user_fName
				FROM
					tbl_user tu
				WHERE
					ts.Created_By_ID = tu.user_id) AS Created_By,
			DATE_FORMAT(ts.Last_Updated_DateTime,
					'%d/%m/%y %H:%i') AS Last_Updated_DateTime,
			(SELECT 
					user_fName
				FROM
					tbl_user tu
				WHERE
					ts.Updated_By_ID = tu.user_id) AS Updated_By
		FROM
			tbl_transaction ts
				LEFT JOIN
			tbl_receiving_header trh ON ts.Receiving_Header_ID = trh.Receiving_Header_ID
				LEFT JOIN
			tbl_picking_header pickh ON ts.Picking_Header_ID = pickh.Picking_Header_ID
				LEFT JOIN
			tbl_shipping_header tsh ON ts.Shipping_Header_ID = tsh.Shipping_Header_ID
				LEFT JOIN
			tbl_part_master tpm ON ts.Part_ID = tpm.Part_ID
				LEFT JOIN
			tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
				LEFT JOIN
			tbl_palletizing_header tph ON ts.Palletizing_Header_ID = tph.Palletizing_Header_ID
			AND tph.Status != 'CANCEL'
			WHERE Trans_Type != 'CANCEL' AND Trans_Type != 'EDIT'
		ORDER BY ID DESC)
		SELECT 
			a.GRN_Number AS 'GRN Number',
			a.TS_Number AS 'PS Number',
			a.GTN_Number AS 'GTN Number',
			a.Invoice_Number AS 'Invoice Number',
			a.Part_No AS 'Part Number',
			a.Serial_Number AS 'Package ID',
			a.Serial_ID AS 'Package Number',
			a.Qty AS 'Qty (PCS)',
			a.Customer_Code AS 'Customer',
			a.From_Area AS 'From Area',
			a.To_Area AS 'To Area',
			a.Trans_Type AS 'Transaction Type',
			a.From_Location_Code AS 'From Location',
			a.To_Location_Code AS 'To Location',
			a.Creation_DateTime AS 'Creation DateTime',
			a.Created_By AS 'Created By',
			a.Last_Updated_DateTime AS 'Last Updated DateTime',
			a.Updated_By AS 'Updated By'
		FROM a
		ORDER BY a.Creation_DateTime DESC, a.ID DESC;";

	return $sql;
}
//ts.Creation_DateTime AS C_Date
//WHERE DATE(a.C_Date) = DATE(curdate())

$mysqli->close();
exit();
