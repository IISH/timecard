<?php 
// 
$arrAfwezigheden = array("Verlof" => "verlof", "Feestdagen" => "feestdagen", "Ziekte/Dokter" => "ziekte");

// achterhaal naam van persoon
$oEmployee = new class_employee($id, $settings);
$employee_name = $oEmployee->getLastFirstname();

// if no name, use login name
if ( $employee_name == '' || $employee_name == ',' ){
	$employee_name = $oEmployee->getLoginName();
	$employee_name = str_replace('.', ' ', $employee_name);
}

// replace special characters by standard characters
setlocale(LC_ALL, 'en_GB');
$employee_name = str_replace(array('"', '\''), '', iconv("ISO-8859-1", "US-ASCII//TRANSLIT", $employee_name));

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

$projects = array();
$projects = getListOfShowSeparatedProjectsOnReports($projects, $year, 0, 0);

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

/** PHPExcel */
require_once 'PHPExcel/PHPExcel.php';
require_once 'PHPExcel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("IISG")
							 ->setLastModifiedBy("IISG");

$objPHPExcel->setActiveSheetIndex(0); //we are selecting a worksheet

$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
if ( $fitToPage == true ) {
	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
}

// margin is set in inches (0.5cm)
$margin = 0.5 / 2.54;
$marginTB = 1.0 / 2.54;
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft($margin);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight($margin);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom($marginTB);

// zet kolombreedtes
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
for ( $i=1; $i<=$nrOfCols; $i++) {
	$objPHPExcel->getActiveSheet()->getColumnDimension(convertSpreadsheatColumnNumberToColumnCharacter($i+1))->setWidth($widthDataColumns);
}
$objPHPExcel->getActiveSheet()->getColumnDimension(convertSpreadsheatColumnNumberToColumnCharacter($i+1))->setWidth($widthTotalColumn);

$boldLeftStyle = array(
		'font' => array(
			'bold' => true
		)
		, 'alignment' => array(
			'horizontal' => 'left'
		)
	);

$centerStyle = array(
		'alignment' => array(
			'horizontal' => 'center'
		)
	);

$rightStyle = array(
		'alignment' => array(
			'horizontal' => 'right'
		)
	);

$boldStyle = array(
		'font' => array(
			'bold' => true
		)
	);

$boldRightStyle = array(
		'font' => array(
			'bold' => true
		)
		, 'alignment' => array(
			'horizontal' => 'right'
		)
	);

$borderStyle = array(
	'borders' => array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	)
);

$greyBackgroundStyle = array(
	'fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array('rgb'=>'E1E0F7'),
	)
);

// PERIODE
$r++;
$c = 1;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, $periode);
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->mergeCells("A" . $r . ":B" . $r);

// NAAM
$c = 3;
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(fixCol($c), $r, $employee_name . " (IISG)");
$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c) . $r)->applyFromArray($boldLeftStyle);
$objPHPExcel->getActiveSheet()->mergeCells("C" . $r . ":K" . $r);

$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);
$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setSize(18);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

function fixCol($value) {
	return $value-1;
}

function getTimecardUrenGroupedByMonth($id, $year, $pid) {
	global $dbConn;

	$retval = array();

	$yearpostfix = '';
//	if ( $year < 2014 ) {
//		$yearpostfix = '_' . $year;
//	}

	$query = "
SELECT SUBSTR(DateWorked,1,7) AS WORKDATE, SUM(TimeInMinutes) AS AANTAL
FROM `Workhours$yearpostfix`
WHERE Employee=" . $id . " AND DateWorked LIKE '" . $year . "-%'
	AND WorkCode IN (SELECT ID FROM `Workcodes$yearpostfix` WHERE ID=" . $pid . " OR ParentID = " . $pid . ")
GROUP BY SUBSTR(DateWorked,1,7) ";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$retval[$row["WORKDATE"]] = $row["AANTAL"];
	}

	return $retval;
}

