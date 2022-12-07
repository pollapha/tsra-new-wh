<?php 
	$clientnumber = "C:/tsra_wh_label/";
	$pdffiles = array();
	$valid_files = array('pdf');
	if(is_dir($clientnumber)){
	foreach(scandir($clientnumber) as $file){
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if(in_array($ext, $valid_files)){
		array_push($pdffiles, $file);
		}   
	}
	}
	echo json_encode($pdffiles);
?>