<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'AssemblyCompleted'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'AssemblyCompleted'}[0] == 0) {
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

include('../common/common.php');
include('../php/connection.php');
if ($type <= 10) //data
{
	if ($type == 1) {
		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
			'obj=>WorkOrder:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
				DATE_FORMAT(tap.Assembly_Date, '%d/%m/%y') AS Assembly_Date,
				tap.Serial_ID,
				tap.WorkOrder,
				tap.Part_No,
				tap.Model,
				tap.Part_Type,
				tap.Package_Type,
				tap.Qty_Package,
				Count,
				tap.status,
				tap.Confirm_Assembled_DateTime
			FROM
				tbl_assembly_pre tap
			WHERE
				WorkOrder = '$WorkOrder'
					AND status = 'WORKING'
					AND Count <= Qty_Package;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'AssemblyCompleted'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {

		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
			'obj=>WorkOrder:s:0:1',
			'obj=>Part_No:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Part_No
			FROM
				tbl_part_master
			WHERE 
				Part_No = '$Part_No';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล Part Number' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_No = $row['Part_No'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
				BIN_TO_UUID(tap.Part_ID, TRUE) AS Part_ID
			FROM
				tbl_assembly_pre tap
			WHERE
				WorkOrder = '$WorkOrder';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			$sql = "SELECT 
				BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
				BIN_TO_UUID(tap.Part_ID, TRUE) AS Part_ID
			FROM
				tbl_assembly_pre tap
			WHERE
				WorkOrder = '$WorkOrder'
					AND Part_No = '$Part_No';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('Part Number ไม่ตรงกับ Work order' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Assembly_Pre_ID = $row['Assembly_Pre_ID'];
				$Part_ID = $row['Part_ID'];
			}


			$sql = "SELECT 
				Assembled_Qty
			FROM
				tbl_assembly_plan
			WHERE 
				Assembly_Date = '$Assembly_Date'
					AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล  Plan ของวันนี้');
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Assembled_Qty = $row['Assembled_Qty'];
			}

			$sql = "UPDATE tbl_assembly_plan
			SET 
				Assembled_Qty = Assembled_Qty+1,
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE 
				Assembly_Date = '$Assembly_Date'
					AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'
					AND Qty >= Assembled_Qty+1;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('Confirm ครบตาม Plan แล้ว ');
			}


			//สแกนแล้วนับเพิ่มที่ละ 1
			$sql = "UPDATE tbl_assembly_pre
			SET 
				Count = Count+1,
				Completed = 'Y',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE 
				BIN_TO_UUID(Assembly_Pre_ID,TRUE) = '$Assembly_Pre_ID'
					AND Qty_Package >= Count+1;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('Confirm ครบแล้ว' . __LINE__);
			}


			//สแกนแล้วนับเพิ่มที่ละ 1
			$sql = "UPDATE tbl_report trt
					INNER JOIN 
				tbl_assembly_pre tap ON trt.WorkOrder = tap.WorkOrder
			SET 
				trt.Assembled_Qty = trt.Assembled_Qty+1,
				trt.Status = 'Assembled',
				trt.Last_Updated_DateTime = NOW(), 
				trt.Updated_By_ID = $cBy
			WHERE
				trt.WorkOrder = '$WorkOrder'
					AND Completed = 'Y'
					AND tap.Updated_By_ID = $cBy;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}



			//ส่วนของการตัด Qty ของ Submaterial 
			//
			// $sql = "SELECT 
			// 	BIN_TO_UUID(tsub.Part_ID,TRUE) AS Part_ID, Part_No, Used
			// FROM
			// 	tbl_sub_part tsub
			// 		INNER JOIN 
			// 	tbl_part_master tpm ON tsub.Sub_Part_ID = tpm.Part_ID
			// WHERE
			// 	BIN_TO_UUID(Sub_Part_ID, TRUE) = '$Part_ID';";
			// $re1 = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re1->num_rows == 0) {
			// 	throw new Exception('ไม่พบข้อมูล' . __LINE__);
			// }
			// while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
			// 	$Part_ID = $row['Part_ID'];
			// 	$Used = $row['Used'];
			// }

			// $sql = "SELECT 
			// 	BIN_TO_UUID(ID,TRUE) AS ID
			// FROM
			// 	tbl_inventory
			// WHERE
			// 	BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'
			// 		AND Assembled_Qty < Qty
			// 	ORDER BY ID ASC LIMIT 1;";
			// $re1 = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re1->num_rows == 0) {
			// 	throw new Exception('ไม่พบข้อมูล' . __LINE__);
			// }
			// while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
			// 	$ID = $row['ID'];
			// }


			// $sql = "UPDATE tbl_inventory
			// SET 
			// 	Assembled_Qty = Assembled_Qty+(1*$Used)
			// WHERE
			// 	BIN_TO_UUID(ID,TRUE) = '$ID'
			// 		AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'
			// 		AND Assembled_Qty < Qty;";
			// sqlError($mysqli, __LINE__, $sql, 1);
			// if ($mysqli->affected_rows == 0) {
			// 	throw new Exception('Sub material ไม่เพียงพอ' . __LINE__);
			// }

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
	if ($_SESSION['xxxRole']->{'AssemblyCompleted'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'AssemblyCompleted'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'AssemblyCompleted'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {

		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
			'obj=>WorkOrder:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}

		$mysqli->autocommit(FALSE);
		try {


			$sql = "SELECT 
				BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
				BIN_TO_UUID(tap.Part_ID, TRUE) AS Part_ID
			FROM
				tbl_assembly_pre tap
			WHERE
				WorkOrder = '$WorkOrder';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_ID = $row['Part_ID'];
			}


			$sql = "SELECT 
				BIN_TO_UUID(tap.Assembly_Pre_ID, TRUE) AS Assembly_Pre_ID,
				BIN_TO_UUID(tap.Part_ID, TRUE) AS Part_ID
			FROM
				tbl_assembly_pre tap
			WHERE
				WorkOrder = '$WorkOrder'
					AND Count != Qty_Package;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('กรุณา Confirm ให้ครบ ');
			}


			//อัพเดท status ใน tbl_assembly_pre
			$sql = "UPDATE tbl_assembly_pre
			SET 
				status = 'PACKING'
			WHERE
				WorkOrder = '$WorkOrder'
					AND Completed = 'Y';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			//เพิ่ม transaction
			$sql = "INSERT INTO
				tbl_transaction(
					Receiving_Header_ID,
				Assembly_Pre_ID,
				Part_ID,
				Serial_ID,
				WorkOrder,
				Qty,
				From_Area,
				To_Area,
				Trans_Type,
				FIFO_No,
				Creation_DateTime,
				Created_By_ID,
				From_Loc_ID,
				To_Loc_ID,
				Last_Updated_DateTime,
				Updated_By_ID)
			SELECT
				tap.Receiving_Header_ID,
				tap.Assembly_Pre_ID,
				tap.Part_ID,
				tap.Serial_ID,
				tap.WorkOrder,
				tap.Count,
				tiv.Area,
				tap.Area,
				'ASSY',
				ROW_NUMBER() OVER (ORDER BY tap.WorkOrder),
				now(),
				$cBy,
				tiv.Location_ID,
				tap.Location_ID,
				now(),
				$cBy
			FROM
				tbl_assembly_pre tap
					INNER JOIN
				tbl_inventory tiv ON tiv.WorkOrder = tap.WorkOrder
			WHERE
				tap.status = 'PACKING'
					AND tap.WorkOrder = '$WorkOrder'
					AND Completed = 'Y';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			// อัปเดตจำนวน Assembled_Qty ในตาราง Report
			$sql = "UPDATE tbl_inventory tiv
				INNER JOIN
			tbl_assembly_pre tap ON tiv.WorkOrder = tap.WorkOrder
				INNER JOIN
			tbl_report trt ON tiv.WorkOrder = trt.WorkOrder
			SET 
				tiv.Assembled_Qty = trt.Assembled_Qty,
				tiv.Status_Working = 'FG',
				tiv.Last_Updated_DateTime = NOW(), 
				tiv.Updated_By_ID = $cBy
			WHERE 
				tap.status = 'PACKING'
					AND tap.Location_ID IS NOT NULL
					AND Completed = 'Y'";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			//อัพเดทสถานะ Completed ใน tbl_assembly_pre
			$sql = "UPDATE tbl_assembly_pre
			SET 
				Completed = 'N'
			WHERE
				Completed = 'Y';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			//exit('s');

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
