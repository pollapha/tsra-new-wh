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
pickh.TS_Number,
DATE_FORMAT(pickh.Pick_Date, '%d-%m-%y') AS Pick_Date,
IF(tph.Serial_Number IS NULL,
    pickp.Serial_ID,
    tph.Serial_Number) AS Serial_Package,
pickp.PO_Number,
tpm.Part_No AS Part_Number ,
tpm.Part_Name,
tpm.Model,
tpm.Mat_SAP1,
tpm.Mat_SAP3,
tpm.Color,
tpm.Picture,
tpm.Type,
tcm.Customer_Code AS Customer ,
tcm.Customer_Name,
IF(tph.Serial_Number IS NULL,
    pickp.Qty_Package,
    SUM(pickp.Qty_Package)) AS Qty_Package,
DATE_FORMAT(pickh.Confirm_Picking_DateTime,
    '%d/%m/%y %H:%i') AS Confirm_Picking_DateTime,
Location_Code
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
tbl_location_master tlm ON pickp.Location_ID = tlm.Location_ID
WHERE
pickh.TS_Number = '$doc'
GROUP BY pickh.TS_Number;";

$q1  .= "SELECT 
pickh.TS_Number,
DATE_FORMAT(pickh.Pick_Date, '%d-%m-%y') AS Pick_Date,
IF(tph.Serial_Number IS NULL,
    pickp.Serial_ID,
    tph.Serial_Number) AS Serial_Package,
pickp.PO_Number,
tpm.Part_No AS Part_Number ,
tpm.Part_Name,
tpm.Model,
tpm.Mat_SAP1,
tpm.Mat_SAP3,
tpm.Color,
tpm.Picture,
tpm.Type,
tcm.Customer_Code AS Customer,
tcm.Customer_Name,
IF(tph.Serial_Number IS NULL,
    pickp.Qty_Package,
    SUM(pickp.Qty_Package)) AS Qty_Package,
