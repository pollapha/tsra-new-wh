<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'GenerateGTN'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'GenerateGTN'}[0] == 0) {
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
			tsh.GTN_Number, 
			tsh.Ship_Date, 
			tsh.Ship_Time,
			Invoice_Number,
			Customer_Code AS Ship_To,
			Truck_ID,
			Truck_Driver,
			Truck_Type,
			Freight
		FROM
			tbl_shipping_header tsh
				LEFT JOIN
			tbl_shipping_pre tsp ON tsh.Shipping_Header_ID = tsp.Shipping_Header_ID
				LEFT JOIN
			tbl_customer_master tcm ON tsh.Ship_To = tcm.Customer_ID
		WHERE
			tsh.Created_By_ID = $cBy
				AND tsh.Status_Shipping = 'PENDING'
				AND (tsp.Shipping_Pre_ID IS NULL OR tsp.status = 'PENDING' OR tsp.status = 'CANCEL')
		GROUP BY tsh.GTN_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);

		$header = jsonRow($re1, true, 0);

		$re = [];

		if (count($header) > 0) {
			$GTN_Number = $header[0]['GTN_Number'];
			$re = select_group1($mysqli, $GTN_Number);
		}

		$returnData = ['header' => $header, 'body' => $re];

		closeDBT($mysqli, 1, $returnData);
	}
	if ($type == 3) {

		$sql = "SELECT 
			GTN_Number,
			tsh.Ship_Date, 
			tsh.Ship_Time,
			Invoice_Number,
			Customer_Code AS Ship_To,
			Truck_ID,
			Truck_Driver,
			Truck_Type,
			Freight
		FROM
			tbl_shipping_header tsh
				LEFT JOIN
			tbl_shipping_pre tsp ON tsh.Shipping_Header_ID = tsp.Shipping_Header_ID
				LEFT JOIN
			tbl_customer_master tcm ON tsh.Ship_To = tcm.Customer_ID
		WHERE
			tsh.Created_By_ID = $cBy
				AND tsh.Status_Shipping = 'PENDING'
				AND (tsp.Shipping_Pre_ID IS NULL OR tsp.status = 'PENDING' OR tsp.status = 'CANCEL')
				AND Truck_ID IS NOT NULL
		GROUP BY tsh.GTN_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);

		$header = jsonRow($re1, true, 0);
		$body = [];

		if (count($header) > 0) {
			$GTN_Number = $header[0]['GTN_Number'];
			$sql = "SELECT 
				BIN_TO_UUID(tsh.Shipping_Header_ID, TRUE) AS Shipping_Header_ID,
				BIN_TO_UUID(tsp.Shipping_Pre_ID, TRUE) AS Shipping_Pre_ID,
				tsp.TS_Number
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
			WHERE
				tsh.GTN_Number = '$GTN_Number'
					AND tsp.status = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}
		$returnData = ['header' => $header, 'body' => $body];

		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 2) {
		$sql = "SELECT 
			BIN_TO_UUID(tsh.Shipping_Header_ID, TRUE) AS Shipping_Header_ID,
			BIN_TO_UUID(tsp.Shipping_Pre_ID, TRUE) AS Shipping_Pre_ID,
			tsh.GTN_Number, 
			tsh.Ship_Date,
			tsh.Ship_Time,
			Invoice_Number,
			Customer_Code AS Ship_To,
			Truck_ID,
			Truck_Driver,
			Truck_Type,
			Freight
		FROM
			tbl_shipping_header tsh
				LEFT JOIN
			tbl_shipping_pre tsp ON tsh.Shipping_Header_ID = tsp.Shipping_Header_ID
				LEFT JOIN
			tbl_customer_master tcm ON tsh.Ship_To = tcm.Customer_ID
		WHERE
			tsh.Created_By_ID = $cBy
				AND tsh.Status_Shipping = 'PENDING'
				AND (tsp.Shipping_Pre_ID IS NULL OR tsp.status = 'PENDING' OR tsp.status = 'CANCEL')
		GROUP BY tsh.GTN_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);

		$header = jsonRow($re1, true, 0);

		if (count($header) > 0) {

			$re = select_group($mysqli);
		}

		$returnData = ['header' => $header, 'body' => $re];

		closeDBT($mysqli, 1, $returnData);
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
	} else if ($type == 6) {
		$val = checkTXT($mysqli, $_GET['filter']['value']);
		if (strlen(trim($val)) == 0) {
			echo "[]";
		}

		$sql = "SELECT 
			Location_Code AS value
		FROM
			tbl_location_master
		WHERE
			Location_Code LIKE '%$val%'
				AND Area = 'Truck Sim';";

		if ($re1 = $mysqli->query($sql)) {
			echo json_encode(jsonRow($re1, false, 0));
		} else {
			echo "[{ID:0,value:'ERROR'}]";
		}
	} else if ($type == 7) {
		$val = checkTXT($mysqli, $_GET['filter']['value']);
		if (strlen(trim($val)) == 0) {
			echo "[]";
		}

		$sql = "SELECT 
			TS_Number AS value
		FROM
			tbl_picking_header
		WHERE
			TS_Number LIKE '%$val%'
				AND Status_Picking = 'COMPLETE';";

		if ($re1 = $mysqli->query($sql)) {
			echo json_encode(jsonRow($re1, false, 0));
		} else {
			echo "[{ID:0,value:'ERROR'}]";
		}
	} else if ($type == 8) {
		$dataParams = array(
			'obj',
			'obj=>GTN_Number:s:0:1',
			'obj=>Ship_Date:s:0:1',
			'obj=>Ship_Time:s:0:1',
			'obj=>Invoice_Number:s:0:1',
			'obj=>Ship_To:s:0:1',
			'obj=>Truck_ID:s:0:1',
			'obj=>Truck_Driver:s:0:1',
			'obj=>Truck_Type:s:0:1',
			//'obj=>Freight:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));


		$sql = "SELECT 
				BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
			FROM
				tbl_customer_master
			WHERE
				Customer_Code = '$Ship_To';";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		if ($re1->num_rows == 0) {
			throw new Exception('ไม่พบข้อมูล' . __LINE__);
		}
		while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
			$Customer_ID = $row['Customer_ID'];
		}


		$re = select_group($mysqli, $Customer_ID);
		closeDBT($mysqli, 1, $re);

		//

	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'GenerateGTN'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {

		$dataParams = array(
			'obj',
			'obj=>Ship_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			// สร้างเลขที่เอกสาร GTN Number
			$GTN_Number = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('gtn',0) GTN_Number", 1))->fetch_array(MYSQLI_ASSOC)['GTN_Number'];

			// สร้างเลขที่เอกสาร Invoice_Number
			$Invoice_Number = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('invoice',0) Invoice_Number", 1))->fetch_array(MYSQLI_ASSOC)['Invoice_Number'];

			$sql = "SELECT 
				GTN_Number
			FROM
				tbl_shipping_header
			WHERE
				GTN_Number = '$GTN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี GTN Number นี้แล้ว' . __LINE__);
			}

			//เพิ่ม GTN_Number
			$sql = "INSERT INTO tbl_shipping_header (
				GTN_Number,
				Ship_Date,
				Ship_Time,
				Invoice_Number,
				Creation_DateTime,
				Created_By_ID,
				Last_Updated_DateTime,
				Updated_By_ID)
			values('$GTN_Number','$Ship_Date',now(), '$Invoice_Number', now(), $cBy, now(), $cBy)";
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
	} else if ($type == 12) {

		$dataParams = array(
			'obj',
			'obj=>GTN_Number:s:0:1',
			'obj=>Ship_Date:s:0:1',
			'obj=>Ship_Time:s:0:1',
			'obj=>Invoice_Number:s:0:1',
			//'obj=>Ship_To:s:0:1',
			'obj=>Truck_ID:s:0:1',
			'obj=>Truck_Driver:s:0:1',
			'obj=>Truck_Type:s:0:1',
			//'obj=>Freight:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				GTN_Number
			FROM
				tbl_shipping_header
			WHERE
				GTN_Number = '$GTN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$GTN_Number = $row['GTN_Number'];
			}

			// $sql = "SELECT 
			// 	BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
			// FROM
			// 	tbl_customer_master
			// WHERE
			// 	Customer_Code = '$Ship_To';";
			// $re1 = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re1->num_rows == 0) {
			// 	throw new Exception('ไม่พบข้อมูล' . __LINE__);
			// }
			// while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
			// 	$Customer_ID = $row['Customer_ID'];
			// }


			$sql = "UPDATE tbl_shipping_header
			SET
				Truck_ID = '$Truck_ID',
				Truck_Driver = '$Truck_Driver',
				Truck_Type = '$Truck_Type',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				GTN_Number = '$GTN_Number';";
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
	} else if ($type == 13) {

		$dataParams = array(
			'obj',
			'obj=>GTN_Number:s:0:1',
			'obj=>Ship_Date:s:0:1',
			'obj=>Ship_Time:s:0:1',
			'obj=>Invoice_Number:s:0:1',
			//'obj=>Ship_To:s:0:1',
			'obj=>Truck_ID:s:0:1',
			'obj=>Truck_Driver:s:0:1',
			'obj=>Truck_Type:s:0:1',
			//'obj=>TS_Number:s:0:1',
			//'obj=>Freight:s:0:1',
			//'obj=>Serial_Number:s:0:1',
			//'obj=>Location_Code:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Shipping_Header_ID, TRUE) AS Shipping_Header_ID
			FROM
				tbl_shipping_header
			WHERE
				Ship_Date = '$Ship_Date'
					AND GTN_Number = '$GTN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Shipping_Header_ID = $row['Shipping_Header_ID'];
			}

			$sql = "INSERT INTO tbl_shipping_pre (
				Shipping_Header_ID,
				TS_Number,
				Area,
				Location_ID,
				Creation_DateTime,
				Created_By_ID)
				SELECT 
					UUID_TO_BIN('$Shipping_Header_ID', TRUE),
					pickh.TS_Number,
					pickp.Area,
					pickp.Location_ID,
					NOW(),
					$cBy
				FROM
					tbl_picking_pre pickp
						INNER JOIN
					tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
				WHERE
					pickh.Pick = 'Y'
						AND pickh.Confirm_Picking_DateTime IS NOT NULL
						AND pickh.Updated_By_ID = $cBy
						GROUP BY pickh.TS_Number;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "UPDATE tbl_picking_header
				SET 
					Pick = 'N',
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = $cBy
				WHERE 
					Pick = 'Y'";
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
	} else if ($type == 14) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$TS_Number  = $explode[0];
		$state  = $explode[1];

		$mysqli->autocommit(FALSE);
		try {


			$sql = "SELECT 
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID,
				BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
				pickh.TS_Number,
				tpm.Type,
				Total_Qty,
				Confirm_Picking_DateTime

			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
			WHERE
				Status_Picking = 'COMPLETE'
					AND pickh.TS_Number = '$TS_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			if ($state == 'on') {
				$sql = "UPDATE tbl_picking_header
					SET
						Pick = 'Y',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						TS_Number = '$TS_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			} else {
				$sql = "UPDATE tbl_picking_header
					SET
						Pick = '',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						TS_Number = '$TS_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}


			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 15) {

		$dataParams = array(
			'obj',
			'obj=>GTN_Number:s:0:1',
			'obj=>Ship_Date:s:0:1',
			'obj=>Ship_Time:s:0:1',
			'obj=>Invoice_Number:s:0:1',
			//'obj=>Ship_To:s:0:1',
			'obj=>Truck_ID:s:0:1',
			'obj=>Truck_Driver:s:0:1',
			'obj=>Truck_Type:s:0:1',
			'obj=>TS_Number:s:0:1',
			//'obj=>Freight:s:0:1',
			//'obj=>Serial_Number:s:0:1',
			//'obj=>Location_Code:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Shipping_Header_ID, TRUE) AS Shipping_Header_ID
			FROM
				tbl_shipping_header
			WHERE
				Ship_Date = '$Ship_Date'
					AND GTN_Number = '$GTN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Shipping_Header_ID = $row['Shipping_Header_ID'];
			}


			$sql = "SELECT 
				Serial_Number
			FROM
				tbl_picking_header
			WHERE
				TS_Number = '$TS_Number'
					AND Status_Picking = 'COMPLETE'
					AND Pick = 'N';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('TS_Number นี้เพิ่มไปแล้ว' . __LINE__);
			}

			$sql = "SELECT 
				Serial_Number
			FROM
				tbl_picking_header
			WHERE
				TS_Number = '$TS_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}


			$sql = "INSERT INTO tbl_shipping_pre (
				Shipping_Header_ID,
				TS_Number,
				Area,
				Location_ID,
				Creation_DateTime,
				Created_By_ID)
				SELECT 
					UUID_TO_BIN('$Shipping_Header_ID', TRUE),
					pickh.TS_Number,
					pickp.Area,
					pickp.Location_ID,
					NOW(),
					$cBy
				FROM
					tbl_picking_pre pickp
						INNER JOIN
					tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
				WHERE
					pickh.TS_Number = '$TS_Number'
						AND pickh.Confirm_Picking_DateTime IS NOT NULL
						GROUP BY pickh.TS_Number;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "UPDATE tbl_picking_header
				SET 
					Pick = 'N',
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = $cBy
				WHERE 
					TS_Number = '$TS_Number'";
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
	if ($_SESSION['xxxRole']->{'GenerateGTN'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'GenerateGTN'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$Shipping_Header_ID  = $explode[0];
		$Shipping_Pre_ID  = $explode[1];

		// echo($Shipping_Header_ID);
		// exit();

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				TS_Number
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
			WHERE
				BIN_TO_UUID(tsp.Shipping_Pre_ID, TRUE) = '$Shipping_Pre_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$TS_Number = $row['TS_Number'];
			}

			// echo($Serial_ID . ' ' . $WorkOrder);
			// exit();


			$sql = "UPDATE tbl_picking_header
			SET
				Pick = '',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				TS_Number = '$TS_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "DELETE FROM tbl_shipping_pre
			WHERE
				BIN_TO_UUID(Shipping_Pre_ID, TRUE) = '$Shipping_Pre_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถลบได้' . __LINE__);
			}


			$mysqli->commit();
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}

		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'GenerateGTN'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {

		$dataParams = array(
			'obj',
			'obj=>GTN_Number:s:0:1',
			'obj=>Ship_Date:s:0:1',
			'obj=>Ship_Time:s:0:1',
			'obj=>Invoice_Number:s:0:1',
			'obj=>Ship_To:s:0:0',
			'obj=>Truck_ID:s:0:1',
			'obj=>Truck_Driver:s:0:1',
			'obj=>Truck_Type:s:0:1',
			//'obj=>Freight:s:0:1',
			'obj=>Location_Code:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(tsh.Shipping_Header_ID, TRUE) AS Shipping_Header_ID
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
			WHERE
				GTN_Number = '$GTN_Number'
					AND status = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Shipping_Header_ID = $row['Shipping_Header_ID'];
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
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Location_ID = $row['Location_ID'];
				$Area = $row['Area'];
			}

			//exit($Area);

			$sql = "SELECT 
				pickp.Serial_ID, BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID, 
				SUM(pickp.Qty_Package) AS Qty_Package
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
					INNER JOIN
				tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
					INNER JOIN
				tbl_report trt ON pickp.Serial_ID = trt.Serial_ID
					AND pickp.Part_ID = trt.Part_ID
			WHERE
				BIN_TO_UUID(tsp.Shipping_Header_ID, TRUE) = '$Shipping_Header_ID'
			GROUP BY pickp.Serial_ID, pickp.Part_ID;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Serial_ID = $row['Serial_ID'];
				$Part_ID = $row['Part_ID'];
				$Qty_Package = $row['Qty_Package'];

				// echo($Serial_ID);

				$sql = "UPDATE tbl_report trt
						INNER JOIN
					tbl_picking_pre pickp ON trt.Serial_ID = pickp.Serial_ID
							AND pickp.Part_ID = trt.Part_ID
						INNER JOIN
					tbl_picking_header pickh ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
						INNER JOIN
					tbl_shipping_pre tsp ON tsp.TS_Number = pickh.TS_Number
				SET 
					trt.Delivery_Qty = trt.Delivery_Qty+$Qty_Package,
					trt.Status = 'Delivery', 
					trt.Main_Status = 'OUT', 
					trt.Last_Updated_DateTime = NOW(), 
					trt.Updated_By_ID = $cBy
				WHERE
					BIN_TO_UUID(tsp.Shipping_Header_ID,TRUE) = '$Shipping_Header_ID'
						AND trt.Serial_ID = '$Serial_ID'
						AND BIN_TO_UUID(trt.Part_ID,TRUE) = '$Part_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}


				$sql = "UPDATE tbl_inventory tiv
						INNER JOIN
					tbl_picking_pre pickp ON tiv.Serial_ID = pickp.Serial_ID
							AND pickp.Part_ID = tiv.Part_ID
						INNER JOIN
					tbl_picking_header pickh ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
						INNER JOIN
					tbl_shipping_pre tsp ON tsp.TS_Number = pickh.TS_Number
				SET 
					tiv.Ship = '',
					tiv.Area = '$Area',
					tiv.Location_ID = UUID_TO_BIN('$Location_ID',TRUE),
					tiv.Last_Updated_DateTime = NOW(),
					tiv.Updated_By_ID = $cBy
				WHERE
					BIN_TO_UUID(tsp.Shipping_Header_ID,TRUE) = '$Shipping_Header_ID'
						AND tiv.Serial_ID = '$Serial_ID'
						AND BIN_TO_UUID(tiv.Part_ID,TRUE) = '$Part_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			//exit('s');


			$sql = "INSERT INTO
				tbl_transaction(
					Picking_Header_ID,
				Shipping_Header_ID,
				Receiving_Header_ID,
				Palletizing_Header_ID,
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
					pickh.Picking_Header_ID,
				tsp.Shipping_Header_ID,
				pickp.Receiving_Header_ID,
				pickp.Palletizing_Header_ID,
				pickp.Part_ID,
				pickp.Serial_ID,
				pickp.WorkOrder,
				pickp.Qty_Package,
				pickp.Area,
				'$Area',
				'OUT',
				ROW_NUMBER() OVER (ORDER BY tsp.Shipping_Pre_ID),
				now(),
				$cBy,
				pickp.Location_ID,
				UUID_TO_BIN('$Location_ID',TRUE),
				now(),
				$cBy
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
					INNER JOIN
				tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
					INNER JOIN
				tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
			WHERE
				tsh.GTN_Number = '$GTN_Number';";
			//exit($sql);
			//GROUP BY pickp.Serial_ID , pickp.WorkOrder
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "SELECT 
				SUM(Qty_Package) AS Qty
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
					INNER JOIN
				tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
			WHERE
				BIN_TO_UUID(tsp.Shipping_Header_ID, TRUE) = '$Shipping_Header_ID'
				GROUP BY tsp.Shipping_Header_ID;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Qty = $row['Qty'];
			}

			// echo($Qty);
			// exit();

			$sql = "UPDATE tbl_shipping_pre 
			SET 
				status = 'COMPLETE'
			WHERE
				BIN_TO_UUID(Shipping_Header_ID, TRUE) = '$Shipping_Header_ID'
					AND status = 'PENDING';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "SELECT 
				Count(Trip_Number) AS Trip_Number
			FROM
				tbl_shipping_header
			WHERE
				Ship_Date = '$Ship_Date'
					AND Status_Shipping = 'COMPLETE';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Trip_Number = $row['Trip_Number'];
			}
			// echo($Trip_Number);
			// exit();


			$sql = "UPDATE tbl_shipping_header 
			SET 
				Total_Qty = $Qty,
				Status_Shipping = 'COMPLETE',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				BIN_TO_UUID(Shipping_Header_ID, TRUE) = '$Shipping_Header_ID'
					AND Status_Shipping = 'PENDING';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "UPDATE tbl_shipping_header 
			SET 
				Trip_Number = $Trip_Number+1,
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				BIN_TO_UUID(Shipping_Header_ID, TRUE) = '$Shipping_Header_ID';";
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


