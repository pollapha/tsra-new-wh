<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ConfirmShip'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ConfirmShip'}[0] == 0) {
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
		$dataParams = array(
			'obj',
			'obj=>GTN_Number:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);

		try {

			$sql = "SELECT 
			GTN_Number,
			Trip_Number,
			Ship_Date,
			Ship_Time,
			Customer_Code,
			Total_Qty,
			Status_Shipping,
			Confirm_Shipping_DateTime,
			Invoice_Number,
			Truck_ID,
			Truck_Driver,
			Truck_Type,
			Freight
		FROM
			tbl_shipping_header tsh
				LEFT JOIN
			tbl_customer_master tcm ON tsh.Ship_To = tcm.Customer_ID
		WHERE
			GTN_Number = '$GTN_Number'
				AND Status_Shipping = 'CONFIRM SHIP';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('GTN นี้ Confirm แล้ว' . __LINE__);
			}

			$mysqli->commit();

			$re = select_group($mysqli, $GTN_Number);
			closeDBT($mysqli, 1, $re);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 4) {
		$val = checkTXT($mysqli, $_GET['filter']['value']);
		if (strlen(trim($val)) == 0) {
			echo "[]";
		}

		$sql = "SELECT 
			GTN_Number AS value
		FROM
			tbl_shipping_header
		WHERE
			GTN_Number LIKE '%$val%'
				AND Status_Shipping = 'COMPLETE'
				GROUP BY GTN_Number
		LIMIT 5;";

		if ($re1 = $mysqli->query($sql)) {
			echo json_encode(jsonRow($re1, false, 0));
		} else {
			echo "[{ID:0,value:'ERROR'}]";
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'ConfirmShip'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ConfirmShip'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'ConfirmShip'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'ConfirmShip'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
		$dataParams = array(
			'obj',
			'obj=>GTN_Number:s:0:1',
			//'obj=>Invoice_Number:s:0:1'
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
				GTN_Number = '$GTN_Number'
					AND Status_Shipping = 'COMPLETE';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Shipping_Header_ID = $row['Shipping_Header_ID'];
			}

			$sql = "UPDATE tbl_shipping_header
			SET 
				Status_Shipping = 'CONFIRM SHIP',
				Confirm_Shipping_DateTime = NOW()
			WHERE
				GTN_Number = '$GTN_Number'
					AND Status_Shipping = 'COMPLETE';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "SELECT 
				pickp.Serial_ID, BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID, pickp.Qty_Package
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
					INNER JOIN
				tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
					INNER JOIN
				tbl_report trt ON pickp.Serial_ID = trt.Serial_ID
					AND (pickp.WorkOrder = trt.WorkOrder
					OR pickp.WorkOrder IS NULL)
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

				// echo($Serial_ID);

				$sql = "UPDATE tbl_inventory tiv
						INNER JOIN
					tbl_picking_pre pickp ON tiv.Serial_ID = pickp.Serial_ID
						AND (tiv.WorkOrder = pickp.WorkOrder
						OR tiv.WorkOrder IS NULL)
						INNER JOIN
					tbl_picking_header pickh ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
						INNER JOIN
					tbl_shipping_pre tsp ON tsp.TS_Number = pickh.TS_Number
				SET 
					tiv.Status_Working = 'Confirm Shipped',
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
				tiv.Area,
				tiv.Area,
				'CONFIRM SHIP',
				ROW_NUMBER() OVER (ORDER BY tiv.Serial_ID),
				NOW(),
				$cBy,
				tiv.Location_ID,
				tiv.Location_ID,
				NOW(),
				$cBy
			FROM
				tbl_shipping_pre tsp
					INNER JOIN
				tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
					INNER JOIN
				tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
					INNER JOIN
				tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
					INNER JOIN
				tbl_inventory tiv ON pickp.Serial_ID = tiv.Serial_ID
			WHERE
				tsh.GTN_Number = '$GTN_Number'
			GROUP BY tiv.ID;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			//exit();

			$mysqli->commit();

			$re = select_group1($mysqli, $GTN_Number);
			closeDBT($mysqli, 1, $re);

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');


function select_group($mysqli, $GTN_Number)
{

	$sql = "SELECT 
    tsh.GTN_Number,
    DATE_FORMAT(tsh.Ship_Date, '%d/%m/%y') AS Ship_Date,
    DATE_FORMAT(tsh.Ship_Time, '%h:%i:%s') AS Ship_Time,
    tsh.Trip_Number,
    Invoice_Number,
    Truck_ID,
    Truck_Driver,
    Truck_Type,
    Freight,
    tcm.Customer_Code,
    tsp.TS_Number,
    pickh.Serial_Number,
    pickp.Serial_ID,
    tpm.Part_No,
    tpm.Part_Name,
    tpm.Model,
    tpm.Part_Type,
    tpm.Type,
    tsh.Total_Qty,
    pickh.Total_Qty AS Qty,
    IF(tph.Serial_Number IS NULL,
        pickp.Serial_ID,
        tph.Serial_Number) AS Serial_Package,
    IF(tph.Serial_Number IS NULL,
        pickp.Qty_Package,
        SUM(pickp.Qty_Package)) AS Qty_Package,
    tsh.Status_Shipping,
    DATE_FORMAT(tsh.Confirm_Shipping_DateTime,
            '%d/%m/%y %H:%i') AS Confirm_Shipping_DateTime
FROM
    tbl_shipping_pre tsp
        INNER JOIN
    tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
        INNER JOIN
    tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
        INNER JOIN
    tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		LEFT JOIN
	tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
		AND tph.Status != 'CANCEL'
        INNER JOIN
    tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
        LEFT JOIN
    tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
	GTN_Number = '$GTN_Number'
		AND tsh.Status_Shipping = 'COMPLETE'
GROUP BY Serial_Package , tpm.Part_ID
ORDER BY GTN_Number DESC, Serial_Package ASC;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('GTN_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'TS_Number',
			'Serial_Package',
			'Serial_ID',
			'Part_No',
			'Part_Name',
			'Part_Type',
			'Type',
			'Model',
			'Qty_Package',
			'Qty',
			//'Customer_Code'
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['GTN_Number'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "GTN_Number" => $key1,
			"Ship_Date" => $value1[0]['Ship_Date'],
			"Ship_Time" => $value1[0]['Ship_Time'],
			"Customer_Code" => $value1[0]['Customer_Code'],
			"Trip_Number" => $value1[0]['Trip_Number'],
			"Invoice_Number" => $value1[0]['Invoice_Number'],
			"Truck_ID" => $value1[0]['Truck_ID'],
			"Truck_Driver" => $value1[0]['Truck_Driver'],
			"Truck_Type" => $value1[0]['Truck_Type'],
			"Status_Shipping" => $value1[0]['Status_Shipping'],
			"Confirm_Shipping_DateTime" => $value1[0]['Confirm_Shipping_DateTime'],
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
    tsh.GTN_Number,
    DATE_FORMAT(tsh.Ship_Date, '%d/%m/%y') AS Ship_Date,
    DATE_FORMAT(tsh.Ship_Time, '%h:%i:%s') AS Ship_Time,
    tsh.Trip_Number,
    Invoice_Number,
    Truck_ID,
    Truck_Driver,
    Truck_Type,
    Freight,
    tcm.Customer_Code,
    tsp.TS_Number,
    pickh.Serial_Number,
    pickp.Serial_ID,
    tpm.Part_No,
    tpm.Part_Name,
    tpm.Model,
    tpm.Part_Type,
    tpm.Type,
    tsh.Total_Qty,
    pickh.Total_Qty AS Qty,
    IF(tph.Serial_Number IS NULL,
        pickp.Serial_ID,
        tph.Serial_Number) AS Serial_Package,
    IF(tph.Serial_Number IS NULL,
        pickp.Qty_Package,
        SUM(pickp.Qty_Package)) AS Qty_Package,
    tsh.Status_Shipping,
    DATE_FORMAT(tsh.Confirm_Shipping_DateTime,
            '%d/%m/%y %H:%i') AS Confirm_Shipping_DateTime
FROM
    tbl_shipping_pre tsp
        INNER JOIN
    tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
        INNER JOIN
    tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
        INNER JOIN
    tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		LEFT JOIN
	tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
		AND tph.Status != 'CANCEL'
        INNER JOIN
    tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
        LEFT JOIN
    tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
	GTN_Number = '$GTN_Number'
		AND tsh.Status_Shipping = 'CONFIRM SHIP'
GROUP BY Serial_Package , tpm.Part_ID
ORDER BY GTN_Number DESC, Serial_Package ASC;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('GTN_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'TS_Number',
			'Serial_Package',
			'Serial_ID',
			'Part_No',
			'Part_Name',
			'Part_Type',
			'Type',
			'Model',
			'Qty_Package',
			'Qty',
			//'Customer_Code'
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['GTN_Number'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "GTN_Number" => $key1,
			"Ship_Date" => $value1[0]['Ship_Date'],
			"Ship_Time" => $value1[0]['Ship_Time'],
			"Customer_Code" => $value1[0]['Customer_Code'],
			"Trip_Number" => $value1[0]['Trip_Number'],
			"Invoice_Number" => $value1[0]['Invoice_Number'],
			"Truck_ID" => $value1[0]['Truck_ID'],
			"Truck_Driver" => $value1[0]['Truck_Driver'],
			"Truck_Type" => $value1[0]['Truck_Type'],
			"Status_Shipping" => $value1[0]['Status_Shipping'],
			"Confirm_Shipping_DateTime" => $value1[0]['Confirm_Shipping_DateTime'],
			"Total_Qty" => $value1[0]['Total_Qty'],
			'Total_Item' => count($value1), "open" => 0, "data" => $sub
		);
		$c++;
	}
	return $dateset;
}

$mysqli->close();
exit();
