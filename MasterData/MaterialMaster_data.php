<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'MaterialMaster'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'MaterialMaster'}[0] == 0) {
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
		
		$re = select_group($mysqli);
		closeDBT($mysqli, 1, $re);
	
	// 	$sql = "SELECT 
	// 	BIN_TO_UUID(tsub.ID, TRUE) AS ID,
	// 	BIN_TO_UUID(tsub.Part_ID, TRUE) AS Part_ID,
	// 	BIN_TO_UUID(tsub.Sub_Part_ID, TRUE) AS Sub_Part_ID,
	// 	(SELECT 
	// 			Part_No
	// 		FROM
	// 			tbl_part tpt
	// 		WHERE
	// 			tsub.Part_ID = tpt.Part_ID) AS Part_No,
	// 	(SELECT 
	// 			Part_No
	// 		FROM
	// 			tbl_part_master tpm
	// 		WHERE
	// 			tsub.Sub_Part_ID = tpm.Part_ID) AS Sub_Part_No,
	// 	tsub.Used,
	// 	Creation_DateTime
	// FROM
	// 	tbl_sub_part tsub
	// ORDER BY Creation_DateTime DESC;";
	// 	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	// 	closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'MaterialMaster'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {

		$dataParams = array(
			'obj',
			'obj=>Part_No:s:0:0',
			'obj=>Sub_Part_No:s:0:0',
			'obj=>Used:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}
		$mysqli->autocommit(FALSE);
		try {


			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID 
			FROM 
				tbl_part_master 
			WHERE 
				Part_No = '$Part_No' AND Part_Type = 'Sub material'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่มี Part_Number นี้');
			}
			$Part_ID = $re1->fetch_array(MYSQLI_ASSOC)['Part_ID'];


			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Sub_Part_ID
			FROM 
				tbl_part_master 
			WHERE 
				Part_No = '$Sub_Part_No' AND Part_Type = 'Assembly part'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่มี Part_Number นี้');
			}
			$Sub_Part_ID = $re1->fetch_array(MYSQLI_ASSOC)['Sub_Part_ID'];

			//exit($Part_ID);

			$sql = "SELECT 
				BIN_TO_UUID(Sub_Part_ID,TRUE) AS Sub_Part_ID,
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID
			FROM 
				tbl_sub_part
			WHERE 
				BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'
					AND BIN_TO_UUID(Sub_Part_ID,TRUE) = '$Sub_Part_ID'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มีการเพิ่ม Part นี้แล้ว');
			}

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Sub_Part_ID
			FROM 
				tbl_part
			WHERE 
				Part_No = '$Part_No'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				//exit('1');
				$sql = "INSERT INTO tbl_part (
					Part_ID,
					Part_No,
					Creation_DateTime)
				VALUES (
					UUID_TO_BIN('$Part_ID',TRUE),
				'$Part_No',
				now()
				)";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
				}

				$sql = "INSERT INTO tbl_sub_part (
					Part_ID,
					Sub_Part_ID,
					Used,
					Creation_DateTime )
					SELECT
					Part_ID,
					UUID_TO_BIN('$Sub_Part_ID',TRUE),
					$Used,
					NOW()
					FROM 
						tbl_part
					WHERE 
						Part_ID = UUID_TO_BIN('$Part_ID',TRUE)";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
				}
			} else {

				$sql = "INSERT INTO tbl_sub_part (
					Part_ID,
					Sub_Part_ID,
					Used,
					Creation_DateTime)
				VALUES (
					UUID_TO_BIN('$Part_ID',TRUE),
					UUID_TO_BIN('$Sub_Part_ID',TRUE),
					$Used,
					now()
				)";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
				}
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
	if ($_SESSION['xxxRole']->{'MaterialMaster'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {


		$dataParams = array(
			'obj',
			'obj=>ID:s:0:0',
			'obj=>Part_No:s:0:0',
			'obj=>Sub_Part_No:s:0:0',
			'obj=>Used:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID 
			FROM 
				tbl_part_master 
			WHERE 
				Part_No = '$Part_No' AND Part_Type = 'Sub material'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่มี Part_Number นี้');
			}
			$Part_ID = $re1->fetch_array(MYSQLI_ASSOC)['Part_ID'];


			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Sub_Part_ID
			FROM 
				tbl_part_master 
			WHERE 
				Part_No = '$Sub_Part_No' AND Part_Type = 'Assembly part'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่มี Part_Number นี้');
			}
			$Sub_Part_ID = $re1->fetch_array(MYSQLI_ASSOC)['Sub_Part_ID'];

			//exit($Part_ID);

			// $sql = "SELECT 
			// 	BIN_TO_UUID(Sub_Part_ID,TRUE) AS Sub_Part_ID,
			// 	BIN_TO_UUID(Part_ID,TRUE) AS Part_ID
			// FROM 
			// 	tbl_sub_part
			// WHERE 
			// 	BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'
			// 		AND BIN_TO_UUID(Sub_Part_ID,TRUE) = '$Sub_Part_ID'";
			// $re1 = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re1->num_rows > 0) {
			// 	throw new Exception('มีการเพิ่ม Part นี้แล้ว');
			// }


			$sql = "UPDATE tbl_sub_part
			SET 
				Part_ID = UUID_TO_BIN('$Part_ID',TRUE),
				Sub_Part_ID = UUID_TO_BIN('$Sub_Part_ID',TRUE),
				Used = $Used
			WHERE
				BIN_TO_UUID(ID,TRUE) = '$ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้');
			}

			//exit($Part_ID);

			$mysqli->commit();


			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'MaterialMaster'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'MaterialMaster'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

function select_group($mysqli)
{

	$sql = "SELECT 
		BIN_TO_UUID(tsub.ID, TRUE) AS ID,
		BIN_TO_UUID(tsub.Part_ID, TRUE) AS Part_ID,
		BIN_TO_UUID(tsub.Sub_Part_ID, TRUE) AS Sub_Part_ID,
		(SELECT 
				Part_No
			FROM
				tbl_part tpt
			WHERE
				tsub.Part_ID = tpt.Part_ID) AS Part_No,
		(SELECT 
				Part_No
			FROM
				tbl_part_master tpm
			WHERE
				tsub.Sub_Part_ID = tpm.Part_ID) AS Sub_Part_No,
		tsub.Creation_DateTime,
		Used
	FROM
		tbl_sub_part tsub
	ORDER BY Creation_DateTime DESC;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('Part_ID', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'ID',
			'Part_No',
			'Sub_Part_No',
			'Used',
			'Creation_DateTime',
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['Part_ID'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "Part_ID" => $key1,
			"Part_No" => $value1[0]['Part_No'],
			'Total_Item' => count($value1), "open" => 0, "data" => $sub
		);
		$c++;
	}
	return $dateset;
}

$mysqli->close();
exit();