function select_group($mysqli, $Customer_ID)
{

	$sql = "SELECT 
		pickh.TS_Number,
		BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
		DATE_FORMAT(pickh.Pick_Date, '%d/%m/%y') AS Pick_Date,
		DATE_FORMAT(pickh.Confirm_Picking_DateTime,
				'%d/%m/%y %H:%i') AS Confirm_Picking_DateTime,
		IF(tph.Serial_Number IS NULL,
			pickp.Serial_ID,
			tph.Serial_Number) AS Serial_Package,
		pickp.Serial_ID,
		pickp.PO_Number,
		tpm.Part_No,
		tpm.Part_Name,
		tpm.Model,
		tpm.Type,
		Customer_Code,
		pickh.Status_Picking,
		IF(tph.Serial_Number IS NULL,
			pickp.Qty_Package,
			SUM(tpp.Qty_Package)) AS Qty_Package,
		pickh.Total_Qty,
		pickh.Pick
	FROM
		tbl_picking_pre pickp
			INNER JOIN
		tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
			INNER JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			INNER JOIN
		tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
			LEFT JOIN
		tbl_palletizing_pre tpp ON pickp.Serial_ID = tpp.Serial_ID
			AND pickp.Created_By_ID = tpp.Updated_By_ID
			AND tpp.status != 'CANCEL'
			LEFT JOIN
		tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
	WHERE
		pickp.status = 'COMPLETE'
			AND pickh.Status_Picking = 'COMPLETE'
			AND BIN_TO_UUID(tpm.Customer_ID, TRUE) = '$Customer_ID'
	GROUP BY Serial_Package , tpm.Part_ID
	ORDER BY Serial_Package ASC;";
	exit($sql);
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('TS_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'Serial_Package',
			'Part_No',
			'Part_Name',
			'PO_Number',
			'Model',
			'Qty_Package',
			'Picking_Pre_ID',
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['TS_Number'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "TS_Number" => $key1,
			"Pick_Date" => $value1[0]['Pick_Date'],
			"Type" => $value1[0]['Type'],
			"Pick" => $value1[0]['Pick'],
			"Customer_Code" => $value1[0]['Customer_Code'],
			"Status_Picking" => $value1[0]['Status_Picking'],
			"Confirm_Picking_DateTime" => $value1[0]['Confirm_Picking_DateTime'],
			"Total_Qty" => $value1[0]['Total_Qty'],
			'Total_Item' => count($value1), "open" => 0, "data" => $sub
		);
		$c++;
	}
	return $dateset;
}

