<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ConfirmPicking'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ConfirmPicking'}[0] == 0) {
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
			pickh.TS_Number,
			pickh.Pick_Date
		FROM
			tbl_picking_header pickh
				LEFT JOIN
			tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		WHERE
			pickh.Created_By_ID = $cBy
				AND pickh.Status_Picking = 'PENDING'
				AND pickp.status = 'COMPLETE'
				AND pickp.Count != 0
				AND Confirm_Picking_DateTime IS NULL
		GROUP BY pickh.TS_Number;";

		$re1 = sqlError($mysqli, __LINE__, $sql, 1);

		$header = jsonRow($re1, true, 0);

		$body = [];

		if (count($header) > 0) {
			$TS_Number = $header[0]['TS_Number'];
			$sql = "SELECT
				DATE_FORMAT(Pick_Date, '%d/%m/%y') AS Pick_Date,
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID,
				BIN_TO_UUID(pickp.Part_ID, TRUE) AS Part_ID,
				pickh.TS_Number,
				IF(tph.Serial_Number IS NULL,
					pickp.Serial_ID,
					tph.Serial_Number) AS Serial_Package,
				pickp.PO_Number,
				tpm.Part_No,
				tpm.Part_Name,
				tpm.Type,
				Customer_Code,
				IF(tph.Serial_Number IS NULL,
					pickp.Qty_Package,
					SUM(pickp.Qty_Package)) AS Qty_Package,
					SUM(pickp.Count) AS Count,
					Status_Picking
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
					INNER JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					LEFT JOIN
				tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					AND tph.Status != 'CANCEL'
			WHERE
				pickh.TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING'
					AND pickp.status = 'COMPLETE'
			GROUP BY Serial_Package , tpm.Part_ID
			ORDER BY Serial_Package ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}
		$returnData = ['header' => $header, 'body' => $body];
		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 2) {
		$dataParams = array(
			'obj',
			'obj=>TS_Number:s:0:1',
			'obj=>Serial_Package:s:0:1'
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);

		try {
			$sql = "WITH a AS(
			SELECT 
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID,
				BIN_TO_UUID(pickp.Part_ID, TRUE) AS Part_ID,
				pickh.TS_Number,
				IF(tph.Serial_Number IS NULL,
					pickp.Serial_ID,
					tph.Serial_Number) AS Serial_Package,
				pickp.PO_Number,
				tpm.Part_No,
				tpm.Part_Name,
				tpm.Type,
				Customer_Code,
				IF(tph.Serial_Number IS NULL,
					pickp.Qty_Package,
					SUM(pickp.Qty_Package)) AS Qty_Package,
					SUM(pickp.Count) AS Count,
					Status_Picking
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
					INNER JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					LEFT JOIN
				tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					AND tph.Status != 'CANCEL'
					WHERE
				pickh.TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING'
					AND pickp.status = 'COMPLETE'
			GROUP BY Serial_Package , tpm.Part_ID
			ORDER BY Serial_Package ASC)
			SELECT a.* FROM a
			WHERE Serial_Package = '$Serial_Package';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}

		$mysqli->commit();

		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 3) {

		$dataParams = array(
			'obj',
			'obj=>TS_Number:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$sql = "SELECT
				DATE_FORMAT(Pick_Date, '%d/%m/%y') AS Pick_Date,
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID,
				BIN_TO_UUID(pickp.Part_ID, TRUE) AS Part_ID,
				pickh.TS_Number,
				IF(tph.Serial_Number IS NULL,
					pickp.Serial_ID,
					tph.Serial_Number) AS Serial_Package,
				pickp.PO_Number,
				tpm.Part_No,
				tpm.Part_Name,
				tpm.Type,
				Customer_Code,
				IF(tph.Serial_Number IS NULL,
					pickp.Qty_Package,
					SUM(pickp.Qty_Package)) AS Qty_Package,
					SUM(pickp.Count) AS Count,
					Status_Picking
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
					INNER JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					LEFT JOIN
				tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					AND tph.Status != 'CANCEL'
			WHERE
				pickh.TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING'
					AND pickp.status = 'COMPLETE'
			GROUP BY Serial_Package , tpm.Part_ID
			ORDER BY Serial_Package ASC;";
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
			TS_Number AS value
		FROM
			tbl_picking_header pickh
				INNER JOIN 
			tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		WHERE
			TS_Number LIKE '%$val%'
				AND Confirm_Picking_DateTime IS NULL
				AND Status_Picking = 'PENDING'
				GROUP BY TS_Number
		LIMIT 5;";

		if ($re1 = $mysqli->query($sql)) {
			echo json_encode(jsonRow($re1, false, 0));
		} else {
			echo "[{ID:0,value:'ERROR'}]";
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'ConfirmPicking'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
		$dataParams = array(
			'obj',
			'obj=>TS_Number:s:0:1',
			'obj=>Serial_Package:s:0:1',
			'obj=>Part_No:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);

		try {

			$sql = "SELECT 
				BIN_TO_UUID(Picking_Header_ID, TRUE) AS Picking_Header_ID
			FROM
				tbl_picking_header
			WHERE
				TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Picking_Header_ID = $row['Picking_Header_ID'];
			}


			$sql = "WITH a AS(
			SELECT 
				BIN_TO_UUID(pickp.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
				IF(tph.Serial_Number IS NULL,
					pickp.Serial_ID,
					tph.Serial_Number) AS Serial_Package,
				tpm.Part_No,
				IF(tph.Serial_Number IS NULL,
					pickp.Qty_Package,
					SUM(pickp.Qty_Package)) AS Qty_Package,
					Package_Type,
					BIN_TO_UUID(tpm.Customer_ID, TRUE) AS Customer_ID
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
					INNER JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					LEFT JOIN
				tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					AND tph.Status != 'CANCEL'
					LEFT JOIN
				tbl_inventory tiv ON pickp.Serial_ID = tiv.Serial_ID
					LEFT JOIN
				tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
			WHERE
				pickh.TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING'
					AND pickp.status = 'COMPLETE'
			GROUP BY Serial_Package , tpm.Part_ID
			ORDER BY Serial_Package ASC)
			SELECT a.* FROM a
			WHERE Serial_Package = '$Serial_Package'
				AND a.Part_No = '$Part_No';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Qty_Package = $row['Qty_Package'];
				$Package_Type = $row['Package_Type'];
				$Palletizing_Header_ID = $row['Palletizing_Header_ID'];
				$Customer_ID = $row['Customer_ID'];
			}


			$sql = "SELECT 
				Part_Type
			FROM
				tbl_part_master
			WHERE
				Part_No = '$Part_No';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_Type = $row['Part_Type'];
			}


			$sql = "SELECT 
				Part_No
			FROM
				tbl_picking_pre
			WHERE
				BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
					AND Part_No = '$Part_No'
					AND Serial_ID = '$Serial_Package'
					AND status = 'COMPLETE'
					AND Count = $Qty_Package;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('Part นี้ Confirm ครบแล้ว' . __LINE__);
			}

			if ($Part_Type == 'Assembly part') {
				$sql = "SELECT 
					BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
					BIN_TO_UUID(pickp.Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
					BIN_TO_UUID(trt.Receiving_Pre_ID, TRUE) AS Receiving_Pre_ID,
					pickp.Serial_ID,
					pickp.WorkOrder,
					SUM(pickp.Qty_Package) AS Qty_Package,
					SUM(trt.Intransit_Qty) AS Intransit_Qty
				FROM
					tbl_picking_pre pickp
						INNER JOIN
					tbl_report trt ON pickp.Serial_ID = trt.Serial_ID
						AND (pickp.WorkOrder = trt.WorkOrder
						OR pickp.WorkOrder IS NULL)
				WHERE
					BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID'
						AND pickp.Part_No = '$Part_No'
						AND pickp.Serial_ID = '$Serial_Package'
						AND pickp.status = 'COMPLETE'
						AND Intransit_Qty != Assembled_Qty
				ORDER BY pickp.Picking_Pre_ID ASC
				LIMIT 1;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Receiving_Header_ID = $row['Receiving_Header_ID'];
					$Receiving_Pre_ID = $row['Receiving_Pre_ID'];
					$Picking_Pre_ID = $row['Picking_Pre_ID'];
				}

				$sql = "UPDATE tbl_picking_pre
				SET 
					Count = Count+$Qty_Package
				WHERE
					BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
						AND Serial_ID = '$Serial_Package'
						AND Part_No = '$Part_No';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}

				$sql = "UPDATE tbl_report trt
						INNER JOIN
					tbl_picking_pre pickp ON trt.Serial_ID = pickp.Serial_ID
						AND (trt.WorkOrder = pickp.WorkOrder
						OR trt.WorkOrder IS NULL)
				SET 
					trt.Intransit_Qty = trt.Intransit_Qty+$Qty_Package,
					trt.Status = 'In-Transit', 
					trt.Last_Updated_DateTime = NOW(), 
					trt.Updated_By_ID = $cBy
				WHERE
					BIN_TO_UUID(trt.Receiving_Pre_ID,TRUE) = '$Receiving_Pre_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			} else {

				if ($Package_Type == 'Box') {

					$sql = "SELECT
						Customer_Code
					FROM
						tbl_customer_master
					WHERE
						BIN_TO_UUID(Customer_ID, TRUE) = '$Customer_ID';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Customer_Code = $row['Customer_Code'];
					}

					if ($Customer_Code != 'TSPT4') {
						$sql = "UPDATE tbl_picking_pre pickp,
						( WITH a AS( 
							SELECT 
								BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
								BIN_TO_UUID(pickp.Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
								BIN_TO_UUID(tph.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
								IF(tph.Serial_Number IS NULL,
									pickp.Serial_ID,
									tph.Serial_Number) AS Serial_Package,
								pickp.Serial_ID,
								pickp.Part_No,
								pickp.Qty_Package,
								pickp.Count
							FROM
								tbl_picking_pre pickp
									INNER JOIN
								tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
									LEFT JOIN
								tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
									AND tph.Status != 'CANCEL'
									INNER JOIN
								tbl_report trt ON pickp.Serial_ID = trt.Serial_ID
							WHERE
								pickh.TS_Number = '$TS_Number'
									AND pickp.status = 'COMPLETE'
									AND Intransit_Qty != IN_Qty
									AND pickp.Count < pickp.Qty_Package  
							ORDER BY pickp.Picking_Pre_ID ASC) SELECT a.* FROM a 
							WHERE Serial_Package = '$Serial_Package' AND a.Part_No = '$Part_No') AS a 
							SET pickp.Count = pickp.Count+a.Qty_Package 
							WHERE BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID' 
							AND BIN_TO_UUID(pickp.Palletizing_Header_ID, TRUE) = a.Palletizing_Header_ID
							AND pickp.Serial_ID = a.Serial_ID AND pickp.Part_No = a.Part_No;";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}

						$sql = "UPDATE tbl_report trt,
						( WITH a AS(
						SELECT 
							BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
							BIN_TO_UUID(pickp.Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
							BIN_TO_UUID(trt.Receiving_Pre_ID, TRUE) AS Receiving_Pre_ID,
							BIN_TO_UUID(pickp.Part_ID, TRUE) AS Part_ID,
							IF(tph.Serial_Number IS NULL,
								pickp.Serial_ID,
								tph.Serial_Number) AS Serial_Package,
							pickp.Serial_ID,
							pickp.Part_No,
							pickp.Qty_Package
						FROM
							tbl_picking_pre pickp
								INNER JOIN
							tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
								LEFT JOIN
							tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
								AND tph.Status != 'CANCEL'
								INNER JOIN
							tbl_report trt ON pickp.Serial_ID = trt.Serial_ID
								LEFT JOIN
							tbl_inventory tiv ON trt.Serial_ID = tiv.Serial_ID
								LEFT JOIN
							tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
						WHERE
							pickh.TS_Number = '$TS_Number'
								AND pickp.status = 'COMPLETE'
								AND Intransit_Qty != IN_Qty
						ORDER BY pickp.Picking_Pre_ID ASC)
						SELECT a.* FROM a
						WHERE Serial_Package = '$Serial_Package'
							AND a.Part_No = '$Part_No') AS a
						SET 
							trt.Intransit_Qty = trt.Intransit_Qty+a.Qty_Package,
							trt.Status = 'In-Transit', 
							trt.Last_Updated_DateTime = NOW(), 
							trt.Updated_By_ID = $cBy
						WHERE
							BIN_TO_UUID(trt.Receiving_Pre_ID,TRUE) = a.Receiving_Pre_ID
								AND trt.Serial_ID = a.Serial_ID
								AND BIN_TO_UUID(trt.Part_ID, TRUE) = a.Part_ID;";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					} else {

						$sql = "SELECT 
							BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
							BIN_TO_UUID(pickp.Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
							BIN_TO_UUID(trt.Receiving_Pre_ID, TRUE) AS Receiving_Pre_ID,
							pickp.Serial_ID,
							Qty_Package
						FROM
							tbl_picking_pre pickp
								INNER JOIN
							tbl_report trt ON pickp.Serial_ID = trt.Serial_ID
								AND pickp.Part_ID = trt.Part_ID
						WHERE
							BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID'
								AND pickp.Part_No = '$Part_No'
								AND pickp.Serial_ID = '$Serial_Package'
								AND pickp.status = 'COMPLETE'
								AND Intransit_Qty != IN_Qty
						ORDER BY pickp.Picking_Pre_ID ASC
						LIMIT 1;";
						$re1 = sqlError($mysqli, __LINE__, $sql, 1);
						if ($re1->num_rows == 0) {
							throw new Exception('ไม่พบข้อมูล' . __LINE__);
						}
						while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
							$Receiving_Header_ID = $row['Receiving_Header_ID'];
							$Receiving_Pre_ID = $row['Receiving_Pre_ID'];
							$Picking_Pre_ID = $row['Picking_Pre_ID'];
							$Qty_Package = $row['Qty_Package'];
						}

						$sql = "UPDATE tbl_picking_pre
					SET 
						Count = Count+$Qty_Package
					WHERE
						BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
							AND Serial_ID = '$Serial_Package'
							AND Part_No = '$Part_No';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}

						$sql = "UPDATE tbl_report trt
							INNER JOIN
						tbl_picking_pre pickp ON trt.Serial_ID = pickp.Serial_ID
							AND trt.Part_ID = pickp.Part_ID
					SET 
						trt.Intransit_Qty = trt.Intransit_Qty+$Qty_Package,
						trt.Status = 'In-Transit', 
						trt.Last_Updated_DateTime = NOW(), 
						trt.Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(trt.Receiving_Pre_ID,TRUE) = '$Receiving_Pre_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}
				} else {
					$sql = "SELECT 
						BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
						BIN_TO_UUID(pickp.Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
						BIN_TO_UUID(trt.Receiving_Pre_ID, TRUE) AS Receiving_Pre_ID,
						pickp.Serial_ID,
						pickp.WorkOrder,
						Qty_Package
					FROM
						tbl_picking_pre pickp
							INNER JOIN
						tbl_report trt ON pickp.Serial_ID = trt.Serial_ID
							AND pickp.Part_ID = trt.Part_ID
					WHERE
						BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID'
							AND pickp.Part_No = '$Part_No'
							AND pickp.Serial_ID = '$Serial_Package'
							AND pickp.status = 'COMPLETE'
							AND Intransit_Qty != IN_Qty
					ORDER BY pickp.Picking_Pre_ID ASC
					LIMIT 1;";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Receiving_Header_ID = $row['Receiving_Header_ID'];
						$Receiving_Pre_ID = $row['Receiving_Pre_ID'];
						$Picking_Pre_ID = $row['Picking_Pre_ID'];
						$Qty_Package = $row['Qty_Package'];
					}

					$sql = "UPDATE tbl_picking_pre
					SET 
						Count = Count+$Qty_Package
					WHERE
						BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
							AND Serial_ID = '$Serial_Package'
							AND Part_No = '$Part_No';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}

					$sql = "UPDATE tbl_report trt
							INNER JOIN
						tbl_picking_pre pickp ON trt.Serial_ID = pickp.Serial_ID
							trt.Part_ID = pickp.Part_ID
					SET 
						trt.Intransit_Qty = trt.Intransit_Qty+$Qty_Package,
						trt.Status = 'In-Transit', 
						trt.Last_Updated_DateTime = NOW(), 
						trt.Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(trt.Receiving_Pre_ID,TRUE) = '$Receiving_Pre_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				}
			}


			$mysqli->commit();

			$sql = "SELECT
				DATE_FORMAT(Pick_Date, '%d/%m/%y') AS Pick_Date,
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID,
				BIN_TO_UUID(pickp.Part_ID, TRUE) AS Part_ID,
				pickh.TS_Number,
				IF(tph.Serial_Number IS NULL,
					pickp.Serial_ID,
					tph.Serial_Number) AS Serial_Package,
				pickp.PO_Number,
				tpm.Part_No,
				tpm.Part_Name,
				tpm.Type,
				Customer_Code,
				IF(tph.Serial_Number IS NULL,
					pickp.Qty_Package,
					SUM(pickp.Qty_Package)) AS Qty_Package,
					SUM(pickp.Count) AS Count,
					Status_Picking
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
					INNER JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					LEFT JOIN
				tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					AND tph.Status != 'CANCEL'
			WHERE
				pickh.TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING'
					AND pickp.status = 'COMPLETE'
			GROUP BY Serial_Package , tpm.Part_ID
			ORDER BY Serial_Package ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ConfirmPicking'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'ConfirmPicking'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'ConfirmPicking'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {

		$dataParams = array(
			'obj',
			'obj=>TS_Number:s:0:1',
			'obj=>Pick_Date:s:0:1'
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Picking_Header_ID, TRUE) AS Picking_Header_ID
			FROM
				tbl_picking_header
			WHERE
				TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Picking_Header_ID = $row['Picking_Header_ID'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
				Part_No,
				SUM(Qty_Package) AS Qty_Package,
				SUM(Count) AS Qty_Count
			FROM
				tbl_picking_pre
			WHERE
				BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
					AND status = 'COMPLETE'
					AND Count IS NOT NULL;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Qty_Package = $row['Qty_Package'];
				$Qty_Count = $row['Qty_Count'];
				$Part_No = $row['Part_No'];
				$Part_ID = $row['Part_ID'];
			}


			$sql = "SELECT 
				Total_Qty
			FROM
				tbl_picking_header
			WHERE
				TS_Number = '$TS_Number'
					AND Status_Picking = 'PENDING'
					AND Total_Qty = $Qty_Count;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ยัง Confirm ไม่ครบ' . __LINE__);
			}


			//อัพเดตผลรวมทั้งหมดใน 1 PS_Number
			$sql = "UPDATE tbl_picking_header 
			SET 
				Total_Qty = $Qty_Package,
				Status_Picking = 'COMPLETE',
				Confirm_Picking_DateTime = NOW()
			WHERE
				TS_Number = '$TS_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			//อัพเดต Pick_Qty ใน Inventory
			$sql = "UPDATE tbl_inventory tiv
					INNER JOIN
    			tbl_picking_pre pickp ON tiv.Serial_ID = pickp.Serial_ID
					AND (tiv.WorkOrder = pickp.WorkOrder
						OR tiv.WorkOrder IS NULL) 
					INNER JOIN
				tbl_report trt ON tiv.Serial_ID = trt.Serial_ID
					AND (tiv.WorkOrder = trt.WorkOrder
					OR tiv.WorkOrder IS NULL)
			SET 
				tiv.Pick_Qty = trt.Intransit_Qty,
				tiv.Status = '',
				tiv.Last_Updated_DateTime = NOW(),
				tiv.Updated_By_ID = $cBy
			WHERE
				BIN_TO_UUID(pickp.Picking_Header_ID,TRUE) = '$Picking_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			//เพิ่ม Trasaction
			$sql = "INSERT INTO
				tbl_transaction(
				Picking_Header_ID,
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
				pickh.Picking_Header_ID,
				pickp.Receiving_Header_ID,
				pickp.Palletizing_Header_ID,
				pickp.Part_ID,
				pickp.Serial_ID,
				pickp.Qty_Package,
				pickp.Area,
				pickp.Area,
				'PICKING',
				ROW_NUMBER() OVER (ORDER BY pickp.Picking_Pre_ID),
				now(),
				$cBy,
				pickp.Location_ID,
				pickp.Location_ID,
				now(),
				$cBy
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
			WHERE
				pickh.TS_Number = '$TS_Number';";
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

$mysqli->close();
exit();
