<?php
include '../fpdf.php';
include '../exfpdf.php';
include '../easyTable.php';
include 'PDF_Code128.php';
include('../../php/connection.php');

$doc = $mysqli->real_escape_string(trim(strtoupper($_REQUEST['data'])));
$dataset = array();


$explode = explode(",", $doc);
$txt = "";
$size = sizeof($explode);
$c = 0;
while ($c < $size - 1) {
    // $txt .= "pickh.TS_NUmber = " . "'" . $explode[$c] . "'" . " OR ";
    $txt .= "BIN_TO_UUID(tiv.ID,TRUE) = " . "'" . $explode[$c] . "'" . " OR ";
    $c++;
}

$str = substr("$txt", 0, -3);

//echo ($str);


$q1  = "SELECT 
    DATE_FORMAT(tap.Assembly_Date, '%d-%m-%y') AS Assembly_Date,
    tiv.Serial_ID,
    tpm.Part_No,
    tpm.Part_Name,
    tiv.Qty,
    tpm.Model,
    tpm.Mat_SAP1,
    tpm.Mat_SAP3,
    tpm.Color,
    tpm.Picture,
    tcm.Customer_Code
FROM
    tbl_inventory tiv
        INNER JOIN
    tbl_assembly_pre tap ON tiv.WorkOrder = tap.WorkOrder
        INNER JOIN
    tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
        LEFT JOIN
    tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
    $str
GROUP BY tiv.Serial_ID , tpm.Part_No;";


$q1  .= "SELECT 
    DATE_FORMAT(tap.Assembly_Date, '%d-%m-%y') AS Assembly_Date,
    tiv.Serial_ID,
    tpm.Part_No,
    tpm.Part_Name,
    tiv.Qty,
    tpm.Model,
    tpm.Mat_SAP1,
    tpm.Mat_SAP3,
    tpm.Color,
    tpm.Picture,
    tcm.Customer_Code
FROM
    tbl_inventory tiv
        INNER JOIN
    tbl_assembly_pre tap ON tiv.WorkOrder = tap.WorkOrder
        INNER JOIN
    tbl_part_master tpm ON tiv.Part_ID = tpm.Part_ID
        LEFT JOIN
    tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
    $str
