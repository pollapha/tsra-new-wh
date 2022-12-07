<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'RePackEndCap'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'RePackEndCap'}[0] == 0) {
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
			tph.Palletizing_Date, tph.Serial_Number
		FROM
			tbl_palletizing_header tph
				LEFT JOIN
			tbl_palletizing_pre tpp ON tph.Palletizing_Header_ID = tpp.Palletizing_Header_ID
		WHERE
			tph.Created_By_ID = $cBy
				AND tph.Status = 'PENDING'
				AND (tpp.Palletizing_Pre_ID IS NULL
				OR tpp.status = 'PENDING')
		GROUP BY tph.Serial_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);

		$header = jsonRow($re1, true, 0);
		$body = [];

		if (count($header) > 0) {
			$Serial_Number = $header[0]['Serial_Number'];
			$sql = "SELECT 
				BIN_TO_UUID(tph.Palletizing_Header_ID,TRUE) AS Palletizing_Header_ID,
				BIN_TO_UUID(tpp.Palletizing_Pre_ID,TRUE) AS Palletizing_Pre_ID,
				tpp.Serial_ID,
				tpm.Part_No,
				tpm.Part_Name,
				tpm.Type,
				tpp.Qty_Package,
				tcm.Customer_Code
			FROM
				tbl_palletizing_pre tpp
					INNER JOIN
				tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					INNER JOIN
				tbl_part_master tpm ON tpp.Part_ID = tpm.Part_ID
					LEFT JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			WHERE
				tph.Serial_Number = '$Serial_Number'
					AND tpp.status = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}

		$returnData = ['header' => $header, 'body' => $body];

		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 2) {
		$dataParams = array(
			'obj',
			'obj=>Part_No:s:0:1',
			'obj=>Customer_Code:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT
				Customer_Code,
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

			//exit($sql);

			$mysqli->commit();

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID
			FROM
				tbl_part_master tpm
			WHERE 
				Part_No = '$Part_No'
					AND BIN_TO_UUID(Customer_ID,TRUE) = '$Customer_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_ID = $row['Part_ID'];
			}

			$sql = "SELECT 
				Part_Type
			FROM
				tbl_inventory tiv
			WHERE 
				BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 3) {

		$sql = "SELECT 
			tph.Palletizing_Date, tph.Serial_Number
		FROM
			tbl_palletizing_header tph
				LEFT JOIN
			tbl_palletizing_pre tpp ON tph.Palletizing_Header_ID = tpp.Palletizing_Header_ID
		WHERE
			tph.Created_By_ID = $cBy
				AND tph.Status = 'PENDING'
				AND (tpp.Palletizing_Pre_ID IS NULL
				OR tpp.status = 'PENDING')
		GROUP BY tph.Serial_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);


		$header = jsonRow($re1, true, 0);
		$body = [];

		if (count($header) > 0) {

			$Serial_Number = $header[0]['Serial_Number'];
			$sql = "SELECT 
				BIN_TO_UUID(tph.Palletizing_Header_ID,TRUE) AS Palletizing_Header_ID,
				BIN_TO_UUID(tpp.Part_ID,TRUE) AS Part_ID,
				tpm.Part_No,
				SUM(tpp.Qty_Package) AS Qty
			FROM
				tbl_palletizing_pre tpp
					INNER JOIN
				tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					INNER JOIN
				tbl_part_master tpm ON tpp.Part_ID = tpm.Part_ID
			WHERE
				tph.Serial_Number = '$Serial_Number'
					AND tpp.status = 'PENDING'
					GROUP BY tpp.Part_ID;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}
		$returnData = ['header' => $header, 'body' => $body];

		closeDBT($mysqli, 1, $returnData);
	} else if ($type == 4) {

		$sql = "SELECT 
			tph.Palletizing_Date, tph.Serial_Number
		FROM
			tbl_palletizing_header tph
				LEFT JOIN
			tbl_palletizing_pre tpp ON tph.Palletizing_Header_ID = tpp.Palletizing_Header_ID
		WHERE
			tph.Created_By_ID = $cBy
				AND tph.Status = 'PENDING'
				AND (tpp.Palletizing_Pre_ID IS NULL
				OR tpp.status = 'PENDING')
		GROUP BY tph.Serial_Number;";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);


		$header = jsonRow($re1, true, 0);
		$body = [];

		if (count($header) > 0) {
			$Serial_Number = $header[0]['Serial_Number'];
			$sql = "SELECT
				SUM(tpp.Qty_Package) AS Total_Qty
			FROM
				tbl_palletizing_pre tpp
					INNER JOIN
				tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
					INNER JOIN
				tbl_part_master tpm ON tpp.Part_ID = tpm.Part_ID
			WHERE
				tph.Serial_Number = '$Serial_Number'
					AND tpp.status = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$body = jsonRow($re1, true, 0);
		}
		$returnData = ['header' => $header, 'body' => $body];

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

		$dataParams = array(
			'obj',
			'obj=>Part_No:s:0:1',
			'obj=>Customer_Code:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$sql = "SELECT
				Customer_Code,
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

		//exit($sql);

		$mysqli->commit();

		$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID
			FROM
				tbl_part_master tpm
			WHERE 
				Part_No = '$Part_No'
					AND BIN_TO_UUID(Customer_ID,TRUE) = '$Customer_ID';";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		if ($re1->num_rows == 0) {
			throw new Exception('ไม่พบข้อมูล' . __LINE__);
		}
		while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
			$Part_ID = $row['Part_ID'];
		}

		$sql = "SELECT 
			SUM(Qty) AS On_hand
			FROM
				getdata
			WHERE
				BIN_TO_UUID(Part_ID, TRUE) = '$Part_ID';";
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'RePackEndCap'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
		$dataParams = array(
			'obj',
			'obj=>Palletizing_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			// สร้าง Serial_ID Rack
			$Serial_Number = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('Rack',0) Serial_Number", 1))->fetch_array(MYSQLI_ASSOC)['Serial_Number'];


			$sql = "SELECT 
				Serial_Number
			FROM
				tbl_palletizing_header
			WHERE
				Serial_Number = '$Serial_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี Serial_ID นี้แล้ว' . __LINE__);
			}

			// เพิ่ม tbl_palletizing_header
			$sql = "INSERT INTO tbl_palletizing_header (
				Palletizing_Date,
				Serial_Number,
				Creation_DateTime,
				Created_By_ID,
				Last_Updated_DateTime,
				Updated_By_ID)
			values('$Palletizing_Date', '$Serial_Number', now(), $cBy, now(), $cBy)";
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
			'obj=>Palletizing_Date:s:0:1',
			'obj=>Serial_Number:s:0:1',
			'obj=>Serial_ID:s:0:1',
			// 'obj=>Customer_Code:s:0:1',
			// 'obj=>Qty:i:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

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
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID
			FROM
				tbl_palletizing_pre
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID'
					AND Serial_ID = '$Serial_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มีการเพิ่ม Package Number นี้แล้ว');
			}


			$sql = "SELECT 
				Serial_ID
			FROM
				tbl_inventory
			WHERE 
				Serial_ID = '$Serial_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}

			$sql = "SELECT 
				Serial_ID
			FROM
				tbl_inventory
			WHERE 
				Serial_ID = '$Serial_ID'
					AND Qty > Used_Qty;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('Package Number นี้ Repack ครบแล้ว' . __LINE__);
			}


			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID,
				Creation_DateTime
			FROM
				tbl_inventory
			WHERE 
				Serial_ID = '$Serial_ID'
					AND Qty > Used_Qty;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_ID = $row['Part_ID'];
				$Creation_DateTime = $row['Creation_DateTime'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
			FROM
				tbl_part_master tpm
			WHERE 
				BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Customer_ID = $row['Customer_ID'];
			}


			$sql = "SELECT
				Customer_Code
			FROM
				tbl_customer_master
			WHERE
				BIN_TO_UUID(Customer_ID,TRUE) = '$Customer_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Customer_Code = $row['Customer_Code'];
			}



			$sql = "SELECT 
				BIN_TO_UUID(tiv.Part_ID, TRUE) AS Part_ID,
				tiv.Creation_DateTime,
				Package_Type
			FROM
				tbl_inventory tiv
					INNER JOIN
				tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
			WHERE
				BIN_TO_UUID(tiv.Part_ID, TRUE) = '$Part_ID'
					AND tiv.Qty > tiv.Used_Qty
					AND Package_Type = 'Box'
					AND (tiv.Creation_DateTime < '$Creation_DateTime' OR tiv.Serial_ID < '$Serial_ID');";
			//exit($sql);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('กรุณาเลือก Package Number ที่เก่ากว่าก่อน');
			}


			$sql = "SELECT 
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID
			FROM
				tbl_palletizing_pre
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Fix_Part_ID = $row['Part_ID'];
				}

				$sql = "SELECT 
					BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
				FROM
					tbl_part_master tpm
				WHERE 
					BIN_TO_UUID(Part_ID,TRUE) = '$Fix_Part_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Fix_Customer_ID = $row['Customer_ID'];
				}


				$sql = "SELECT
					Customer_Code
				FROM
					tbl_customer_master
				WHERE
					BIN_TO_UUID(Customer_ID,TRUE) = '$Fix_Customer_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Fix_Customer_Code = $row['Customer_Code'];
				}

				if ($Customer_Code != $Fix_Customer_Code) {
					throw new Exception('Customer ไม่ตรงกัน');
				}
			}


			$sql = "INSERT INTO tbl_palletizing_pre(
			Palletizing_Header_ID,
			Receiving_Header_ID,
			Serial_ID,
			Part_ID,
			Part_No,
			Qty_Package,
			Area,
			Location_ID,
			Creation_DateTime,
			Created_By_ID )
			SELECT 
				uuid_to_bin('$Palletizing_Header_ID',true),
				tiv.Receiving_Header_ID,
				tiv.Serial_ID,
				tiv.Part_ID,
				trp.Part_No,
				tiv.Qty-tiv.Used_Qty,
				tiv.Area,
				tiv.Location_ID,
				NOW(),
				$cBy
			FROM
				tbl_inventory tiv
					INNER JOIN
				tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = trp.Receiving_Pre_ID
			WHERE 
				tiv.Serial_ID = '$Serial_ID';";
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
							AND Serial_ID = '$Serial_ID'
							AND BIN_TO_UUID(Part_ID, TRUE) = '$Part_ID'
				) AS tpp
			SET
				tiv.Used_Qty = tiv.Used_Qty+tpp.Qty_Package,
				Status_Working = 'FG',
				tiv.Last_Updated_DateTime = NOW(),
				tiv.Updated_By_ID = $cBy
			WHERE
				tiv.Serial_ID = tpp.Serial_ID
					AND tiv.Part_ID = tpp.Part_ID
					AND tiv.Used_Qty+tpp.Qty_Package <= tiv.Qty;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
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
	if ($_SESSION['xxxRole']->{'RePackEndCap'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$Part_ID  = $explode[0];
		$Palletizing_Header_ID  = $explode[1];
		$Qty  = $explode[2];
		//exit($Palletizing_Pre_ID);

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				SUM(Qty_Package) AS Qty_Package
			FROM
				tbl_palletizing_pre
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID'
					AND BIN_TO_UUID(Part_ID, TRUE) = '$Part_ID'
						ORDER BY Creation_DateTime DESC LIMIT 1;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Qty_Package = $row['Qty_Package'];
			}


			$sql = "SELECT 
				BIN_TO_UUID(Palletizing_Pre_ID,TRUE) AS Palletizing_Pre_ID,
				Serial_ID
			FROM
				tbl_palletizing_pre
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID'
					AND BIN_TO_UUID(Part_ID, TRUE) = '$Part_ID'
						ORDER BY Palletizing_Pre_ID DESC LIMIT 1;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Palletizing_Pre_ID = $row['Palletizing_Pre_ID'];
				$Serial_ID = $row['Serial_ID'];
			}

			// echo $Qty_Package . ' / ' . $Qty;
			// exit();

			if ($Qty_Package < $Qty) {
				$Qty = $Qty - $Qty_Package;
				// echo  $Qty;
				// exit('n');

				if ($Qty != 1) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}

				$sql = "UPDATE tbl_palletizing_pre 
				SET 
					Qty_Package = Qty_Package+$Qty,
					Creation_DateTime = NOW()
				WHERE
					BIN_TO_UUID(Palletizing_Header_ID,TRUE) = '$Palletizing_Header_ID'
						AND BIN_TO_UUID(Palletizing_Pre_ID, TRUE) = '$Palletizing_Pre_ID'
						AND BIN_TO_UUID(Part_ID, TRUE) = '$Part_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}


				$sql = "UPDATE
					tbl_inventory
				SET
					Used_Qty = Used_Qty+$Qty,
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = $cBy
				WHERE
					Serial_ID = '$Serial_ID'
						AND Used_Qty+$Qty <= Qty;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}
			} else if ($Qty_Package > $Qty) {

				$Qty = $Qty_Package - $Qty;
				// echo  $Qty;
				// exit('s');

				if ($Qty != 1) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}

				$sql = "UPDATE tbl_palletizing_pre 
				SET 
					Qty_Package = Qty_Package-$Qty,
					Creation_DateTime = NOW()
				WHERE
					BIN_TO_UUID(Palletizing_Header_ID,TRUE) = '$Palletizing_Header_ID'
						AND BIN_TO_UUID(Palletizing_Pre_ID, TRUE) = '$Palletizing_Pre_ID'
						AND BIN_TO_UUID(Part_ID, TRUE) = '$Part_ID'
						AND Qty_Package-$Qty > 0;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}


				$sql = "UPDATE
					tbl_inventory
				SET
					Used_Qty = Used_Qty-$Qty,
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = $cBy
				WHERE
					Serial_ID = '$Serial_ID'
						AND Used_Qty-$Qty > 0;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
				}
			} else {
				throw new Exception('ไม่สามารถแก้ไขได้' . __LINE__);
			}

			//exit('s');



			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}

		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'RePackEndCap'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$Palletizing_Pre_ID  = $explode[0];
		//exit($Palletizing_Pre_ID);

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Serial_ID,
				Qty_Package
			FROM
				tbl_palletizing_pre
			WHERE
				Palletizing_Pre_ID = UUID_TO_BIN('$Palletizing_Pre_ID',TRUE);";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Serial_ID = $row['Serial_ID'];
				$Qty_Package = $row['Qty_Package'];
			}

			// echo($Qty_Package);
			// exit();

			$sql = "UPDATE
				tbl_inventory
			SET
				Used_Qty = Used_Qty-$Qty_Package,
				Status_Working = 'Wait Re-pack',
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				Serial_ID = '$Serial_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้' . __LINE__);
			}


			$sql = "DELETE FROM 
				tbl_palletizing_pre
			WHERE
				BIN_TO_UUID(Palletizing_Pre_ID, TRUE) = '$Palletizing_Pre_ID';";
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
	if ($_SESSION['xxxRole']->{'RePackEndCap'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {


		$dataParams = array(
			'obj',
			'obj=>Serial_Number:s:0:1',
			'obj=>Palletizing_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$sql = "SELECT 
				BIN_TO_UUID(tph.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
				BIN_TO_UUID(tpp.Part_ID, TRUE) AS Part_ID,
				Part_No,
				SUM(Qty_Package) AS Qty
			FROM
				tbl_palletizing_pre tpp
					INNER JOIN
				tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
			WHERE
				Serial_Number = '$Serial_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Qty = $row['Qty'];
				$Part_No = $row['Part_No'];
				$Part_ID = $row['Part_ID'];
				$Palletizing_Header_ID = $row['Palletizing_Header_ID'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID,
				SNP_Per_Rack
			FROM
				tbl_part_master tpm
			WHERE 
				Part_No = '$Part_No';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$SNP_Per_Rack = $row['SNP_Per_Rack'];
			}


			$sql = "UPDATE tbl_palletizing_pre 
			SET
				status = 'COMPLETE'
			WHERE
				BIN_TO_UUID(Palletizing_Header_ID, TRUE) = '$Palletizing_Header_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "UPDATE tbl_palletizing_header 
			SET
				Total_Qty = $Qty,
				Status = 'COMPLETE',
				Confirm_DateTime = NOW(),
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = $cBy
			WHERE
				Serial_Number = '$Serial_Number'
				AND $Qty <= $SNP_Per_Rack*2;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('เกินจำนวนต่อ 1 Rack ' . __LINE__);
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
				'RE-PACK',
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
				tbl_inventory tiv ON tiv.Serial_ID = tpp.Serial_ID AND (tiv.WorkOrder = tpp.WorkOrder OR tiv.WorkOrder IS NULL)
			WHERE
				tph.Serial_Number = '$Serial_Number';";
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
