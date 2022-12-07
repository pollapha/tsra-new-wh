<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'AssemblyPlan'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'AssemblyPlan'}[0] == 0) {
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
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}

		$sql = "SELECT 
			Assembly_Date,
			BIN_TO_UUID(tap.ID, TRUE) AS ID,
			BIN_TO_UUID(tap.Part_ID, TRUE) AS Part_ID,
			tpm.Part_No,
			tpm.Part_Name,
			tpm.Side,
			Qty,
			Assembled_Qty,
			tap.Status
		FROM
			tbl_assembly_plan tap
				INNER JOIN
			tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
			WHERE Assembly_Date = '$Assembly_Date'
		ORDER BY tpm.Creation_DateTime ASC;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'AssemblyPlan'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {

		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
			'obj=>Part_No:s:0:1',
			'obj=>Qty:s:0:1',
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
				Part_No = '$Part_No'
				AND Part_Type = 'Assembly part'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล Part Number นี้' . __LINE__);
			}

			$Part_ID = getPartID($mysqli, $Part_No);

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID
			FROM
				tbl_assembly_plan
			WHERE
				Assembly_Date = '$Assembly_Date'
				AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'
				AND (Status = 'WORKING' OR Status = 'PENDING');";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี Plan นี้แล้ว' . __LINE__);
			}

			//เพิ่ม Plan
			$sql = "INSERT INTO tbl_assembly_plan (
				Assembly_Date,
				Part_ID,
				Qty,
				Creation_DateTime,
				Created_By_ID )
			VALUES (
				'$Assembly_Date',
				UUID_TO_BIN('$Part_ID',TRUE),
			'$Qty',
			now(),
			$cBy )";
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
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'AssemblyPlan'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		$dataParams = array(
			'obj',
			'obj=>ID:s:0:0',
			'obj=>Assembly_Date:s:0:0',
			'obj=>Part_No:s:0:0',
			'obj=>Qty:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {


			$Part_ID = getPartID($mysqli, $Part_No);


			//อัพเดท Qty ได้แต่ต้องไม่ต่ำกว่าจำนวนที่สแกน Completed ไปแล้ว
			$sql = "UPDATE tbl_assembly_plan 
				SET 
					Qty = '$Qty',
					Last_Updated_DateTime = now(),
					Updated_By_ID = $cBy
				WHERE
					ID = UUID_TO_BIN('$ID',TRUE)
						AND $Qty >= Assembled_Qty
						AND Status != 'COMPLETE'";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
			}

			$mysqli->commit();

			$sql = "SELECT 
			Assembly_Date,
			BIN_TO_UUID(tap.ID, TRUE) AS ID,
			BIN_TO_UUID(tap.Part_ID, TRUE) AS Part_ID,
			tpm.Part_No,
			Part_Name,
			Qty,
			tap.Status
		FROM
			tbl_assembly_plan tap
				INNER JOIN
			tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
			WHERE Assembly_Date = '$Assembly_Date'
		ORDER BY tpm.Creation_DateTime ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'AssemblyPlan'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {

		$ID  = $_POST['obj'];

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Assembly_Date
			FROM
				tbl_assembly_plan
			WHERE
				ID = UUID_TO_BIN('$ID',TRUE);";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			$Assembly_Date = $re1->fetch_array(MYSQLI_ASSOC)['Assembly_Date'];

			//ลบ plan ที่ Assembled_Qty เป็น 0
			$sql = "DELETE FROM 
				tbl_assembly_plan
			WHERE 
				ID = UUID_TO_BIN('$ID',TRUE)
					AND Assembled_Qty = 0
					AND Status = 'PENDING';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถลบข้อมูลได้' . __LINE__);
			}

			$mysqli->commit();

			$sql = "SELECT 
			Assembly_Date,
			BIN_TO_UUID(tap.ID, TRUE) AS ID,
			BIN_TO_UUID(tap.Part_ID, TRUE) AS Part_ID,
			tpm.Part_No,
			Part_Name,
			Qty,
			Assembled_Qty,
			tap.Status
		FROM
			tbl_assembly_plan tap
				INNER JOIN
			tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
			WHERE Assembly_Date = '$Assembly_Date'
		ORDER BY tpm.Creation_DateTime ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'AssemblyPlan'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

$mysqli->close();
exit();
