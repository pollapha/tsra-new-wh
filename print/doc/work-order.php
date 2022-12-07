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
            trp.WorkOrder,
			DATE_FORMAT(trh.Receive_Date, '%d/%m/%y') AS Receive_Date,
			trp.Serial_ID,
			trp.Part_No,
			tpm.Part_Name,
			trp.Model,
			trp.Part_Type,
			trp.Package_Type,
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
				LEFT JOIN
			tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
		WHERE 
			trh.GRN_Number= '$doc' 
		GROUP BY 
            trp.Serial_ID, trp.WorkOrder;";

$q1  .= "WITH a AS (
    SELECT 
        trh.GRN_Number,
        trp.WorkOrder,
        DATE_FORMAT(trh.Receive_Date, '%d-%m-%y') AS Receive_Date,
        trp.Serial_ID,
        trp.Part_No,
        tpm.Part_Name,
        trp.Model,
        tpm.Type AS Part_Type,
        trp.Package_Type,
        trp.Qty_Package,
        trp.Qty_Unit,
        tcm.Customer_Code,
        tcm.Customer_Name,
        trh.Status_Receiving,
        DATE_FORMAT(trh.Confirm_Receive_DateTime,
                '%d/%m/%y %H:%i') AS Confirm_Receive_DateTime
    FROM
        tbl_receiving_pre trp
            INNER JOIN
        tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
            INNER JOIN
        tbl_part_master tpm ON trp.Part_ID = tpm.Part_ID
            LEFT JOIN
        tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
    WHERE
        trh.GRN_Number = '$doc'
    ORDER BY trp.Serial_ID ASC)
    SELECT a.*, SUM(trp.Qty_Package) AS Total_Qty FROM a
    cross join tbl_receiving_pre trp
    where a.Serial_ID = trp.Serial_ID
    GROUP BY a.Serial_ID, a.Part_No;";
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
$data2 = sizeof($headerData);
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

