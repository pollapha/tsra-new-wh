<?php
include '../fpdf.php';
include '../exfpdf.php';
include '../easyTable.php';
include 'PDF_Code128.php';
include('../../php/connection.php');
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$doc = $mysqli->real_escape_string(trim(strtoupper($_REQUEST['data'])));
$dataset = array();
$q1  = "SELECT 
tsh.GTN_Number,
DATE_FORMAT(tsh.Ship_Date, '%d/%m/%y') AS Ship_Date,
DATE_FORMAT(tsh.Ship_Time, '%h:%i:%s') AS Ship_Time,
DATE_FORMAT(pickh.Pick_Date, '%d/%m/%y') AS Pick_Date,
tsh.Trip_Number,
Invoice_Number,
Truck_ID,
Truck_Driver,
Truck_Type,
tcm.Customer_Code AS Customer ,
tsp.TS_Number,
pickh.Serial_Number,
pickp.Serial_ID,
tpm.Part_No AS Part_Number ,
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
tsh.GTN_Number = '$doc'
	AND tsp.status = 'COMPLETE'
	AND (tsh.Status_Shipping = 'COMPLETE'
	OR tsh.Status_Shipping = 'CONFIRM SHIP')
GROUP BY GTN_Number;";


// $q1  .= "WITH a as (
// 	SELECT 
// 		GTN_Number,
// 		tsp.TS_Number,
// 		DATE_FORMAT(Ship_Date, '%Y-%m-%d') AS Ship_Date,
// 		DATE_FORMAT(Confirm_Shipping_DateTime, '%d/%m/%y') AS Confirm_Shipping_DateTime,
// 		DATE_FORMAT(Pick_Date, '%d/%m/%y') AS Pick_Date,
// 		pickp.Serial_ID,
//         pickp.Part_ID,
//         tpm.Part_No,
//         tpm.Mat_SAP1,
//         tpm.Mat_SAP3,
//         tpm.Mat_GI,
// 		tpm.Model,
// 		tsh.Remark,
// 		Status_Shipping,
// 		tsh.Creation_DateTime,
// 		tpm.Part_No AS Part_Number,
// 		tpm.Part_Name,
// 		pickp.Qty_Package AS Qty,
// 		tpm.Type,
// 		pickh.Serial_Number,
// 		Location_Code,
// 		tcm.Customer_Code AS Customer
// 		FROM
// 		tbl_shipping_header tsh
// 			LEFT JOIN
// 		tbl_shipping_pre tsp ON tsh.Shipping_Header_ID = tsp.Shipping_Header_ID
// 			INNER JOIN 
// 		tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
// 			INNER JOIN 
// 		tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
// 			INNER JOIN
// 		tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
// 			INNER JOIN
// 		tbl_inventory tiv ON pickp.Serial_ID = tiv.Serial_ID
// 			LEFT JOIN
// 		tbl_location_master tlm ON tiv.Location_ID = tlm.Location_ID
// 			LEFT JOIN
// 		tbl_customer_master tcm ON tsh.Ship_To = tcm.Customer_ID
// 	WHERE
// 		tsh.GTN_Number = '$doc'
// 	GROUP BY pickh.Serial_Number, pickp.Serial_ID, pickp.Part_ID
// ),
// b as (
// SELECT a.*, 
// 	ifnull(a.Serial_Number, a.Serial_ID) AS Serial_Package, 
// 	SUM(a.Qty) AS Qty_Package
// FROM a
// GROUP BY Serial_Package, a.Part_ID)
// SELECT b.*, SUM(pickp.Qty_Package) AS Total_Qty FROM b
// CROSS JOIN tbl_picking_pre pickp
// WHERE b.Serial_ID = pickp.Serial_ID
// GROUP BY b.Serial_ID, b.Part_ID
// ORDER BY Serial_Package, Part_ID;";

$q1  .= "SELECT 
tsh.GTN_Number,
DATE_FORMAT(tsh.Ship_Date, '%d/%m/%y') AS Ship_Date,
DATE_FORMAT(tsh.Ship_Time, '%h:%i:%s') AS Ship_Time,
DATE_FORMAT(pickh.Pick_Date, '%d/%m/%y') AS Pick_Date,
tsh.Trip_Number,
Invoice_Number,
Truck_ID,
Truck_Driver,
Truck_Type,
tcm.Customer_Code AS Customer ,
tsp.TS_Number,
pickh.Serial_Number,
pickp.Serial_ID,
tpm.Part_No AS Part_Number ,
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
tsh.GTN_Number = '$doc'
	AND tsp.status = 'COMPLETE'
	AND (tsh.Status_Shipping = 'COMPLETE'
	OR tsh.Status_Shipping = 'CONFIRM SHIP')
