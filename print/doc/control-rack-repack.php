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
	tph.Serial_Number,
	BIN_TO_UUID(tpp.Palletizing_Pre_ID, TRUE) AS Palletizing_Pre_ID,
	BIN_TO_UUID(tpp.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
	DATE_FORMAT(tph.Palletizing_Date, '%d/%m/%y') AS Palletizing_Date,
	tpp.Serial_ID,
	tpp.Part_No,
	tpm.Part_Name,
	tpm.Model,
	tpm.Part_Type,
	tpm.Type,
	tph.Total_Qty,
	tpp.Qty_Package,
	tpp.Area,
	tcm.Customer_Code,
	tph.Status,
	DATE_FORMAT(tph.Confirm_DateTime, '%d/%m/%y %H:%i') AS Confirm_DateTime,
	Status_Working,
	tph.Pick,
	Package_Type
FROM
	tbl_palletizing_pre tpp
		INNER JOIN
	tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
		INNER JOIN
	tbl_inventory tiv ON tpp.Serial_ID = tiv.Serial_ID
		INNER JOIN
	tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = tiv.Receiving_Pre_ID
		INNER JOIN
	tbl_part_master tpm ON tpp.Part_ID = tpm.Part_ID
		LEFT JOIN
	tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
	tph.Serial_Number = '$doc'
		AND tpm.Type = 'End cap'
		AND tph.Status = 'COMPLETE'
GROUP BY Serial_Number;";

// exit($q1);

$q1  .= "SELECT 
	tph.Serial_Number,
	BIN_TO_UUID(tpp.Palletizing_Pre_ID, TRUE) AS Palletizing_Pre_ID,
	BIN_TO_UUID(tpp.Palletizing_Header_ID, TRUE) AS Palletizing_Header_ID,
	DATE_FORMAT(tph.Palletizing_Date, '%d/%m/%y') AS Palletizing_Date,
	tpp.Serial_ID,
	tpp.Part_No,
	tpm.Part_Name,
	tpm.Model,
	tpm.Part_Type,
	tpm.Type,
	tph.Total_Qty,
	tpp.Qty_Package,
	tpp.Area,
	tcm.Customer_Code,
	tph.Status,
	DATE_FORMAT(tph.Confirm_DateTime, '%d/%m/%y %H:%i') AS Confirm_DateTime,
	Status_Working,
	tph.Pick,
	Package_Type
FROM
	tbl_palletizing_pre tpp
		INNER JOIN
	tbl_palletizing_header tph ON tpp.Palletizing_Header_ID = tph.Palletizing_Header_ID
		INNER JOIN
	tbl_inventory tiv ON tpp.Serial_ID = tiv.Serial_ID
		INNER JOIN
	tbl_receiving_pre trp ON tiv.Receiving_Pre_ID = tiv.Receiving_Pre_ID
		INNER JOIN
	tbl_part_master tpm ON tpp.Part_ID = tpm.Part_ID
		LEFT JOIN
	tbl_customer_master tcm ON tpm.Customer_ID = tcm.Customer_ID
WHERE
	tph.Serial_Number = '$doc'
		AND tpm.Type = 'End cap'
		AND tph.Status = 'COMPLETE'
GROUP BY tpp.Serial_ID, tpp.Part_No
ORDER BY Serial_Number DESC , Serial_ID ASC;";
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
	function __construct($orientation='P', $unit='mm', $format='A4')
	{
		parent::__construct($orientation,$unit,$format);
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
		$header->easyCell('', 'img:images/abt-logo.gif, w30;align:C', '');
      $header->easyCell('ALBATROSS LOGISTICS CO., LTD.
	  336/7 MOO 7 BOWIN, SRIRACHA CHONBURI 20230
	  Phone +66 38 058 021, +66 38 058 081-2
	  Fax : +66 38 058 007
	  ', 'valign:M;align:L');
      $header->easyCell($v[0]['Serial_Number'], 'valign:B;align:C');
      $header->printRow();
      $header->endTable(2);
      	

      	$header=new easyTable($this->instance, '%{100}','border:0;font-family:THSarabun;font-size:20; font-style:B;');
      	$header->easyCell(utf8Th('CONTROL RACK'), 'valign:M;align:C;border:TB');
      	$header->printRow();
      	$header->endTable(1);

        $header=new easyTable($this->instance, '%{20,15,15,20,15,15}','border:0;font-family:THSarabun;font-size:14;');
        $header->easyCell("Receipt Date Time :", 'valign:T;align:L;font-style:B;');
        $header->easyCell(utf8Th($v[0]['Palletizing_Date']), 'valign:T;align:L;');
        $header->easyCell("Package ID :", 'valign:T;align:L;font-style:B;');
        $header->easyCell(utf8Th($v[0]['Serial_Number']), 'valign:T;align:L;');
		$header->easyCell(utf8Th('Part Type : '), 'valign:T;align:L;font-style:B;');
		$header->easyCell($v[0]['Type'], 'valign:T;align:L;');
        $header->printRow();
        // $header->easyCell("Customer : ", 'valign:T;align:L;font-style:B;');
		// $header->easyCell("TSRA", 'valign:T;align:L;');
        //$header->easyCell($v[0]['Customer_Code'], 'valign:T;align:L;');
        $header->printRow();
        $header->endTable(2);

	    $headdetail =new easyTable($this->instance, '{10,35,45,70,20,25,20,35}',
	    'width:300;border:1;font-family:THSarabun;font-size:10; font-style:B;bgcolor:#C8C8C8;');
		$headdetail->easyCell(utf8Th('No.'), 'align:C');
        $headdetail->easyCell(utf8Th('Package Number'), 'align:C');
        $headdetail->easyCell(utf8Th('Part Number'), 'align:C');
        $headdetail->easyCell(utf8Th('Part Name'), 'align:C');
		$headdetail->easyCell(utf8Th('Model'), 'align:C');
        $headdetail->easyCell(utf8Th('Package Type'), 'align:C');
		$headdetail->easyCell(utf8Th('Qty (PCS)'), 'align:C');
        $headdetail->easyCell(utf8Th('Status'), 'align:C');
        //$headdetail->easyCell(utf8Th('Remark'), 'align:C');
		$headdetail->printRow(); 
		$headdetail->endTable(0);

		$this->instance->Code128(145, 20, $v[0]['Serial_Number'], 55, 7);
  	}
  	function Footer()
  	{
  		$this->SetXY(-20,0);
	    $this->SetFont('THSarabun','I',8);
	    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
  	}
}

$pdf=new PDF('P');

$pdf->AddFont('THSarabun','','THSarabun.php');
$pdf->AddFont('THSarabun','I','THSarabun Italic.php');
$pdf->AddFont('THSarabun','B','THSarabun Bold.php');
$pdf->AddFont('THSarabun','BI','THSarabun Bold Italic.php');
$pdf->setInstance($pdf);
$pdf->setHeaderData($headerData);
$pdf->AddPage();
$docno = $headerData[0]['Serial_Number'];
$pdf->SetTitle($docno);
$detail =new easyTable($pdf, '{10,35,45,70,20,25,20,35}','width:300;border:1;font-family:THSarabun;font-size:10;valign:M;');
$data = sizeof($detailData);
//echo($data);
// หน้าละ15row
$pagebreak = 18;
$i = 0;
$countrow = 1;
$nn = 1;
$sumqty=0;
$sumBoxes=0;
$sumCBM=0;
while ( $i <  $data)
{
if ($countrow > $pagebreak) 
{
  $pdf->AddPage();
  $countrow = 1;
}
$countrow++;
$x=$pdf->GetX();
$y=$pdf->GetY();
$detail->easyCell(utf8Th($nn), 'align:C');
$detail->easyCell(utf8Th($detailData[$i]["Serial_ID"]), 'align:C;font-style:B;font-size:10;');
$detail->easyCell(utf8Th($detailData[$i]["Part_No"]), 'align:C;font-style:B;font-size:10;');
$detail->easyCell(utf8Th($detailData[$i]["Part_Name"]), 'align:L;font-size:9;');
$detail->easyCell(utf8Th($detailData[$i]["Model"]), 'align:C;font-size:12;');
$detail->easyCell(utf8Th($detailData[$i]["Package_Type"]), 'align:C;font-size:12;');
$detail->easyCell(utf8Th($detailData[$i]["Qty_Package"]), 'align:C;font-style:B;font-size:14;');
$detail->easyCell(utf8Th($detailData[$i]["Status_Working"]), 'align:C;font-style:B;font-size:10;');
//$detail->easyCell(utf8Th(''), 'align:C;font-style:B;font-size:12;');
$detail->printRow();
$sumqty += $detailData[$i]['Qty_Package'];
$i++;$nn++;

}
$detail->easyCell(utf8Th('Total :'), 'align:R;font-style:B;;colspan:6;font-size:14;');
$detail->easyCell(utf8Th($sumqty), 'align:C;font-style:B;font-size:14;');
$detail->easyCell(utf8Th(''), 'align:C');
$detail->easyCell(utf8Th(''), 'align:C;font-size:14;');
$detail->easyCell(utf8Th(''), 'align:C;colspan:3');
$detail->printRow();
$detail->endTable(10);

// $lastfooter =new easyTable($pdf, '%{20,30,20,30}','width:300;border:0;font-family:THSarabun;font-size:12;');
// $lastfooter->easyCell(utf8Th('Data Entry By :'), 'align:C;font-size:14;');
// $lastfooter->easyCell(utf8Th('____________________'), 'align:C;font-size:14;');
// $lastfooter->easyCell(utf8Th('Check By :'), 'align:C;font-size:14;');
// $lastfooter->easyCell(utf8Th('____________________'), 'align:C;font-size:14;');
// $lastfooter->printRow();
// $lastfooter->endTable(3);

$pdf->Output();

function utf8Th($v)
{
	return iconv( 'UTF-8','TIS-620//TRANSLIT',$v);
}
