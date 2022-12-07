<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'UploadPart'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'UploadPart'}[0] == 0) {
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


require('../vendor/autoload.php');
include('../common/common.php');
include('../php/connection.php');
if ($type <= 10) //data
{
	if ($type == 1) {

		$re = select_group($mysqli);
		closeDBT($mysqli, 1, $re);
		//

	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'UploadPart'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'UploadPart'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'UploadPart'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'UploadPart'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {

		if (!isset($_FILES["upload"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบไฟล์ UPLOAD'));
			closeDB($mysqli);
		}
		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
		$fileName = $randomString . '_' . $_FILES["upload"]["name"];
		$tempName = $_FILES["upload"]["tmp_name"];
		if (move_uploaded_file($tempName, "../file_part/" . $fileName)) {
			$file_info = pathinfo("../file_part/" . $fileName);
			$myfile = fopen("../file_part/" . $file_info['basename'], "r") or die("Unable to open file!");
			$data_file = fread($myfile, filesize("../file_part/" . $file_info['basename']));
			$file_ext = pathinfo($fileName, PATHINFO_EXTENSION);
			$allowed_ext = ['xls', 'csv', 'xlsx'];
			fclose($myfile);

			$mysqli->autocommit(FALSE);
			try {
				if (in_array($file_ext, $allowed_ext)) {
					$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('../file_part/' . $fileName);
					$data = $spreadsheet->getActiveSheet()->toArray();
					$count = 0;

					foreach ($data as $row) {
						if ($count > 0) {

							$Part_No = $row[1];
							$Part_Name = $row[2];
							$Mat_SAP1 = $row[3];
							$Mat_SAP3 = $row[4];
							$Model = $row[5];
							$Color = $row[6];
							$Customer = $row[7];
							$Type = $row[8];
							$Packing = $row[9];
							$Mat_GI = $row[10];
							$Side = $row[11];
							$Part_Type = $row[12];
							$Width_Part = $row[13];
							$Length_Part = $row[14];
							$Height_Part = $row[15];

							//$Customer_ID = getCustomerID($mysqli, $Customer);

							$sql = "SELECT 
								BIN_TO_UUID(Customer_ID,true) as Customer_ID
							FROM
								tbl_customer_master
							WHERE 
								Customer_Code = '$Customer';";
							$re1 = sqlError($mysqli, __LINE__, $sql, 1);
							if ($re1->num_rows == 0) {
								throw new Exception('ไม่พบ Customer');
							}
							while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
								$Customer_ID = $row['Customer_ID'];
							}

							$sqlArray[] = array(
								'Part_No' => stringConvert($Part_No),
								'Part_Name' => stringConvert($Part_Name),
								'Model' => stringConvert($Model),
								'Side' => stringConvert($Side),
								'Color' => stringConvert($Color),
								'Mat_SAP3' => stringConvert($Mat_SAP3),
								'Mat_SAP1' => stringConvert($Mat_SAP1),
								'Type' => stringConvert($Type),
								'Part_Type' => stringConvert($Part_Type),
								'Width_Part' => $Width_Part,
								'Length_Part' => $Length_Part,
								'Height_Part' => $Height_Part,
								'Customer_ID' => 'uuid_to_bin("' . $Customer_ID . '",true)',
								'Mat_GI' => stringConvert($Mat_GI),
								'Packing' => stringConvert($Packing),
							);
						} else {
							$count = 1;
						}
					}

					$total = 0;
					if (count($sqlArray) > 0) {
						$sqlName = prepareNameInsert($sqlArray[0]);
						$sqlChunk = array_chunk($sqlArray, 500);

						for ($i = 0, $len = count($sqlChunk); $i < $len; $i++) {
							$sqlValues = prepareValueInsert($sqlChunk[$i]);
							$sql = "INSERT IGNORE INTO tbl_part_master $sqlName VALUES $sqlValues";
							sqlError($mysqli, __LINE__, $sql, 1, 0);
							$total += $mysqli->affected_rows;
						}
						$mysqli->commit();

						if ($total == 0) throw new Exception('ไม่มีรายการอัพเดท' . $mysqli->error);
						echo '{"status":"server","mms":"Upload สำเร็จ ' . $total . '","data":[]}';
						closeDB($mysqli);
					} else {
						echo '{"status":"server","mms":"ไม่พบข้อมูลในไฟล์ ' . count($sqlArray) . '","data":[]}';
						closeDB($mysqli);
					}
				}

				$re = select_group($mysqli);
				closeDBT($mysqli, 1, $re);
			} catch (Exception $e) {
				$mysqli->rollback();
				echo '{"status":"server","mms":"' . $e->getMessage() . '","sname":[]}';
				closeDB($mysqli);
			}
		} else echo json_encode(array('status' => 'server', 'mms' => 'ข้อมูลในไฟล์ไม่ถูกต้อง', 'sname' => array()));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');



function prepareNameInsert($data)
{
	$dataReturn = array();
	foreach ($data as $key => $value) {
		$dataReturn[] = $key;
	}
	return '(' . join(',', $dataReturn) . ')';
}
function prepareValueInsert($data)
{
	$dataReturn = array();
	foreach ($data as $valueAr) {
		$typeV;
		$keyV;
		$valueV;
		$dataAr = array();
		foreach ($valueAr as $key => $value) {
			$keyV = $key;
			$valueV = $value;
			$dataAr[] = $valueV;
		}
		$dataReturn[] = '(' . join(',', $dataAr) . ')';
	}
	return join(',', $dataReturn);
}
function stringConvert($data)
{
	if (strlen($data) > 0) {
		return "'$data'";
	} else {
		return 'null';
	}
}
function insert($mysqli, $tableName, $data, $error)
{
	$sql = "INSERT into $tableName" . prepareInsert($data);
	sqlError($mysqli, __LINE__, $sql, 1);
	if ($mysqli->affected_rows == 0) {
		throw new Exception($error);
	}
}
function convertDate($valueV)
{
	if (strlen($valueV) > 0) {
		if (is_a($valueV, 'DateTime')) {
			$v = "'" . $valueV->format('Y-m-d') . "'";
		} else {
			$valueV1 = explode('-', $valueV);
			$valueV2 = explode('/', $valueV);
			$valueV3 = explode('.', $valueV);
			$valueV4 = strlen($valueV);
			if (count($valueV1) == 3) {
				$v = switchDate($valueV1);
			} else if (count($valueV2) == 3) {
				$v = switchDate($valueV2);
			} else if (count($valueV3) == 3) {
				$v = switchDate($valueV3);
			} else if ($valueV4 == 8) {
				$v = "'" . substr($valueV, 0, 4) . '-' . substr($valueV, 4, 2) . '-' . substr($valueV, 6, 2) . "'";
			} else {
				$UNIX_DATE = ($valueV - 25569) * 86400;
				$v = "'" . gmdate("Y-m-d", $UNIX_DATE) . "'";
			}
		}
	} else {
		return 'null';
	}


	return $v;
}

function switchDate($d)
{
	if (strlen($d[0]) == 4) {
		return "'" . "$d[0]-$d[1]-$d[2]" . "'";
	} else {
		return "'" . "$d[2]-$d[1]-$d[0]" . "'";
	}
}

function select_group($mysqli)
{
	$sql = "SELECT 
		DATE_FORMAT(Receive_Date, '%d/%m/%y') AS Receive_Date,
		DN_Number,
		Serial_ID,
		Part_No,
		Qty,
		BIN_TO_UUID(DN_ID, TRUE) AS DN_ID,
		DATE_FORMAT(Creation_Date, '%d/%m/%y') AS Creation_Date,
		Receive_Status
	FROM
		tbl_dn_order
	ORDER BY DN_ID;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('DN_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'Serial_ID',
			'Part_No',
			'Receive_Status',
			'Qty'
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['DN_Number'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "DN_Number" => $key1,
			"Receive_Date" => $value1[0]['Receive_Date'],
			'Total_Item' => count($value1), "open" => 0, "data" => $value1
		);
		$c++;
	}
	return $dateset;
}

$mysqli->close();
exit();