GROUP BY Serial_Package , tpm.Part_ID
ORDER BY GTN_Number DESC, Serial_Package ASC;";


// $q1  .= "WITH a as (
// 	SELECT 
// 		GTN_Number,
// 		tsp.TS_Number,
// 		DATE_FORMAT(Ship_Date, '%Y-%m-%d') AS Ship_Date,
// 		DATE_FORMAT(Confirm_Shipping_DateTime, '%d/%m/%y') AS Confirm_Shipping_DateTime,
// 		DATE_FORMAT(Pick_Date, '%d/%m/%y') AS Pick_Date,
// 		pickp.Serial_ID,
//         pickp.Part_ID,
//         tpm.Part_No,
//         tpm.Mat_SAP1,
//         tpm.Mat_SAP3,
//         tpm.Mat_GI,
// 		tpm.Model,
// 		tsh.Remark,
// 		Status_Shipping,
// 		tsh.Creation_DateTime,
// 		tpm.Part_No AS Part_Number,
// 		tpm.Part_Name,
// 		pickp.Qty_Package AS Qty,
// 		tpm.Type,
// 		pickh.Serial_Number,
// 		Location_Code,
// 		tcm.Customer_Code AS Customer
// 		FROM
// 		tbl_shipping_header tsh
// 			LEFT JOIN
// 		tbl_shipping_pre tsp ON tsh.Shipping_Header_ID = tsp.Shipping_Header_ID
// 			INNER JOIN 
// 		tbl_picking_header pickh ON tsp.TS_Number = pickh.TS_Number
// 			INNER JOIN 
// 		tbl_picking_pre pickp ON pickh.Picking_Header_ID = pickp.Picking_Header_ID
// 			INNER JOIN
// 		tbl_part_master tpm ON pickp.Part_ID = tpm.Part_ID
// 			INNER JOIN
// 		tbl_inventory tiv ON pickp.Serial_ID = tiv.Serial_ID
// 			LEFT JOIN
// 		tbl_location_master tlm ON tiv.Location_ID = tlm.Location_ID
// 			LEFT JOIN
// 		tbl_customer_master tcm ON tsh.Ship_To = tcm.Customer_ID
// 	WHERE
// 		tsh.GTN_Number = '$doc'
// 	GROUP BY pickh.Serial_Number, pickp.Serial_ID, pickp.Part_ID
// ),
// b as (
// SELECT a.*, 
// 	ifnull(a.Serial_Number, a.Serial_ID) AS Serial_Package, 
// 	SUM(a.Qty) AS Qty_Package
// FROM a
// GROUP BY Serial_Package, a.Part_ID)
// SELECT b.*, SUM(pickp.Qty_Package) AS Total_Qty FROM b
// CROSS JOIN tbl_picking_pre pickp
// WHERE b.Serial_ID = pickp.Serial_ID
// GROUP BY b.Serial_ID, b.Part_ID
// ORDER BY Serial_Package, Part_ID;";



