<?php
include('../php-barcode/sample/fpdf.php');
include('../php-barcode/php-barcode.php');
include('../../php/connection.php');


if (
   !isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['WorkOrder'])
   || !isset($_REQUEST['printType']) || !isset($_REQUEST['warter']) || !isset($_REQUEST['Date'])
)
   closeDBT($mysqli, 2, 'ข้อมูลไม่ถูกต้อง 1');
$printerName = checkTXT($mysqli, $_REQUEST['printerName']);
$copy = checkINT($mysqli, $_REQUEST['copy']);
$WorkOrder = checkTXT($mysqli, $_REQUEST['WorkOrder']);
$printType = checkTXT($mysqli, $_REQUEST['printType']);
$warter = checkTXT($mysqli, $_REQUEST['warter']);
$Date = checkTXT($mysqli, $_REQUEST['Date']);

$dataset = array();
$sql  = "SELECT 
            tap.Part_No, 
            tap.WorkOrder,
            Side, 
            Assembly_Date, 
            Receive_Date, 
            tap.Qty_Package-tap.Used AS Qty_Package
         FROM
            tbl_assembly_pre tap
               INNER JOIN 
            tbl_part_master tpm ON tap.Part_ID = tpm.Part_ID
               INNER JOIN 
            tbl_receiving_pre trp ON tap.WorkOrder = trp.WorkOrder
               INNER JOIN 
            tbl_receiving_header trh ON trp.Receiving_Header_ID = trh.Receiving_Header_ID
         WHERE
            tap.WorkOrder = '$WorkOrder';";
$re1 = sqlError($mysqli, __LINE__, $sql, 1);
if ($re1->num_rows == 0) {
   throw new Exception('ไม่พบข้อมูล' . __LINE__);
}
while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
   $Part_No = $row['Part_No'];
   $WorkOrder = $row['WorkOrder'];
   $Side = $row['Side'];
   $Qty_Package = $row['Qty_Package'];
   $Assembly_Date = $row['Assembly_Date'];
   $Receive_Date = $row['Receive_Date'];
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



$pdf = new FPDF('P', 'mm', array(60, 30));
$pdf->AddPage();

$i = 1;
$countrow = 1;
while ($i <= $Qty_Package) {
   if ($countrow > 1) {
      $pdf->AddPage();
      $countrow = 1;
   }
   $countrow++;

   //BARCODE
   $data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $type, array('code' => $code, 'rect' => $rect), $width, null);

   if ($i <= 9) {
      $i = '0' . $i;
   }

   $pdf->SetFont('Arial', 'B', 8);
   $pdf->Text(38, 5, $WorkOrder . '-' . $i);

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
   $i++;
}

$pdf->Output('../../label_file/doc-TSRAWH_PRINT_LABEL-' . '' . $Qty_Package . '' . '.pdf', 'F');
$pdf->Output('../../label_file/doc-TSRAWH_PRINT_LABEL-' . '' . $Qty_Package . '' . '.pdf', 'I');

// $pdf->Output('C:/tsra_wh_label/doc-TSRAWH_PRINT_LABEL-' . '' . $Qty_Package . '' . '.pdf', 'F');
// $pdf->Output('C:/tsra_wh_label/doc-TSRAWH_PRINT_LABEL-' . '' . $Qty_Package . '' . '.pdf', 'I');


// $dir = 'C:/tsra_wh_label/';
// $myfiles = array_diff(scandir($dir), array('.', '..'));
// //print_r($myfiles);
// $filename = $myfiles[2];
// $file = $dir . $filename;

// if (file_exists($file)) {
// 	header('Content-Description: File Transfer');
// 	header('Content-Type: application/octet-stream');
// 	header('Content-Disposition: attachment; filename="' . basename($file) . '"');
// 	header('Expires: 0');
// 	header('Cache-Control: must-revalidate');
// 	header('Pragma: public');
// 	header('Content-Length: ' . filesize($file));
// 	readfile($file);

//    unlink(urldecode($file));
// 	exit;
// }


// $localIP = getHostByName(getHostName());
// // echo $localIP; 

// function movetype($ext, $src, $dest)
// {
//    // (A) CREATE DESTINATION FOLDER
//    if (!file_exists($dest)) {
//       mkdir($dest);
//       echo "$dest created\r\n";
//    }

//    // (B) GET ALL FILES
//    $files = glob($src . "*.{" . $ext . "}", GLOB_BRACE);

//    // (C) MOVE
//    if (count($files) > 0) {
//       foreach ($files as $f) {
//          $moveTo =  $dest . basename($f);
//          echo rename($f, $moveTo)
//             ? "$f moved to $moveTo\r\n"
//             : "Error moving $f to $moveTo\r\n";
//       }
//    }
// }
// movetype("pdf", "C:/tsra_wh_label/", $localIP . '/C:/tsra_wh_label/');
//movetype("pdf", "C:/tsra_wh_label/", '');


//$localIP = getHostByName(getHostName());
// echo $localIP; 
//$localIP = $_SERVER['REMOTE_ADDR'];
// echo 'User IP Address - '.$_SERVER['REMOTE_ADDR'];
// exit();

// echo realpath('C:\tsra_wh_label\\'), PHP_EOL;
// exit();

/*url of zipped file at old server*/
//$file = '../../label_file/doc-TSRAWH_PRINT_LABEL-' . '' . $Qty_Package . '' . '.pdf';
 
// /*what should it name at new server*/
// $dest = 'C:/tsra_wh_label/doc-TSRAWH_PRINT_LABEL-' . '' . $Qty_Package . '' . '.pdf';
 
// /*get file contents and create same file here at new server*/
// $data = file_get_contents($file);
// $handle = fopen($dest,"w");
// fwrite($handle, $data);
// fclose($handle);
// echo 'Copied Successfully.';