GROUP BY tiv.Serial_ID , tpm.Part_No;
ORDER BY tiv.Serial_ID, tiv.Part_No ASC;";
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
    protected $col = 0; // Current column
    protected $y = 10;      // Ordinate of column start
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
    function SetCol($col)
    {
        // Set position at a given column
        //echo($col);
        $this->col = $col;
        $x = 5 + $col * 50;
        $this->SetLeftMargin($x);
        $this->SetX($x);
    }
    function Header()
    {
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
$pdf->setHeaderData($detailData);
//$pdf->AddPage();
// $docno = $headerData[0]['Serial_ID'];
// $pdf->SetTitle($docno);
$pdf->SetTitle('Tag Card');
$data = sizeof($detailData);
//echo($data);
//หน้าละ15row
$pagebreak = 6;
$i = 0;
$countrow = 1;
$j = 0;
$countrow1 = 1;
$nn = 1;
$sumqty = 0;
$sumBoxes = 0;
$sumCBM = 0;
while ($i < $data) {

    if (($j % 6) == 0) {
        $pdf->AddPage();
        //echo ($j);
        $pdf->Code128(38, 30.5, $detailData[$j]['Part_No'], 60, 7);
        $pdf->Code128(37.5, 62, $detailData[$j]['Mat_SAP1'], 30, 6);
        $pdf->Code128(37.5, 74, $detailData[$j]['Mat_SAP3'], 30, 6);
        $pdf->Code128(73, 87, $detailData[$j]['Serial_ID'], 30, 7);
        $countrow1 = 1;
    }
    if (($j % 6) == 1) {
        //echo ($j);
        $pdf->Code128(134, 30.5, $detailData[$j]['Part_No'], 60, 7);
        $pdf->Code128(133, 62, $detailData[$j]['Mat_SAP1'], 30, 6);
        $pdf->Code128(133.5, 74, $detailData[$j]['Mat_SAP3'], 30, 6);
        $pdf->Code128(168.5, 87, $detailData[$j]['Serial_ID'], 30, 7);
        $countrow1 = 1;
    }
    if (($j % 6) == 2) {
        $pdf->Code128(38, 118, $detailData[$j]['Part_No'], 60, 7);
        $pdf->Code128(37.5, 150, $detailData[$j]['Mat_SAP1'], 30, 6);
        $pdf->Code128(37.5, 162, $detailData[$j]['Mat_SAP3'], 30, 6);
        $pdf->Code128(73, 175, $detailData[$j]['Serial_ID'], 30, 7);
        $countrow1 = 1;
    }
    if (($j % 6) == 3) {
        $pdf->Code128(134, 118, $detailData[$j]['Part_No'], 60, 7);
        $pdf->Code128(133, 150, $detailData[$j]['Mat_SAP1'], 30, 6);
        $pdf->Code128(133.5, 162, $detailData[$j]['Mat_SAP3'], 30, 6);
        $pdf->Code128(168.5, 175, $detailData[$j]['Serial_ID'], 30, 7);
        $countrow1 = 1;
    }
    if (($j % 6) == 4) {
        $pdf->Code128(38, 206, $detailData[$j]['Part_No'], 60, 7);
        $pdf->Code128(37.5, 237.5, $detailData[$j]['Mat_SAP1'], 30, 6);
        $pdf->Code128(37.5, 250, $detailData[$j]['Mat_SAP3'], 30, 6);
        $pdf->Code128(73, 262.5, $detailData[$j]['Serial_ID'], 30, 7);
        $countrow1 = 1;
    }
    if (($j % 6) == 5) {
        $pdf->Code128(134, 206, $detailData[$j]['Part_No'], 60, 7);
        $pdf->Code128(133, 237.5, $detailData[$j]['Mat_SAP1'], 30, 6);
        $pdf->Code128(133.5, 250, $detailData[$j]['Mat_SAP3'], 30, 6);
        $pdf->Code128(168.5, 262.5, $detailData[$j]['Serial_ID'], 30, 7);
        $countrow1 = 1;
    }

    $j++;
    $countrow1++;

    $countrow++;
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    if (($i % 2) == 0) {
        $header = new easyTable($pdf, '%{25,50,25}', 'width:94.5;align:L;border:TRLB;font-family:THSarabun;font-size:9; font-style:B;');
        $header->easyCell('', 'img:images/tsra-logo.jpg, w13;align:C; rowspan:2;', '');
        $header->easyCell(utf8Th('TAG CARD'), 'valign:M;align:C; font-family:THSarabun;font-size:16; font-style:B; rowspan:2');
        $header->easyCell(utf8Th('Customer'), 'valign:C;align:C; font-family:THSarabun;font-size:9; font-style:B;');
        $header->printRow();
        $header->easyCell(utf8Th($headerData[$i]['Customer_Code']), 'valign:B;align:C; font-family:THSarabun;font-size:11; font-style:B;font-color:#FF0000;');
        $header->easyCell('', 'border:B; valign:C;', '');
        $header->easyCell('', 'border:B; valign:C;', '');
        $header->printRow();

        $img = utf8Th($detailData[$i]['Picture']);
        $image = substr($img, 11);

        $detail = new easyTable($pdf, '%{25,75}', 'width:94.5;align:L;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Part Number : '), 'valign:M;align:L;border:LRB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Part_No']), 'valign:T;align:C;border:LRTB;font-size:12;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,75}', 'width:94.5;align:L;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Barcode : '), 'valign:M;align:L;border:LRB;font-size:9;', '');
        $detail->easyCell('', 'align:C;font-style:B;paddingY:5; border:LRB;');
        $detail->printRow();

        $detail->easyCell(utf8Th('Part Name :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Part_Name']), 'valign:T;align:C;border:LRTB;font-size:10;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,40,10,25}', 'width:94.5;align:L;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Picture :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'img:../../' . '' . $image . '' . ', w13;align:C; border:LRTB', '');
        $detail->easyCell(utf8Th('Qty :'), 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Qty']), 'valign:M;align:C;border:LRTB;font-size:14;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,75}', 'width:94.5;align:L;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Color : '), 'valign:M;align:L;border:LRB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Color']), 'valign:T;align:C;border:LRTB;font-size:11;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,40,35}', 'width:94.5;align:L;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell('TSRA_Mat SAP1  ', 'valign:M;align:L;border:BLR;font-size:9;rowspan:2;', '');
        $detail->easyCell('', 'align:C;font-style:B;paddingY:1; border:LR;');
        $detail->easyCell('Model : ', 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->printRow();

        $detail->easyCell(utf8Th($detailData[$i]['Mat_SAP1']), 'valign:B;align:C;border:LR;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Model']), 'valign:T;align:C;border:LR;font-size:11;bgcolor:#DFDFDF;', '');
        $detail->printRow();

        $detail->easyCell('Customer_Mat SAP3  ', 'valign:M;align:L;border:TLR;font-size:9;rowspan:2;', '');
        $detail->easyCell('', 'valign:M;align:C;paddingY:1;border:LRT;font-size:4;', '');
        $detail->easyCell('Package ID : ', 'valign:M;align:C;;border:LRTB;font-size:9;', '');
        $detail->printRow();

        $detail->easyCell(utf8Th($detailData[$i]['Mat_SAP3']), 'valign:B;align:C;border:LR;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Serial_ID']), 'valign:T;align:C;border:LR;font-size:12;bgcolor:#DFDFDF;', '');
        $detail->printRow();


        $detail = new easyTable($pdf, '%{25,20,20,35}', 'width:94.5;align:L;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Date :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Assembly_Date']), 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th('Shift/กะ : '), 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'valign:M;align:L;border:LRTB;font-size:9;rowspan:2;', '');
        $detail->printRow();

        $detail->easyCell(utf8Th('ผู้รับผิดชอบ :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->printRow();

        $header->endTable(1);
        $detail->endTable(1);
        $final_vposition = $pdf->GetY();
    }

    $pdf->SetY($y);
    if (($i % 2) == 1) {
        $header = new easyTable($pdf, '%{25,50,25}', 'width:94.5;align:R;border:TRLB;font-family:THSarabun;font-size:9; font-style:B;');
        $header->easyCell('', 'img:images/tsra-logo.jpg, w13;align:C; rowspan:2;', '');
        $header->easyCell(utf8Th('TAG CARD'), 'valign:M;align:C; font-family:THSarabun;font-size:16; font-style:B; rowspan:2');
        $header->easyCell(utf8Th('Customer'), 'valign:C;align:C; font-family:THSarabun;font-size:9; font-style:B;');
        $header->printRow();
        $header->easyCell(utf8Th($headerData[$i]['Customer_Code']), 'valign:B;align:C; font-family:THSarabun;font-size:11; font-style:B;font-color:#FF0000;');
        $header->easyCell('', 'border:B; valign:C;', '');
        $header->easyCell('', 'border:B; valign:C;', '');
        $header->printRow();

        $img = utf8Th($detailData[$i]['Picture']);
        $image = substr($img, 11);

        $detail = new easyTable($pdf, '%{25,75}', 'width:94.5;align:R;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Part Number : '), 'valign:M;align:L;border:LRB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Part_No']), 'valign:T;align:C;border:LRTB;font-size:12;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,75}', 'width:94.5;align:R;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Barcode : '), 'valign:M;align:L;border:LRB;font-size:9;', '');
        $detail->easyCell('', 'align:C;font-style:B;paddingY:5; border:LRB;');
        $detail->printRow();

        $detail->easyCell(utf8Th('Part Name :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Part_Name']), 'valign:T;align:C;border:LRTB;font-size:10;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,40,10,25}', 'width:94.5;align:R;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Picture :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'img:../../' . '' . $image . '' . ', w13;align:C; border:LRTB', '');
        $detail->easyCell(utf8Th('Qty :'), 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Qty']), 'valign:M;align:C;border:LRTB;font-size:14;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,75}', 'width:94.5;align:R;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Color : '), 'valign:M;align:L;border:LRB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Color']), 'valign:T;align:C;border:LRTB;font-size:11;bgcolor:#DFDFDF;');
        $detail->printRow();

        $detail = new easyTable($pdf, '%{25,40,35}', 'width:94.5;align:R;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell('TSRA_Mat SAP1 ', 'valign:M;align:L;border:BLR;font-size:9;rowspan:2;', '');
        $detail->easyCell('', 'align:C;font-style:B;paddingY:1; border:LR;');
        $detail->easyCell('Model : ', 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->printRow();

        $detail->easyCell(utf8Th($detailData[$i]['Mat_SAP1']), 'valign:B;align:C;border:LR;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Model']), 'valign:T;align:C;border:LR;font-size:11;bgcolor:#DFDFDF;', '');
        $detail->printRow();

        $detail->easyCell('Customer_Mat SAP3 ', 'valign:M;align:L;border:TLR;font-size:9;rowspan:2;', '');
        $detail->easyCell('', 'valign:M;align:C;paddingY:1;border:LRT;font-size:4;', '');
        $detail->easyCell('Package ID  : ', 'valign:M;align:C;;border:LRTB;font-size:9;', '');
        $detail->printRow();

        $detail->easyCell(utf8Th($detailData[$i]['Mat_SAP3']), 'valign:B;align:C;border:LR;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Serial_ID']), 'valign:T;align:C;border:LR;font-size:12;bgcolor:#DFDFDF;', '');
        $detail->printRow();


        $detail = new easyTable($pdf, '%{25,20,20,35}', 'width:94.5;align:R;border:0;font-family:THSarabun;font-style:B');
        $detail->easyCell(utf8Th('Date :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th($detailData[$i]['Assembly_Date']), 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell(utf8Th('Shift/กะ : '), 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'valign:M;align:R;border:LRTB;font-size:9;rowspan:2;', '');
        $detail->printRow();

        $detail->easyCell(utf8Th('ผู้รับผิดชอบ :'), 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'valign:M;align:C;border:LRTB;font-size:9;', '');
        $detail->easyCell('', 'valign:M;align:L;border:LRTB;font-size:9;', '');
        $detail->printRow();

        $header->endTable(1);
        $detail->endTable(1);
        $pdf->SetY(max($final_vposition, $pdf->GetY()));
    }

    $i++;
    $nn++;
}

$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
$date = date("Ymd");
$pdf->Output('I', 'TAGCARD_' . $date . '_' . $randomString . '.pdf');


function utf8Th($v)
{
    return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}
