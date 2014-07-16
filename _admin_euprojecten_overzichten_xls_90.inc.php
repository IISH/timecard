<?php 
// toon: Datum, Handtekening (3x)
$r += 3;
$c = 2;
$arrHandtekeningen = array("Datum", "Handtekening medewerker", "Handtekening projectleider", "Handtekening leidinggevende");
foreach ( $arrHandtekeningen as $handtekening ) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, $handtekening);
	$objPHPExcel->getActiveSheet()->mergeCells("B" . $r . ":F" . $r);
	$r += 4;
}

//die('zzzz');
// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$fnaam = protectFilename($periode . '-' . $employee_name) . ".xlsx";

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $fnaam . "\"");
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>