if (!$mysqli->multi_query($q1)) {
	echo "Multi query failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
do {
	if ($res = $mysqli->store_result()) {
		array_push($dataset, $res->fetch_all(MYSQLI_ASSOC));
		$res->free();
	}
} while ($mysqli->more_results() && $mysqli->next_result());
$headerData = $dataset[0];
$detailData = $dataset[1];

class PDF extends PDF_Code128
{
	function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format);
		$this->AliasNbPages();
	}
	public function setHeaderData($v)
	{
		$this->headerData = $v;
	}
	public function setInstance($v)
	{
		$this->instance = $v;
	}
	function Header()
	{
		$v = $this->headerData;
		$header = new easyTable($this->instance, '%{50,50}', 'border:0;font-family:THSarabun;font-size:12; font-style:B;');
		$header->easyCell('ALBATROSS LOGISTICS CO., LTD.', 'valign:T;align:L;font-size:16;');
		$header->easyCell('', 'img:images/abt-logo.gif, w23;align:R;valign:T;paddingY:3;', '');
		$header->printRow();
		$header->endTable(2);


		$header = new easyTable($this->instance, '%{100}', 'border:0;font-family:THSarabun;font-size:16; font-style:B;');
		$header->easyCell(utf8Th('GOOD TRANSFER NOTE'), 'valign:M;align:C;border:T');
		$header->printRow();
		$header->endTable(1);

		$header = new easyTable($this->instance, '%{15,15,15,20,15,20}', 'border:0;font-family:THSarabun;font-size:12;');
		$header->easyCell("Customer: ", 'valign:T;align:R;border:TL;');
		$header->easyCell('TSRA', 'valign:T;align:L;font-style:B;border:T;');
		$header->easyCell("Truck License: ", 'valign:T;align:R;border:T;');
		$header->easyCell(utf8Th($v[0]['Truck_ID']), 'valign:T;align:L;font-style:B;border:T;');
		$header->easyCell("GTN Number: ", 'valign:T;align:R;border:T;');
		$header->easyCell(utf8Th($v[0]['GTN_Number']), 'valign:T;align:L;border:TR;font-style:B');
		$header->printRow();

		$header->easyCell("Transfer from: ", 'valign:T;align:R;border:L;');
		$header->easyCell('TSRA', 'valign:T;align:L;font-style:B;');
		$header->easyCell("Driver Name:", 'valign:T;align:R;');
		$header->easyCell(utf8Th($v[0]['Truck_Driver']), 'valign:T;align:L;font-style:B');
		$header->easyCell("", 'valign:T;align:L;font-style:B;');
		$header->easyCell("", 'valign:T;align:L;border:R;');
		$header->printRow();

		$header->easyCell("Transfer to: ", 'valign:T;align:R;border:LB;');
		$header->easyCell(utf8Th($v[0]['Customer']), 'valign:T;align:L;font-style:B;border:B;');
		//$header->easyCell("Total Package:", 'valign:T;align:R;border:B;');
		//$header->easyCell(utf8Th($v[0]["sumcase"]), 'valign:T;align:L;border:B;font-style:B');
		$header->easyCell("Ship Date:", 'valign:T;align:R;border:B;');
		$header->easyCell(utf8Th($v[0]['Ship_Date']), 'valign:T;align:L;border:B;font-style:B');
		$header->easyCell("Ship Time:", 'valign:T;align:R;border:B;');
		$header->easyCell(utf8Th($v[0]['Ship_Time']), 'valign:T;align:L;border:RB;font-style:B');
		$header->printRow();

		$header->easyCell("", 'valign:T;align:L; paddingY:2;');
		$header->printRow();
		$header->endTable(2);

		$headdetail = new easyTable(
			$this->instance,
			'{10,20,25,45,65,15,20,20,15,20}',
			'width:300;border:1;font-family:THSarabun;font-size:9; font-style:B;bgcolor:#C8C8C8;'
		);
		$headdetail->easyCell(utf8Th('No.'), 'align:C');
		$headdetail->easyCell(utf8Th('Packing Plan'), 'align:C');
		$headdetail->easyCell(utf8Th('Package ID.'), 'align:C');
		$headdetail->easyCell(utf8Th('Part Number'), 'align:C');
		$headdetail->easyCell(utf8Th('Part Name'), 'align:C');
		$headdetail->easyCell(utf8Th('Model'), 'align:C');
		$headdetail->easyCell(utf8Th('Mat 1'), 'align:C');
		$headdetail->easyCell(utf8Th('Mat 3'), 'align:C');
		//$headdetail->easyCell(utf8Th('Ship To'), 'align:C');
		$headdetail->easyCell(utf8Th('Qty.'), 'align:C');
		$headdetail->easyCell(utf8Th('Remarks'), 'align:C');
		$headdetail->printRow();
		$headdetail->endTable(0);
		$this->instance->Code128(142, 50.5, $v[0]['GTN_Number'], 45, 6);
	}
	function Footer()
	{
		$this->SetXY(-22, 26);
		$this->SetFont('THSarabun', '', 10);
		$this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
	}
}

$pdf = new PDF('P');

