<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ViewRePackEndcap'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ViewRePackEndcap'}[0] == 0) {
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
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'ViewRePackEndcap'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ViewRePackEndcap'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		$obj  = $_POST['obj'];
		$Serial_Number  = $obj;

		$mysqli->autocommit(FALSE);
		try {


			$sql = "SELECT 
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID
			FROM
				tbl_palletizing_header
			WHERE
				Serial_Number = '$Serial_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			$Palletizing_Header_ID = $re1->fetch_array(MYSQLI_ASSOC)['Palletizing_Header_ID'];

			$sql = "SELECT 
				Serial_Number
			FROM
			tbl_palletizing_header
			WHERE 
				Serial_Number = '$Serial_Number'
					AND Pick != '';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('แก้ไขไม่สำเร็จ (มีการ Ship ไปแล้ว) กรุณาไปทำการยกเลิกขั้นตอนการ Ship ก่อน ' . __LINE__);
			}

			$sql = "UPDATE tbl_palletizing_header 
			SET
				Total_Qty = 0,
				Status = 'PENDING',
				Confirm_DateTime = null,
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				Serial_Number = '$Serial_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "UPDATE tbl_palletizing_pre 
			SET
				status = 'PENDING'
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "INSERT INTO
				tbl_transaction(
					Receiving_Header_ID,
				Palletizing_Header_ID,
				Part_ID,
				Serial_ID,
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
				tpp.Receiving_Header_ID,
				tpp.Palletizing_Header_ID,
				tiv.Part_ID,
				tiv.Serial_ID,
				tpp.Qty_Package,
				tpp.Area,
				tpp.Area,
				'EDIT',
				ROW_NUMBER() OVER (ORDER BY tiv.Serial_ID),
				now(),
				$cBy,
				tpp.Location_ID,
				tpp.Location_ID,
				now(),
				$cBy
			FROM
				tbl_palletizing_header tph
					INNER JOIN 
				tbl_palletizing_pre tpp ON tph.Palletizing_Header_ID = tpp.Palletizing_Header_ID 
					INNER JOIN 
				tbl_inventory tiv ON tiv.Serial_ID = tpp.Serial_ID
			WHERE
				tph.Serial_Number = '$Serial_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "WITH a AS (
				SELECT 
					Serial_Number,
					Creation_DateTime,
					MONTH(Creation_DateTime) AS Creation_Month,
					tdate.Date
				FROM 
					tbl_palletizing_header tph
					CROSS JOIN 
						tbl_date tdate
				WHERE 
					YEAR(tdate.Date) = YEAR(curdate())
				ORDER BY 
					Serial_Number, tdate.Date)
				SELECT a.*
				FROM a 
				WHERE Creation_Month = MONTH(curdate()) 
				AND Serial_Number = '$Serial_Number'
				GROUP BY Serial_Number;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่สามารถยกเลิกได้' . __LINE__);
			}

			//exit('สำเร็จ');

			$mysqli->commit();
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}

		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'ViewRePackEndcap'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {

		$obj  = $_POST['obj'];
		$Serial_Number  = $obj;

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID
			FROM
				tbl_palletizing_header
			WHERE
				Serial_Number = '$Serial_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			$Palletizing_Header_ID = $re1->fetch_array(MYSQLI_ASSOC)['Palletizing_Header_ID'];

			$sql = "SELECT 
				Serial_Number
			FROM
			tbl_palletizing_header
			WHERE 
				Serial_Number = '$Serial_Number'
					AND Pick != '';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('แก้ไขไม่สำเร็จ (มีการ Ship ไปแล้ว) กรุณาไปทำการยกเลิกขั้นตอนการ Ship ก่อน ' . __LINE__);
			}

			$sql = "UPDATE tbl_palletizing_header 
			SET
				Status = 'CANCEL',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				Serial_Number = '$Serial_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "UPDATE tbl_palletizing_pre 
			SET
				status = 'CANCEL'
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "INSERT INTO
				tbl_transaction(
					Receiving_Header_ID,
				Palletizing_Header_ID,
				Part_ID,
				Serial_ID,
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
				tpp.Receiving_Header_ID,
				tpp.Palletizing_Header_ID,
				tiv.Part_ID,
				tiv.Serial_ID,
				tpp.Qty_Package,
				tpp.Area,
				tpp.Area,
				'CANCEL',
				ROW_NUMBER() OVER (ORDER BY tiv.Serial_ID),
				now(),
				$cBy,
				tpp.Location_ID,
				tpp.Location_ID,
				now(),
				$cBy
			FROM
				tbl_palletizing_header tph
					INNER JOIN 
				tbl_palletizing_pre tpp ON tph.Palletizing_Header_ID = tpp.Palletizing_Header_ID 
					INNER JOIN 
				tbl_inventory tiv ON tiv.Serial_ID = tpp.Serial_ID
			WHERE
				tph.Serial_Number = '$Serial_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "UPDATE
				tbl_inventory AS tiv,
				(
					SELECT
						*
					FROM
						tbl_palletizing_pre
					WHERE
						BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID'
				) AS tpp
			SET
				tiv.Used_Qty = tiv.Used_Qty-tpp.Qty_Package,
				tiv.Status_Working = 'Wait Re-pack',
				tiv.Last_Updated_DateTime = NOW(),
				tiv.Updated_By_ID = $cBy
			WHERE
				tiv.Serial_ID = tpp.Serial_ID;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
			}


			$sql = "WITH a AS (
				SELECT 
					Serial_Number,
					Creation_DateTime,
					MONTH(Creation_DateTime) AS Creation_Month,
					tdate.Date
				FROM 
					tbl_palletizing_header tph
					CROSS JOIN 
						tbl_date tdate
				WHERE 
					YEAR(tdate.Date) = YEAR(curdate())
				ORDER BY 
					Serial_Number, tdate.Date)
				SELECT a.*
				FROM a 
				WHERE Creation_Month = MONTH(curdate()) 
				AND Serial_Number = '$Serial_Number'
				GROUP BY Serial_Number;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่สามารถยกเลิกได้' . __LINE__);
			}

			//exit('สำเร็จ');

			$mysqli->commit();
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}

		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'ViewRePackEndcap'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');


