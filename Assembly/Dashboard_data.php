<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'Dashboard'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'Dashboard'}[0] == 0) {
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
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}

		$sql = "SELECT 
			DATE_FORMAT(tplan.Assembly_Date, '%d-%m-%y') AS Assembly_Date,
			BIN_TO_UUID(tplan.Part_ID, TRUE) AS Part_ID,
			Side,
			tpm.Part_No,
			Part_Name,
			tplan.Qty,
			tplan.Assembled_Qty,
			tplan.Assembled_Qty - tplan.Qty AS Balance,
			CONCAT(FORMAT((tplan.Assembled_Qty / tplan.Qty) * 100,
						0),
					'%') AS Ratio,
			if(isnull(SUM(tiv.Qty-tiv.Assembled_Qty)),0,SUM(tiv.Qty-tiv.Assembled_Qty)) AS On_hand
		FROM
			tbl_assembly_plan tplan
				INNER JOIN
			tbl_part_master tpm ON tplan.Part_ID = tpm.Part_ID
				LEFT JOIN
			tbl_inventory tiv ON tplan.Part_ID = tiv.Part_ID
		WHERE
			tplan.Assembly_Date = '$Assembly_Date'
		GROUP BY tplan.Part_ID
		ORDER BY tpm.Creation_DateTime ASC";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 2) {
		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}

		$sql = "SELECT 
			DATE_FORMAT(tap.Assembly_Date, '%d-%m-%y') AS Assembly_Date,
			SUM(Qty) AS Total_Plan,
			SUM(Assembled_Qty) AS Total_Actual,
			SUM(Assembled_Qty - Qty) AS Total_Balance,
			CONCAT(FORMAT((SUM(Assembled_Qty) / SUM(Qty)) * 100,0),'%') AS Success,
			CONCAT(FORMAT(((SUM(Qty) - SUM(Assembled_Qty)) / SUM(Qty)) * 100, 0),'%') AS Failure
		FROM
			tbl_assembly_plan tap
				INNER JOIN
			tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
		WHERE
			Assembly_Date = '$Assembly_Date';";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 3) {
		$dataParams = array(
			'obj',
			'obj=>Assembly_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}

		$sql = "SELECT 
			CONCAT(FORMAT((SUM(Assembled_Qty) / SUM(Qty)) * 100,0),'%') AS Percent,
			'Percent' as Percent1,
			'Success' AS Goal,
			'color' AS color1,
			'#30b358' AS color
		FROM
			tbl_assembly_plan tap
				INNER JOIN
			tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
		WHERE
			Assembly_Date = '$Assembly_Date';";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		$success = jsonRow($re1, true, 0);

		$sql = "SELECT 
			CONCAT(FORMAT(((SUM(Qty) - SUM(Assembled_Qty)) / SUM(Qty)) * 100, 0),'%') AS Percent,
			'Percent' as Percent1,
			'Failure' AS Goal,
			'color' AS color1,
			'#e8591c' AS color
		FROM
			tbl_assembly_plan tap
				INNER JOIN
			tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
		WHERE
			Assembly_Date = '$Assembly_Date';";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		$failure = jsonRow($re1, true, 0);

		$returnData = ['success' => $success, 'failure' => $failure];

		closeDBT($mysqli, 1, $returnData);


		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'Dashboard'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'Dashboard'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'Dashboard'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'Dashboard'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

$mysqli->close();
exit();
