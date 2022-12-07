<?php
 include 'fpdf.php';
 include 'exfpdf.php';
 include 'PDF_Code128.php';
 include 'easyTable.php';
 

 class PDF extends PDF_Code128
 {
    var $headerData;
    var $instance;
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
      $header=new easyTable($this->instance, '%{25, 50, 25,}','border:0;font-family:Trirong;font-size:15; font-style:B;');
      $header->easyCell('', 'img:images/ttv-logo.gif, w35;align:L','');
      $header->easyCell('TITAN-VNS AUTOLOGISTICS CO., LTD.', 'valign:M;align:C');
      $header->easyCell('', 'img:images/aat-ford.jpg, w35;align:R');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance, '%{30,25,7.5,37.5}','border:1;font-family:Trirong;font-size:8; font-style:B;valign:M;');
      $header->easyCell('PICKUP SHEET EDC PROJECT', '');
      $header->easyCell('PUS : '.$v['pusChild'], 'rowspan:2;valign:T;align:C');
      $header->easyCell(utf8Th("Actual Time\nเวลาจริง"), 'rowspan:2;valign:B;align:C;bgcolor:#e0e0e0;');
      $header->easyCell('', 'rowspan:2;img:images/truckseal.jpg,w90,h15;align:C;');
      $header->printRow();

      $header->easyCell('PICKUP Date : '.$v['pusDate'], '');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(100,28,$v['pusChild'],55,6);
    }
    function Footer()
    {
      $this->SetXY(-20,-10);
      $this->SetFont('Arial','I',8);
      $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
 }
 $headerData = array('pusDate'=>'2017-10-11','pusChild'=>'PUS17120100001');
 $pdf=new PDF('L');
 $pdf->AddFont('Trirong','','Trirong-Regular.php');
 $pdf->AddFont('Trirong','B','Trirong-Bold.php');
 $pdf->SetAutoPageBreak(true,10);
 $pdf->setInstance($pdf);
 $pdf->setHeaderData($headerData);
 $pdf->AddPage();
 $pdf->AddPage();
 $pdf->AddPage();
 $pdf->Output(); 

 function utf8Th($v)
 {
   return iconv( 'UTF-8','TIS-620//TRANSLIT',$v);
 }
 ?>