function select_group($mysqli)
{

	$sql = "SELECT 
    tph.Serial_Number,
	BIN_TO_UUID(tpp.Palletizing_Pre_ID,TRUE) AS Palletizing_Pre_ID,
    BIN_TO_UUID(tpp.Palletizing_Header_ID,TRUE) AS Palletizing_Header_ID,
    DATE_FORMAT(tph.Palletizing_Date, '%d/%m/%y') AS Palletizing_Date,
    tpp.Serial_ID,
    tpp.Part_No,
    tpm.Part_Name,
    tpm.Model,
    tpm.Part_Type,
    tpm.Type,
    tph.Total_Qty,
    tpp.Qty_Package,
    tpp.Area,
    tcm.Customer_Code,
    tph.Status,
    DATE_FORMAT(tph.Confirm_DateTime,
            '%d/%m/%y %H:%i') AS Confirm_DateTime,
	Status_Working,
	tpp.Pick
FROM
    tbl_palletizing_pre tpp
        INNER JOIN
    tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
		INNER JOIN
    tbl_inventory tiv ON tpp.Serial_ID = tiv.Serial_ID
        INNER JOIN
    tbl_part_master tpm ON tpp.Part_ID = tpm.Part_ID
        LEFT JOIN
    tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
	tpm.Type = 'End cap'
		AND tpp.status = 'COMPLETE'
    	AND tph.Status = 'COMPLETE'
ORDER BY Confirm_DateTime DESC, Serial_ID ASC;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('Serial_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'Palletizing_Pre_ID',
			'Serial_ID',
			'Part_No',
			'Part_Name',
			'Model',
			'Qty_Package',
			'Area',
			'Status_Working'
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['Serial_Number'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "Serial_Number" => $key1,
			"Palletizing_Date" => $value1[0]['Palletizing_Date'],
			"Pick" => $value1[0]['Pick'],
			"Status" => $value1[0]['Status'],
			"Part_Type" => $value1[0]['Part_Type'],
			"Type" => $value1[0]['Type'],
			"Area" => $value1[0]['Area'],
			"Customer_Code" => $value1[0]['Customer_Code'],
			"Confirm_DateTime" => $value1[0]['Confirm_DateTime'],
			"Total_Qty" => $value1[0]['Total_Qty'],
			'Total_Item' => count($value1), "open" => 0, "data" => $sub
		);
		$c++;
	}
	return $dateset;
}


$mysqli->close();
exit();
