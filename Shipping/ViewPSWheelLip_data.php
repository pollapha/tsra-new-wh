<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ViewPSWheelLip'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ViewPSWheelLip'}[0] == 0) {
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
	if ($_SESSION['xxxRole']->{'ViewPSWheelLip'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ViewPSWheelLip'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		$obj  = $_POST['obj'];
		$TS_Number  = $obj;

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID
			FROM
				tbl_picking_header pickh
			WHERE
				TS_Number = '$TS_Number'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Picking_Header_ID = $row['Picking_Header_ID'];
			}

			$sql = "SELECT 
				TS_Number
			FROM
				tbl_picking_header
			WHERE 
				TS_Number = '$TS_Number'
					AND Pick = 'N';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('แก้ไขไม่สำเร็จ (มีการ Ship ไปแล้ว) กรุณาไปทำการยกเลิกขั้นตอนการ Ship ก่อน ' . __LINE__);
			}

			//exit('s');

			$sql = "UPDATE tbl_picking_header 
			SET 
				Status_Picking = 'PENDING',
				Total_Qty = 0,
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				TS_Number = '$TS_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			//เช็คว่ามี gtn ใน transaction
			$sql = "SELECT 
				TS_Number
			FROM
				tbl_transaction tts
					INNER JOIN
				tbl_picking_header pickh ON tts.Picking_Header_ID = pickh.Picking_Header_ID
			WHERE
				TS_Number = '$TS_Number'
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
					tbl_picking_header pickh ON tts.Picking_Header_ID = pickh.Picking_Header_ID
						INNER JOIN
					tbl_part_master tpm ON tts.Part_ID = tpm.Part_ID
				WHERE
					TS_Number = '$TS_Number'
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
					BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID
				FROM
					tbl_picking_header pickh
				WHERE
					TS_Number = '$TS_Number'
						AND Confirm_Picking_DateTime IS NULL";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {

					$sql = "INSERT INTO
						tbl_transaction(
						Picking_Header_ID,
						Receiving_Header_ID,
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
						pickp.Part_ID,
						pickp.Serial_ID,
						pickp.Qty_Package,
						pickp.Area,
						pickp.Area,
						'EDIT',
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


					$sql = "UPDATE tbl_picking_pre 
					SET 
						status = 'PENDING',
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy

					WHERE
						BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
					}
				}
				//
				else {

					$sql = "SELECT 
						Area, BIN_TO_UUID(Location_ID, TRUE) AS Location_ID,
						BIN_TO_UUID(Picking_Pre_ID, TRUE) AS Picking_Pre_ID
					FROM
						tbl_picking_pre pickp
							INNER JOIN
						tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					WHERE
						TS_Number = '$TS_Number';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$From_Area = $row['Area'];
						$From_Loc_ID = $row['Location_ID'];
						$Picking_Pre_ID = $row['Picking_Pre_ID'];

						//confirm and put away
						$sql = "UPDATE tbl_picking_pre 
						SET 
							Area = '$From_Area',
							Location_ID = UUID_TO_BIN('$From_Loc_ID',TRUE),
							status = 'PENDING',
							Count = 0,
							Last_Updated_DateTime = NOW(),
							Updated_By_ID = $cBy

						WHERE
							BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
								AND BIN_TO_UUID(Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
						}

						$sql = "INSERT INTO
							tbl_transaction(
							Picking_Header_ID,
						Receiving_Header_ID,
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
							pickp.Part_ID,
							pickp.Serial_ID,
							pickp.Qty_Package,
							'$To_Area',
							'$From_Area',
							'EDIT',
							ROW_NUMBER() OVER (ORDER BY pickp.Picking_Pre_ID),
							now(),
							$cBy,
							UUID_TO_BIN('$To_Loc_ID',TRUE),
							UUID_TO_BIN('$From_Loc_ID',TRUE),
							now(),
							$cBy
						FROM
							tbl_picking_pre pickp
								INNER JOIN
							tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
						WHERE
							pickh.TS_Number = '$TS_Number'
								AND BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}

					$sql = "SELECT 
						pickp.Serial_ID,
						pickp.Qty_Package,
						BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID
					FROM
						tbl_picking_pre pickp
					WHERE
						BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID'
					GROUP BY pickp.Serial_ID , pickp.Part_ID;";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Serial_ID = $row['Serial_ID'];
						$Part_ID = $row['Part_ID'];
						$Qty_Package = $row['Qty_Package'];

						$sql = "UPDATE tbl_report trt
								INNER JOIN
							tbl_picking_pre pickp ON pickp.Serial_ID = trt.Serial_ID
								AND (pickp.WorkOrder = trt.WorkOrder
								OR pickp.WorkOrder IS NULL)
						SET 
							trt.Intransit_Qty = trt.Intransit_Qty-$Qty_Package,
							trt.Main_Status = 'IN', 
							trt.Last_Updated_DateTime = NOW(), 
							trt.Updated_By_ID = $cBy
						WHERE
							BIN_TO_UUID(pickp.Picking_Header_ID,TRUE) = '$Picking_Header_ID'
								AND trt.Serial_ID = '$Serial_ID'
								AND BIN_TO_UUID(trt.Part_ID,TRUE) = '$Part_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}

						$sql = "UPDATE tbl_inventory tiv
							INNER JOIN
						tbl_picking_pre  pickp ON tiv.Serial_ID = pickp.Serial_ID
							AND (tiv.WorkOrder = pickp.WorkOrder
							OR tiv.WorkOrder IS NULL)
						SET 
							tiv.Pick_Qty = tiv.Pick_Qty-$Qty_Package,
							tiv.Location_ID = pickp.Location_ID, 
							tiv.Area = pickp.Area,
							Ship = '',
							tiv.Last_Updated_DateTime = NOW(), 
							tiv.Updated_By_ID = $cBy
						WHERE 
							BIN_TO_UUID(pickp.Picking_Header_ID,TRUE) = '$Picking_Header_ID'
								AND tiv.Serial_ID = '$Serial_ID'
								AND BIN_TO_UUID(tiv.Part_ID,TRUE) = '$Part_ID'";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}


					$sql = "UPDATE tbl_picking_header 
					SET 
						Confirm_Picking_DateTime = null,
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						TS_Number = '$TS_Number';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				}
			} else {
				//exit('2');
				//ยังไม่ put away
				$sql = "INSERT INTO
					tbl_transaction(
					Picking_Header_ID,
					Receiving_Header_ID,
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
					pickp.Part_ID,
					pickp.Serial_ID,
					pickp.Qty_Package,
					pickp.Area,
					pickp.Area,
					'EDIT',
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

				$sql = "UPDATE tbl_picking_pre  
				SET 
					status = 'PENDING'
				WHERE 
					BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$sql = "WITH a AS (
				SELECT 
					TS_Number,
					Creation_DateTime,
					MONTH(Creation_DateTime) AS Creation_Month,
					tdate.Date
				FROM 
					tbl_picking_header pickh
					CROSS JOIN 
						tbl_date tdate
				WHERE 
					YEAR(tdate.Date) = YEAR(curdate())
				ORDER BY 
					TS_Number, tdate.Date)
				SELECT a.*
				FROM a 
				WHERE Creation_Month = MONTH(curdate()) 
				AND TS_Number = '$TS_Number'
				GROUP BY TS_Number;";
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
	if ($_SESSION['xxxRole']->{'ViewPSWheelLip'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {

		$obj  = $_POST['obj'];
		$TS_Number  = $obj;

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID
			FROM
				tbl_picking_header pickh
			WHERE
				TS_Number = '$TS_Number'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Picking_Header_ID = $row['Picking_Header_ID'];
			}

			$sql = "SELECT 
				TS_Number
			FROM
				tbl_picking_header
			WHERE 
				TS_Number = '$TS_Number'
					AND Pick = 'N';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('แก้ไขไม่สำเร็จ (มีการ Ship ไปแล้ว) กรุณาไปทำการยกเลิกขั้นตอนการ Ship ก่อน ' . __LINE__);
			}

			$sql = "UPDATE tbl_picking_header 
			SET 
				Status_Picking = 'CANCEL',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				TS_Number = '$TS_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "UPDATE tbl_picking_pre  
			SET 
				status = 'CANCEL'
			WHERE 
				BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			//เช็คว่ามี gtn ใน transaction
			$sql = "SELECT 
				TS_Number
			FROM
				tbl_transaction tts
					INNER JOIN
				tbl_picking_header pickh ON tts.Picking_Header_ID = pickh.Picking_Header_ID
			WHERE
				TS_Number = '$TS_Number'
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
					tbl_picking_header pickh ON tts.Picking_Header_ID = pickh.Picking_Header_ID
						INNER JOIN
					tbl_part_master tpm ON tts.Part_ID = tpm.Part_ID
				WHERE
					TS_Number = '$TS_Number'
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
					BIN_TO_UUID(pickh.Picking_Header_ID, TRUE) AS Picking_Header_ID
				FROM
					tbl_picking_header pickh
				WHERE
					TS_Number = '$TS_Number'
						AND Confirm_Picking_DateTime IS NULL";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {

					$sql = "INSERT INTO
						tbl_transaction(
						Picking_Header_ID,
						Receiving_Header_ID,
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
						pickp.Part_ID,
						pickp.Serial_ID,
						pickp.Qty_Package,
						pickp.Area,
						pickp.Area,
						'CANCEL',
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

					$sql = "SELECT 
						pickp.Serial_ID,
						pickp.Qty_Package,
						BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID
					FROM
						tbl_picking_pre pickp
					WHERE
						BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID'
					GROUP BY pickp.Serial_ID , pickp.Part_ID;";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Serial_ID = $row['Serial_ID'];
						$Part_ID = $row['Part_ID'];
						$Qty_Package = $row['Qty_Package'];

						$sql = "UPDATE tbl_inventory tiv
							INNER JOIN
						tbl_picking_pre  pickp ON tiv.Serial_ID = pickp.Serial_ID
							AND (tiv.WorkOrder = pickp.WorkOrder
							OR tiv.WorkOrder IS NULL)
						SET 
							Ship = '',
							Status_Working = 'FG',
							tiv.Last_Updated_DateTime = NOW(), 
							tiv.Updated_By_ID = $cBy
						WHERE 
							BIN_TO_UUID(pickp.Picking_Header_ID,TRUE) = '$Picking_Header_ID'
								AND tiv.Serial_ID = '$Serial_ID'
								AND BIN_TO_UUID(tiv.Part_ID,TRUE) = '$Part_ID'";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}
				} else {
					$sql = "SELECT 
						Area, BIN_TO_UUID(Location_ID, TRUE) AS Location_ID,
						BIN_TO_UUID(Picking_Pre_ID, TRUE) AS Picking_Pre_ID
					FROM
						tbl_picking_pre pickp
							INNER JOIN
						tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
					WHERE
						TS_Number = '$TS_Number';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$From_Area = $row['Area'];
						$From_Loc_ID = $row['Location_ID'];
						$Picking_Pre_ID = $row['Picking_Pre_ID'];

						//confirm and put away
						$sql = "UPDATE tbl_picking_pre 
						SET 
							Area = '$From_Area',
							Location_ID = UUID_TO_BIN('$From_Loc_ID',TRUE),
							Count = 0,
							Last_Updated_DateTime = NOW(),
							Updated_By_ID = $cBy

						WHERE
							BIN_TO_UUID(Picking_Header_ID, TRUE) = '$Picking_Header_ID'
								AND BIN_TO_UUID(Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
						}

						$sql = "INSERT INTO
							tbl_transaction(
							Picking_Header_ID,
						Receiving_Header_ID,
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
							pickp.Part_ID,
							pickp.Serial_ID,
							pickp.Qty_Package,
							'$To_Area',
							'$From_Area',
							'CANCEL',
							ROW_NUMBER() OVER (ORDER BY pickp.Picking_Pre_ID),
							now(),
							$cBy,
							UUID_TO_BIN('$To_Loc_ID',TRUE),
							UUID_TO_BIN('$From_Loc_ID',TRUE),
							now(),
							$cBy
						FROM
							tbl_picking_pre pickp
								INNER JOIN
							tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
						WHERE
							pickh.TS_Number = '$TS_Number'
								AND BIN_TO_UUID(pickp.Picking_Pre_ID, TRUE) = '$Picking_Pre_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}

					$sql = "SELECT 
						pickp.Serial_ID,
						pickp.Qty_Package,
						BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID
					FROM
						tbl_picking_pre pickp
					WHERE
						BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID'
					GROUP BY pickp.Serial_ID , pickp.Part_ID;";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Serial_ID = $row['Serial_ID'];
						$Part_ID = $row['Part_ID'];
						$Qty_Package = $row['Qty_Package'];

						$sql = "UPDATE tbl_report trt
								INNER JOIN
							tbl_picking_pre pickp ON pickp.Serial_ID = trt.Serial_ID
								AND (pickp.WorkOrder = trt.WorkOrder
								OR pickp.WorkOrder IS NULL)
						SET 
							trt.Intransit_Qty = trt.Intransit_Qty-$Qty_Package,
							trt.Main_Status = 'IN', 
							trt.Last_Updated_DateTime = NOW(), 
							trt.Updated_By_ID = $cBy
						WHERE
							BIN_TO_UUID(pickp.Picking_Header_ID,TRUE) = '$Picking_Header_ID'
								AND trt.Serial_ID = '$Serial_ID'
								AND BIN_TO_UUID(trt.Part_ID,TRUE) = '$Part_ID';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}

						$sql = "UPDATE tbl_inventory tiv
							INNER JOIN
						tbl_picking_pre  pickp ON tiv.Serial_ID = pickp.Serial_ID
							AND (tiv.WorkOrder = pickp.WorkOrder
							OR tiv.WorkOrder IS NULL)
						SET 
							tiv.Pick_Qty = tiv.Pick_Qty-$Qty_Package,
							tiv.Location_ID = pickp.Location_ID, 
							tiv.Area = pickp.Area,
							Ship = '',
							Status_Working = 'FG',
							tiv.Last_Updated_DateTime = NOW(), 
							tiv.Updated_By_ID = $cBy
						WHERE 
							BIN_TO_UUID(pickp.Picking_Header_ID,TRUE) = '$Picking_Header_ID'
								AND tiv.Serial_ID = '$Serial_ID'
								AND BIN_TO_UUID(tiv.Part_ID,TRUE) = '$Part_ID'";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}
				}
			} else {
				//exit('2');
				//ยังไม่ put away
				$sql = "INSERT INTO
					tbl_transaction(
					Picking_Header_ID,
					Receiving_Header_ID,
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
					pickp.Part_ID,
					pickp.Serial_ID,
					pickp.Qty_Package,
					pickp.Area,
					pickp.Area,
					'CANCEL',
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


				$sql = "SELECT 
					pickp.Serial_ID,
					pickp.Qty_Package,
					BIN_TO_UUID(pickp.Part_ID,TRUE) AS Part_ID
				FROM
					tbl_picking_pre pickp
				WHERE
					BIN_TO_UUID(pickp.Picking_Header_ID, TRUE) = '$Picking_Header_ID'
				GROUP BY pickp.Serial_ID , pickp.Part_ID;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Serial_ID = $row['Serial_ID'];
					$Part_ID = $row['Part_ID'];
					$Qty_Package = $row['Qty_Package'];

					$sql = "UPDATE tbl_inventory tiv
						INNER JOIN
					tbl_picking_pre  pickp ON tiv.Serial_ID = pickp.Serial_ID
						AND (tiv.WorkOrder = pickp.WorkOrder
						OR tiv.WorkOrder IS NULL)
					SET 
						Ship = '',
						Status_Working = 'FG',
						tiv.Last_Updated_DateTime = NOW(), 
						tiv.Updated_By_ID = $cBy
					WHERE 
						BIN_TO_UUID(pickp.Picking_Header_ID,TRUE) = '$Picking_Header_ID'
							AND tiv.Serial_ID = '$Serial_ID'
							AND BIN_TO_UUID(tiv.Part_ID,TRUE) = '$Part_ID'";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				}
			}

			$sql = "WITH a AS (
				SELECT 
					TS_Number,
					Creation_DateTime,
					MONTH(Creation_DateTime) AS Creation_Month,
					tdate.Date
				FROM 
					tbl_picking_header pickh
					CROSS JOIN 
						tbl_date tdate
				WHERE 
					YEAR(tdate.Date) = YEAR(curdate())
				ORDER BY 
					TS_Number, tdate.Date)
				SELECT a.*
				FROM a 
				WHERE Creation_Month = MONTH(curdate()) 
				AND TS_Number = '$TS_Number'
				GROUP BY TS_Number;";
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
	if ($_SESSION['xxxRole']->{'ViewPSWheelLip'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');


function select_group($mysqli)
{

	$sql = "SELECT 
		pickh.TS_Number,
		BIN_TO_UUID(pickp.Picking_Pre_ID,TRUE) AS Picking_Pre_ID,
		DATE_FORMAT(pickh.Pick_Date, '%d/%m/%y') AS Pick_Date,
		pickh.Serial_Number,
		pickp.Serial_ID,
		pickp.WorkOrder,
		tpm.Part_No,
		tpm.Part_Name,
		tpm.Model,
		tpm.Type,
		pickh.Total_Qty,
		pickp.Qty_Package,
		tcm.Customer_Code,
		pickh.Status_Picking,
		DATE_FORMAT(pickh.Confirm_Picking_DateTime,
				'%d/%m/%y %H:%i') AS Confirm_Picking_DateTime
	FROM
		tbl_picking_pre pickp
			INNER JOIN
		tbl_picking_header pickh ON pickp.Picking_Header_ID = pickh.Picking_Header_ID
			INNER JOIN
		tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
			LEFT JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
	WHERE
		tpm.Type = 'Wheel lip'
			AND pickp.status = 'COMPLETE'
			AND (pickh.Status_Picking = 'PENDING'
			OR pickh.Status_Picking = 'COMPLETE')
	ORDER BY pickh.Creation_DateTime DESC, Serial_ID ASC;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('TS_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'Serial_ID',
			'WorkOrder',
			'Part_No',
			'Part_Name',
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
			"Serial_Number" => $value1[0]['Serial_Number'],
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
