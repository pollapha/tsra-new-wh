<?php

use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;

if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ViewReceive'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ViewReceive'}[0] == 0) {
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
		$Date = date("Y-m-d");
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
						$filename = $filenameprefix . '-' . $Date . $randomString . '.xlsx';
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
	if ($_SESSION['xxxRole']->{'ViewReceive'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ViewReceive'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$GRN_Number  = $explode[0];
		//exit($GRN_Number .' , '.$FG_Serial_Number);

		$mysqli->autocommit(FALSE);
		try {


			$sql = "SELECT 
				Part_No,
				Part_Type,
				Area
			FROM
				tbl_receiving_header trh
					INNER JOIN
				tbl_receiving_pre trp ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			WHERE
				GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_Type = $row['Part_Type'];
				$Area = $row['Area'];
			}

			//exit($Area);

			$sql = "SELECT 
				trp.Serial_ID,
				trp.WorkOrder,
				trp.Part_No,
				trp.Part_Type,
				trp.Qty_Package
			FROM
				tbl_receiving_pre trp
					INNER JOIN
				tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
					INNER JOIN
				tbl_inventory tiv ON trp.Receiving_Pre_ID = tiv.Receiving_Pre_ID
			WHERE
				GRN_Number = '$GRN_Number'
					AND tiv.Status_Working != 'FG' AND tiv.Status_Working != 'Wait Assembly';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('แก้ไขไม่สำเร็จ มีบางรายการเลือกไปแล้ว' . __LINE__);
			}

			$sql = "SELECT 
				BIN_TO_UUID(trh.Receiving_Header_ID, TRUE) AS Receiving_Header_ID
			FROM
				tbl_receiving_header trh
			WHERE
				GRN_Number = '$GRN_Number'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
			}



			$sql = "UPDATE tbl_receiving_header 
			SET 
				Status_Receiving = 'PENDING',
				Confirm_Receive_DateTime = NULL,
				Total_Qty = 0,
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				GRN_Number = '$GRN_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "UPDATE tbl_receiving_pre 
				SET 
					status = 'PENDING'
				WHERE 
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			//มีข้อมูลใน Inventory หรือไม่
			$sql = "SELECT 
					trp.Serial_ID,
					trp.WorkOrder,
					trp.Part_No,
					trp.Part_Type,
					trp.Qty_Package
				FROM
					tbl_receiving_pre trp
						INNER JOIN
					tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
						INNER JOIN
					tbl_inventory tiv ON trp.Serial_ID = tiv.Serial_ID
						AND (trp.WorkOrder = tiv.WorkOrder
						OR trp.WorkOrder IS NULL)
				WHERE
					GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				//เพิ่งเพิ่มไม่มีข้อมูลใน Inventory
				//exit('1');
				$sql = "INSERT INTO
					tbl_transaction(
						Receiving_Header_ID,
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
					Last_Updated_DateTime,
					Updated_By_ID)
				SELECT
					trp.Receiving_Header_ID,
					trp.Part_ID,
					trp.Serial_ID,
					trp.WorkOrder,
					trp.Qty_Package,
					trp.Area,
					trp.Area,
					'EDIT',
					ROW_NUMBER() OVER (ORDER BY trp.Serial_ID),
					now(),
					$cBy,
					now(),
					$cBy
				FROM
					tbl_receiving_header trh
						LEFT JOIN 
					tbl_receiving_pre trp ON trh.Receiving_Header_ID = trp.Receiving_Header_ID 
				WHERE
					trh.GRN_Number = '$GRN_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			} else {
				//มีช้อมูลใน Inventory
				//exit('2');

				$sql = "SELECT 
					To_Area,
					BIN_TO_UUID(To_Loc_ID, TRUE) AS To_Loc_ID,
					(SELECT Location_Code FROM tbl_location_master where To_Loc_ID = Location_ID )AS From_Loc
				FROM
					tbl_transaction tts
						INNER JOIN
    				tbl_receiving_header trh ON tts.Receiving_Header_ID = trh.Receiving_Header_ID
				WHERE
					GRN_Number = '$GRN_Number'
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
    				tbl_receiving_header trh ON tts.Receiving_Header_ID = trh.Receiving_Header_ID
				WHERE
					GRN_Number = '$GRN_Number'
						AND Trans_Type = 'IN'
						ORDER BY tts.Creation_DateTime DESC LIMIT 1;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$From_Area = $row['From_Area'];
					$From_Loc_ID = $row['From_Loc_ID'];
				}
				//  N/A                   FG/Received
				//exit($From_Area . ' , ' . $To_Area);
				//exit($From_Loc_ID .' , '.$To_Loc_ID);


				//confirm

				// $sql = "UPDATE tbl_receiving_pre 
				// SET 
				// 	Count = 0
				// WHERE 
				// 	BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				// sqlError($mysqli, __LINE__, $sql, 1);
				// if ($mysqli->affected_rows == 0) {
				// 	throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				// }


				$sql = "DELETE FROM tbl_report
				WHERE
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถลบได้' . __LINE__);
				}


				$sql = "DELETE FROM tbl_inventory
				WHERE
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถลบได้' . __LINE__);
				}

				$sql = "INSERT INTO
					tbl_transaction(
						Receiving_Header_ID,
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
					trp.Receiving_Header_ID,
					trp.Part_ID,
					trp.Serial_ID,
					trp.WorkOrder,
					trp.Qty_Package,
					'$To_Area',
					'$From_Area',
					'EDIT',
					ROW_NUMBER() OVER (ORDER BY trp.Serial_ID),
					now(),
					$cBy,
					UUID_TO_BIN('$To_Loc_ID',TRUE),
					UUID_TO_BIN('$From_Loc_ID',TRUE),
					now(),
					$cBy
				FROM
					tbl_receiving_header trh
						LEFT JOIN 
					tbl_receiving_pre trp ON trh.Receiving_Header_ID = trp.Receiving_Header_ID 
				WHERE
					trh.GRN_Number = '$GRN_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$sql = "WITH a AS (
			SELECT 
				GRN_Number,
				Creation_DateTime,
				MONTH(Creation_DateTime) AS Creation_Month,
				tdate.Date
			FROM 
				tbl_receiving_header trh
				CROSS JOIN 
					tbl_date tdate
			WHERE 
				YEAR(tdate.Date) = YEAR(curdate())
			ORDER BY 
				GRN_Number, tdate.Date)
			SELECT a.*
			FROM a 
			WHERE Creation_Month = MONTH(curdate()) 
			AND GRN_Number = '$GRN_Number'
			GROUP BY GRN_Number;";
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
	if ($_SESSION['xxxRole']->{'ViewReceive'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$GRN_Number  = $explode[0];
		//exit($GRN_Number .' , '.$FG_Serial_Number);

		$mysqli->autocommit(FALSE);
		try {


			$sql = "SELECT 
				Part_No,
				Part_Type,
				Area
			FROM
				tbl_receiving_header trh
					INNER JOIN
				tbl_receiving_pre trp ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			WHERE
				GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_Type = $row['Part_Type'];
				$Area = $row['Area'];
			}

			//exit($Area);

			$sql = "SELECT 
				trp.Serial_ID,
				trp.WorkOrder,
				trp.Part_No,
				trp.Part_Type,
				trp.Qty_Package
			FROM
				tbl_receiving_pre trp
					INNER JOIN
				tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
					INNER JOIN
				tbl_inventory tiv ON trp.Receiving_Pre_ID = tiv.Receiving_Pre_ID
			WHERE
				GRN_Number = '$GRN_Number'
					AND tiv.Status_Working != 'FG' AND tiv.Status_Working != 'Wait Assembly';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('ยกเลิกไม่สำเร็จ มีบางรายการเลือกไปแล้ว' . __LINE__);
			}


			$sql = "SELECT 
				BIN_TO_UUID(trh.Receiving_Header_ID, TRUE) AS Receiving_Header_ID
			FROM
				tbl_receiving_header trh
			WHERE
				GRN_Number = '$GRN_Number'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
			}


			$sql = "UPDATE tbl_receiving_header 
			SET 
				Status_Receiving = 'CANCEL',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				GRN_Number = '$GRN_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "UPDATE tbl_receiving_pre 
				SET 
					status = 'CANCEL'
				WHERE 
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			//มีข้อมูลใน Inventory หรือไม่
			$sql = "SELECT 
					trp.Serial_ID,
					trp.WorkOrder,
					trp.Part_No,
					trp.Part_Type,
					trp.Qty_Package
				FROM
					tbl_receiving_pre trp
						INNER JOIN
					tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
						INNER JOIN
					tbl_inventory tiv ON trp.Serial_ID = tiv.Serial_ID
						AND (trp.WorkOrder = tiv.WorkOrder
						OR trp.WorkOrder IS NULL)
				WHERE
					GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				//เพิ่งเพิ่มไม่มีข้อมูลใน Inventory
				//exit('1');
				$sql = "INSERT INTO
					tbl_transaction(
						Receiving_Header_ID,
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
					Last_Updated_DateTime,
					Updated_By_ID)
				SELECT
					trp.Receiving_Header_ID,
					trp.Part_ID,
					trp.Serial_ID,
					trp.WorkOrder,
					trp.Qty_Package,
					trp.Area,
					trp.Area,
					'CANCEL',
					ROW_NUMBER() OVER (ORDER BY trp.Serial_ID),
					now(),
					$cBy,
					now(),
					$cBy
				FROM
					tbl_receiving_header trh
						LEFT JOIN 
					tbl_receiving_pre trp ON trh.Receiving_Header_ID = trp.Receiving_Header_ID 
				WHERE
					trh.GRN_Number = '$GRN_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			} else {
				//มีช้อมูลใน Inventory
				//exit('2');
				$sql = "SELECT 
					To_Area,
					BIN_TO_UUID(To_Loc_ID, TRUE) AS To_Loc_ID,
					(SELECT Location_Code FROM tbl_location_master where To_Loc_ID = Location_ID )AS From_Loc
				FROM
					tbl_transaction tts
						INNER JOIN
    				tbl_receiving_header trh ON tts.Receiving_Header_ID = trh.Receiving_Header_ID
				WHERE
					GRN_Number = '$GRN_Number'
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
    				tbl_receiving_header trh ON tts.Receiving_Header_ID = trh.Receiving_Header_ID
				WHERE
					GRN_Number = '$GRN_Number'
						AND Trans_Type = 'IN'
						ORDER BY tts.Creation_DateTime DESC LIMIT 1;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล Location' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$From_Area = $row['From_Area'];
					$From_Loc_ID = $row['From_Loc_ID'];
				}
				//  N/A                   FG/Received
				//exit($From_Area . ' , ' . $To_Area);
				//exit($From_Loc_ID .' , '.$To_Loc_ID);


				//confirm
				$sql = "DELETE FROM tbl_report
				WHERE
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถลบได้' . __LINE__);
				}


				$sql = "DELETE FROM tbl_inventory
				WHERE
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถลบได้' . __LINE__);
				}

				$sql = "INSERT INTO
					tbl_transaction(
						Receiving_Header_ID,
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
					trp.Receiving_Header_ID,
					trp.Part_ID,
					trp.Serial_ID,
					trp.WorkOrder,
					trp.Qty_Package,
					'$To_Area',
					'$From_Area',
					'CANCEL',
					ROW_NUMBER() OVER (ORDER BY trp.Serial_ID),
					now(),
					$cBy,
					UUID_TO_BIN('$To_Loc_ID',TRUE),
					UUID_TO_BIN('$From_Loc_ID',TRUE),
					now(),
					$cBy
				FROM
					tbl_receiving_header trh
						LEFT JOIN 
					tbl_receiving_pre trp ON trh.Receiving_Header_ID = trp.Receiving_Header_ID 
				WHERE
					trh.GRN_Number = '$GRN_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}


			$sql = "WITH a AS (
			SELECT 
				GRN_Number,
				Creation_DateTime,
				MONTH(Creation_DateTime) AS Creation_Month,
				tdate.Date
			FROM 
				tbl_receiving_header trh
				CROSS JOIN 
					tbl_date tdate
			WHERE 
				YEAR(tdate.Date) = YEAR(curdate())
			ORDER BY 
				GRN_Number, tdate.Date)
			SELECT a.*
			FROM a 
			WHERE Creation_Month = MONTH(curdate()) 
			AND GRN_Number = '$GRN_Number'
			GROUP BY GRN_Number;";
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
	if ($_SESSION['xxxRole']->{'ViewReceive'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

function select_group($mysqli)
{

	$sql = "SELECT 
    trh.GRN_Number,
    trh.DN_Number,
    trh.Serial_Number,
    DATE_FORMAT(trh.Receive_Date, '%d/%m/%y') AS Receive_Date,
    trp.Serial_ID,
    trp.WorkOrder,
    trp.Part_No,
    tpm.Part_Name,
    trp.Model,
    trp.Part_Type,
    tpm.Type,
    trp.Package_Type,
    trh.Total_Qty,
    trp.Qty_Package,
    trp.Area,
    tcm.Customer_Code,
    trh.Status_Receiving,
    DATE_FORMAT(trh.Confirm_Receive_DateTime,
            '%d/%m/%y %H:%i') AS Confirm_Receive_DateTime,
	Status_Working
FROM
    tbl_receiving_pre trp
        INNER JOIN
    tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
		LEFT JOIN
    tbl_inventory tiv ON trp.Receiving_Pre_ID = tiv.Receiving_Pre_ID
        INNER JOIN
    tbl_part_master tpm ON trp.Part_ID = tpm.Part_ID
        LEFT JOIN
    tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
    (trh.Status_Receiving = 'PENDING'
        OR trh.Status_Receiving = 'COMPLETE')
        AND (trp.status = 'PENDING'
        OR trp.status = 'COMPLETE')
ORDER BY GRN_Number DESC , Serial_ID ASC;";
	$re1 = sqlError($mysqli, __LINE__, $sql, 1);
	$value = jsonRow($re1, false, 0);
	$data = group_by('GRN_Number', $value); //group datatable tree
	$dateset = array();
	$c = 1;
	foreach ($data as $key1 => $value1) {
		$sub = selectColumnFromArray($value1, array(
			'Serial_ID',
			'WorkOrder',
			'Part_No',
			'Part_Name',
			'Model',
			'Total_Qty',
			'Qty_Package',
			'Area',
			'Status_Working'
		)); //ที่จะให้อยู่ในตัว Child rows
		$c2 = 1;
		foreach ($sub as $key2 => $value2) {
			$sub[$key2]['GRN_Number'] = $c2;
			$sub[$key2]['Is_Header'] = 'NO';
			$c2++;
		}

		$dateset[] =  array(
			"No" => $c, 'Is_Header' => 'YES', "GRN_Number" => $key1,
			"DN_Number" => $value1[0]['DN_Number'],
			"Customer_Code" => $value1[0]['Customer_Code'],
			"Serial_Number" => $value1[0]['Serial_Number'],
			"Receive_Date" => $value1[0]['Receive_Date'],
			"Status_Receiving" => $value1[0]['Status_Receiving'],
			"Package_Type" => $value1[0]['Package_Type'],
			"Part_Type" => $value1[0]['Part_Type'],
			"Type" => $value1[0]['Type'],
			"Area" => $value1[0]['Area'],
			"Confirm_Receive_DateTime" => $value1[0]['Confirm_Receive_DateTime'],
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
	$sql = "SELECT 
		DATE_FORMAT(trh.Receive_Date, '%Y-%m-%d') AS 'Receive Date',
		DATE_FORMAT(trh.Confirm_Receive_DateTime, '%h:%i:%s') AS 'Receive Time',
		trh.GRN_Number AS 'GRN Number',
		'Thai Summit Rayong (TSRA)' AS Customer,
		trp.Part_No AS 'Part Number',
		tpm.Part_Name AS 'Part Name',
		trp.Serial_ID AS 'Package Number',
		trp.WorkOrder AS 'Work order',
		tpm.Color AS Color,
		trh.Total_Qty AS Qty,
		trp.Qty_Unit AS Unit,
		trp.Qty_Package AS 'Qty Package',
		tpm.Type AS 'Part Type',
		trp.Package_Type AS 'Package Type',
		CONCAT(FORMAT(Width_Part, 0),
				'x',
				FORMAT(Length_Part, 0),
				'x',
				FORMAT(Height_Part, 0)) AS Dimansion,
		trp.Model AS Model,
		tcm.Customer_Code AS 'Ship To',
		Mat_SAP1 AS 'Mat SAP 1',
		Mat_SAP3 AS 'Mat_SAP 3',
		DN_Number AS 'DN Number',
		CONCAT(user_fName,
				' ',
				SUBSTRING(user_lname, 1, 1),
				'.') AS 'Created By',
		'' AS Remark
	FROM
		tbl_receiving_pre trp
			INNER JOIN
		tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			INNER JOIN
		tbl_part_master tpm ON trp.Part_ID = tpm.Part_ID
			LEFT JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			LEFT JOIN
		tbl_user tuser ON trh.Created_By_ID = tuser.user_id
	WHERE
		Confirm_Receive_DateTime IS NOT NULL
			AND (trh.Status_Receiving = 'PENDING' OR trh.Status_Receiving = 'COMPLETE')
			AND (trp.status = 'PENDING' OR trp.status = 'COMPLETE')
			AND MONTH(Receive_Date) = MONTH(CURDATE())
	ORDER BY GRN_Number , Serial_ID , WorkOrder ASC;";

	return $sql;
}

$mysqli->close();
exit();
