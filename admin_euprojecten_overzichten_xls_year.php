<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

date_default_timezone_set('Europe/London');

$year = substr($protect->request_positive_number_or_empty('get', "y"), 0, 4);
$id = substr($protect->request_positive_number_or_empty('get', "id"), 0, 4);

$oEmployee = new class_employee($id, $settings);
$oDate = new class_date($year, 1, 1);

if ( $year == '' || $id == '' ) {
	die('go to <a href="admin_euprojecten_overzichten.php">view</a>');
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

$r = 0;
$periode = $year;
$nrOfCols = 16;
$widthDataColumns = 7.0;
$widthTotalColumn = 11.0;
$fitToPage = false;

include_once "_admin_euprojecten_overzichten_xls_10.inc.php";

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// PROJECT
$r += 2;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Project");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);
$month = 0;
for ( $i=1; $i<=16; $i++ ) {
	$month++;

	if ( $i % 4 == 0 ) {
		// QUARTER COLUMN
		$month--;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, "Q" . ($i/4));
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	} else {
		// DATA COLUMN
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, getMonthNameInDutch($month));
	}

	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($centerStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, "Totaal uren");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// TODOXXXSLOW - duurt 3 seconden
$projectsStartRow = $r+1;
$rememberRows = " ";
foreach ( $projects as $one_project ) {
	$r++;

	if ( $one_project[3] >= 0 ) {
		$rememberRows .= "::CHAR::" . $r . " ";
	}

	$c = 1;
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, $one_project[0] . getProjectName( $one_project[1], $dbConn, $year ) );
	if ( $one_project[3] >= 0 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);

	$month = 0;
	$arrUren = getTimecardUrenGroupedByMonth($id, $year, $one_project[1]);

	for ( $i = 1; $i <= 16; $i++ ) {
		$month++;

		if ( $i % 4 == 0 ) {
			// QUARTER COLUMN
			$month--;
			$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);

			if ( $one_project[3] >= 0 ) {
				$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldStyle);
			}
		} else {
			// DATA COLUMN

			if ( $one_project[3] > 0 ) {
				$uren = "=sum(" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+1) . ":" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+$one_project[3]) . ")";
			} else {
				//
				$uren = $arrUren[$year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)];

				//
				$uren = convertMinutesToHours($uren);
			}

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);
		}

		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00;-###0.00;;@');
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($rightStyle);
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
	}

	// quarter columns
	$objPHPExcel->getActiveSheet()->setCellValue('E' . $r, '=SUM(B' . $r . ':D' . $r . ')');
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $r, '=SUM(F' . $r . ':H' . $r . ')');
	$objPHPExcel->getActiveSheet()->setCellValue('M' . $r, '=SUM(J' . $r . ':L' . $r . ')');
	$objPHPExcel->getActiveSheet()->setCellValue('Q' . $r, '=SUM(N' . $r . ':P' . $r . ')');

	// last column
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('R' . $r, '=E' . $r . '+I' . $r . '+M' . $r . '+Q' . $r);
	if ( $one_project[3] >= 0 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// Afdeling
$r++;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Afdeling");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);

$month = 0;
for ( $i = 1; $i <= 16; $i++ ) {
	$month++;

	if ( $i % 4 == 0 ) {
		// QUARTER COLUMN
		$month--;
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldStyle);
	} else {
		// DATA COLUMN
		$formulePart1 = "-" . str_replace('::CHAR::', convertSpreadsheatColumnNumberToColumnCharacter($c+$i), str_replace(' ', '-', trim($rememberRows)));
		$uren = "=" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+1) . $formulePart1;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);
	}

	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00;-###0.00;;@');
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}

