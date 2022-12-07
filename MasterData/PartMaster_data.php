<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'PartMaster'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'PartMaster'}[0] == 0) {
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
		$sql = "SELECT 
			BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
			Part_No,
			Part_Name,
			Side,
			Model,
			Color,
			Customer_Code,
			Mat_SAP1,
			Mat_SAP3,
			Mat_GI,
			Packing,
			Type,
			Part_Type,
			Picture,
			SNP_Per_Rack,
			SNP_Per_Box,
			SNP_Per_Bag,
			Width_Part,
			Height_Part,
			Length_Part,
			Weight_Part,
			CONCAT(FORMAT(Width_Part, 0),
					'x',
					FORMAT(Length_Part, 0),
					'x',
					FORMAT(Height_Part, 0)) AS Dimansion,
			tpm.Status,
			DATE_FORMAT(tpm.Creation_Date, '%d/%m/%y') AS Creation_Date
		FROM
			tbl_part_master tpm
				LEFT JOIN
			tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
		WHERE tpm.Status = 'ACTIVE'
		ORDER BY Part_Type ASC;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 5) {
		$val = checkTXT($mysqli, $_GET['filter']['value']);
		if (strlen(trim($val)) == 0) {
			echo "[]";
		}

		$sql = "SELECT 
			Customer_Code AS value
		FROM
			tbl_customer_master
		WHERE
			Customer_Code LIKE '%$val%';";

		if ($re1 = $mysqli->query($sql)) {
			echo json_encode(jsonRow($re1, false, 0));
		} else {
			echo "[{ID:0,value:'ERROR'}]";
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'PartMaster'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {

		$dataParams = array(
			'obj',
			'obj=>Part_No:s:0:5',
			'obj=>Part_Name:s:0:3',
			'obj=>Side:s:0:0',
			'obj=>Model:s:0:0',
			'obj=>Color:s:0:0',
			'obj=>Customer_Code:s:0:0',
			'obj=>Packing:s:0:0',
			//'obj=>Package_Code:s:0:0',
			'obj=>Mat_SAP1:s:0:0',
			'obj=>Mat_SAP3:s:0:0',
			'obj=>Mat_GI:s:0:0',
			'obj=>Type:s:0:0',
			'obj=>Part_Type:s:0:0',
			'obj=>SNP_Per_Rack:i:0:0',
			'obj=>SNP_Per_Box:i:0:0',
			'obj=>SNP_Per_Bag:i:0:0',
			'obj=>Weight_Part:f:0:0',
			'obj=>Width_Part:f:0:0',
			'obj=>Length_Part:f:0:0',
			'obj=>Height_Part:f:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}
		$mysqli->autocommit(FALSE);
		try {

			// $mydir = '../images/part/';
			// $myfiles = array_diff(scan_dir($mydir), array('.', '..'));
			// krsort($myfiles);
			// //print_r($myfiles);
			// $Picture = "../tsra-wh/images/part/" . end($myfiles);
			// //exit($Picture);

			$sql = "SELECT 
				Part_No 
			FROM 
				tbl_part_master 
			WHERE 
				Part_No = '$Part_No'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี Part_Number นี้แล้ว');
			}

			$sql = "SELECT 
				Part_Name 
			FROM 
				tbl_part_master 
			WHERE 
				Part_Name = '$Part_Name'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี Part_Name นี้แล้ว');
			}

			// $sql = "SELECT 
			// 	Package_Code
			// FROM
			// 	tbl_package_master
			// WHERE 
			// 	Package_Code = '$Package_Code';";
			// $re1 = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re1->num_rows == 0) {
			// 	throw new Exception('ไม่พบ Package');
			// }

			//exit($Package_Code);

			if ($Part_Type != 'Sub material') {
				$sql = "SELECT 
					Customer_Code
				FROM
					tbl_customer_master
				WHERE 
					Customer_Code = '$Customer_Code';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบ Customer');
				}
			}




			// Rack_ID,
			// Box_ID,
			// (SELECT Package_ID FROM tbl_package_master WHERE Package_Code = '$Package_Code' AND Package_Type = 'Rack'),
			// (SELECT Package_ID FROM tbl_package_master WHERE Package_Code = '$Package_Code' AND Package_Type = 'Box'),
			$sql = "INSERT INTO tbl_part_master (
				Part_No,
				Part_Name,
				Side, 
				Model,
				Color,
				Mat_SAP1,
				Mat_SAP3,
				Mat_GI,
				Packing,
				Type,
				Part_Type,
				SNP_Per_Rack,
				SNP_Per_Box,
				SNP_Per_Bag,
				Weight_Part,
				Width_Part,
				Length_Part,
				Height_Part,
				Customer_ID,
				Creation_Date,
				Creation_DateTime,
				Created_By_ID )
			VALUES (
				'$Part_No',
			'$Part_Name',
			'$Side',
			'$Model',
			'$Color',
			'$Mat_SAP1',
			'$Mat_SAP3',
			'$Mat_GI',
			'$Packing',
			'$Type',
			'$Part_Type',
			$SNP_Per_Rack,
			$SNP_Per_Box,
			$SNP_Per_Bag,
			$Weight_Part,
			'$Width_Part',
			'$Length_Part',
			'$Height_Part',
			(SELECT Customer_ID FROM tbl_customer_master WHERE Customer_Code = '$Customer_Code'),
			curdate(),
			now(),
			$cBy )";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
			}
			$mysqli->commit();


			$sql = "SELECT 
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
				Part_No,
				Part_Name,
				Side,
				Model,
				Color,
				Customer_Code,
				Mat_SAP1,
				Mat_SAP3,
				Mat_GI,
				Packing,
				Type,
				Part_Type,
				Picture,
				SNP_Per_Rack,
				SNP_Per_Box,
				SNP_Per_Bag,
				Width_Part,
				Height_Part,
				Length_Part,
				Weight_Part,
				CONCAT(FORMAT(Width_Part, 0),
						'x',
						FORMAT(Length_Part, 0),
						'x',
						FORMAT(Height_Part, 0)) AS Dimansion,
				tpm.Status,
				DATE_FORMAT(tpm.Creation_Date, '%d/%m/%y') AS Creation_Date
			FROM
				tbl_part_master tpm
					LEFT JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			WHERE tpm.Status = 'ACTIVE'
			ORDER BY Part_Type ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
			//echo '{"status":"server","mms":"' . $e->getMessage() . '","sname":[]}';
			//closeDB($mysqli);
		}
	} else if ($type == 12) {

		if (!isset($_FILES["upload"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบไฟล์ UPLOAD'));
			closeDB($mysqli);
		}
		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
		$fileName = $randomString . '_' . $_FILES["upload"]["name"];
		$tempName = $_FILES["upload"]["tmp_name"];
		if (move_uploaded_file($tempName, "../images/part/" . $fileName)) {
			$file_info = pathinfo("../images/part/" . $fileName);
			$myfile = fopen("../images/part/" . $file_info['basename'], "r") or die("Unable to open file!");
			fclose($myfile);
		} else echo json_encode(array('status' => 'server', 'mms' => 'ข้อมูลในไฟล์ไม่ถูกต้อง', 'sname' => array()));
	} else if ($type == 13) {


		$dataParams = array(
			'obj',
			'obj=>Part_ID:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) {
			closeDBT($mysqli, 2, join('<br>', $chkPOST));
		}
		$mysqli->autocommit(FALSE);
		try {

			$mydir = '../images/part/';
			$myfiles = array_diff(scan_dir($mydir), array('.', '..'));
			krsort($myfiles);
			//print_r($myfiles);
			$Picture = "../tsra-wh/images/part/" . end($myfiles);
			//exit($Picture);

			// echo($Part_ID);
			// exit();


			$sql = "UPDATE tbl_part_master 
			SET 
				Picture = '$Picture',
				Last_Updated_Date = CURDATE(),
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
			}


			$mysqli->commit();


			$sql = "SELECT 
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
				Part_No,
				Part_Name,
				Side,
				Model,
				Color,
				Customer_Code,
				Mat_SAP1,
				Mat_SAP3,
				Mat_GI,
				Packing,
				Type,
				Part_Type,
				Picture,
				SNP_Per_Rack,
				SNP_Per_Box,
				SNP_Per_Bag,
				Width_Part,
				Height_Part,
				Length_Part,
				Weight_Part,
				CONCAT(FORMAT(Width_Part, 0),
						'x',
						FORMAT(Length_Part, 0),
						'x',
						FORMAT(Height_Part, 0)) AS Dimansion,
				tpm.Status,
				DATE_FORMAT(tpm.Creation_Date, '%d/%m/%y') AS Creation_Date
			FROM
				tbl_part_master tpm
					LEFT JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			WHERE tpm.Status = 'ACTIVE'
			ORDER BY Part_Type ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
			//echo '{"status":"server","mms":"' . $e->getMessage() . '","sname":[]}';
			//closeDB($mysqli);
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'PartMaster'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		$dataParams = array(
			'obj',
			'obj=>Part_ID:s:0:0',
			'obj=>Part_No:s:0:5',
			'obj=>Part_Name:s:0:3',
			'obj=>Side:s:0:0',
			'obj=>Model:s:0:0',
			'obj=>Color:s:0:0',
			'obj=>Customer_Code:s:0:0',
			'obj=>Packing:s:0:0',
			//'obj=>Package_Code:s:0:0',
			'obj=>Mat_SAP1:s:0:0',
			'obj=>Mat_SAP3:s:0:0',
			'obj=>Mat_GI:s:0:0',
			'obj=>Type:s:0:0',
			'obj=>Part_Type:s:0:0',
			'obj=>SNP_Per_Rack:i:0:0',
			'obj=>SNP_Per_Box:i:0:0',
			'obj=>SNP_Per_Bag:i:0:0',
			'obj=>Weight_Part:f:0:0',
			'obj=>Width_Part:f:0:0',
			'obj=>Length_Part:f:0:0',
			'obj=>Height_Part:f:0:0',
			'obj=>Status:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			if ($Part_Type != 'Sub material') {
				$sql = "SELECT 
					Customer_Code
				FROM
					tbl_customer_master
				WHERE 
					Customer_Code = '$Customer_Code';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบ Customer');
				}
			}


			// $sql = "SELECT 
			// 	Package_Code
			// FROM
			// 	tbl_package_master
			// WHERE 
			// 	Package_Code = '$Package_Code';";
			// $re1 = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re1->num_rows == 0) {
			// 	throw new Exception('ไม่พบ Package');
			// }

			// $mydir = '../images/part/';
			// $myfiles = array_diff(scan_dir($mydir), array('.', '..'));
			// krsort($myfiles);
			// //print_r($myfiles);
			// $Picture = "../tsra-wh/images/part/" . end($myfiles);
			// //exit($Picture);


			// Rack_ID = (SELECT Package_ID FROM tbl_package_master WHERE Package_Code = '$Package_Code' AND Package_Type = 'Rack'),
			// Box_ID = (SELECT Package_ID FROM tbl_package_master WHERE Package_Code = '$Package_Code' AND Package_Type = 'Box'),

			//Picture= '$Picture'

			$sql = "UPDATE tbl_part_master 
			SET 
				Part_No = '$Part_No',
				Part_Name = '$Part_Name',
				Side = '$Side',
				Model = '$Model',
				Color = '$Color',
				Customer_ID = (SELECT 
						Customer_ID
					FROM
						tbl_customer_master
					WHERE
						Customer_Code = '$Customer_Code'),
						
				Packing = '$Packing',
				Mat_SAP1 = '$Mat_SAP1',
				Mat_SAP3 = '$Mat_SAP3',
				Mat_GI = '$Mat_GI',
				Type = '$Type',
				Part_Type = '$Part_Type',
				SNP_Per_Rack = $SNP_Per_Rack,
				SNP_Per_Box = $SNP_Per_Box,
				SNP_Per_Bag = $SNP_Per_Bag,
				Status = '$Status',
				Weight_Part = $Weight_Part,
				Width_Part = $Width_Part,
				Length_Part = $Length_Part,
				Height_Part = $Height_Part,
				Last_Updated_Date = CURDATE(),
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้');
			}

			//exit($Part_ID);

			$mysqli->commit();

			$sql = "SELECT
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
				Part_No,
				Part_Name,
				Side,
				Model,
				Color,
				Customer_Code,
				Mat_SAP1,
				Mat_SAP3,
				Mat_GI,
				Packing,
				Type,
				Part_Type,
				Picture,
				SNP_Per_Rack,
				SNP_Per_Box,
				SNP_Per_Bag,
				Width_Part,
				Height_Part,
				Length_Part,
				Weight_Part,
				CONCAT(FORMAT(Width_Part, 0),
						'x',
						FORMAT(Length_Part, 0),
						'x',
						FORMAT(Height_Part, 0)) AS Dimansion,
				tpm.Status,
				DATE_FORMAT(tpm.Creation_Date, '%d/%m/%y') AS Creation_Date
			FROM
				tbl_part_master tpm
					LEFT JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			WHERE tpm.Status = 'ACTIVE'
			ORDER BY Part_Type ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'PartMaster'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'PartMaster'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
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


function scan_dir($dir)
{
	$ignored = array('.', '..', '.svn', '.htaccess'); // -- ignore these file names
	$files = array(); //----------------------------------- create an empty files array to play with
	foreach (scandir($dir) as $file) {
		if ($file[0] === '.') continue; //----------------- ignores all files starting with '.'
		if (in_array($file, $ignored)) continue; //-------- ignores all files given in $ignored
		$files[$file] = filemtime($dir . '/' . $file); //-- add to files list
	}
	arsort($files); //------------------------------------- sort file values (creation timestamps)
	$files = array_keys($files); //------------------------ get all files after sorting
	return ($files) ? $files : false;
}


$mysqli->close();
exit();