function getTimecardUrenGroupedByDay($id, $year, $month, $pid) {
	global $dbConn;

	$retval = array();

	$yearpostfix = '';
//	if ( $year < 2014 ) {
//		$yearpostfix = '_' . $year;
//	}

	$query = "
SELECT SUBSTR(DateWorked,1,10) AS WORKDATE, SUM(TimeInMinutes) AS AANTAL
FROM `Workhours$yearpostfix`
WHERE Employee=" . $id . "
	AND DateWorked LIKE '" . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%'
	AND WorkCode IN (
			SELECT ID FROM `Workcodes$yearpostfix` WHERE ID=" . $pid . " OR ParentID = " . $pid . "
		)
GROUP BY SUBSTR(DateWorked,1,10)
";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$retval[$row["WORKDATE"]] = $row["AANTAL"];
	}

	return $retval;
}

function getProtimeUrenGroupedByDay($protimeId, $year, $month, $view, $timecardid) {
	global $dbConn;

	$retval = array();

	// Verlof
	// 1	Bijzonder verlof
	// 2	Calamiteitenverlof
	// 8	Onbetaald verlof
	// 9	Ouderschapsverlof
	// 10	Sabbatical
	// 11	Studieverlof
	// 16	Zorgverlof
	// 17	Zwangerschapsverlof
	// 7	Levensloop
	// 3	Cursus
	// 19	Compensatie Overuren
	// 12	Vakantie

	// Feestdagen
	// 6	Feestdag

	// Ziekte
	// 15	Ziekte
	// 5	Dokter/Tandarts

	// werk buiten iisg, werk thuis, dienstreis
	// 4	Dienstreis
	// 13	Werk buiten IISG
	// 18	Werk thuis

	$query = "
SELECT SUBSTR(BOOKDATE, 1, 10) AS WORKDATE, SUM(ABSENCE_VALUE) AS AANTAL
FROM protime_p_absence
WHERE PERSNR=" . $protimeId . " AND BOOKDATE LIKE '" . $year . substr('0'.$month,-2) . "%' AND ABSENCE IN ( ";
	switch ( $view ) {
		case "verlof":
			$query .= "1,2,8,9,10,11,16,17,7,3,19,12";
			break;
		case "feestdagen":
			$query .= "6";
			break;
		case "ziekte":
			$query .= "15,5";
			break;
		case "werkbuiten":
			$query .= "4,13,18";
			break;
	}
	$query .= "	) GROUP BY SUBSTR(BOOKDATE, 1, 10) ";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result2 = $stmt->fetchAll();
	foreach ($result2 as $row2) {
		$oD = new TCDateTime();
		$oD->setFromString($row2["WORKDATE"], 'Ymd');
		$retval[ $oD->get()->format("Y-m-d") ] = $row2["AANTAL"];
	}

	if ( $view == 'verlof' ) {
		// achterhaal 'eerder weg'

		$oDate = new class_date( $year, $month, 1 );
		$arrEerderWeg = getEerderNaarHuisGroupedByDay($timecardid, $oDate);
		foreach ( $arrEerderWeg as $ndx => $value ) {
			if ( isset($retval[$ndx]) ) {
				$retval[$ndx] += $value;
			} else {
				$retval[$ndx] = $value;
			}
		}
	}

	return $retval;
}

function getProtimeUren($id, $year, $month, $view, $timecardid) {
	global $dbConn;

	$retval = 0.0;

	// Verlof
	// 1	Bijzonder verlof
	// 2	Calamiteitenverlof
	// 8	Onbetaald verlof
	// 9	Ouderschapsverlof
	// 10	Sabbatical
	// 11	Studieverlof
	// 16	Zorgverlof
	// 17	Zwangerschapsverlof
	// 7	Levensloop
	// 3	Cursus
	// 19	Compensatie Overuren
	// 12	Vakantie

	// Feestdagen
	// 6	Feestdag

	// Ziekte
	// 15	Ziekte
	// 5	Dokter/Tandarts

	// werk buiten iisg, werk thuis, dienstreis
	// 4	Dienstreis
	// 13	Werk buiten IISG
	// 18	Werk thuis

	$query = "SELECT SUM(ABSENCE_VALUE) AS AANTAL FROM protime_p_absence WHERE PERSNR=" . $id . " AND BOOKDATE LIKE '" . $year . substr('0'.$month,-2) . "%' AND ABSENCE IN ( ";
	switch ( $view ) {
		case "verlof":
			$query .= "1,2,8,9,10,11,16,17,7,3,19,12";
			break;
		case "feestdagen":
			$query .= "6";
			break;
		case "ziekte":
			$query .= "15,5";
			break;
		case "werkbuiten":
			$query .= "4,13,18";
			break;
	}
	$query .= "	) ";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result2 = $stmt->fetchAll();
	foreach ($result2 as $row2) {
		$retval += $row2[0];
	}

	// if 'verlof', add also 'eerder weg'
	if ( $view == 'verlof' ) {
		$retval += getEerderNaarHuisMonthTotal($timecardid, new class_date( $year, $month, 1 ));
	}

	return $retval;
}

function getProjectName( $id, $handle, $year ) {
	global $dbConn;

	$retval = '';

	$yearpostfix = '';
//	if ( $year < 2014 ) {
//		$yearpostfix = '_' . $year;
//	}

	$queryPN = "SELECT Description, ProjectnummerEu FROM `Workcodes$yearpostfix` WHERE ID=" . $id;


	$stmt = $dbConn->prepare($queryPN);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $rowPN) {
		$retval = $rowPN["Description"] . " (" . trim($rowPN["ProjectnummerEu"]) . ")";
	}

	$retval = str_replace('()', '', $retval);

	$retval = trim($retval);

	return $retval;
}