$objPHPExcel->getActiveSheet()->setCellValue('E' . $r, '=SUM(B' . $r . ':D' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('I' . $r, '=SUM(F' . $r . ':H' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('M' . $r, '=SUM(J' . $r . ':L' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('Q' . $r, '=SUM(N' . $r . ':P' . $r . ')');

$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
$objPHPExcel->getActiveSheet()->setCellValue('R' . $r, '=E' . $r . '+I' . $r . '+M' . $r . '+Q' . $r);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// Totaal
$r++;
$totalsStartRow = $r;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Totaal");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);

$month = 0;
for ( $i = 1; $i <= 16; $i++ ) {
	$month++;

	if ( $i % 4 == 0 ) {
		// QUARTER COLUMN
		$month--;
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldStyle);
	} else {
		// DATA COLUMN
		$t_date = array();
		$t_date["y"] = $year;
		$t_date["m"] = substr('0'.$month,-2);
		$t_date["d"] = substr('0'.$i,-2);

		$uren = "=" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+4) . "-SUM(" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+1) . ":" . convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . ($r+3) . ")";
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
	}

	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}

$objPHPExcel->getActiveSheet()->setCellValue('E' . $r, '=SUM(B' . $r . ':D' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('I' . $r, '=SUM(F' . $r . ':H' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('M' . $r, '=SUM(J' . $r . ':L' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('Q' . $r, '=SUM(N' . $r . ':P' . $r . ')');

$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
$objPHPExcel->getActiveSheet()->setCellValue('R' . $r, '=E' . $r . '+I' . $r . '+M' . $r . '+Q' . $r);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

$startSecondPart = $r+1;

foreach ($arrAfwezigheden as $a => $b ) {
	$r++;
	$c = 1;
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, $a);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);

	$month = 0;
	for ( $i = 1; $i <= 16; $i++ ) {
		$month++;

		if ( $i % 4 == 0 ) {
			// QUARTER COLUMN
			$month--;
			$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
			$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldStyle);
		} else {
			// DATA COLUMN
			// TODOXXXYEARSLOW
			$uren = getProtimeUren( $oEmployee->getProtimeId(), $year, $month, $b, $id );
			$uren = convertMinutesToHours($uren);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);
		}
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
	}

	$objPHPExcel->getActiveSheet()->setCellValue('E' . $r, '=SUM(B' . $r . ':D' . $r . ')');
	$objPHPExcel->getActiveSheet()->setCellValue('I' . $r, '=SUM(F' . $r . ':H' . $r . ')');
	$objPHPExcel->getActiveSheet()->setCellValue('M' . $r, '=SUM(J' . $r . ':L' . $r . ')');
	$objPHPExcel->getActiveSheet()->setCellValue('Q' . $r, '=SUM(N' . $r . ':P' . $r . ')');

	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
	$objPHPExcel->getActiveSheet()->setCellValue('R' . $r, '=E' . $r . '+I' . $r . '+M' . $r . '+Q' . $r);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
}

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

// Dagtotaal
$r++;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, "Dagtotaal");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($borderStyle);
$month = 0;
for ( $i = 1; $i <= 16; $i++ ) {
	$month++;

	if ( $i % 4 == 0 ) {
		// QUARTER COLUMN
		$month--;
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	} else {
		// DATA COLUMN

		$t_date = array();
		$t_date["y"] = $year;
		$t_date["m"] = substr('0'.$month,-2);
		$t_date["d"] = 0;
		// TODOXXXSLOW SUPERTRAAG
		$uren = $oEmployee->getProtimeMonthTotal($t_date);
		$uren = convertMinutesToHours($uren);
		if ( $uren == '' ) {
			$uren = 0;
		}
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c+$i), $r, $uren);

	}
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
	$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
}

$objPHPExcel->getActiveSheet()->setCellValue('E' . $r, '=SUM(B' . $r . ':D' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('I' . $r, '=SUM(F' . $r . ':H' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('M' . $r, '=SUM(J' . $r . ':L' . $r . ')');
$objPHPExcel->getActiveSheet()->setCellValue('Q' . $r, '=SUM(N' . $r . ':P' . $r . ')');

$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00');
$objPHPExcel->getActiveSheet()->setCellValue('R' . $r, '=E' . $r . '+I' . $r . '+M' . $r . '+Q' . $r);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($boldRightStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($borderStyle);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

include_once "_admin_euprojecten_overzichten_xls_90.inc.php";