DATE_FORMAT(pickh.Confirm_Picking_DateTime,
    '%d/%m/%y %H:%i') AS Confirm_Picking_DateTime,
		Location_Code
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
tbl_location_master tlm ON pickp.Location_ID = tlm.Location_ID
WHERE
pickh.TS_Number = '$doc'
GROUP BY Serial_Package , tpm.Part_ID
    ORDER BY pickh.TS_Number, Serial_Package, pickp.Picking_Pre_ID ASC;";
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
		$header = new easyTable($this->instance, '%{20, 50, 30,}', 'border:0;font-family:THSarabun;font-size:12; font-style:B;');
		$header->easyCell('', 'img:images/abt-logo.gif, w35;align:L', '');
		$header->easyCell('ALBATROSS LOGISTICS CO., LTD.
	  336/7 MOO 7 BOWIN, SRIRACHA CHONBURI 20230
	  Phone +66 38 058 021, +66 38 058 081-2
	  Fax : +66 38 058 007
	  ', 'valign:M;align:L');
		$header->easyCell($v[0]['TS_Number'], 'valign:B;align:C');
		$header->printRow();
		$header->endTable(2);


		$header = new easyTable($this->instance, '%{100}', 'border:0;font-family:THSarabun;font-size:20; font-style:B;');
		$header->easyCell(utf8Th('PICK SHEET'), 'valign:M;align:C;border:TB');
		$header->printRow();
		$header->endTable(1);

		$header = new easyTable($this->instance, '%{10,15,15,20,20,20}', 'border:0;font-family:THSarabun;font-size:13;');
		$header->easyCell("Shipper : ", 'valign:T;align:L;font-style:B;');
		$header->easyCell('TSRA', 'valign:T;align:L;');
		$header->easyCell("Pick Date Time :", 'valign:T;align:L;font-style:B;');
		$header->easyCell(utf8Th($v[0]['Pick_Date']), 'valign:T;align:L;');
		$header->easyCell("Pick Sheet Number :", 'valign:T;align:L;font-style:B;');
		$header->easyCell(utf8Th($v[0]['TS_Number']), 'valign:T;align:L;');
		$header->printRow();
		$header->easyCell("Customer :", 'valign:T;align:L;font-style:B;');
		$header->easyCell(utf8Th($v[0]['Customer']), 'valign:T;align:L;');
		// $header->easyCell("Trip Number :", 'valign:T;align:L;font-style:B;');
		// $header->easyCell(utf8Th($v[0]['Trip_Number']), 'valign:T;align:L;');
		$header->printRow();
		$header->endTable(2);

		$headdetail = new easyTable(
			$this->instance,
			'{10,35,60,30,25,25,25,25}',
			'width:300;border:1;font-family:THSarabun;font-size:10; font-style:B;bgcolor:#C8C8C8;valign:M;'
		);
		$headdetail->easyCell(utf8Th('No.'), 'align:C');
		$headdetail->easyCell(utf8Th('Part Number'), 'align:C');
		$headdetail->easyCell(utf8Th('Part Name'), 'align:C');
		$headdetail->easyCell(utf8Th('Package ID'), 'align:C');
		$headdetail->easyCell(utf8Th('Qty Package (PCS)'), 'align:C');
		$headdetail->easyCell(utf8Th('Location'), 'align:C');
		$headdetail->easyCell(utf8Th('Confirm 
		Picking Date'), 'align:C');
		$headdetail->easyCell(utf8Th('Remarks'), 'align:C');
		$headdetail->printRow();
		$headdetail->endTable(0);

		$this->instance->Code128(145, 20, $v[0]['TS_Number'], 55, 7);
	}
	function Footer()
	{
		$this->SetXY(-20, 0);
		$this->SetFont('THSarabun', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
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
$docno = $headerData[0]['TS_Number'];
$pdf->SetTitle($docno);
$detail = new easyTable($pdf, '{10,35,60,30,25,25,25,25}', 'width:300;border:1;font-family:THSarabun;font-size:10;valign:M;');
$data = sizeof($detailData);
// หน้าละ15row
$pagebreak = 15;
$i = 0;
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
	$detail->easyCell(utf8Th($nn), 'align:C');
	$detail->easyCell(utf8Th($detailData[$i]["Part_Number"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Part_Name"]), 'align:L;font-size:9;');
	$detail->easyCell(utf8Th($detailData[$i]["Serial_Package"]), 'align:C;font-style:B;font-size:10;');
	$detail->easyCell(utf8Th($detailData[$i]["Qty_Package"]), 'align:C;font-style:B;font-size:14;');
	$detail->easyCell(utf8Th($detailData[$i]["Location_Code"]), 'align:C;');
	$detail->easyCell(utf8Th($detailData[$i]["Confirm_Picking_DateTime"]), 'align:C;');
	$detail->easyCell(utf8Th(''), 'align:C;font-style:B;font-size:14;');
	$detail->printRow();
	$sumqty += $detailData[$i]['Qty_Package'];
	$i++;
	$nn++;
}
$detail->easyCell(utf8Th('Total :'), 'align:R;font-style:B;colspan:4;font-size:14;');
$detail->easyCell(utf8Th($sumqty), 'align:C;font-style:B;font-size:14;');
$detail->easyCell(utf8Th(''), 'align:C;border:TB;');
$detail->easyCell(utf8Th(''), 'align:C;font-size:14;border:TB;');
$detail->easyCell(utf8Th(''), 'align:C;colspan:3;border:TBR;');
$detail->printRow();
$detail->endTable(10);

$lastfooter = new easyTable($pdf, '%{10,25,10,25,10,20}', 'width:300;border:0;font-family:THSarabun;font-size:12;');
$lastfooter->easyCell(utf8Th('Pick By :'), 'align:C;font-size:14;');
$lastfooter->easyCell(utf8Th('____________________'), 'align:C;font-size:14;');
$lastfooter->easyCell(utf8Th('Check By :'), 'align:C;font-size:14;');
$lastfooter->easyCell(utf8Th('____________________'), 'align:C;font-size:14;');
$lastfooter->easyCell(utf8Th('Date :'), 'align:C;font-size:14;');
$lastfooter->easyCell(utf8Th('____________________'), 'align:C;font-size:14;');
$lastfooter->printRow();
$lastfooter->endTable(3);

$pdf->Output('I',$docno.'.pdf');

function utf8Th($v)
{
	return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}
