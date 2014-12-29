<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

date_default_timezone_set('Europe/London');

$year = substr($protect->request_positive_number_or_empty('get', "y"), 0, 4);
$month = substr($protect->request_positive_number_or_empty('get', "m"), 0, 2);
$id = substr($protect->request_positive_number_or_empty('get', "id"), 0, 4);

$oEmployee = new class_employee($id, $settings);
$oDate = new class_date($year, $month, 1);

if ( $year == '' || $month == '' || $id == '' ) {
	die('go to <a href="admin_euprojecten_overzichten.php">view</a>');
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

$r = 0;
$periode = $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT);
$nrOfCols = 31;
$widthDataColumns = 5.5;
$widthTotalColumn = 7.0;
$fitToPage = true;

include_once "_admin_euprojecten_overzichten_xls_10.inc.php";

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// PROJECT
$r += 2;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Project / Dag");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);
$lastDayOfMonth = date("t", mktime(0,0,0,$month, 1, $year));
for ( $i=1; $i<=31; $i++ ) {
	if ( $lastDayOfMonth < $i ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	} else {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $i);

		// zaterdag/zondag grey background
		setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, "Totaal uren");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

$projectsStartRow = $r+1;
$rememberRows = " ";
foreach ( $projects as $one_project ) {
	$r++;

	if ( $one_project[3] >= 0 ) {
		$rememberRows .= "::CHAR::" . $r . " ";
	}

	$c = 1;
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, $one_project[0] . getProjectName($one_project[1], $oConn->getConnection()));
	if ( $one_project[3] >= 0 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);

	$arrUren = getTimecardUrenGroupedByDay($id, $year, $month, $one_project[1]);
	for ( $i = 1; $i <= 31; $i++ ) {
		if ( $lastDayOfMonth < $i ) {
			$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
		} else {

			if ( $one_project[3] > 0 ) {
				$uren = "=sum(" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+1) . ":" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+$one_project[3]) . ")";
			} else {
				$uren = $arrUren[$year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT)];

				//
				$uren = convertMinutesToHours($uren);
			}

			$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00;-###0.00;;@');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);

			// zaterdag/zondag grey background
			setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i);
			setNumberFormatForSaturdayAndSunday($c, $r, $year, $month, $i);
		}

		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('AG' . $r, '=SUM(B' . $r . ':AF' . $r . ')');
	if ( $one_project[3] >= 0 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// Afdeling
$r++;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Afdeling");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);
for ( $i = 1; $i <= 31; $i++ ) {
	if ( $lastDayOfMonth < $i ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	} else {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00;-###0.00;;@');

		$formulePart1 = "-" . str_replace('::CHAR::', convertSpreadsheatColumnNumberToColumnCharacter($c+$i), str_replace(' ', '-', trim($rememberRows)));
		$uren = "=" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+1) . $formulePart1;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);

		// zaterdag/zondag grey background
		setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i);
		setNumberFormatForSaturdayAndSunday($c, $r, $year, $month, $i);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
$objPHPExcel->getActiveSheet()->setCellValue('AG' . $r, '=SUM(B' . $r . ':AF' . $r . ')');
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// Totaal
$r++;
$totalsStartRow = $r;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Totaal");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);
for ( $i = 1; $i <= 31; $i++ ) {
	if ( $lastDayOfMonth < $i ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	} else {
		$t_date = array();
		$t_date["y"] = $year;
		$t_date["m"] = substr('0'.$month,-2);
		$t_date["d"] = substr('0'.$i,-2);

		$uren = "=" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+4) . "-SUM(" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+1) . ":" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+3) . ")";
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);

		// zaterdag/zondag grey background
		setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i);
		setNumberFormatForSaturdayAndSunday($c, $r, $year, $month, $i);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
$objPHPExcel->getActiveSheet()->setCellValue('AG' . $r, '=SUM(B' . $r . ':AF' . $r . ')');
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

$startSecondPart = $r+1;

foreach ($arrAfwezigheden as $a => $b ) {
	$r++;
	$c = 1;
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, $a);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);
	$arrUren = getProtimeUrenGroupedByDay($oEmployee->getProtimeId(), $year, $month, $b, $id);
	for ( $i = 1; $i <= 31; $i++ ) {
		if ( $lastDayOfMonth < $i ) {
			$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
		} else {
			$uren = $arrUren[$year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT)];
			$uren = convertMinutesToHours($uren);
			$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);

			// zaterdag/zondag grey background
			setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i);
			setNumberFormatForSaturdayAndSunday($c, $r, $year, $month, $i);
		}
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
	}

	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('AG' . $r, '=SUM(B' . $r . ':AF' . $r . ')');
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// Dagtotaal
$r++;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Dagtotaal");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);
$arrUren = $oEmployee->getProtimeDayTotalGroupedByDay($t_date);
for ( $i = 1; $i <= 31; $i++ ) {
	if ( $lastDayOfMonth < $i ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	} else {
		$t_date = array();
		$t_date["y"] = $year;
		$t_date["m"] = substr('0'.$month,-2);
		$t_date["d"] = substr('0'.$i,-2);

		$uren = $arrUren[$t_date["y"] . $t_date["m"] . $t_date["d"]];

		$uren = convertMinutesToHours($uren);
		if ( $uren == '' ) {
			$uren = 0;
		}

		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);

		// zaterdag/zondag grey background
		setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i);
		setNumberFormatForSaturdayAndSunday($c, $r, $year, $month, $i);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
$objPHPExcel->getActiveSheet()->setCellValue('AG' . $r, '=SUM(B' . $r . ':AF' . $r . ')');
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// close data entry for specified employee
closeDataEntry($year, $month, $id);

//
include_once "_admin_euprojecten_overzichten_xls_90.inc.php";