function select_group1($mysqli, $GTN_Number)
{
	$sql = "SELECT 
    BIN_TO_UUID(tsh.Shipping_Header_ID, TRUE) AS Shipping_Header_ID,
    BIN_TO_UUID(tsp.Shipping_Pre_ID, TRUE) AS Shipping_Pre_ID,
    BIN_TO_UUID(pickp.Part_ID, TRUE) AS Part_ID,
    tsp.TS_Number,
    IF(tph.Serial_Number IS NULL,
			pickp.Serial_ID,
			tph.Serial_Number) AS Serial_Package,
    pickp.Serial_ID,
    pickp.WorkOrder,
    pickp.Part_No,
    tpm.Part_Name,
    tpm.Model,
    tpm.Type,
    pickh.Total_Qty,
    IF(tph.Serial_Number IS NULL,
			pickp.Qty_Package,
			SUM(pickp.Qty_Package)) AS Qty_Package,
    tcm.Customer_Code,
    pickh.Status_Picking,
    pickh.Pick,
    DATE_FORMAT(pickh.Confirm_Picking_DateTime,
            '%d/%m/%y %H:%i') AS Confirm_Picking_DateTime
FROM
    tbl_shipping_pre tsp
        INNER JOIN
    tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
        INNER JOIN
    tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
        INNER JOIN
    tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
        INNER JOIN
    tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
        LEFT JOIN
    tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
		LEFT JOIN
	tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
		AND tph.Status != 'CANCEL'
WHERE
    tsh.GTN_Number = '$GTN_Number'
        AND tsp.status = 'PENDING'
GROUP BY Serial_Package , tpm.Part_ID
ORDER BY Confirm_Picking_DateTime ASC , Serial_Package ASC;";
//exit($sql);
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('TS_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'Serial_Package',
			'WorkOrder',
			'Part_No',
			'Part_Name',
			'Model',
			'Qty_Package',
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['TS_Number'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "TS_Number" => $key1,
			"Shipping_Header_ID" => $value1[0]['Shipping_Header_ID'],
			"Shipping_Pre_ID" => $value1[0]['Shipping_Pre_ID'],
			"Pick" => $value1[0]['Pick'],
			"Type" => $value1[0]['Type'],
			"Customer_Code" => $value1[0]['Customer_Code'],
			"Status_Picking" => $value1[0]['Status_Picking'],
			"Confirm_Picking_DateTime" => $value1[0]['Confirm_Picking_DateTime'],
			"Total_Qty" => $value1[0]['Total_Qty'],
			'Total_Item' => count($value1), "open" => 0, "data" => $sub
		);
		$c++;
	}
	return $dateset;
}

$mysqli->close();
exit();
