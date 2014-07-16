<?php 
// 
$arrAfwezigheden = array("Verlof" => "verlof", "Feestdagen" => "feestdagen", "Ziekte/Dokter" => "ziekte");

// achterhaal naam van persoon
$oEmployee = new class_employee($id, $settings);
$employee_name = $oEmployee->getLastname() . ', ' . $oEmployee->getFirstname();

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 

$projects = array();
$projects = getListOfShowSeparatedProjectsOnReports($projects, $year, 0, 0);
//debug($projects);
// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

/** PHPExcel */
require_once 'PHPExcel/PHPExcel.php';
require_once('PHPExcel/PHPExcel/IOFactory.php');

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
//$objPHPExcel->getActiveSheet()->getPageMargins()->setTop($marginTB);
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

// TODOEXPLAIN
function fixCol($value) {
	return $value-1;
}

// TODOEXPLAIN
function convertSpreadsheatColumnNumberToColumnCharacter($i, $choices = "ABCDEFGHIJKLMNOPQRSTUVWXYZ") {
	$len = strlen($choices);

	$mod = ($i-1) % $len;
	if ( $i - ($mod+1) > 0 ) {
		$rest = ($i - ($mod+1))/$len;
		$retval = convertSpreadsheatColumnNumberToColumnCharacter($rest, $choices) . substr($choices, $mod, 1);
	} else {
		$retval = substr($choices, $mod, 1);
	}

	return $retval;
}

// TODOEXPLAIN
function getTimecardUrenGroupedByMonth($id, $year, $pid) {
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$retval = array();

	$query = "SELECT SUBSTR(DateWorked,1,7) AS WORKDATE, SUM(TimeInMinutes) AS AANTAL FROM Workhours WHERE Employee=" . $id . " ::DATE:: "
		. " AND WorkCode IN (SELECT ID FROM Workcodes WHERE ID=" . $pid . " OR ParentID = " . $pid . ") ";

	$query = str_replace("::DATE::", " AND DateWorked LIKE '" . $year . "-%' ", $query);

	$query .= " GROUP BY SUBSTR(DateWorked,1,7) ";

	//debug($query);

	$result = mysql_query($query, $oConn->getConnection());
	while ($row = mysql_fetch_assoc($result)) {
		$retval[$row["WORKDATE"]] = $row["AANTAL"];
	}

	mysql_free_result($result);

	return $retval;
}

// TODOEXPLAIN
function getTimecardUrenGroupedByDay($id, $year, $month, $pid) {
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$retval = array();

	$query = "
SELECT SUBSTR(DateWorked,1,10) AS WORKDATE, SUM(TimeInMinutes) AS AANTAL
FROM Workhours
WHERE Employee=" . $id . "
	AND DateWorked LIKE '" . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%'
	AND WorkCode IN (
			SELECT ID FROM Workcodes WHERE ID=" . $pid . " OR ParentID = " . $pid . "
		)
GROUP BY SUBSTR(DateWorked,1,10)
";

	$result = mysql_query($query, $oConn->getConnection());
	while ($row = mysql_fetch_assoc($result)) {
		$retval[$row["WORKDATE"]] = $row["AANTAL"];
	}

	mysql_free_result($result);

	return $retval;
}

// TODOEXPLAIN
function getTimecardUren($id, $year, $month, $day, $pid) {
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$retval = 0;

	$query = "SELECT SUM(TimeInMinutes) AS AANTAL FROM Workhours WHERE Employee=" . $id . " ::DATE:: "
		. " AND WorkCode IN (SELECT ID FROM Workcodes WHERE ID=" . $pid . " OR ParentID = " . $pid . ") ";

	if ( $day > 0 ) {
		$query = str_replace("::DATE::", " AND DateWorked LIKE '" . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT) . "%' ", $query);
	} else {
		$query = str_replace("::DATE::", " AND DateWorked LIKE '" . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%' ", $query);
	}

//die( $query );

	$result = mysql_query($query, $oConn->getConnection());
	if ($row = mysql_fetch_row($result)) {
		$retval = $row[0];
	}

	mysql_free_result($result);

	return $retval;
}