$pdf->AddFont('THSarabun', '', 'THSarabun.php');
$pdf->AddFont('THSarabun', 'I', 'THSarabun Italic.php');
$pdf->AddFont('THSarabun', 'B', 'THSarabun Bold.php');
$pdf->AddFont('THSarabun', 'BI', 'THSarabun Bold Italic.php');
$pdf->setInstance($pdf);
$pdf->setHeaderData($headerData);
$pdf->AddPage();
$docno = $headerData[0]['GTN_Number'];
$pdf->SetTitle($docno);
$detail = new easyTable($pdf, '{10,20,25,45,65,15,20,20,15,20}', 'width:300;border:1;font-family:THSarabun;valign:M;');
$data = sizeof($detailData);
// หน้าละ15row 
$pagebreak = 15;
$i = 0;
$j = 0;
$countrow = 1;
$nn = 1;
$sumqty = 0;
$sumBoxes = 0;
$sumCBM = 0;


while ($i <  $data) {
	if ($countrow > $pagebreak) {
		$pdf->AddPage();
		$countrow = 1;
	}
	$countrow++;
	$x = $pdf->GetX();
	$y = $pdf->GetY();

	$Package_Type = '';
	$str = $detailData[$i]["Serial_Package"];
	$Package = substr($str, 0, 1);

	// $detailData[$i]["Serial_ID"]
	//echo($str);
	if ($Package == 'R') {
		$Package_Type = 'Rack';
	} else if ($Package == 'B') {
		$Package_Type = 'Box';
	} else {
		$Package_Type = 'Rack';
	}


	$detail->easyCell(utf8Th($nn), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Pick_Date"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Serial_Package"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Part_Number"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Part_Name"]), 'align:L:font-style:B;font-size:9;');
	$detail->easyCell(utf8Th($detailData[$i]["Model"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Mat_SAP1"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Mat_SAP3"]), 'align:C;font-style:B;font-size:10;');
	//$detail->easyCell(utf8Th($detailData[$i]["Customer"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Qty_Package"]), 'align:C;font-style:B;font-size:12;');
	$detail->easyCell(utf8Th(''), 'align:C;font-style:B;font-size:10;');
	$detail->printRow();
	$sumqty += $detailData[$i]['Qty_Package'];
	$i++;
	$nn++;
}

$detail->easyCell(utf8Th('Total :'), 'align:R;font-style:B;colspan:8;font-size:14;border:LTBR;');
$detail->easyCell(utf8Th($sumqty), 'align:C;font-style:B;font-size:14;border:TBR;');
$detail->easyCell(utf8Th(''), 'align:C;border:TBR;');
$detail->easyCell(utf8Th(''), 'align:C;font-size:14;border:TBR;');
$detail->printRow();
$detail->endTable(10);

$lastfooter = new easyTable($pdf, '%{20,25,10,20,25}', 'width:300;border:0;paddingY:3;font-family:THSarabun;font-size:12;');
$lastfooter->easyCell(utf8Th('Delivery By:'), 'align:R;font-size:14;');
$lastfooter->easyCell(utf8Th('_________________________'), 'align:L;font-size:14;');
$lastfooter->easyCell('', 'align:C;font-size:14;');
$lastfooter->easyCell(utf8Th('TSRA/CIC Checked By:'), 'align:R;font-size:14;');
$lastfooter->easyCell(utf8Th('_________________________'), 'align:L;font-size:14;');
$lastfooter->printRow();

$lastfooter->easyCell(utf8Th('Delivery Date:'), 'align:R;font-size:14;');
$lastfooter->easyCell(utf8Th('____/____/____'), 'align:L;font-size:14;');
$lastfooter->easyCell('', 'align:C;font-size:14;');
$lastfooter->easyCell(utf8Th('Checked Date:'), 'align:R;font-size:14;');
$lastfooter->easyCell(utf8Th('____/____/____'), 'align:L;font-size:14;');
$lastfooter->printRow();

$lastfooter->easyCell(utf8Th('Received By (ABT):'), 'align:R;font-size:14;');
$lastfooter->easyCell(utf8Th('_________________________'), 'align:L;font-size:14;');
$lastfooter->printRow();

$lastfooter->easyCell(utf8Th('Received Date:'), 'align:R;font-size:14;');
$lastfooter->easyCell(utf8Th('____/____/____'), 'align:L;font-size:14;');
$lastfooter->printRow();


$lastfooter->endTable(3);

$pdf->Output();

function utf8Th($v)
{
	return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}
