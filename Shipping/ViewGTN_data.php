<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ViewGTN'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ViewGTN'}[0] == 0) {
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
	} else if ($type == 5) {
		$obj  = $_POST['obj'];
		$filenameprefix = $mysqli->real_escape_string(trim($obj['filenameprefix']));
		$sql = sqlexport_excel();
		$mysqli->autocommit(FALSE);
		try {
			if ($sql != '') {
				if ($re1 = $mysqli->query($sql)) {
					if ($re1->num_rows > 0) {
						$data = excelRow($re1);
						$writer = new XLSXWriter();
						$writer->writeSheet($data);
						$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
						$filename = $filenameprefix . '-' . $randomString . '.xlsx';
						ob_end_clean();
						header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
						header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
						header('Content-Transfer-Encoding: binary');
						header('Cache-Control: must-revalidate');
						header('Pragma: public');
						$writer->writeToStdOut();
					} else {
						echo json_encode(array('ch' => 2, 'data' => "ไม่พบข้อมูลในระบบ"));
					}
				} else {
					echo json_encode(array('ch' => 2, 'data' => "Error SP"));
				}
			} else {
				echo json_encode(array('ch' => 2, 'data' => "Error SP"));
			}
			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'ViewGTN'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ViewGTN'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		$obj  = $_POST['obj'];
		//$explode = explode("/", $obj);
		$GTN_Number  = $obj;

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(tsh.Shipping_Header_ID, TRUE) AS Shipping_Header_ID,
				Confirm_Shipping_DateTime
			FROM
				tbl_shipping_header tsh
			WHERE
				GTN_Number = '$GTN_Number'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Shipping_Header_ID = $row['Shipping_Header_ID'];
				$Confirm_Shipping_DateTime = $row['Confirm_Shipping_DateTime'];
			}

			$sql = "UPDATE tbl_shipping_header 
			SET 
				Total_Qty = 0,
				Status_Shipping = 'PENDING',
				Trip_Number = 0,
				Confirm_Shipping_DateTime = null,
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy,
				Created_By_ID = $cBy
			WHERE
				GTN_Number = '$GTN_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			//เช็คว่ามีหา gtn ใน transaction
			$sql = "SELECT 
				GTN_Number
			FROM
				tbl_transaction tts
					INNER JOIN
				tbl_shipping_header tsh ON tts.Shipping_Header_ID = tsh.Shipping_Header_ID
			WHERE
				GTN_Number = '$GTN_Number'
			ORDER BY tts.Creation_DateTime DESC
			LIMIT 1;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);

			if ($re1->num_rows > 0) {
				//exit('1');

				$sql = "SELECT 
					To_Area,
					BIN_TO_UUID(To_Loc_ID, TRUE) AS To_Loc_ID,
					(SELECT Location_Code FROM tbl_location_master where To_Loc_ID = Location_ID )AS From_Loc
				FROM
					tbl_transaction tts
						INNER JOIN
					tbl_shipping_header tsh ON tts.Shipping_Header_ID = tsh.Shipping_Header_ID
						INNER JOIN
					tbl_part_master tpm ON tts.Part_ID = tpm.Part_ID
				WHERE
					GTN_Number = '$GTN_Number'
						ORDER BY tts.Creation_DateTime DESC LIMIT 1;";

				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$To_Area = $row['To_Area'];
					$To_Loc_ID = $row['To_Loc_ID'];
				}

				$sql = "SELECT 
					From_Area,
					BIN_TO_UUID(From_Loc_ID, TRUE) AS From_Loc_ID,
					(SELECT Location_Code FROM tbl_location_master where From_Loc_ID = Location_ID )AS To_Loc
				FROM
					tbl_transaction tts
						INNER JOIN
					tbl_shipping_header tsh ON tts.Shipping_Header_ID = tsh.Shipping_Header_ID
						INNER JOIN
					tbl_part_master tpm ON tts.Part_ID = tpm.Part_ID
				WHERE
					GTN_Number = '$GTN_Number'
						ORDER BY tts.Creation_DateTime DESC LIMIT 1;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$From_Area = $row['From_Area'];
					$From_Loc_ID = $row['From_Loc_ID'];
				}
				//  Pick			Truck Sim
				//exit($From_Area . ' , ' . $To_Area);
				//exit($From_Loc_ID .' , '.$To_Loc_ID);


				if ($To_Area == 'Truck Sim') {

					$sql = "SELECT 
						pickp.Area,
						BIN_TO_UUID(pickp.Location_ID, TRUE) AS Location_ID,
						BIN_TO_UUID(Picking_Pre_ID, TRUE) AS Picking_Pre_ID
					FROM
						tbl_shipping_pre tsp
							INNER JOIN
						tbl_shipping_header tsh ON tsh.Shipping_Header_ID = tsp.Shipping_Header_ID
							INNER JOIN
						tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
							INNER JOIN 
						tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
					WHERE
						GTN_Number = '$GTN_Number';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$From_Area = $row['Area'];
						$From_Loc_ID = $row['Location_ID'];
						$Picking_Pre_ID = $row['Picking_Pre_ID'];


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
							'$To_Area',
							'$From_Area',
							'EDIT',
							ROW_NUMBER() OVER (ORDER BY tsp.Shipping_Pre_ID),
							now(),
							$cBy,
							UUID_TO_BIN('$To_Loc_ID',TRUE),
							UUID_TO_BIN('$From_Loc_ID',TRUE),
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
							tsh.GTN_Number = '$GTN_Number'
								AND BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}


					$sql = "SELECT 
						pickp.Serial_ID,
						BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID,
						SUM(pickp.Qty_Package) AS Qty_Package
					FROM
						tbl_picking_pre pickp
							INNER JOIN
						tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
							INNER JOIN
						tbl_shipping_pre tsp ON pickh.TS_Number = tsp.TS_Number
							INNER JOIN
						tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
					WHERE
						BIN_TO_UUID(tsp.Shipping_Header_ID, TRUE) = '$Shipping_Header_ID'
					GROUP BY pickp.Serial_ID , pickp.Part_ID;";

					//exit($sql);
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Serial_ID = $row['Serial_ID'];
						$Qty_Package = $row['Qty_Package'];
						$Part_ID = $row['Part_ID'];

						$sql = "UPDATE tbl_inventory tiv
								INNER JOIN
							tbl_picking_pre pickp ON tiv.Serial_ID = pickp.Serial_ID
									AND tiv.Part_ID = pickp.Part_ID
								INNER JOIN
							tbl_picking_header pickh ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
								INNER JOIN
							tbl_shipping_pre tsp ON tsp.TS_Number = pickh.TS_Number
						SET 
							tiv.Location_ID = tsp.Location_ID,
							tiv.Area = tsp.Area,
							tiv.Ship = 'Y',
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

						$sql = "UPDATE tbl_report trt
								INNER JOIN
							tbl_picking_pre pickp ON trt.Serial_ID = pickp.Serial_ID
									AND trt.Part_ID = pickp.Part_ID
								INNER JOIN
							tbl_picking_header pickh ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
								INNER JOIN
							tbl_shipping_pre tsp ON tsp.TS_Number = pickh.TS_Number
						SET 
							trt.Delivery_Qty = trt.Delivery_Qty-$Qty_Package,
							trt.Status = 'In-Transit', 
							trt.Main_Status = 'IN', 
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
					}
				}
			} else {
				//ยังไม่ put away
				//exit('4');

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
					pickp.Area,
					'EDIT',
					ROW_NUMBER() OVER (ORDER BY tsp.Shipping_Pre_ID),
					now(),
					$cBy,
					pickp.Location_ID,
					pickp.Location_ID,
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
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}


			$sql = "UPDATE tbl_shipping_pre 
			SET 
				status = 'PENDING',
				Created_By_ID = $cBy
			WHERE 
				BIN_TO_UUID(Shipping_Header_ID, TRUE) = '$Shipping_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "WITH a AS (
			SELECT 
				GTN_Number,
				Creation_DateTime,
				MONTH(Creation_DateTime) AS Creation_Month,
				tdate.Date
			FROM 
				tbl_shipping_header tsh
				CROSS JOIN 
					tbl_date tdate
			WHERE 
				YEAR(tdate.Date) = YEAR(curdate())
			ORDER BY 
				GTN_Number, tdate.Date)
			SELECT a.*
			FROM a 
			WHERE Creation_Month = MONTH(curdate()) 
			AND GTN_Number = '$GTN_Number'
			GROUP BY GTN_Number;";
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
	if ($_SESSION['xxxRole']->{'ViewGTN'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {


		$obj  = $_POST['obj'];
		$GTN_Number  = $obj;

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(tsh.Shipping_Header_ID, TRUE) AS Shipping_Header_ID
			FROM
				tbl_shipping_header tsh
			WHERE
				GTN_Number = '$GTN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Shipping_Header_ID = $row['Shipping_Header_ID'];
			}

			$sql = "SELECT 
				TS_Number
			FROM
				tbl_shipping_pre tsp
			WHERE
				BIN_TO_UUID(Shipping_Header_ID,TRUE) = '$Shipping_Header_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$TS_Number = $row['TS_Number'];

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
			}

			$sql = "UPDATE tbl_shipping_header 
			SET 
				Status_Shipping = 'CANCEL',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				GTN_Number = '$GTN_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "UPDATE tbl_shipping_pre
			SET 
				status = 'CANCEL'
			WHERE
				BIN_TO_UUID(Shipping_Header_ID, TRUE) = '$Shipping_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
			}


			//เช็คว่ามีหา gtn ใน transaction
			$sql = "SELECT 
				GTN_Number
			FROM
				tbl_transaction tts
					INNER JOIN
				tbl_shipping_header tsh ON tts.Shipping_Header_ID = tsh.Shipping_Header_ID
			WHERE
				GTN_Number = '$GTN_Number'
			ORDER BY tts.Creation_DateTime DESC
			LIMIT 1;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);

			if ($re1->num_rows > 0) {
				//exit('1');
				$sql = "SELECT 
					To_Area,
					BIN_TO_UUID(To_Loc_ID, TRUE) AS To_Loc_ID,
					(SELECT Location_Code FROM tbl_location_master where To_Loc_ID = Location_ID )AS From_Loc
				FROM
					tbl_transaction tts
						INNER JOIN
					tbl_shipping_header tsh ON tts.Shipping_Header_ID = tsh.Shipping_Header_ID
						INNER JOIN
					tbl_part_master tpm ON tts.Part_ID = tpm.Part_ID
				WHERE
					GTN_Number = '$GTN_Number'
						ORDER BY tts.Creation_DateTime DESC LIMIT 1;";

				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$To_Area = $row['To_Area'];
					$To_Loc_ID = $row['To_Loc_ID'];
				}



				if ($To_Area == 'Truck Sim') {
					//put away
					//exit('5');

					$sql = "SELECT 
						pickp.Area,
						BIN_TO_UUID(pickp.Location_ID, TRUE) AS Location_ID,
						BIN_TO_UUID(Picking_Pre_ID, TRUE) AS Picking_Pre_ID
					FROM
						tbl_shipping_pre tsp
							INNER JOIN
						tbl_shipping_header tsh ON tsh.Shipping_Header_ID = tsp.Shipping_Header_ID
							INNER JOIN
						tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
							INNER JOIN 
						tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
					WHERE
						GTN_Number = '$GTN_Number';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$From_Area = $row['Area'];
						$From_Loc_ID = $row['Location_ID'];
						$Picking_Pre_ID = $row['Picking_Pre_ID'];


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
							'$To_Area',
							'$From_Area',
							'CANCEL',
							ROW_NUMBER() OVER (ORDER BY tsp.Shipping_Pre_ID),
							now(),
							$cBy,
							UUID_TO_BIN('$To_Loc_ID',TRUE),
							UUID_TO_BIN('$From_Loc_ID',TRUE),
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
							tsh.GTN_Number = '$GTN_Number'
								AND BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}


					$sql = "SELECT 
						pickp.Serial_ID,
						BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID,
						SUM(pickp.Qty_Package) AS Qty_Package
					FROM
						tbl_picking_pre pickp
							INNER JOIN
						tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
							INNER JOIN
						tbl_shipping_pre tsp ON pickh.TS_Number = tsp.TS_Number
							INNER JOIN
						tbl_shipping_header tsh ON tsp.Shipping_Header_ID = tsh.Shipping_Header_ID
					WHERE
						BIN_TO_UUID(tsp.Shipping_Header_ID, TRUE) = '$Shipping_Header_ID'
					GROUP BY pickp.Serial_ID, pickp.Part_ID;";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Serial_ID = $row['Serial_ID'];
						$Qty_Package = $row['Qty_Package'];
						$Part_ID = $row['Part_ID'];

						$sql = "UPDATE tbl_inventory tiv
								INNER JOIN
							tbl_picking_pre pickp ON tiv.Serial_ID = pickp.Serial_ID
									AND tiv.Part_ID = pickp.Part_ID
								INNER JOIN
							tbl_picking_header pickh ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
								INNER JOIN
							tbl_shipping_pre tsp ON tsp.TS_Number = pickh.TS_Number
						SET 
							tiv.Location_ID = tsp.Location_ID,
							tiv.Area = tsp.Area,
							tiv.Ship = '',
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

						$sql = "UPDATE tbl_report trt
								INNER JOIN
							tbl_picking_pre pickp ON trt.Serial_ID = pickp.Serial_ID
									AND trt.Part_ID = pickp.Part_ID
								INNER JOIN
							tbl_picking_header pickh ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
								INNER JOIN
							tbl_shipping_pre tsp ON tsp.TS_Number = pickh.TS_Number
						SET 
							trt.Delivery_Qty = trt.Delivery_Qty-$Qty_Package,
							trt.Status = 'In-Transit', 
							trt.Main_Status = 'IN', 
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
					}
				}
			} else {

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
					pickp.Area,
					'CANCEL',
					ROW_NUMBER() OVER (ORDER BY tsp.Shipping_Pre_ID),
					now(),
					$cBy,
					pickp.Location_ID,
					pickp.Location_ID,
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
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$sql = "WITH a AS (
			SELECT 
				GTN_Number,
				Creation_DateTime,
				MONTH(Creation_DateTime) AS Creation_Month,
				tdate.Date
			FROM 
				tbl_shipping_header tsh
				CROSS JOIN 
					tbl_date tdate
			WHERE 
				YEAR(tdate.Date) = YEAR(curdate())
			ORDER BY 
				GTN_Number, tdate.Date)
			SELECT a.*
			FROM a 
			WHERE Creation_Month = MONTH(curdate()) 
			AND GTN_Number = '$GTN_Number'
			GROUP BY GTN_Number;";
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
	if ($_SESSION['xxxRole']->{'ViewGTN'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');



function select_group($mysqli)
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
    tsp.status = 'COMPLETE'
        AND (tsh.Status_Shipping = 'PENDING'
        OR tsh.Status_Shipping = 'COMPLETE'
        OR tsh.Status_Shipping = 'CONFIRM SHIP')
GROUP BY Serial_Package , tpm.Part_ID
ORDER BY tsh.Creation_DateTime DESC, Serial_Package ASC;";
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

function excelRow($result, $row = true, $seq = 0)
{
	$exceldata = array();
	$headdata = array();
	$data = array();
	$c = 0;
	if ($row) {
		$i = $seq;
		array_push($headdata, 'NO');
		while ($row = $result->fetch_field()) {
			array_push($headdata, $row->name);
		}
		$data[] = $headdata;
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			array_unshift($row, ++$c);
			$data[] = $row;
		}
	}
	return $data;
}

function sqlexport_excel()
{
	$sql = "WITH a as (
		SELECT 
			tsh.GTN_Number,
			DATE_FORMAT(tsh.Ship_Date, '%d/%m/%y') AS Ship_Date,
			DATE_FORMAT(tsh.Ship_Time, '%h:%i:%s') AS Ship_Time,
			tsh.Trip_Number,
			Invoice_Number,
			Truck_ID,
			Truck_Driver,
			Truck_Type,
			tcm.Customer_Code,
			tsp.TS_Number,
			pickh.Serial_Number,
			pickp.Serial_ID,
			tpm.Part_No,
			tpm.Part_Name,
			tpm.Model,
			tpm.Part_Type,
			tpm.Type,
			color,
			Mat_SAP1,
			Mat_SAP3,
			tsh.Total_Qty,
			pickh.Total_Qty AS Qty,
			IF(tph.Serial_Number IS NULL,
				pickp.Serial_ID,
				tph.Serial_Number) AS Serial_Package,
			IF(tph.Serial_Number IS NULL,
				pickp.Qty_Package,
				SUM(pickp.Qty_Package)) AS Qty_Package,
			tsh.Status_Shipping,
			DATE_FORMAT(tsh.Creation_DateTime, '%Y-%m-%d') AS Creation_DateTime,
			CONCAT(user_fName,' ',SUBSTRING(user_lname,1,1),'.') AS Created_By
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
				LEFT JOIN
			tbl_user tuser ON tsh.Created_By_ID = tuser.user_id
		WHERE
			tsp.status = 'COMPLETE'
				AND (tsh.Status_Shipping = 'COMPLETE'
				OR tsh.Status_Shipping = 'CONFIRM SHIP')
		GROUP BY Serial_Package , tpm.Part_ID
		ORDER BY tsh.Creation_DateTime DESC, Serial_Package ASC
		)
		SELECT
			a.GTN_Number AS 'GTN Number',
			a.Invoice_Number AS 'Invoice Number',
			a.TS_Number AS 'Pick Sheet Number',
			a.Part_No AS 'Part Number',
			a.Part_Name AS 'Part Name',
			a.color,
			a.Serial_Package AS 'Package ID',
			a.Total_Qty AS 'Qty',
			'Pcs' AS 'Unit',
			a.Qty_Package AS 'Qty Package',
			a.Type AS 'Part Type',
			if(SUBSTRING(a.Serial_Package, 1, 1) = 'B', 'Box', 'Rack') AS 'Package Type',
			a.Model,
			a.Customer_Code AS 'Ship To',
			a.Ship_Date AS 'Ship Date',
			a.Ship_Time AS 'Ship Time',
			a.Truck_ID AS 'Truck ID',
			a.Truck_Type AS 'Truck Type',
			a.Mat_SAP1 AS 'Mat SAP 1',
			a.Mat_SAP3 AS 'Mat_SAP 3',
			a.Creation_DateTime AS 'Creation Date',
			a.Created_By AS 'Created By',
			a.Status_Shipping AS 'Status',
			'' AS Remark
		FROM a;";

	return $sql;
}

$mysqli->close();
exit();