// TODOEXPLAIN
function getProtimeUrenGroupedByDay($protimeId, $year, $month, $view, $timecardid) {
	global $settings;

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

	$query = "SELECT SUBSTR(BOOKDATE, 1, 10) AS WORKDATE, SUM(ABSENCE_VALUE) AS AANTAL FROM PROTIME_P_ABSENCE WHERE PERSNR=" . $protimeId . " AND BOOKDATE LIKE '" . $year . substr('0'.$month,-2) . "%' ";

	$query .= "	AND ABSENCE IN ( ";
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

//echo $query . ' +<br>';

	$oTc = new class_mysql($settings, 'timecard');
	$oTc->connect();

	$result2 = mysql_query($query, $oTc->getConnection());

	while ($row2 = mysql_fetch_assoc($result2)) {
		$retval[$row2["WORKDATE"]] = $row2["AANTAL"];
	}
	mysql_free_result($result2);

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

// TODOEXPLAIN
function getProtimeUren($id, $year, $month, $day, $view, $timecardid) {
	global $settings;

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

	if ( $day > 0 ) {
		$query = "SELECT SUM(ABSENCE_VALUE) AS AANTAL FROM PROTIME_P_ABSENCE WHERE PERSNR=" . $id . " AND BOOKDATE='" . $year . substr('0'.$month,-2) . substr('0'.$day,-2) . "' ";
	} else {
		$query = "SELECT SUM(ABSENCE_VALUE) AS AANTAL FROM PROTIME_P_ABSENCE WHERE PERSNR=" . $id . " AND BOOKDATE LIKE '" . $year . substr('0'.$month,-2) . "%' ";
	}
	$query .= "	AND ABSENCE IN ( ";
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

//echo $query . ' +<br>';

	$oTc = new class_mysql($settings, 'timecard');
	$oTc->connect();

	$result2 = mysql_query($query, $oTc->getConnection());

	while ($row2 = mysql_fetch_row($result2)) {
		$retval += $row2[0];
	}
	mysql_free_result($result2);

	if ( $view == 'verlof' ) {
		// achterhaal ook 'eerder weg'

		$oDate = new class_date( $year, $month, $day );
		if ( $day > 0 ) {
			$eerderweg = getEerderNaarHuisDayTotal($timecardid, $oDate);
		} else {
			$eerderweg = getEerderNaarHuisMonthTotal($timecardid, $oDate);
		}
		$retval += $eerderweg;
	}

	return $retval;
}

// TODOEXPLAIN
function getProjectName( $id, $handle ) {
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$retval = '';

	$queryPN = "SELECT Description, ProjectnummerEu FROM Workcodes WHERE ID=" . $id;

	$resultPN = mysql_query($queryPN, $oConn->getConnection());
	if ($rowPN = mysql_fetch_array($resultPN)) {
		$retval = $rowPN["Description"] . " (" . trim($rowPN["ProjectnummerEu"]) . ")";
	}

	mysql_free_result($resultPN);

	$retval = str_replace('()', '', $retval);

	$retval = trim($retval);

	return $retval;
}

// TODOEXPLAIN
function convertMinutesToHours($value) {
	if ( $value == 0 || $value == '' ) {
		$retval = '';
	} else {
		$retval = $value*1.0;
		$retval /= 60;
	}

	return $retval;
}

// TODOEXPLAIN
function setBackgroundForSaturdayAndSunday($c, $r, $year, $month, $i) {
	global $objPHPExcel, $greyBackgroundStyle;

	if ( date("w", mktime(0,0,0,$month, $i, $year)) == 0 || date("w", mktime(0,0,0,$month, $i, $year)) == 6 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->applyFromArray($greyBackgroundStyle);
	}
}

// TODOEXPLAIN
function setNumberFormatForSaturdayAndSunday($c, $r, $year, $month, $i) {
	global $objPHPExcel, $greyBackgroundStyle;

	if ( date("w", mktime(0,0,0,$month, $i, $year)) == 0 || date("w", mktime(0,0,0,$month, $i, $year)) == 6 ) {
		$objPHPExcel->getActiveSheet()->getStyle(convertSpreadsheatColumnNumberToColumnCharacter($c+$i) . $r)->getNumberFormat()->setFormatCode('###0.00;-###0.00;;@');
	}
}

// TODOEXPLAIN
function getMonthNameInDutch( $m, $length = 3) {
	$retval = '-error-';

	if ( $m >= 1 && $m <= 12 ) {
		$arr = array("Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December");
		$retval = $arr[$m-1];
		$retval = substr($retval, 0, $length);
	}

	return $retval;
}

// TODOEXPLAIN
function getListOfShowSeparatedProjectsOnReports( $retval, $year, $level, $parent_id = 0 ) {
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();
	$query = "SELECT * FROM Workcodes ::WHERE:: ORDER BY Description ";

	if ( $level > 0 ) {
		$query = str_replace("::WHERE::", " WHERE ParentID=" . $parent_id, $query);
	} else {
		// TODOXXXSLOW
		$query = str_replace("::WHERE::", " WHERE show_separate_in_reports=1
			AND ID IN (
					SELECT WorkCode FROM `Workhours` WHERE DateWorked LIKE '$year%' GROUP BY WorkCode
				)
			 ", $query);
	}

//debug($query);
	$result = mysql_query($query, $oConn->getConnection());

	while ($row = mysql_fetch_array($result)) {
		$spaces = str_repeat(' ', $level);
		$id = $row["ID"];

		$parent_id = $row["ParentID"];
		if ( $parent_id == 1 ) {
			$parent_id = 0;
		}

		if ( $level > 0  ) {
			$number_of_children = -1;
		} else {
			$number_of_children = getNumberOfChildren( $id );
		}
		$retval[] = array($spaces, $id, $parent_id, $number_of_children);

		// ONLY ONE LEVEL DEEP
		if ( $number_of_children > 0 && $level == 0) {
			$newlevel = $level+1;
			$retval = getListOfShowSeparatedProjectsOnReports( $retval, $year, $newlevel, $id);
		}
	}
	mysql_free_result($result);

	return $retval;
}

// TODOEXPLAIN
function getNumberOfChildren( $projectId ) {
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$retval = 0;

	$query = "SELECT COUNT(*) AS AANTAL FROM Workcodes WHERE ParentID=" . $projectId;

	$result = mysql_query($query, $oConn->getConnection());
	if ($row = mysql_fetch_array($result)) {
		$retval = $row["AANTAL"];
	}
	mysql_free_result($result);

	return $retval;
}
