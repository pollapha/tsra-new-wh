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
			trh.GRN_Number,
			DATE_FORMAT(trh.Receive_Date, '%d/%m/%y') AS Receive_Date,
			trp.Serial_ID,
			trp.Part_No,
			tpm.Part_Name,
			trp.Model,
			trp.Part_Type,
			trp.Package_Type,
			SUM(trp.Qty_Package) AS Total_Qty,
            trp.Qty_Unit,
			tcm.Customer_Code,
			tcm.Customer_Name,
			trh.Status_Receiving,
			DATE_FORMAT(trh.Confirm_Receive_DateTime,'%d/%m/%y %H:%i') AS Confirm_Receive_DateTime
		FROM
			tbl_receiving_pre trp
				INNER JOIN
			tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
				INNER JOIN
			tbl_part_master tpm ON trp.Part_ID = tpm.Part_ID
				INNER JOIN
			tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
		WHERE 
			trh.GRN_Number= '$doc' 
		GROUP BY 
			trp.Serial_ID;";

$q1  .= "SELECT 
		trh.GRN_Number,
		DATE_FORMAT(trh.Receive_Date, '%d-%m-%y') AS Receive_Date,
		trp.Serial_ID,
		trp.Part_No,
		tpm.Part_Name,
		trp.Model,
		tpm.Type AS Part_Type,
		trp.Package_Type,
		trh.Total_Qty,
		trp.Qty_Package,
        trp.Qty_Unit,
		tcm.Customer_Code,
		tcm.Customer_Name,
		trh.Status_Receiving,
		DATE_FORMAT(trh.Confirm_Receive_DateTime,'%d/%m/%y %H:%i') AS Confirm_Receive_DateTime
	FROM
		tbl_receiving_pre trp
			INNER JOIN
		tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
			INNER JOIN
		tbl_part_master tpm ON trp.Part_ID = tpm.Part_ID
			INNER JOIN
		tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
	WHERE 
		trh.GRN_Number= '$doc'
        ORDER BY trp.Serial_ID ASC;";
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
        // $v = $this->headerData;
        // $data = sizeof($v);
        // echo($data);
        // $i = 0;
        // $countrow = 1;
        // $nn = 1;
        // $sumqty = 0;
        // $sumBoxes = 0;
        // $sumCBM = 0;
        // while ($i <  $data) {

        // }

        // $this->instance->Code128(85, 88, $v[0]['Part_No'], 130, 15);
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
$docno = $headerData[0]['GRN_Number'];
$pdf->SetTitle($docno);
$data = sizeof($detailData);
//echo($data);
//หน้าละ15row
$pagebreak = 2;
$i = 0;
$countrow = 1;
$j = 0;
$countrow1 = 1;
$nn = 1;
$sumqty = 0;
$sumBoxes = 0;
$sumCBM = 0;
//echo ($data);
// while ($j < $data) {
//     if (fmod($countrow1, 2)  != 0) {
//         //echo ($j);
//         $pdf->Code128(94, 115, $detailData[$j]["Serial_ID"], 60, 8);
//         //$pdf->Code128(94, 249, $detailData[$j - 1]["Serial_ID"], 60, 8);
//         $countrow1 = 1;
//     }
// }
while ($i <  $data) {
    if (($j%5) == 0) {
        //echo ($j);
        $pdf->AddPage();
        $pdf->Code128(101.5, 20.5, $detailData[$i]['Part_No'], 90, 9);
        $pdf->Code128(75, 52, $detailData[$j]["Serial_ID"], 60, 8);
        $countrow1 = 1;
    }
    if (($j%5) == 1) {
        //echo ($j);
        $pdf->Code128(101.5, 74, $detailData[$i]['Part_No'], 90, 9);
        $pdf->Code128(75, 105, $detailData[$j]["Serial_ID"], 60, 8);
        $countrow1 = 1;
    }
    if (($j%5) == 2) {
        //echo ($j);
        $pdf->Code128(101.5, 127, $detailData[$i]['Part_No'], 90, 9);
        $pdf->Code128(75, 158.25, $detailData[$j]["Serial_ID"], 60, 8);
        $countrow1 = 1;
    }
    if (($j%5) == 3) {
        //echo ($j);
        $pdf->Code128(101.5, 180.5, $detailData[$i]['Part_No'], 90, 9);
        $pdf->Code128(75, 211.5, $detailData[$j]["Serial_ID"], 60, 8);
        $countrow1 = 1;
    }
    if (($j%5) == 4) {
        //echo ($j);
        $pdf->Code128(101.5, 233.75, $detailData[$i]['Part_No'], 90, 9);
        $pdf->Code128(75, 264.75, $detailData[$j]["Serial_ID"], 60, 8);
        $countrow1 = 1;
    }
    

    // while ($i < $data) {
    //     if ($countrow > $pagebreak) {
    //         $pdf->AddPage();
    //         $countrow = 1;
    //     }
    $countrow++;
    $countrow1++;
    $j++;
    $x = $pdf->GetX();
    $y = $pdf->GetY();


    $header = new easyTable($pdf, '%{40,25,35}', 'border:TB;font-family:THSarabun;font-size:8; font-style:B;');
    $header->easyCell('ALBATROSS LOGISTICS CO., LTD.
	  336/7 MOO 7 BOWIN, SRIRACHA CHONBURI 20230
	  ', 'valign:M;align:L;border:LTB;');
    $header->easyCell(utf8Th('RECEIVE TAG'), 'valign:B;align:C; font-family:THSarabun;font-size:16; font-style:B');
    $header->easyCell('', 'img:images/abt-logo.gif, w10;align:R;border:RTB;', '');
    $header->printRow();
    //$header->endTable(2);

    $header = new easyTable($pdf, '%{10,35,55}', 'border:0;font-family:THSarabun;font-style:B');
    $header->easyCell(utf8Th('Part Number : '), 'valign:M;align:L;border:LB;font-size:10;', '');
    $header->easyCell(utf8Th($detailData[$i]['Part_No']), 'valign:M;align:L;border:RB;font-size:12;bgcolor:#C8C8C8;border:TBLR;');
    $header->easyCell('', 'border:LR;');
    $header->printRow();

    $header = new easyTable($pdf, '%{10,35,55}', 'border:0;font-family:THSarabun;font-style:B');
    $header->easyCell(utf8Th('Part Name : '), 'valign:M;align:L;border:LB;font-size:10;', '');
    $header->easyCell(utf8Th($detailData[$i]['Part_Name']), 'valign:M;align:L;border:RB;font-size:10;');
    $header->easyCell('', 'border:LBR;');
    $header->printRow();

    $detail = new easyTable($pdf, '%{15,15,15,25,15,15}', 'border:RLB;font-family:THSarabun;font-size:10;valign:M;');
    $detail->easyCell(utf8Th('Receive Date'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Model'), 'align:C;font-style:B;');
    //$detail->easyCell(utf8Th('Qty(Total)'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Part Type'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Package Type'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Qty Package'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Qty(Unit)'), 'align:C;font-style:B;');
    $detail->printRow();

    $detail = new easyTable($pdf, '%{15,15,15,25,15,15}', 'border:RTLB;font-family:THSarabun;font-size:11;valign:M;bgcolor:#C8C8C8;');
    $detail->easyCell(utf8Th($detailData[$i]["Receive_Date"]), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th($detailData[$i]["Model"]), 'align:C;font-style:B;');
    //$detail->easyCell(utf8Th($detailData[$i]["Total_Qty"]), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th($detailData[$i]["Part_Type"]), 'align:C;font-style:B;font-size:10;');
    $detail->easyCell(utf8Th($detailData[$i]["Package_Type"]), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th($detailData[$i]["Qty_Package"]), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th($detailData[$i]["Qty_Unit"]), 'align:C;font-style:B;');
    $detail->printRow();

    $detail = new easyTable($pdf, '%{30,40,15,15}', 'border:RLB;font-family:THSarabun;font-size:11;valign:M;');
    $detail->easyCell(utf8Th('Package Number'), 'align:C;font-style:B;border:LB;');
    $detail->easyCell('', 'align:C;font-style:B;border:RB;');
    $detail->easyCell(utf8Th('Qty packing record'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Packing by'), 'align:C;font-style:B;');
    $detail->printRow();


    $detail = new easyTable($pdf, '%{30,40,15,15}', 'border:RLTB;font-family:THSarabun;font-size:12;valign:M;');
    $detail->easyCell(utf8Th($detailData[$i]["Serial_ID"]), 'align:C;font-style:B;bgcolor:#C8C8C8;font-size:18;');
    $detail->easyCell('', 'align:C;font-style:B;paddingY:5.5');
    $detail->easyCell('', 'align:C;font-style:B;');
    $detail->easyCell('', 'align:C;font-style:B;');
    $detail->printRow();

    // $detail = new easyTable($pdf, '%{35,35,20,10}', 'border:RLB;font-family:THSarabun;font-size:10;valign:M;');
    // $detail->easyCell(utf8Th($detailData[$i]["Serial_ID"]), 'align:C;font-style:B;');
    // $detail->easyCell('', 'align:C;font-style:B;paddingY:7');

    
    // $detail->printRow();

    // $detail = new easyTable($pdf, '%{40,20,20,20}', 'border:RLB;font-family:THSarabun;font-size:10;valign:M;');
    
    
    
    // $detail->printRow();

    // $detail = new easyTable($pdf, '%{40,20,20,20}', 'border:RLB;font-family:THSarabun;font-size:18;valign:M;');
    
    
    // $detail->easyCell('', 'align:C;font-style:B;');
    // $detail->easyCell('', 'align:C;font-style:B;');
    // $detail->printRow();
    $header->endTable(1);
    $detail->endTable(1);
    $i++;
    $nn++;
}

$pdf->Output('I', 'RECEIVETAG_' . $docno . '.pdf');

function utf8Th($v)
{
    return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}
