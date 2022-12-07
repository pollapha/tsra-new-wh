<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'Pick'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'Pick'}[0] == 0) {
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
			pickh.TS_Number, pickh.Pick_Date, pickh.Serial_Number
		FROM
			tbl_picking_header pickh
				LEFT JOIN
			tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		WHERE
			pickh.Created_By_ID = $cBy
				AND pickh.Status_Picking = 'PENDING'
				AND (pickp.Picking_Pre_ID IS NULL OR pickp.status = 'PENDING')
		GROUP BY pickh.TS_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);


		$header = jsonRow($re1, true, 0);
		$body = [];

		if (count($header) > 0) {
			$TS_Number = $header[0]['TS_Number'];

			$sql = "SELECT 
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID,
				BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) AS Picking_Pre_ID,
				BIN_TO_UUID(pickp.Part_ID, TRUE) AS Part_ID,
				BIN_TO_UUID(tph.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
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
					SUM(pickp.Qty_Package)) AS Qty_Package
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
					INNER JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					LEFT JOIN
				tbl_palletizing_header tph ON pickp.Palletizing_Header_ID = tph.Palletizing_Header_ID  AND tph.Status != 'CANCEL'
			WHERE
				pickh.TS_Number = '$TS_Number'
					AND pickp.status = 'PENDING'
			GROUP BY tph.Palletizing_Header_ID, Serial_Package, tpm.Part_ID
			ORDER BY Serial_Package ASC;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}
		$returnData = ['header' => $header, 'body' => $body];

		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 2) {
		$dataParams = array(
			'obj',
			'obj=>Pick_Date:s:0:1',
			'obj=>TS_Number:s:0:1',
			'obj=>Customer_Code:s:0:1',
			'obj=>type_pack:s:0:0',
			'obj=>PO_Number:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT
				BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
			FROM
				tbl_customer_master
			WHERE
				Customer_Code = '$Customer_Code';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Customer_ID = $row['Customer_ID'];
			}


			$mysqli->commit();


			if ($Customer_Code == 'TSPT4') {
				$sql = "SELECT 
					BIN_TO_UUID(ID, TRUE) AS ID,
					tiv.Serial_ID,
					tpm.Part_No,
					tpm.Part_Name,
					tpm.Model,
					tpm.Part_Type,
					tpm.Type,
					tiv.Qty,
					tcm.Customer_Code,
					tiv.Status_Working,
					tiv.Pick,
					Package_Type
				FROM
					tbl_inventory tiv
						INNER JOIN
					tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
						INNER JOIN
					tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
						LEFT JOIN
					tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
				WHERE
					tpm.Type = 'End Cap'
						AND tiv.Status_Working = 'FG'
						AND BIN_TO_UUID(tpm.Customer_ID,TRUE) = '$Customer_ID'
				ORDER BY Serial_ID ASC;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				closeDBT($mysqli, 1, jsonRow($re1, true, 0));
			} else if ($Customer_Code == 'TSPT') {

				$sql = "SELECT 
					BIN_TO_UUID(ID, TRUE) AS ID,
					tiv.Serial_ID,
					tiv.WorkOrder,
					tpm.Part_No,
					tpm.Part_Name,
					tpm.Model,
					tpm.Part_Type,
					tpm.Type,
					tiv.Qty,
					tcm.Customer_Code,
					tiv.Status_Working,
					tiv.Pick,
					Package_Type
				FROM
					tbl_inventory tiv
						INNER JOIN
					tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
						INNER JOIN
					tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
						LEFT JOIN
					tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
				WHERE
					tpm.Type = 'Wheel lip'
						AND tiv.Status_Working = 'FG'
						AND BIN_TO_UUID(tpm.Customer_ID,TRUE) = '$Customer_ID'
				ORDER BY Serial_ID ASC;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				closeDBT($mysqli, 1, jsonRow($re1, true, 0));
			} else {
				// echo($type_pack);
				// exit();
				if ($type_pack == '') {
					throw new Exception('กรุณาเลือก Type Package');
				} else if ($type_pack == 'Repack') {
					//exit('1');
					$sql = "SELECT 
						BIN_TO_UUID(ID, TRUE) AS ID,
						Package_Type
					FROM
						tbl_inventory tiv
							INNER JOIN
						tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
							INNER JOIN
						tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
							LEFT JOIN
						tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					WHERE
						tiv.Status_Working = 'FG'
							AND Package_Type = 'Box'
							AND BIN_TO_UUID(tpm.Customer_ID, TRUE) = '$Customer_ID';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล' . __LINE__);
					}

					$re = select_group1($mysqli, $Customer_ID, $cBy);
					closeDBT($mysqli, 1, $re);
				} else {
					//exit('2');
					$sql = "SELECT 
						BIN_TO_UUID(ID, TRUE) AS ID,
						tiv.Serial_ID,
						tpm.Part_No,
						tpm.Part_Name,
						tpm.Model,
						tpm.Part_Type,
						tpm.Type,
						tiv.Qty,
						tcm.Customer_Code,
						tiv.Status_Working,
						tiv.Pick,
						Package_Type
					FROM
						tbl_inventory tiv
							INNER JOIN
						tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
							INNER JOIN
						tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
							LEFT JOIN
						tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
					WHERE
						tpm.Type = 'End Cap'
							AND Package_Type = 'Rack'
							AND tiv.Status_Working = 'FG'
							AND BIN_TO_UUID(tpm.Customer_ID,TRUE) = '$Customer_ID'
					ORDER BY Serial_ID ASC;";
					//exit($sql);
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล' . __LINE__);
					}
					closeDBT($mysqli, 1, jsonRow($re1, true, 0));
				}
			}
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 3) {

		$sql = "SELECT 
			pickh.TS_Number, pickh.Pick_Date
		FROM
			tbl_picking_header pickh
				LEFT JOIN
			tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		WHERE
			pickh.Created_By_ID = $cBy
				AND pickh.Status_Picking = 'PENDING'
				AND (pickp.Picking_Pre_ID IS NULL OR pickp.status = 'PENDING')
		GROUP BY pickh.TS_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);


		$header = jsonRow($re1, true, 0);
		$body = [];

		if (count($header) > 0) {
			$TS_Number = $header[0]['TS_Number'];
			$sql = "SELECT
				BIN_TO_UUID(pickh.Picking_Header_ID,TRUE) AS Picking_Header_ID,
				BIN_TO_UUID(pickp.Picking_Pre_ID,TRUE) AS Picking_Pre_ID,
				pickp.Part_No,
				SUM(pickp.Qty_Package) AS Qty_Package
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
			WHERE
				pickh.TS_Number = '$TS_Number'
					AND pickp.status = 'PENDING'
				GROUP BY pickp.Part_No;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}
		$returnData = ['header' => $header, 'body' => $body];

		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 4) {

		$sql = "SELECT 
			pickh.TS_Number, pickh.Pick_Date
		FROM
			tbl_picking_header pickh
				LEFT JOIN
			tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		WHERE
			pickh.Created_By_ID = $cBy
				AND pickh.Status_Picking = 'PENDING'
				AND (pickp.Picking_Pre_ID IS NULL OR pickp.status = 'PENDING')
		GROUP BY pickh.TS_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);


		$header = jsonRow($re1, true, 0);
		$body = [];

		if (count($header) > 0) {
			$TS_Number = $header[0]['TS_Number'];
			$sql = "SELECT
				SUM(pickp.Qty_Package) AS Total_Qty
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
			WHERE
				pickh.TS_Number = '$TS_Number'
					AND pickp.status = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}
		$returnData = ['header' => $header, 'body' => $body];

		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 5) {

		$sql = "SELECT 
			pickh.Serial_Number
		FROM
			tbl_picking_header pickh
				LEFT JOIN
			tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
		WHERE
			pickh.Created_By_ID = $cBy
				AND pickh.Status_Picking = 'PENDING'
				AND (pickp.Picking_Pre_ID IS NULL OR pickp.status = 'PENDING')
				AND pickh.Serial_Number IS NOT NULL
		GROUP BY pickh.TS_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		$header = jsonRow($re1, true, 0);
		$returnData = ['header' => $header];
		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 6) {
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
	if ($_SESSION['xxxRole']->{'Pick'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {


		$dataParams = array(
			'obj',
			'obj=>Pick_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			// สร้างเลขที่เอกสาร Trip Sheet Number
			$TS_Number = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('ps',0) TS_Number", 1))->fetch_array(MYSQLI_ASSOC)['TS_Number'];

			$sql = "SELECT 
				TS_Number
			FROM
				tbl_picking_header
			WHERE
				TS_Number = '$TS_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี Trip Sheet Number นี้แล้ว' . __LINE__);
			}

			//เพิ่ม TS_Number
			$sql = "INSERT INTO tbl_picking_header (
				TS_Number,
				Pick_Date,
				Creation_DateTime,
				Created_By_ID,
				Last_Updated_DateTime,
				Updated_By_ID)
			values('$TS_Number','$Pick_Date', now(), $cBy, now(), $cBy)";
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
			'obj=>Pick_Date:s:0:1',
			'obj=>TS_Number:s:0:1',
			'obj=>Customer_Code:s:0:1',
			'obj=>type_pack:s:0:0',
			'obj=>PO_Number:s:0:1',
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
				Pick_Date = '$Pick_Date'
					AND TS_Number = '$TS_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Picking_Header_ID = $row['Picking_Header_ID'];
			}

			$sql = "SELECT
			BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
		FROM
			tbl_customer_master
		WHERE
			Customer_Code = '$Customer_Code';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Customer_ID = $row['Customer_ID'];
			}


			if ($Customer_Code == 'TSPT4') {
				$sql = "INSERT INTO tbl_picking_pre (
					Picking_Header_ID,
					Receiving_Header_ID,
					PO_Number,
					Serial_ID,
					WorkOrder,
					Part_ID,
					Part_No,
					Qty_Package,
					Area,
					Location_ID,
					Creation_DateTime,
					Created_By_ID)
					SELECT 
						UUID_TO_BIN('$Picking_Header_ID', TRUE),
						tiv.Receiving_Header_ID,
						'$PO_Number',
						tiv.Serial_ID,
						tiv.WorkOrder,
						tiv.Part_ID,
						tpm.Part_No,
						tiv.Qty,
						tiv.Area,
						tiv.Location_ID,
						NOW(),
						$cBy
					FROM
						tbl_inventory tiv
							INNER JOIN
						tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
					WHERE
						tiv.Pick = 'Y'
							AND tiv.Updated_By_ID = $cBy;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
				}
			} else if ($Customer_Code == 'TSPT') {
				$sql = "INSERT INTO tbl_picking_pre (
					Picking_Header_ID,
					Receiving_Header_ID,
					PO_Number,
					Serial_ID,
					WorkOrder,
					Part_ID,
					Part_No,
					Qty_Package,
					Area,
					Location_ID,
					Creation_DateTime,
					Created_By_ID)
					SELECT 
						UUID_TO_BIN('$Picking_Header_ID', TRUE),
						tiv.Receiving_Header_ID,
						'$PO_Number',
						tiv.Serial_ID,
						tiv.WorkOrder,
						tiv.Part_ID,
						tpm.Part_No,
						tiv.Qty,
						tiv.Area,
						tiv.Location_ID,
						NOW(),
						$cBy
					FROM
						tbl_inventory tiv
							INNER JOIN
						tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
					WHERE
						tiv.Pick = 'Y'
							AND tiv.Updated_By_ID = $cBy;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
				}
			} else {
				if ($type_pack == '') {
					throw new Exception('กรุณาเลือก Type Package');
				} else if ($type_pack == 'Repack') {
					$sql = "INSERT INTO tbl_picking_pre(
						Picking_Header_ID,
						Receiving_Header_ID,
						Palletizing_Header_ID,
						PO_Number,
						Serial_ID,
						WorkOrder,
						Part_ID,
						Part_No,
						Qty_Package,
						Area,
						Location_ID,
						Creation_DateTime,
						Created_By_ID)
						SELECT 
							UUID_TO_BIN('$Picking_Header_ID', TRUE),
							tiv.Receiving_Header_ID,
							tpp.Palletizing_Header_ID,
							'$PO_Number',
							tiv.Serial_ID,
							tiv.WorkOrder,
							tiv.Part_ID,
							tpm.Part_No,
							IF((tpp.Qty_Package IS NULL),tiv.Qty, tpp.Qty_Package) AS Qty,
							tiv.Area,
							tiv.Location_ID,
							NOW(),
							$cBy
						FROM
							tbl_inventory tiv
								INNER JOIN
							tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
								LEFT JOIN
							tbl_palletizing_pre tpp ON tiv.Serial_ID = tpp.Serial_ID AND tpp.status != 'CANCEL'
								LEFT JOIN
							tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
						WHERE
							tpp.Pick = 'N'
								AND tiv.Updated_By_ID = $cBy;";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
					}

					$sql = "UPDATE tbl_palletizing_pre
					SET
						Pick = 'P',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						Pick = 'N'
							AND Updated_By_ID = $cBy;";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				} else {
					$sql = "INSERT INTO tbl_picking_pre (
						Picking_Header_ID,
						Receiving_Header_ID,
						PO_Number,
						Serial_ID,
						WorkOrder,
						Part_ID,
						Part_No,
						Qty_Package,
						Area,
						Location_ID,
						Creation_DateTime,
						Created_By_ID)
						SELECT 
							UUID_TO_BIN('$Picking_Header_ID', TRUE),
							tiv.Receiving_Header_ID,
							'$PO_Number',
							tiv.Serial_ID,
							tiv.WorkOrder,
							tiv.Part_ID,
							tpm.Part_No,
							tiv.Qty,
							tiv.Area,
							tiv.Location_ID,
							NOW(),
							$cBy
						FROM
							tbl_inventory tiv
								INNER JOIN
							tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
						WHERE
							tiv.Pick = 'Y'
								AND tiv.Updated_By_ID = $cBy;";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
					}
				}
			}


			$sql = "UPDATE tbl_inventory
			SET
				Pick = '',
				Status_Working = 'Pick Sheet',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				Pick = 'Y'
					AND Updated_By_ID = $cBy;";
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

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$ID  = $explode[0];
		$state  = $explode[1];

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				tiv.Serial_ID,
				tiv.WorkOrder,
				BIN_TO_UUID(tiv.Part_ID, TRUE) AS Part_ID,
				tpm.Part_No,
				tpm.Part_Name,
				Qty,
				tpm.Part_Type,
				tpm.Type,
				tlm.Location_Code,
				tiv.Area,
				tiv.Pick,
				tiv.Creation_DateTime
			FROM
				tbl_inventory tiv
					INNER JOIN
				tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
					LEFT JOIN
				tbl_location_master tlm ON tiv.Location_ID = tlm.Location_ID
			WHERE
				BIN_TO_UUID(tiv.ID,TRUE) = '$ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Serial_ID = $row['Serial_ID'];
				$Part_ID = $row['Part_ID'];
				$Creation_DateTime = $row['Creation_DateTime'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(tiv.Part_ID, TRUE) AS Part_ID,
				tiv.Creation_DateTime,
				Package_Type
			FROM
				tbl_inventory tiv
					INNER JOIN
				tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
					INNER JOIN
				tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			WHERE
				BIN_TO_UUID(tiv.Part_ID, TRUE) = '$Part_ID'
					AND tiv.Pick = ''
					AND trh.Serial_Number IS NULL
					AND Status_Working = 'FG'
					AND (tiv.Creation_DateTime < '$Creation_DateTime' OR tiv.Serial_ID < '$Serial_ID');";
					//exit($sql);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('กรุณาเลือก Package Number ที่เก่ากว่าก่อน');
			}

			//exit($sql);

			if ($state == 'on') {
				$sql = "UPDATE tbl_inventory
					SET
						Pick = 'Y',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(ID,TRUE) = '$ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			} else {
				$sql = "UPDATE tbl_inventory
					SET
						Pick = '',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(ID,TRUE) = '$ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			//exit();


			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 14) {

		$dataParams = array(
			'obj',
			'obj=>Pick_Date:s:0:1',
			'obj=>TS_Number:s:0:1',
			'obj=>Serial_Number:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			if ($Serial_Number == null) {

				$sql = "SELECT 
					BIN_TO_UUID(Picking_Header_ID, TRUE) AS Picking_Header_ID
				FROM
					tbl_picking_header
				WHERE
					Pick_Date = '$Pick_Date'
						AND TS_Number = '$TS_Number';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Picking_Header_ID = $row['Picking_Header_ID'];
				}

				$sql = "SELECT 
					BIN_TO_UUID(Receiving_Header_ID, TRUE) AS Receiving_Header_ID
				FROM
					tbl_picking_pre
				WHERE
					BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Receiving_Header_ID = $row['Receiving_Header_ID'];
				}


				$sql = "SELECT 
					Serial_Number
				FROM
					tbl_receiving_header
				WHERE
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Serial_Number = $row['Serial_Number'];
				}

				$sql = "UPDATE tbl_picking_header 
				SET 
					Serial_Number = '$Serial_Number',
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = $cBy
				WHERE
					BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
						AND Status_Picking = 'PENDING'
						AND Serial_Number IS NULL;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 15) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$Serial_Number  = $explode[0];
		$state  = $explode[1];

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
				Creation_DateTime
			FROM
				tbl_palletizing_header
			WHERE
				Serial_Number = '$Serial_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Palletizing_Header_ID = $row['Palletizing_Header_ID'];
				$Creation_DateTime = $row['Creation_DateTime'];
			}


			$sql = "SELECT 
				BIN_TO_UUID(Palletizing_Pre_ID, TRUE) AS Palletizing_Pre_ID,
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID
			FROM
				tbl_palletizing_pre
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Palletizing_Pre_ID = $row['Palletizing_Pre_ID'];
				$Part_ID = $row['Part_ID'];

				$sql = "SELECT 
					Serial_Number,
					Confirm_DateTime,
					tpp.Pick
				FROM
					tbl_palletizing_pre tpp
						INNER JOIN
					tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
						INNER JOIN
					tbl_receiving_pre trp ON tpp.Serial_ID = trp.Serial_ID
				WHERE
					BIN_TO_UUID(tpp.Part_ID, TRUE) = '$Part_ID'
						AND tpp.Pick = ''
						AND Package_Type = 'Box'
						AND tpp.status != 'CANCEL'
						AND tpp.Creation_DateTime < '$Creation_DateTime';";
				//exit($sql);
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {
					throw new Exception('กรุณาเลือก Package ID ที่เก่ากว่าก่อน');
				}
			}

			//exit($sql);

			if ($state == 'on') {
				$sql = "UPDATE tbl_palletizing_pre
					SET
						Pick = 'N',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
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
					tiv.Pick = 'Y',
					tiv.Last_Updated_DateTime = NOW(),
					tiv.Updated_By_ID = $cBy
				WHERE
					tiv.Serial_ID = tpp.Serial_ID;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}
			} else {
				$sql = "UPDATE tbl_palletizing_pre
					SET
						Pick = '',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
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
					tiv.Pick = '',
					tiv.Last_Updated_DateTime = NOW(),
					tiv.Updated_By_ID = $cBy
				WHERE
					tiv.Serial_ID = tpp.Serial_ID;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}
			}


			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'Pick'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'Pick'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {


		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$Picking_Header_ID  = $explode[0];
		$Picking_Pre_ID  = $explode[1];
		$Palletizing_Header_ID  = $explode[2];



		$mysqli->autocommit(FALSE);
		try {

			//echo $Palletizing_Header_ID.'<br>';

			if ($Palletizing_Header_ID == 'null') {
				//echo '1';
				$sql = "SELECT
					Serial_ID,
					BIN_TO_UUID(Part_ID,TRUE) AS Part_ID
				FROM
					tbl_picking_pre pickp
						INNER JOIN
					tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
				WHERE
					BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Serial_ID = $row['Serial_ID'];
					$Part_ID = $row['Part_ID'];
				}


				$sql = "SELECT 
					BIN_TO_UUID(ID, TRUE) AS ID
				FROM
					tbl_inventory
				WHERE
					Serial_ID = '$Serial_ID'
						AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'
						AND Status_Working = 'Pick Sheet';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$ID = $row['ID'];
				}

				$sql = "UPDATE tbl_inventory
				SET
					Status_Working = 'FG',
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = $cBy
				WHERE
					BIN_TO_UUID(ID,TRUE) = '$ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}


				$sql = "DELETE FROM tbl_picking_pre
				WHERE
					BIN_TO_UUID(Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถลบได้' . __LINE__);
				}

				//

			} else {
				//echo '2';
				$sql = "SELECT
					BIN_TO_UUID(Part_ID,TRUE) AS Part_ID
				FROM
					tbl_picking_pre pickp
						INNER JOIN
					tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
				WHERE
					BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Part_ID = $row['Part_ID'];
				}

				$sql = "SELECT
					BIN_TO_UUID(Picking_Pre_ID,TRUE) AS Picking_Pre_ID
				FROM
					tbl_picking_pre pickp
						INNER JOIN
					tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
				WHERE
					BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID' 
						AND BIN_TO_UUID(pickp.Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID' 
						AND BIN_TO_UUID(Part_ID, TRUE) = '$Part_ID';";
				//exit($sql);
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Picking_Pre_ID = $row['Picking_Pre_ID'];


					$sql = "DELETE FROM tbl_picking_pre
					WHERE
						BIN_TO_UUID(Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถลบได้' . __LINE__);
					}
				}


				$sql = "SELECT 
					BIN_TO_UUID(Palletizing_Pre_ID, TRUE) AS Palletizing_Pre_ID,
					BIN_TO_UUID(Part_ID,TRUE) AS Part_ID,
					Serial_ID
				FROM
					tbl_palletizing_pre
				WHERE
					BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID'
						AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID'";
				//exit($sql);
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Palletizing_Pre_ID = $row['Palletizing_Pre_ID'];
					$Serial_ID = $row['Serial_ID'];
					$Part_ID = $row['Part_ID'];

					$sql = "UPDATE tbl_inventory
					SET
						Status_Working = 'FG',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						Serial_ID = '$Serial_ID'
							AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}


					$sql = "UPDATE tbl_palletizing_pre
					SET
						Pick = '',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID'
							AND BIN_TO_UUID(Palletizing_Pre_ID,TRUE) = '$Palletizing_Pre_ID'
							AND BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				}
			}
			//exit();

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
	if ($_SESSION['xxxRole']->{'Pick'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {

		$dataParams = array(
			'obj',
			'obj=>Pick_Date:s:0:1',
			'obj=>TS_Number:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID,
				SUM(Qty_Package) AS Qty
			FROM
				tbl_picking_pre pickp
					INNER JOIN
				tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
			WHERE
				TS_Number = '$TS_Number'
					AND status = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Qty = $row['Qty'];
				$Picking_Header_ID = $row['Picking_Header_ID'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(tph.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
				tph.Serial_Number
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
					AND pickp.status = 'PENDING'
					AND tph.Palletizing_Header_ID IS NOT NULL
			GROUP BY tph.Palletizing_Header_ID;";
			//exit($sql);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Palletizing_Header_ID = $row['Palletizing_Header_ID'];

					$sql = "UPDATE tbl_palletizing_pre
					SET
						Pick = 'Y',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				}
			}


			$sql = "UPDATE tbl_picking_header 
			SET 
				Total_Qty = $Qty,
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
					AND Status_Picking = 'PENDING';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('เกินจำนวนต่อ Rack ');
			}


			$sql = "UPDATE tbl_picking_pre 
			SET 
				status = 'COMPLETE'
			WHERE
				BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
					AND status = 'PENDING';";
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


function select_group1($mysqli, $Customer_ID, $cBy)
{

	$sql = "SELECT 
		tph.Serial_Number,
		BIN_TO_UUID(tpp.Palletizing_Pre_ID, TRUE) AS Palletizing_Pre_ID,
		BIN_TO_UUID(tpp.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
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
		DATE_FORMAT(tph.Confirm_DateTime, '%d/%m/%y %H:%i') AS Confirm_DateTime,
		Status_Working,
		tpp.Pick,
		Package_Type
	FROM
		tbl_palletizing_pre tpp
			INNER JOIN
		tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
			INNER JOIN
		tbl_inventory tiv ON tpp.Serial_ID = tiv.Serial_ID
			INNER JOIN
		tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
			INNER JOIN
		tbl_part_master tpm ON tpp.Part_ID = tpm.Part_ID
			LEFT JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
	WHERE
		tpm.Type = 'End cap'
			AND Package_Type = 'Box'
			AND (tpp.Pick = '' OR tpp.Pick = 'N')
			AND BIN_TO_UUID(tpm.Customer_ID, TRUE) = '$Customer_ID'
			AND tph.Status = 'COMPLETE'
			AND tpp.status = 'COMPLETE'
	ORDER BY tpp.Creation_DateTime ASC , Serial_ID ASC;";
	//exit($sql);
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
			'Customer_Code',
			'Status_Working',
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
			"Total_Qty" => $value1[0]['Total_Qty'],
			"Pick" => $value1[0]['Pick'],
			"Package_Type" => $value1[0]['Package_Type'],
			"Status" => $value1[0]['Status'],
			"Part_Type" => $value1[0]['Part_Type'],
			"Type" => $value1[0]['Type'],
			"Area" => $value1[0]['Area'],
			"Confirm_DateTime" => $value1[0]['Confirm_DateTime'],
			'Total_Item' => count($value1), "open" => 0, "data" => $sub
		);
		$c++;
	}
	return $dateset;
}


$mysqli->close();
exit();
