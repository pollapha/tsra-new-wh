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
Weld_On_No,
date_format(Delivery_DateTime, '%d/%m/%y %H:%i:%s') AS Delivery_DateTime,
Qty,
SNP,
MMTH_Part_No,
Part_Descri
FROM tbl_weld_on_order
where Weld_On_No = '$doc'
GROUP BY Weld_On_No
order by Delivery_DateTime, Weld_On_No;";

$q1  .= "SELECT 
Weld_On_No,
date_format(Delivery_DateTime, '%d/%m/%y %H:%i:%s') AS Delivery_DateTime,
Qty,
SNP,
MMTH_Part_No,
Part_Descri
FROM tbl_weld_on_order
where Weld_On_No = '$doc'
order by Delivery_DateTime, Weld_On_No;";
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

        $header = new easyTable(
            $this->instance,
            '{ 100, 60 }',
            'width:300;border:1;font-family:THSarabun;font-size:16; font-style:B; bgcolor:#C8C8C8; paddingY:3;'
        );
        // Header starts /// 
        $header->rowStyle('align:{CCCCC}; bgcolor:#ffffff; ');
        $header->easyCell("Delivery Note Tachi-s frame ", 'font-size:18;',);
        $header->easyCell("13/07/2022 10:19:47");
        $header->printRow();


        $header = new easyTable(
            $this->instance,
            '{ 60, 100 }',
            'width:300;border:1; font-family:THSarabun;font-size:16; font-style:B; bgcolor:#C8C8C8; paddingY:3;'
        );
        $header->rowStyle('align:{CCCCC}; bgcolor:#ffffff;');
        $header->easyCell(utf8Th($v[0]['Weld_On_No']), 'valign:T;align:M; font-size:18;');
        $this->instance->Code128(110, 24.5, $v[0]['Weld_On_No'], 55, 10);
        $header->printRow();

        $headdetail = new easyTable(
            $this->instance,
            '{40, 100, 20}',
            'width:300;border:1;font-family:THSarabun;font-size:12; font-style:B; bgcolor:#C8C8C8; paddingY:1;'
        );
        $headdetail->rowStyle('align:{CCCCC};');
        $headdetail->easyCell("Part No.");
        $headdetail->easyCell("Part Description");
        $headdetail->easyCell("Qty.");
        $headdetail->printRow();
        $headdetail->endTable(0);

        //$this->instance->Code128(145, 10, $v[0]['Weld_On_No'], 55, 7);
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

$docno = $headerData[0]['Weld_On_No'];
$pdf->SetTitle($docno);
$detail = new easyTable($pdf, '{40, 100, 20}', 'width:300;border:1;font-family:THSarabun;font-size:12;valign:M;');
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
    $detail->rowStyle('align:{CLC};');
    $detail->easyCell(utf8Th($detailData[$i]["MMTH_Part_No"]), 'font-style:B;font-size:18;');
    $detail->easyCell(utf8Th($detailData[$i]["Part_Descri"]), 'font-size:12;');
    //$detail->easyCell("FRAME ASSY-CUSH,FR SEAT LH 4WAY ISO-FIX TETHER", 'font-size:12;');
    $detail->easyCell(utf8Th($detailData[$i]["SNP"]), 'font-style:B;font-size:18;');
    $detail->printRow();
    $i++;
    $nn++;
}
$detail->endTable(10);

$pdf->Output();

function utf8Th($v)
{
    return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}
