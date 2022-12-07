<?php
include('../php-barcode/sample/fpdf.php');
include('../php-barcode/php-barcode.php');
include('../../php/connection.php');

if (
   !isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['Part_No'])
   || !isset($_REQUEST['printType']) || !isset($_REQUEST['warter']) || !isset($_REQUEST['Date'])
)
   closeDBT($mysqli, 2, 'ข้อมูลไม่ถูกต้อง 1');
$printerName = checkTXT($mysqli, $_REQUEST['printerName']);
$copy = checkINT($mysqli, $_REQUEST['copy']);
$Part_No = checkTXT($mysqli, $_REQUEST['Part_No']);
$printType = checkTXT($mysqli, $_REQUEST['printType']);
$warter = checkTXT($mysqli, $_REQUEST['warter']);
$Date = checkTXT($mysqli, $_REQUEST['Date']);

$dataset = array();
$sql  = "SELECT 
            Part_No, 
            Side
         FROM
            tbl_part_master tpm
         WHERE
            Part_No = '$Part_No';";
$re1 = sqlError($mysqli, __LINE__, $sql, 1);
if ($re1->num_rows == 0) {
   throw new Exception('ไม่พบข้อมูล Part Number');
}
while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
   $Part_No = $row['Part_No'];
   $Side = $row['Side'];
}


//echo($Side);
$explode = explode('_', $Side);
//echo($explode[0]);

if ($explode[0] == 'RH' || $explode[0] == 'LH') {
   //echo (' 1');
   $Side1 = '';
   $Side2 = $explode[0];
} else {
   //echo (' 2');
   $Side1 = $explode[0];
   $substr = substr($Side, -2, 2);
   $Side2 = $substr;
}
//exit();

$fontSize = 10;
$marge    = 10;   // between barcode and hri in pixel
$x        = 34;  // barcode center
$y        = 14;  // barcode center
$height   = 0.8;   // barcode height in 1D ; module size in 2D
$width    = 0.8;    // barcode height in 1D ; not use in 2D
$angle    = 0;   // rotation in degrees

$code     = $Part_No; // barcode, of course ;)
$rect     = false;
$type     = 'datamatrix';
$black    = '000000'; // color in hexa


// -------------------------------------------------- //
//            ALLOCATE FPDF RESSOURCE
// -------------------------------------------------- //

$pdf = new FPDF('P', 'mm', array(60, 30));
$pdf->AddPage();

// -------------------------------------------------- //
//                      BARCODE
// -------------------------------------------------- //

$data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $type, array('code' => $code, 'rect' => $rect), $width, null);

// -------------------------------------------------- //
//                      HRI
// -------------------------------------------------- //



$pdf->SetFont('Arial', 'B', 9);
$pdf->Text(43, 5, $Date);

$pdf->SetFont('Arial', 'B', 13);
$pdf->Text(10.5, 7, $Side1);

$pdf->SetFont('Arial', 'B', 28);
$pdf->Text(44, 18.5, $Side2);

$length = strlen($Part_No);
//echo $length;
if ($length > 18) {
   $pdf->SetFont('Arial', 'B', 12.5);
   $pdf->Text(7, 27.5, $Part_No);
} else {
   $pdf->SetFont('Arial', 'B', 12.5);
   $pdf->Text(10.5, 27.5, $Part_No);
}

function clean($string)
{
   $string = str_replace(' ', ',', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[-]/', '', $string); // Removes special chars.
}

$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);
$dateoutput = date("Ymd", strtotime($Date));
$PartNoOutPut = clean($Part_No);

$pdf->Output('LABEL_' . $PartNoOutPut . '_' . $dateoutput . '_' . $randomString . '.pdf', 'I');
//$pdf->Output('I', 'LABEL_' . $Part_No . '_' . $Date . '.pdf');

$pdf->Output();