while ($i <  $data) {
    if (($j % 2) == 0) {
        //echo ($j);
        $pdf->AddPage();
        $pdf->Code128(122, 41, $detailData[$j]['WorkOrder'], 60, 8);
        $pdf->Code128(45, 66, $detailData[$j]['Part_No'], 120, 8);
        $pdf->Code128(94, 116, $detailData[$j]['Serial_ID'], 60, 8);
        $countrow1 = 1;
    }
    if (($j % 2) != 0) {
        //echo ($j);
        $pdf->Code128(122, 174, $detailData[$j]['WorkOrder'], 60, 8);
        $pdf->Code128(45, 199, $detailData[$j]['Part_No'], 120, 8);
        $pdf->Code128(94, 248.5, $detailData[$j]['Serial_ID'], 60, 8);
        $countrow1 = 1;
    }


    // while ($i <  $data) {
    //     if ($countrow > $pagebreak) {
    //         $pdf->AddPage();
    //         $countrow = 1;
    //     }
    $countrow1++;
    $j++;
    $countrow++;
    $x = $pdf->GetX();
    $y = $pdf->GetY();


    $header = new easyTable($pdf, '%{40,25,35}', 'border:TB;font-family:THSarabun;font-size:9; font-style:B;');
    $header->easyCell('ALBATROSS LOGISTICS CO., LTD.
	  324/7 MOO 7 BOWIN, SRIRACHA CHONBURI 20230
	  Phone +66 38 058 021, +66 38 058 081-2
	  Fax : +66 38 058 007
	  ', 'valign:M;align:L;border:LTB;');
    $header->easyCell(utf8Th('WORK ORDER'), 'valign:B;align:C; font-family:THSarabun;font-size:24; font-style:B');
    $header->easyCell('', 'img:images/abt-logo.gif, w20;align:R;border:RTB;', '');
    $header->printRow();
    //$header->endTable(2);



    $header = new easyTable($pdf, '%{50,50}', 'border:0;font-family:THSarabun;font-style:B');
    $header->easyCell(utf8Th('Customer : '), 'valign:M;align:L;border:LR;font-size:12;', '');
    $header->easyCell(utf8Th('Work order : '), 'valign:M;align:L;border:LR;font-size:12;', '');
    $header->printRow();

    $header->easyCell(utf8Th('Thai Summit Rayong (TSRA)'), 'valign:T;align:C;border:LR;font-size:16;rowspan:2', '');
    $header->easyCell(utf8Th($detailData[$i]['WorkOrder']), 'valign:T;align:C;border:LR;font-size:16;rowspan:2', '');
    $header->printRow();

    $header->easyCell('', 'valign:M;align:L;border:LBR;font-size:28;', '');
    $header->easyCell('', 'valign:M;align:L;border:LBR;font-size:28;paddingY:9;', '');
    $header->printRow();

    $header = new easyTable($pdf, '%{100}', 'border:0;font-family:THSarabun;font-style:B');
    $header->easyCell(utf8Th('Part Number'), 'valign:M;align:L;border:LR;font-size:12;', '');
    $header->printRow();

    $header->easyCell(utf8Th($detailData[$i]['Part_No']), 'valign:T;align:C;border:LR;font-size:16;');
    $header->printRow();
    $header->easyCell('', 'border:LBR;paddingY:5;');
    $header->printRow();

    $header->easyCell(utf8Th('Part Name'), 'valign:M;align:L;border:LR;font-size:12;', '');
    $header->printRow();
    $header->easyCell(utf8Th($detailData[0]['Part_Name']), 'valign:T;align:C;border:LRB;font-size:16;');
    $header->printRow();

    $detail = new easyTable($pdf, '%{40,20,20,20}', 'border:RLB;font-family:THSarabun;font-size:12;valign:M;');
    $detail->easyCell(utf8Th('Receive Date'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Model'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Qty(Total)'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Qty(Unit)'), 'align:C;font-style:B;');
    $detail->printRow();

    $detail = new easyTable($pdf, '%{40,20,20,20}', 'border:RLTB;font-family:THSarabun;font-size:16;valign:M;bgcolor:#C8C8C8;');
    $detail->easyCell(utf8Th($detailData[$i]["Receive_Date"]), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th($detailData[$i]["Model"]), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th($detailData[$i]["Total_Qty"]), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th($detailData[$i]["Qty_Unit"]), 'align:C;font-style:B;');
    $detail->printRow();

    $detail = new easyTable($pdf, '%{80,20}', 'border:RLB;font-family:THSarabun;font-size:12;valign:M;');
    $detail->easyCell(utf8Th('Package Number'), 'align:L;font-style:B;');
    $detail->easyCell(utf8Th('Part Type'), 'align:C;font-style:B;');
    $detail->printRow();

    $detail = new easyTable($pdf, '%{40,40,20}', 'border:RLTB;font-family:THSarabun;font-size:16;valign:M;');
    $detail->easyCell(utf8Th($detailData[$i]["Serial_ID"]), 'align:C;font-style:B;bgcolor:#C8C8C8; font-size:20;');
    $detail->easyCell('', 'align:C;font-style:B;paddingY:6');
    $detail->easyCell(utf8Th($detailData[$i]["Part_Type"]), 'align:C;font-style:B;bgcolor:#C8C8C8;');
    $detail->printRow();

    $detail = new easyTable($pdf, '%{40,20,20,20}', 'border:RLB;font-family:THSarabun;font-size:12;valign:M;');
    $detail->easyCell(utf8Th('Package Type'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Qty Package(PCS)'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Qty Assembly record'), 'align:C;font-style:B;');
    $detail->easyCell(utf8Th('Assembly by'), 'align:C;font-style:B;');
    $detail->printRow();

    $detail = new easyTable($pdf, '%{40,20,20,20}', 'border:RTLB;font-family:THSarabun;font-size:16;valign:M;');
    $detail->easyCell(utf8Th($detailData[$i]["Package_Type"]), 'align:C;font-style:B;bgcolor:#C8C8C8;');
    $detail->easyCell(utf8Th($detailData[$i]["Qty_Package"]), 'align:C;font-style:B;bgcolor:#C8C8C8;');
    $detail->easyCell('', 'align:C;font-style:B;');
    $detail->easyCell('', 'align:C;font-style:B;');
    $detail->printRow();
    $header->endTable(1);
    $detail->endTable(1);
    $i++;
    $nn++;
}

$pdf->Output('I', 'WORKORDER_' . $docno . '.pdf');

function utf8Th($v)
{
    return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}
