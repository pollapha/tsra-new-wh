<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'TranferLocation'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'TranferLocation'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
if($type<=10)//data
{
	if($type == 1)
	{
		$dataParams = array(
			'obj',
			'obj=>Serial_ID:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$Number = substr($Serial_ID, 0, 2);

		//exit($Number);
		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT
				BIN_TO_UUID(Location_ID,TRUE) AS Location_ID
			FROM 
				tbl_location_master
			WHERE 
				Location_Code = 'N/A';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Location_ID = $row['Location_ID'];
			}

			$sql = "SELECT 
				tiv.Serial_ID,
				tpm.Part_No,
				tpm.Part_Name,
				tpm.Type,
				tiv.Qty,
				tiv.Area,
				tlm.Location_Code
			FROM
				tbl_inventory tiv
					LEFT JOIN 
				tbl_location_master tlm ON tiv.Location_ID = tlm.Location_ID
					LEFT JOIN 
				tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
			WHERE
				tiv.Serial_ID = '$Serial_ID'
					AND BIN_TO_UUID(tiv.Location_ID,TRUE) != '$Location_ID'
					AND Status_Working != 'Confirm Shipped';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

				$mysqli->commit();
				closeDBT($mysqli, 1, jsonRow($re1, true, 0));

		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	
	}
	else if ($type == 2) {
		$dataParams = array(
			'obj',
			'obj=>Serial_ID:s:0:1',
			'obj=>Location_Code:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Location_Code
			FROM 
				tbl_location_master
			WHERE 
				Location_Code = '$Location_Code';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			$mysqli->commit();
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 3) {
		
		$dataParams = array(
			'obj',
			'obj=>Serial_ID:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$Number = substr($Serial_ID, 0, 2);

		//exit($Number);
		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				tiv.Serial_ID,
				tpm.Part_No,
				tpm.Part_Name,
				tpm.Type,
				tiv.Qty,
				tiv.Area,
				tlm.Location_Code
			FROM
				tbl_inventory tiv
					LEFT JOIN 
				tbl_location_master tlm ON tiv.Location_ID = tlm.Location_ID
					LEFT JOIN 
				tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
			WHERE
				tiv.Serial_ID = '$Serial_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

				$mysqli->commit();
				closeDBT($mysqli, 1, jsonRow($re1, true, 0));

		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}

	} else if ($type == 5) {
		$val = checkTXT($mysqli, $_GET['filter']['value']);
		if (strlen(trim($val)) == 0) {
			echo "[]";
		}

		$sql = "SELECT 
			Location_Code AS value
		FROM
			tbl_location_master
		WHERE
			Location_Code LIKE '%$val%';";
		if ($re1 = $mysqli->query($sql)) {
			echo json_encode(jsonRow($re1, false, 0));
		} else {
			echo "[{ID:0,value:'ERROR'}]";
		}
	} else if ($type == 6) {
		$val = checkTXT($mysqli, $_GET['filter']['value']);
		if (strlen(trim($val)) == 0) {
			echo "[]";
		}

		$sql = "SELECT
			BIN_TO_UUID(Location_ID,TRUE) AS Location_ID
		FROM 
			tbl_location_master
		WHERE 
			Location_Code = 'N/A';";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		if ($re1->num_rows == 0) {
			throw new Exception('ไม่พบข้อมูล' . __LINE__);
		}
		while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
			$Location_ID = $row['Location_ID'];
		}

		$sql = "SELECT 
			Serial_ID AS value
		FROM
			tbl_inventory
		WHERE
			Serial_ID LIKE '%$val%'
				AND BIN_TO_UUID(Location_ID,TRUE) != '$Location_ID'
				AND Status_Working != 'Confirm Shipped'
			GROUP BY Serial_ID
		ORDER BY Creation_DateTime DESC 
		LIMIT 5;";
		if ($re1 = $mysqli->query($sql)) {
			echo json_encode(jsonRow($re1, false, 0));
		} else {
			echo "[{ID:0,value:'ERROR'}]";
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'TranferLocation'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{

	}
	else if($type == 12)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>20 && $type<=30)//update
{
	if($_SESSION['xxxRole']->{'TranferLocation'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'TranferLocation'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'TranferLocation'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		$dataParams = array(
			'obj',
			'obj=>Serial_ID:s:0:1',
			'obj=>Location_Code:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(tiv.Location_ID, TRUE) AS Location_ID,
				tiv.Serial_ID,
				tiv.Qty,
				tiv.Area
			FROM
				tbl_inventory tiv
			WHERE
				tiv.Serial_ID = '$Serial_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Old_Area = $row['Area'];
				$Old_Location_ID = $row['Location_ID'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(Location_ID,TRUE) AS Location_ID,
				Area
			FROM 
				tbl_location_master
			WHERE 
				Location_Code = '$Location_Code';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$New_Area = $row['Area'];
				$New_Location_ID = $row['Location_ID'];
			}

			$sql = "UPDATE tbl_inventory tiv
			SET 
				tiv.Location_ID = UUID_TO_BIN('$New_Location_ID',TRUE), 
				tiv.Area = '$New_Area',
				tiv.Last_Updated_DateTime = NOW(), 
				tiv.Updated_By_ID = $cBy
			WHERE 
				Serial_ID = '$Serial_ID'";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			// if($Old_Area == 'Assembly'){
				$sql = "INSERT INTO
					tbl_transaction(
						Receiving_Header_ID,
					Receiving_Pre_ID,
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
					Receiving_Header_ID,
					Receiving_Pre_ID,
					Part_ID,
					Serial_ID,
					WorkOrder,
					Qty,
					'$Old_Area',
					Area,
					'MOVE',
					ROW_NUMBER() OVER (ORDER BY Serial_ID, WorkOrder),
					now(),
					$cBy,
					UUID_TO_BIN('$Old_Location_ID',TRUE),
					Location_ID,
					now(),
					$cBy
				FROM
					tbl_inventory
				WHERE
					Serial_ID = '$Serial_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			// }

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

$mysqli->close();
exit();
?>