function convertMinutesToHours($value) {
	if ( $value == 0 || $value == '' ) {
		$retval = '';
	} else {
		$retval = $value*1.0;
		$retval /= 60;
	}

	return $retval;
}

function setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i) {
	global $objPHPExcel, $greyBackgroundStyle;

	if ( date("w", mktime(0,0,0,$month, $i, $year)) == 0 || date("w", mktime(0,0,0,$month, $i, $year)) == 6 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	}
}

function setNumberFormatForSaturdayAndSunday($c, $r, $year, $month, $i) {
	global $objPHPExcel, $greyBackgroundStyle;

	if ( date("w", mktime(0,0,0,$month, $i, $year)) == 0 || date("w", mktime(0,0,0,$month, $i, $year)) == 6 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00;-###0.00;;@');
	}
}

function getMonthNameInDutch( $m, $length = 3) {
	$retval = '-error-';

	if ( $m >= 1 && $m <= 12 ) {
		$arr = array("Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December");
		$retval = $arr[$m-1];
		$retval = substr($retval, 0, $length);
	}

	return $retval;
}

function getListOfShowSeparatedProjectsOnReports( $retval, $year, $level, $parent_id = 0 ) {
	global $dbConn;

	$yearpostfix = '';
//	if ( $year < 2014 ) {
//		$yearpostfix = '_' . $year;
//	}

	$query = "SELECT * FROM `Workcodes$yearpostfix` ::WHERE:: ORDER BY Description ";

	if ( $level > 0 ) {
		$query = str_replace("::WHERE::", " WHERE ParentID=" . $parent_id, $query);
	} else {
		// TODOXXXSLOW
		$query = str_replace("::WHERE::", " WHERE show_separate_in_reports=1
			AND ID IN (
					SELECT WorkCode FROM `Workhours$yearpostfix` WHERE DateWorked LIKE '$year%' GROUP BY WorkCode
				)
			 ", $query);
	}

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$spaces = str_repeat(' ', $level);
		$id = $row["ID"];

		$parent_id = $row["ParentID"];
		if ( $parent_id == 1 ) {
			$parent_id = 0;
		}

		if ( $level > 0  ) {
			$number_of_children = -1;
		} else {
			$number_of_children = getNumberOfChildren( $id, $year );
		}
		$retval[] = array($spaces, $id, $parent_id, $number_of_children);

		// ONLY ONE LEVEL DEEP
		if ( $number_of_children > 0 && $level == 0) {
			$newlevel = $level+1;
			$retval = getListOfShowSeparatedProjectsOnReports( $retval, $year, $newlevel, $id);
		}
	}

	return $retval;
}

function getNumberOfChildren( $projectId, $year ) {
	global $dbConn;

	$retval = 0;

	$yearpostfix = '';
//	if ( $year < 2014 ) {
//		$yearpostfix = '_' . $year;
//	}

	$query = "SELECT COUNT(*) AS AANTAL FROM `Workcodes$yearpostfix` WHERE ParentID=" . $projectId;

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$retval = $row["AANTAL"];
	}

	return $retval;
}
