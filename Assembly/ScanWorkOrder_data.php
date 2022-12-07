<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ScanWorkOrder'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ScanWorkOrder'}[0] == 0) {
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


include('../php/connection.php');
if ($type <= 10) //data
{
	if ($type == 1) {

		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Assembly_Date
			FROM
				tbl_assembly_pre
			WHERE
				Created_By_ID = $cBy
					AND Assembly_Date = '$Assembly_Date'
					AND (Assembly_Pre_ID IS NULL
					OR status = 'PENDING')
					AND Area = 'Assembly';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);

			$header = jsonRow($re1, true, 0);
			$body = [];

			if (count($header) > 0) {
				$sql = "SELECT 
					BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
					tap.Serial_ID,
					tap.WorkOrder,
					tap.Part_No,
					tpm.Part_Name,
					tap.Model,
					tap.Part_Type,
					tap.Package_Type,
					tap.Qty_Package
				FROM
					tbl_assembly_pre tap
						INNER JOIN
					tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
				WHERE
					tap.Assembly_Date = '$Assembly_Date'
						AND tap.status = 'PENDING'
						AND Area = 'Assembly';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				$body = jsonRow($re1, true, 0);
			}
			$returnData = ['header' => $header, 'body' => $body];
			closeDBT($mysqli, 1, $returnData);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'ScanWorkOrder'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {

		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
			'obj=>WorkOrder:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {


			$sql = "SELECT 
				WorkOrder
			FROM
				tbl_assembly_pre
			WHERE 
				WorkOrder = '$WorkOrder';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('Work Order สแกนไปแล้ว' . __LINE__);
			}

			$sql = "SELECT 
				BIN_TO_UUID(Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
				Creation_DateTime
			FROM
				tbl_receiving_pre
			WHERE
				WorkOrder = '$WorkOrder';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
				$Part_ID = $row['Part_ID'];
				$Creation_DateTime = $row['Creation_DateTime'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(tiv.Part_ID, TRUE) AS Part_ID,
				tiv.Creation_DateTime,
				tiv.WorkOrder,
				trp.Package_Type
			FROM
				tbl_inventory tiv
					INNER JOIN
				tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
			WHERE
				BIN_TO_UUID(tiv.Part_ID, TRUE) = '$Part_ID'
					AND Status_Working = 'Wait Assembly'
					AND trp.Package_Type = 'Rack'
					AND (tiv.Creation_DateTime < '$Creation_DateTime'
					OR tiv.WorkOrder < '$WorkOrder')
					AND NOT EXISTS( SELECT 
						*
					FROM
						tbl_assembly_pre tap
					WHERE
						tap.WorkOrder = tiv.WorkOrder);";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('กรุณาเลือก Package Number ที่เก่ากว่าก่อน');
			}


			$sql = "SELECT 
				SUM(Used) AS Used
			FROM
				tbl_assembly_pre
			WHERE 
				WorkOrder = '$WorkOrder';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Used = $row['Used'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
				Part_No,
				Serial_ID,
				Model,
				Part_Type,
				Package_Type,
    			Qty_Package
			FROM
				tbl_receiving_pre trp
			WHERE 
				WorkOrder = '$WorkOrder'
				AND Area = 'Assembly';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_ID = $row['Part_ID'];
				$Part_No = $row['Part_No'];
				$Serial_ID = $row['Serial_ID'];
				$Model = $row['Model'];
				$Part_Type = $row['Part_Type'];
				$Package_Type = $row['Package_Type'];
				$Qty_Package = $row['Qty_Package'];
			}

			$sql = "SELECT 
				WorkOrder,
				Qty,
				Assy_Qty,
				SUM(Qty-Assy_Qty) AS Total_Qty
			FROM
				tbl_inventory tiv
			WHERE
				WorkOrder = '$WorkOrder';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Total_Qty = $row['Total_Qty'];
			}

			
			$sql = "INSERT INTO tbl_assembly_pre(
				Assembly_Date,
				Part_ID,
				Part_No,
				Serial_ID,
				WorkOrder,
				Model,
				Part_Type,
				Qty_Package,
				Package_Type,
				Area,
				Location_ID,
				Receiving_Header_ID,
				Creation_DateTime,
				Created_By_ID )
			SELECT
				'$Assembly_Date',
				tiv.Part_ID,
				'$Part_No',
				tiv.Serial_ID,
				'$WorkOrder',
				'$Model',
				'$Part_Type',
				$Total_Qty,
				'$Package_Type',
				tiv.Area,
				tiv.Location_ID,
				tiv.Receiving_Header_ID,
				NOW(),
				$cBy
			FROM 
				tbl_inventory tiv
			WHERE WorkOrder = '$WorkOrder';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ScanWorkOrder'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'ScanWorkOrder'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'ScanWorkOrder'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {

		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$sql = "SELECT
				Part_No
			FROM
				tbl_assembly_pre
			WHERE
				Assembly_Date = '$Assembly_Date';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			$sql = "UPDATE tbl_assembly_pre
			SET 
				status = 'WORKING',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE Assembly_Date = '$Assembly_Date'
					AND status = 'PENDING'
					AND Created_By_ID = $cBy;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

$mysqli->close();
exit();
