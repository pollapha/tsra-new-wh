<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'Receive'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'Receive'}[0] == 0) {
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

include('../common/common.php');
include('../php/connection.php');
if ($type <= 10) //data
{
	if ($type == 1) {
	} else if ($type == 4) {

		$sql = "SELECT 
			trh.GRN_Number, trh.Receive_Date, trh.DN_Number, trp.Package_Type
		FROM
			tbl_receiving_header trh
				LEFT JOIN
			tbl_receiving_pre trp ON trh.Receiving_Header_ID = trp.Receiving_Header_ID
		WHERE
			trh.Created_By_ID = $cBy
				AND trh.Status_Receiving = 'PENDING'
				AND (trp.Receiving_Pre_ID IS NULL OR trp.status = 'PENDING')
		GROUP BY trh.GRN_Number;";

		$re1 = sqlError($mysqli, __LINE__, $sql, 1);

		$header = jsonRow($re1, true, 0);

		$body = [];

		if (count($header) > 0) {
			$GRN_Number = $header[0]['GRN_Number'];
			$sql = "SELECT 
				trh.GRN_Number,
				trh.DN_Number,
				BIN_TO_UUID(trp.Receiving_Pre_ID,TRUE) AS Receiving_Pre_ID,
				trp.Serial_ID,
				trp.WorkOrder,
				trp.Part_No,
				tpm.Part_Name,
				trp.Model,
				trp.Part_Type,
				tpm.Type,
				trp.Package_Type,
				trp.Qty_Package,
				trp.Pick,
				tcm.Customer_Code,
    			ROW_NUMBER() OVER (ORDER BY Receiving_Pre_ID) AS row_count
			FROM
				tbl_receiving_pre trp
					INNER JOIN
				tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
					INNER JOIN
				tbl_part_master tpm ON trp.Part_ID = tpm.Part_ID
					LEFT JOIN
				tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
			WHERE
				trh.GRN_Number = '$GRN_Number'
					AND trp.status = 'PENDING'
					ORDER BY Serial_ID, WorkOrder ASC;";
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
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$mysqli->commit();

			$sql = "SELECT 
				Part_Type
			FROM
				tbl_part_master tpm
			WHERE 
				Part_No = '$Part_No';";
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
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'Receive'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
		$dataParams = array(
			'obj',
			'obj=>DN_Number:s:0:1',
			//'obj=>Receive_Date:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			// สร้างเลขที่เอกสาร
			$GRN_Number = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('grn',0) GRN_Number", 1))->fetch_array(MYSQLI_ASSOC)['GRN_Number'];


			$sql = "SELECT 
				GRN_Number
			FROM
				tbl_receiving_header
			WHERE
				GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี GRN นี้แล้ว' . __LINE__);
			}

			$sql = "SELECT 
				DN_Number
			FROM
				tbl_receiving_header
			WHERE
				GRN_Number = '$GRN_Number'
				AND DN_Number = '$DN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('มี DN นี้แล้ว' . __LINE__);
			}

			// เพิ่ม tbl_receiving_header
			$sql = "INSERT INTO tbl_receiving_header (
				GRN_Number,
				DN_Number, 
				Receive_Date,
				Creation_DateTime,
				Created_By_ID,
				Last_Updated_DateTime,
				Updated_By_ID)
			values('$GRN_Number','$DN_Number', now(), now(), $cBy, now(), $cBy)";
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
			'obj=>DN_Number:s:0:1',
			'obj=>GRN_Number:s:0:1',
			'obj=>Package_Type:s:0:1',
			'obj=>Part_No:s:0:1',
			'obj=>Customer_Code:s:0:0',
			'obj=>Qty:i:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Receiving_Header_ID, TRUE) AS Receiving_Header_ID
			FROM
				tbl_receiving_header
			WHERE
				GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			$Receiving_Header_ID = $re1->fetch_array(MYSQLI_ASSOC)['Receiving_Header_ID'];


			$sql = "SELECT 
				Part_Type
			FROM
				tbl_part_master tpm
			WHERE 
				Part_No = '$Part_No';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_Type = $row['Part_Type'];
			}
			if ($Part_Type == 'Finish good') {
				$sql = "SELECT 
					BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
				FROM
					tbl_customer_master
				WHERE 
					Customer_Code = '$Customer_Code';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบ Customer');
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Customer_ID = $row['Customer_ID'];
				}

				$sql = "SELECT 
					BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
				FROM
					tbl_part_master
				WHERE 
				Part_No = '$Part_No' 
						AND Customer_ID = UUID_TO_BIN('$Customer_ID',TRUE);";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบ Customer ใน Part นี้');
				}

				$sql = "SELECT 
					BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
					Part_No,
					Model,
					Part_Type,
					SNP_Per_Rack,
					SNP_Per_Box,
					Weight_Part,
					tpm.Status,
					DATE_FORMAT(tpm.Creation_Date, '%d/%m/%y') AS Creation_Date
				FROM
					tbl_part_master tpm
				WHERE 
					Part_No = '$Part_No' 
						AND Customer_ID = UUID_TO_BIN('$Customer_ID',TRUE);";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Part_ID = $row['Part_ID'];
					$Model = $row['Model'];
					$Part_Type = $row['Part_Type'];
					$SNP_Per_Rack = $row['SNP_Per_Rack'];
					$SNP_Per_Box = $row['SNP_Per_Box'];
				}
			} else {
				$sql = "SELECT 
					BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
					Part_No,
					Model,
					Part_Type,
					SNP_Per_Rack,
					SNP_Per_Box,
					Weight_Part,
					tpm.Status,
					DATE_FORMAT(tpm.Creation_Date, '%d/%m/%y') AS Creation_Date
				FROM
					tbl_part_master tpm
				WHERE 
					Part_No = '$Part_No';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Part_ID = $row['Part_ID'];
					$Model = $row['Model'];
					$Part_Type = $row['Part_Type'];
					$SNP_Per_Rack = $row['SNP_Per_Rack'];
					$SNP_Per_Box = $row['SNP_Per_Box'];
				}
			}


			//Finish good
			if ($Part_Type == 'Finish good') {
				if ($Package_Type == 'Box') {
					$Qty_Box = floor($Qty / $SNP_Per_Box); //แบ่ง Box (1 Box ใส่ได้ 2 ชิ้น)
					$Qty_Box_Scrap = fmod($Qty, $SNP_Per_Box); // เศษ
					$Qty_Package = $Qty_Box;
					if ($Qty_Box_Scrap != 0) {
						$Qty_Package = $Qty_Package + 1;
					}

					if ($Qty < $SNP_Per_Box) {
						for ($i = 1, $len = 1; $i <= $len; $i++) {

							// สร้าง Serial_ID Box
							$Serial_ID = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('box',0) Serial_ID", 1))->fetch_array(MYSQLI_ASSOC)['Serial_ID'];

							$sql = "INSERT INTO tbl_receiving_pre(
								Receiving_Header_ID,
								Part_ID,
								Part_No,
								Serial_ID,
								Model,
								Part_Type,
								Qty_Package,
								Package_Type,
								Creation_DateTime,
								Created_By_ID )
								VALUES (
								uuid_to_bin('$Receiving_Header_ID',true),
								uuid_to_bin('$Part_ID',true),
								'$Part_No',
								'$Serial_ID',
								'$Model',
								'$Part_Type',
								$Qty,
								'$Package_Type',
								NOW(),
								$cBy );";
							sqlError($mysqli, __LINE__, $sql, 1);
							if ($mysqli->affected_rows == 0) {
								throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
							}
						}
					} else {
						for ($i = 1, $len = $Qty_Package; $i <= $len; $i++) {

							// สร้าง Serial_ID Rack
							$Serial_ID = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('box',0) Serial_ID", 1))->fetch_array(MYSQLI_ASSOC)['Serial_ID'];

							$sql = "INSERT INTO tbl_receiving_pre(
								Receiving_Header_ID,
								Part_ID,
								Part_No,
								Serial_ID,
								Model,
								Part_Type,
								Qty_Package,
								Package_Type,
								Creation_DateTime,
								Created_By_ID )
								VALUES (
								uuid_to_bin('$Receiving_Header_ID',true),
								uuid_to_bin('$Part_ID',true),
								'$Part_No',
								'$Serial_ID',
								'$Model',
								'$Part_Type',
								$SNP_Per_Box,
								'$Package_Type',
								NOW(),
								$cBy );";
							sqlError($mysqli, __LINE__, $sql, 1);
							if ($mysqli->affected_rows == 0) {
								throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
							}
						}

						if ($Qty_Box_Scrap != 0) {
							$sql = "UPDATE tbl_receiving_pre 
							SET 
								Qty_Package = $Qty_Box_Scrap
							WHERE
								BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
								ORDER BY Receiving_Pre_ID DESC LIMIT 1;";
							sqlError($mysqli, __LINE__, $sql, 1);
							if ($mysqli->affected_rows == 0) {
								throw new Exception('ไม่สามารถแก้ไขข้อมูลได้');
							}
						}
					}
				} else if ($Package_Type == 'Rack') {
					$SNP_Per_Rack = $SNP_Per_Rack * 2;
					$Qty_Rack = floor($Qty / ($SNP_Per_Rack));
					$Qty_Rack_Scrap = fmod($Qty, $SNP_Per_Rack);
					$Qty_Package = $Qty_Rack;
					if ($Qty_Rack_Scrap != 0) {
						$Qty_Package = $Qty_Package + 1;
					}

					if ($Qty < $SNP_Per_Rack) {
						for ($i = 1, $len = 1; $i <= $len; $i++) {

							$sql = "INSERT INTO tbl_receiving_pre(
									Receiving_Header_ID,
									Part_ID,
									Part_No,
									Model,
									Part_Type,
									Qty_Package,
									Package_Type,
									Creation_DateTime,
									Created_By_ID )
									VALUES (
									uuid_to_bin('$Receiving_Header_ID',true),
									uuid_to_bin('$Part_ID',true),
									'$Part_No',
									'$Model',
									'$Part_Type',
									$Qty,
									'$Package_Type',
									NOW(),
									$cBy );";
							sqlError($mysqli, __LINE__, $sql, 1);
							if ($mysqli->affected_rows == 0) {
								throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
							}
						}
					} else {
						for ($i = 1, $len = $Qty_Package; $i <= $len; $i++) {

							// สร้าง Serial_ID Rack
							$Serial_ID = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('rack',0) Serial_ID", 1))->fetch_array(MYSQLI_ASSOC)['Serial_ID'];

							$sql = "INSERT INTO tbl_receiving_pre(
								Receiving_Header_ID,
								Part_ID,
								Part_No,
								Serial_ID,
								Model,
								Part_Type,
								Qty_Package,
								Package_Type,
								Creation_DateTime,
								Created_By_ID )
								VALUES (
								uuid_to_bin('$Receiving_Header_ID',true),
								uuid_to_bin('$Part_ID',true),
								'$Part_No',
								'$Serial_ID',
								'$Model',
								'$Part_Type',
								$SNP_Per_Rack,
								'$Package_Type',
								NOW(),
								$cBy );";
							sqlError($mysqli, __LINE__, $sql, 1);
							if ($mysqli->affected_rows == 0) {
								throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
							}
						}

						if ($Qty_Rack_Scrap != 0) {
							$sql = "UPDATE tbl_receiving_pre 
							SET 
								Serial_ID = null,
								Qty_Package = $Qty_Rack_Scrap
							WHERE
								BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
								ORDER BY Receiving_Pre_ID DESC LIMIT 1;";
							sqlError($mysqli, __LINE__, $sql, 1);
							if ($mysqli->affected_rows == 0) {
								throw new Exception('ไม่สามารถแก้ไขข้อมูลได้');
							}
						}
					}
				}
			}

			//Assembly part
			else if ($Part_Type == 'Assembly part') {
				$Qty_Rack = floor($Qty / $SNP_Per_Rack); //แบ่ง Box (1 Box ใส่ได้ 2 ชิ้น)
				$Qty_Rack_Scrap = fmod($Qty, $SNP_Per_Rack); // เศษ
				$Qty_Package = $Qty_Rack;
				if ($Qty_Rack_Scrap != 0) {
					$Qty_Package = $Qty_Package + 1;
				}

				if ($Qty < $SNP_Per_Rack) {
					for ($i = 1, $len = 1; $i <= $len; $i++) {

						// สร้าง WorkOrder
						$WorkOrder = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('workorder',0) WorkOrder", 1))->fetch_array(MYSQLI_ASSOC)['WorkOrder'];

						$sql = "INSERT INTO tbl_receiving_pre(
								Receiving_Header_ID,
								Part_ID,
								Part_No,
								WorkOrder,
								Model,
								Part_Type,
								Qty_Package,
								Package_Type,
								Creation_DateTime,
								Created_By_ID )
								VALUES (
								uuid_to_bin('$Receiving_Header_ID',true),
								uuid_to_bin('$Part_ID',true),
								'$Part_No',
								'$WorkOrder',
								'$Model',
								'$Part_Type',
								$Qty,
								'$Package_Type',
								NOW(),
								$cBy );";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
						}
					}
				} else {
					for ($i = 1, $len = $Qty_Package; $i <= $len; $i++) {

						// สร้าง Serial_ID Rack or Box
						$Serial_ID = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('rack',0) Serial_ID", 1))->fetch_array(MYSQLI_ASSOC)['Serial_ID'];
						// สร้าง WorkOrder
						$WorkOrder = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('workorder',0) WorkOrder", 1))->fetch_array(MYSQLI_ASSOC)['WorkOrder'];

						$sql = "INSERT INTO tbl_receiving_pre(
							Receiving_Header_ID,
							Part_ID,
							Part_No,
							Serial_ID,
							WorkOrder,
							Model,
							Part_Type,
							Qty_Package,
							Package_Type,
							Creation_DateTime,
							Created_By_ID )
							VALUES (
							uuid_to_bin('$Receiving_Header_ID',true),
							uuid_to_bin('$Part_ID',true),
							'$Part_No',
							'$Serial_ID',
							'$WorkOrder',
							'$Model',
							'$Part_Type',
							$SNP_Per_Rack,
							'$Package_Type',
							NOW(),
							$cBy );";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
						}
					}

					if ($Qty_Rack_Scrap != 0) {
						$sql = "UPDATE tbl_receiving_pre 
						SET 
							Serial_ID = null,
							Qty_Package = $Qty_Rack_Scrap
						WHERE
							BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
							ORDER BY Receiving_Pre_ID DESC LIMIT 1;";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถแก้ไขข้อมูลได้');
						}
					}
				}
			}

			//Sub material
			else if ($Part_Type == 'Sub material') {

				// สร้าง Serial_ID
				$Serial_ID = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('sn',0) Serial_ID", 1))->fetch_array(MYSQLI_ASSOC)['Serial_ID'];

				$sql = "INSERT INTO tbl_receiving_pre(
					Receiving_Header_ID,
					Part_ID,
					Part_No,
					Serial_ID,
					Part_Type,
					Qty_Package,
					Package_Type,
					Creation_DateTime,
					Created_By_ID )
					VALUES (
					uuid_to_bin('$Receiving_Header_ID',true),
					uuid_to_bin('$Part_ID',true),
					'$Part_No',
					'$Serial_ID',
					'$Part_Type',
					$Qty,
					'Bag',
					NOW(),
					$cBy );";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
				}
			}

			//exit('success');

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 13) {

		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$Receiving_Pre_ID  = $explode[0];
		$state  = $explode[1];

		// echo($Receiving_Pre_ID);
		// exit();

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(Receiving_Header_ID, TRUE) AS Receiving_Header_ID
			FROM
				tbl_receiving_pre
			WHERE
				BIN_TO_UUID(Receiving_Pre_ID,TRUE) = '$Receiving_Pre_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
			}

			if ($state == 'on') {

				$sql = "SELECT 
					Serial_ID
				FROM
					tbl_receiving_pre
				WHERE
					BIN_TO_UUID(Receiving_Pre_ID,TRUE) = '$Receiving_Pre_ID'
						AND Serial_ID IS NOT NULL;";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {
					throw new Exception('รายการนี้มี Package Number อยู่แล้ว ');
				}
				$sql = "UPDATE tbl_receiving_pre
					SET
						Pick = 'Y',
						Created_By_ID = $cBy
					WHERE
						BIN_TO_UUID(Receiving_Pre_ID,TRUE) = '$Receiving_Pre_ID'
							AND Serial_ID IS NULL;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}

				$sql = "SELECT
					BIN_TO_UUID(Part_ID,TRUE) AS Part_ID,
					SUM(Qty_Package) AS Qty_Package
				FROM
					tbl_receiving_pre
				WHERE
					BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
						AND Pick = 'Y';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Qty_Package = $row['Qty_Package'];
					$Part_ID = $row['Part_ID'];
				}

				$sql = "SELECT 
					SNP_Per_Rack,
					Part_Type
				FROM
					tbl_part_master
				WHERE 
					BIN_TO_UUID(Part_ID,TRUE) = '$Part_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล');
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$SNP_Per_Rack = $row['SNP_Per_Rack'];
					$Part_Type = $row['Part_Type'];
				}

				if ($Part_Type == 'Finish good') {
					$SNP_Per_Rack = $SNP_Per_Rack * 2;

					if ($Qty_Package > $SNP_Per_Rack) {
						throw new Exception('เกินจำนวน SNP/Rack ');
					}
				} else {
					if ($Qty_Package > $SNP_Per_Rack) {
						throw new Exception('เกินจำนวน SNP/Rack ');
					}
				}
			} else {
				$sql = "UPDATE tbl_receiving_pre
					SET
						Pick = '',
						Created_By_ID = $cBy
					WHERE
						BIN_TO_UUID(Receiving_Pre_ID,TRUE) = '$Receiving_Pre_ID';";
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
	}
	if ($type == 14) {
		$dataParams = array(
			'obj',
			'obj=>GRN_Number:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(trh.Receiving_Header_ID, TRUE) AS Receiving_Header_ID
			FROM
				tbl_receiving_pre trp
					INNER JOIN
				tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			WHERE
				GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
			}

			// สร้าง Serial_ID Rack
			$Serial_ID = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('rack',0) Serial_ID", 1))->fetch_array(MYSQLI_ASSOC)['Serial_ID'];

			$sql = "SELECT 
				Serial_ID
			FROM
				tbl_receiving_pre
			WHERE
				BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
					AND Pick = 'Y';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('กรุณาเลือกรายการที่ต้องการสร้าง Package Number ');
			}

			$sql = "UPDATE tbl_receiving_pre 
			SET 
				Serial_ID = '$Serial_ID',
				Pick = '',
				Created_By_ID = $cBy
			WHERE
				BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
					AND Pick = 'Y'
					AND Created_By_ID = $cBy
					AND Serial_ID IS NULL;";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('มี Package Number อยู่แล้ว');
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
	if ($_SESSION['xxxRole']->{'Receive'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'Receive'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {


		$obj  = $_POST['obj'];
		$explode = explode("/", $obj);
		$GRN_Number  = $explode[0];
		$Receiving_Pre_ID  = $explode[1];
		//exit($Receiving_Pre_ID);

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				BIN_TO_UUID(trh.Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
				Part_No,
				Serial_ID,
				WorkOrder,
				trp.Qty_Package
			FROM
				tbl_receiving_pre trp
					INNER JOIN
				tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			WHERE
				GRN_Number = '$GRN_Number'
					AND BIN_TO_UUID(Receiving_Pre_ID, TRUE) = '$Receiving_Pre_ID';";
			//exit($sql);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
			}

			// exit($Serial_ID );

			$sql = "DELETE FROM tbl_receiving_pre
			WHERE
				BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID'
					AND BIN_TO_UUID(Receiving_Pre_ID, TRUE) = '$Receiving_Pre_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถลบได้' . __LINE__);
			}

			$mysqli->commit();
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}

		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'Receive'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {

		$dataParams = array(
			'obj',
			'obj=>GRN_Number:s:0:1'
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$sql = "SELECT 
				BIN_TO_UUID(trh.Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
				BIN_TO_UUID(Part_ID,TRUE) AS Part_ID,
				Part_No,
				SUM(Qty_Package) AS Qty,
				Package_Type
			FROM
				tbl_receiving_pre trp
					INNER JOIN
				tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			WHERE
				GRN_Number = '$GRN_Number';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Qty = $row['Qty'];
				$Part_No = $row['Part_No'];
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
				$Package_Type = $row['Package_Type'];
				$Part_ID = $row['Part_ID'];
			}


			$sql = "SELECT 
				Serial_ID
			FROM
				tbl_receiving_pre
			WHERE
				BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
					AND Serial_ID IS NULL;";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				throw new Exception('กรุณา Create Package Number');
			}

			$sql = "SELECT 
				Part_Type,
				BIN_TO_UUID(Customer_ID,TRUE) AS Customer_ID
			FROM
				tbl_part_master
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
				Part_Type,
				SNP_Per_Rack
			FROM
				tbl_part_master tpm
			WHERE Part_No = '$Part_No';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Part_Type = $row['Part_Type'];
				$SNP_Per_Rack = $row['SNP_Per_Rack'];
			}

			if ($Part_Type == 'Finish good' || $Part_Type == 'Sub material') {
				$sql = "UPDATE tbl_receiving_header 
					SET
						Total_Qty = $Qty,
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = $cBy
					WHERE
						GRN_Number = '$GRN_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}

				$sql = "SELECT 
						BIN_TO_UUID(Location_ID, TRUE) AS Location_ID,
						Area
					FROM
						tbl_location_master
					WHERE
						Area = 'FG';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Area = $row['Area'];
					$Location_ID = $row['Location_ID'];
				}


				//อัพเดท Area ใน tbl_receiving_pre
				$sql = "UPDATE tbl_receiving_pre
					SET 
						Area = '$Area'
					WHERE
						BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}

				if ($Package_Type == 'Box') {
					//exit('1');
					if ($Customer_Code != 'TSPT4') {

						$sql = "SELECT 
							Serial_Number
						FROM
							tbl_receiving_header
						WHERE
							GRN_Number = '$GRN_Number'
								AND Serial_Number IS NULL;";
						$re1 = sqlError($mysqli, __LINE__, $sql, 1);
						if ($re1->num_rows > 0) {
							//exit('1');
							// สร้าง Serial_ID Rack or Box
							$Serial_Number = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('rack',0) Serial_Number", 1))->fetch_array(MYSQLI_ASSOC)['Serial_Number'];

							$sql = "UPDATE tbl_receiving_header 
							SET
								Serial_Number = '$Serial_Number',
								Last_Updated_DateTime = NOW(),
								Updated_By_ID = $cBy
							WHERE
								GRN_Number = '$GRN_Number';";
							sqlError($mysqli, __LINE__, $sql, 1);
							if ($mysqli->affected_rows == 0) {
								throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
							}
						}

						$sql = "SELECT 
							GRN_Number,
							Total_Qty
						FROM
							tbl_receiving_header
						WHERE
							GRN_Number = '$GRN_Number'
								AND Total_Qty > $SNP_Per_Rack*2;";
						$re1 = sqlError($mysqli, __LINE__, $sql, 1);
						if ($re1->num_rows > 0) {
							throw new Exception('เกินจำนวนต่อ 1 Rack');
						}

						$sql = "INSERT INTO tbl_inventory
						(Receiving_Header_ID, 
						Receiving_Pre_ID, 
						Part_ID, 
						Serial_ID, 
						WorkOrder, 
						Qty, 
						Part_Type, 
						Area, 
						Location_ID, 
						Status_Working,
						Creation_DateTime,
						Created_By_ID)
						SELECT
							trh.Receiving_Header_ID,
							trp.Receiving_Pre_ID ,
							trp.Part_ID ,
							trp.Serial_ID,
							trp.WorkOrder,
							trp.Qty_Package,
							trp.Part_Type,
							trp.Area,
							UUID_TO_BIN('$Location_ID',TRUE),
							'Wait Re-pack',
							NOW(),
							$cBy
						FROM
							tbl_receiving_header trh
						LEFT JOIN tbl_receiving_pre trp ON
							trh.Receiving_Header_ID = trp.Receiving_Header_ID 
							WHERE trh.GRN_Number = '$GRN_Number'
							ON DUPLICATE KEY UPDATE 
							Last_Updated_DateTime = NOW(),
							Updated_By_ID = '$cBy';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					} else {
						$sql = "INSERT INTO tbl_inventory
						(Receiving_Header_ID, 
						Receiving_Pre_ID, 
						Part_ID, 
						Serial_ID, 
						WorkOrder, 
						Qty, 
						Part_Type, 
						Area, 
						Location_ID, 
						Status_Working,
						Creation_DateTime,
						Created_By_ID)
						SELECT
							trh.Receiving_Header_ID,
							trp.Receiving_Pre_ID ,
							trp.Part_ID ,
							trp.Serial_ID,
							trp.WorkOrder,
							trp.Qty_Package,
							trp.Part_Type,
							trp.Area,
							UUID_TO_BIN('$Location_ID',TRUE),
							'FG',
							NOW(),
							$cBy
						FROM
							tbl_receiving_header trh
						LEFT JOIN tbl_receiving_pre trp ON
							trh.Receiving_Header_ID = trp.Receiving_Header_ID 
							WHERE trh.GRN_Number = '$GRN_Number'
							ON DUPLICATE KEY UPDATE 
							Last_Updated_DateTime = NOW(),
							Updated_By_ID = '$cBy';";
						sqlError($mysqli, __LINE__, $sql, 1);
						if ($mysqli->affected_rows == 0) {
							throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
						}
					}
				} else if ($Package_Type == 'Bag') {

					$sql = "SELECT 
						BIN_TO_UUID(Location_ID, TRUE) AS Location_ID,
						Area
					FROM
						tbl_location_master
					WHERE
						Area = 'Assembly';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows == 0) {
						throw new Exception('ไม่พบข้อมูล' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$Area = $row['Area'];
						$Location_ID = $row['Location_ID'];
					}

					//อัพเดท Area ใน tbl_receiving_pre
					$sql = "UPDATE tbl_receiving_pre
					SET 
						Area = '$Area'
					WHERE
						BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}

					$sql = "INSERT INTO tbl_inventory
					(Receiving_Header_ID, 
					Receiving_Pre_ID, 
					Part_ID, 
					Serial_ID, 
					WorkOrder, 
					Qty, 
					Part_Type, 
					Area, 
					Location_ID, 
					Status_Working,
					Creation_DateTime,
					Created_By_ID)
					SELECT
						trh.Receiving_Header_ID,
						trp.Receiving_Pre_ID ,
						trp.Part_ID ,
						trp.Serial_ID,
						trp.WorkOrder,
						trp.Qty_Package,
						trp.Part_Type,
						trp.Area,
						UUID_TO_BIN('$Location_ID',TRUE),
						'Wait Assembly',
						NOW(),
						$cBy
					FROM
						tbl_receiving_header trh
					LEFT JOIN tbl_receiving_pre trp ON
						trh.Receiving_Header_ID = trp.Receiving_Header_ID 
						WHERE trh.GRN_Number = '$GRN_Number'
						ON DUPLICATE KEY UPDATE 
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = '$cBy';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				} else if ($Package_Type == 'Rack') {

					$sql = "INSERT INTO tbl_inventory
					(Receiving_Header_ID, 
					Receiving_Pre_ID, 
					Part_ID, 
					Serial_ID, 
					WorkOrder, 
					Qty, 
					Part_Type, 
					Area, 
					Location_ID, 
					Status_Working,
					Creation_DateTime,
					Created_By_ID)
					SELECT
						trh.Receiving_Header_ID,
						trp.Receiving_Pre_ID ,
						trp.Part_ID ,
						trp.Serial_ID,
						trp.WorkOrder,
						trp.Qty_Package,
						trp.Part_Type,
						trp.Area,
						UUID_TO_BIN('$Location_ID',TRUE),
						'FG',
						NOW(),
						$cBy
					FROM
						tbl_receiving_header trh
					LEFT JOIN tbl_receiving_pre trp ON
						trh.Receiving_Header_ID = trp.Receiving_Header_ID 
						WHERE trh.GRN_Number = '$GRN_Number'
						ON DUPLICATE KEY UPDATE 
						Last_Updated_DateTime = NOW(),
						Updated_By_ID = '$cBy';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				}
			} else if ($Part_Type == 'Assembly part') {

				// สร้าง Serial_ID Rack or Box
				$Serial_ID = (sqlError($mysqli, __LINE__, "SELECT func_GenRuningNumber('rack',0) Serial_ID", 1))->fetch_array(MYSQLI_ASSOC)['Serial_ID'];

				$sql = "UPDATE tbl_receiving_pre 
				SET 
					Serial_ID = '$Serial_ID'
				WHERE
					BIN_TO_UUID(Receiving_Header_ID,TRUE) = '$Receiving_Header_ID'
						AND Serial_ID IS NULL;";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}

				$sql = "UPDATE tbl_receiving_header
				SET
					Total_Qty = $Qty,
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = $cBy
				WHERE
					GRN_Number = '$GRN_Number';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}


				$sql = "SELECT 
					BIN_TO_UUID(Location_ID, TRUE) AS Location_ID,
					Area
				FROM
					tbl_location_master
				WHERE
					Area = 'Assembly';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows == 0) {
					throw new Exception('ไม่พบข้อมูล' . __LINE__);
				}
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$Area = $row['Area'];
					$Location_ID = $row['Location_ID'];
				}

				//อัพเดท Area ใน tbl_receiving_pre
				$sql = "UPDATE tbl_receiving_pre
				SET 
					Area = '$Area'
				WHERE
					BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					//throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}


				$sql = "INSERT INTO tbl_inventory
				(Receiving_Header_ID, 
				Receiving_Pre_ID, 
				Part_ID, 
				Serial_ID, 
				WorkOrder, 
				Qty, 
				Part_Type, 
				Area, 
				Location_ID, 
				Status_Working,
				Creation_DateTime,
				Created_By_ID)
				SELECT
					trh.Receiving_Header_ID,
					trp.Receiving_Pre_ID ,
					trp.Part_ID ,
					trp.Serial_ID,
					trp.WorkOrder,
					trp.Qty_Package,
					trp.Part_Type,
					trp.Area,
					UUID_TO_BIN('$Location_ID',TRUE),
					'Wait Assembly',
					NOW(),
					$cBy
				FROM
					tbl_receiving_header trh
				LEFT JOIN tbl_receiving_pre trp ON
					trh.Receiving_Header_ID = trp.Receiving_Header_ID 
					WHERE trh.GRN_Number = '$GRN_Number'
					ON DUPLICATE KEY UPDATE 
					Last_Updated_DateTime = NOW(),
					Updated_By_ID = '$cBy';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$sql = "UPDATE tbl_receiving_pre 
			SET
				status = 'COMPLETE'
			WHERE
				BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID'
					AND status = 'PENDING';";

			sqlError($mysqli, __LINE__, $sql, 1);

			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "SELECT 
				BIN_TO_UUID(Receiving_Header_ID, TRUE) AS Receiving_Header_ID,
				DN_Number
			FROM
				tbl_receiving_header
			WHERE
				GRN_Number = '$GRN_Number'
					AND Status_Receiving = 'PENDING';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$Receiving_Header_ID = $row['Receiving_Header_ID'];
				$DN_Number = $row['DN_Number'];
			}

			$sql = "SELECT 
				BIN_TO_UUID(Part_ID, TRUE) AS Part_ID,
				Part_No,
				SUM(Qty_Package) AS Qty_Package,
				SUM(Count) AS Qty_Count
			FROM
				tbl_receiving_pre
			WHERE
				BIN_TO_UUID(Receiving_Header_ID, TRUE) = '$Receiving_Header_ID'
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

			$sql = "UPDATE tbl_receiving_header 
			SET 
				Status_Receiving = 'COMPLETE',
				Confirm_Receive_DateTime = NOW()
			WHERE
				GRN_Number = '$GRN_Number';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$sql = "INSERT INTO tbl_report
			(Receiving_Header_ID, Receiving_Pre_ID, Part_ID, Serial_ID,WorkOrder, IN_Qty, Part_Type, Status, Main_Status, Creation_DateTime, Created_By_ID)
			SELECT
				trh.Receiving_Header_ID,
				trp.Receiving_Pre_ID,
				trp.Part_ID,
				trp.Serial_ID,
				trp.WorkOrder,
				trp.Qty_Package,
				trp.Part_Type,
				'IN',
				'IN',
				NOW(),
				$cBy
			FROM
				tbl_receiving_header trh
			LEFT JOIN tbl_receiving_pre trp ON
				trh.Receiving_Header_ID = trp.Receiving_Header_ID 
				WHERE trh.GRN_Number = '$GRN_Number'
				ON DUPLICATE KEY UPDATE 
				Last_Updated_DateTime = NOW(),
				Updated_By_ID = '$cBy';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}


			$sql = "INSERT INTO
				tbl_transaction(
					Receiving_Header_ID,
				DN_Number,
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
				UUID_TO_BIN('$Receiving_Header_ID',TRUE),
				'$DN_Number',
				tiv.Part_ID,
				tiv.Serial_ID,
				tiv.WorkOrder,
				tiv.Qty,
				tiv.Area,
				tiv.Area,
				'IN',
				ROW_NUMBER() OVER (ORDER BY tiv.Serial_ID),
				now(),
				$cBy,
				tiv.Location_ID,
				tiv.Location_ID,
				now(),
				$cBy
			FROM
				tbl_receiving_header trh
					LEFT JOIN 
					tbl_inventory tiv ON trh.Receiving_Header_ID = tiv.Receiving_Header_ID 
			WHERE
				trh.GRN_Number = '$GRN_Number';";
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
