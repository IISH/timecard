<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$projectId = $protect->request_positive_number_or_empty('get', 'ID');
$oProject = new class_project( $projectId );

$year = $protect->request('get', 'y', '/^[0-9][0-9][0-9][0-9]$/');
if ( $year == '' ) {
	$year = date("Y");
}
if ( $year > date("Y") ) {
	die('We cannot predict the future... Please select current year or a year in the past :)');
}
if ( $year < "2000" ) {
	die('No data found...');
}

$output = $protect->request('get', 'o');
$output = trim(substr($output, 0, 10));
if ( !in_array( $output, array("xls", "xlsx", "pdf", "html") ) ) {
	$output = "html";
}

$oProjectTotals = new class_project_totals( $oProject->getId(), $year );

// + + + + + + + + +

// STYLES
$style_names = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'left' )
);

$style_header_months = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'center' )
);

$style_totals = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'right' )
);

// + + + + + + + + +

/** PHPExcel */
require_once 'PHPExcel/PHPExcel.php';
require_once('PHPExcel/PHPExcel/IOFactory.php');

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("IISG")
	->setLastModifiedBy("IISG");

//$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();

//
$sheet->getColumnDimension( c(1) )->setWidth( 20 );
$width = 7;
for( $i = 2; $i <= 16; $i++ ) {
	$sheet->getColumnDimension( c($i) )->setWidth( $width );
}
$sheet->getColumnDimension( c(17) )->setWidth( 12 );

// merge cells
$sheet->mergeCells('B1:N1');
$sheet->mergeCells('B2:Q2');
$sheet->mergeCells('B3:Q3');
$sheet->mergeCells('B4:Q4');
$sheet->mergeCells('A5:Q5');

$r = 1;
$sheet->SetCellValue( rc($r,1), 'Project:');
$sheet->SetCellValue( rc($r,2), $oProject->getDescription() );
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_names );

$sheet->mergeCells('O1:Q1');
$sheet->SetCellValue( rc($r,15), 'Print: ' . date("d-m-Y H:i") );

$r++;
$sheet->SetCellValue( rc($r,1), 'Year:');
$sheet->SetCellValue( rc($r,2), $year);
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_names );

$r++;
$sheet->SetCellValue( rc($r,1), 'Project number:');
$sheet->SetCellValue( rc($r,2), $oProject->getProjectnumber());
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_names );

$r++;
$sheet->SetCellValue( rc($r,1), 'Project leader:');
$sheet->SetCellValue( rc($r,2), $oProject->getProjectleader()->getFirstLastname());
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_names );

// HEADER LINE
$r++;
$r++;
$sheet->SetCellValue( rc($r,1), 'Werknemer');
$sheet->SetCellValue( rc($r,2), 'Jan');
$sheet->SetCellValue( rc($r,3), 'Feb');
$sheet->SetCellValue( rc($r,4), 'Maa');
$sheet->SetCellValue( rc($r,5), 'Q1');
$sheet->SetCellValue( rc($r,6), 'Apr');
$sheet->SetCellValue( rc($r,7), 'Mei');
$sheet->SetCellValue( rc($r,8), 'Jun');
$sheet->SetCellValue( rc($r,9), 'Q2');
$sheet->SetCellValue( rc($r,10), 'Jul');
$sheet->SetCellValue( rc($r,11), 'Aug');
$sheet->SetCellValue( rc($r,12), 'Sep');
$sheet->SetCellValue( rc($r,13), 'Q3');
$sheet->SetCellValue( rc($r,14), 'Okt');
$sheet->SetCellValue( rc($r,15), 'Nov');
$sheet->SetCellValue( rc($r,15), 'Dec');
$sheet->SetCellValue( rc($r,16), 'Q4');
$sheet->SetCellValue( rc($r,17), 'Totaal uren');

// set HEADER LINE bold
$sheet->getStyle( rc($r,1) )->applyFromArray( $style_names );
for ( $i = 2; $i <= 16; $i++ ) {
	$sheet->getStyle( rc($r,$i) )->applyFromArray( $style_header_months );
}
$sheet->getStyle( rc($r,17) )->applyFromArray( $style_totals );

//
$r++;
$totals = array();
foreach ( $oProjectTotals->getIds() as $id ) {
	// name
	$c = 1;
	$oEmployee = new class_employee($id, $settings);
	$sheet->SetCellValue( rc($r,$c), $oEmployee->getFirstLastname());
	$sheet->getStyle( rc($r,$c) )->applyFromArray( $style_names );

	//

	//

	$r++;
}

// TOTALS LINE
$sheet->SetCellValue( rc($r,1), 'Maand totalen');
for( $c = 2; $c <= 17; $c++ ) {
	if ( isset( $totals[$i] ) ) {
		$sheet->SetCellValue( rc($r,$c), $totals[$i]);
	} else {
		$sheet->SetCellValue( rc($r,$c), '&nbsp;');
	}

}

// set TOTALS LINE bold
for ( $i = 1; $i <= 17; $i++ ) {
	$sheet->getStyle( rc($r,$i) )->getFont()->setBold(true);
}

// send output to browser or file
switch ( $output ) {
	case "xlsx":
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"TESTTEST.xlsx\"");
		$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
		$objWriter->save('php://output');
		break;
	default:
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
		$objWriter->save('php://output');
}

class class_project_totals {
	private $databases;

	private $projectId;
	private $year;
	private $arr = array();
	private $ids = array();

	// TODOEXPLAIN
	function class_project_totals($projectId, $year) {
		global $databases;
		$this->databases = $databases;

		$this->projectId = $projectId;
		$this->year = $year;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "
SELECT LEFT(DateWorked, 7) AS YearMonth, WorkCode AS ProjectId, TimeInMinutes, Employees.ID AS TimecardId, ProtimePersNr, NAME, FIRSTNAME
FROM Workhours
	INNER JOIN Employees ON Workhours.Employee = Employees.ID
	INNER JOIN PROTIME_CURRIC ON Employees.ProtimePersNr = PROTIME_CURRIC.PERSNR
WHERE WorkCode = {$this->projectId}
AND Workhours.isdeleted = 0
AND DateWorked LIKE '{$this->year}%'
GROUP BY LEFT(DateWorked, 7), WorkCode, TimeInMinutes, Employees.ID, ProtimePersNr, NAME, FIRSTNAME
ORDER BY NAME, FIRSTNAME, LEFT(DateWorked, 7)
";

//		echo $query . ' +<br>';

		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
//			echo '<pre>';
//			print_r( $r );
//			echo '</pre>';
			//
			$item = new class_project_totals_item();
			$item->setYearMonth( $r['YearMonth'] );
			$item->setProjectId( $r['ProjectId'] );
			$item->setTimeInMinutes( $r['TimeInMinutes'] );
			$item->setTimecardId( $r['TimecardId'] );
			$item->setProtimePersNr( $r['ProtimePersNr'] );

			//
			if ( !in_array( $r['TimecardId'] , $this->ids ) ) {
				$this->ids[] = $r['TimecardId'];
			}

			//
			$this->arr[] = $item;

		}
		mysql_free_result($res);

//		echo '<pre>';
//print_r( $this->arr );
//		echo '</pre>';

	}

	public function getIds() {
		return $this->ids;
	}
}

class class_project_totals_item {
	private $year;
	private $month;
	private $yearMonth;
	private $projectId;
	private $timeInMinutes;
	private $timecardId;
	private $protimePersNr;

	// TODOEXPLAIN
	function class_project_totals_item() {
	}

	//
	public function setYearMonth( $value ) {
		$this->yearMonth = $value;
		$this->year = substr($value, 0, 4);
		$this->month = substr($value, -2)+0;
	}
	public function getYearMonth() {
		return $this->yearMonth;
	}
	public function getYear() {
		return $this->year;
	}
	public function getMonth() {
		return $this->month;
	}

	//
	public function setProjectId( $value ) {
		$this->projectId = $value;
	}
	public function getProjectId() {
		return $this->projectId;
	}

	//
	public function setTimeInMinutes( $value ) {
		$this->timeInMinutes = $value;
	}
	public function getTimeInMinutes() {
		return $this->timeInMinutes;
	}
	public function getTimeInHours() {
		return $this->timeInMinutes/60;
	}

	//
	public function setTimecardId( $value ) {
		$this->timecardId = $value;
	}
	public function getTimecardId() {
		return $this->timecardId;
	}

	//
	public function setProtimePersNr( $value ) {
		$this->protimePersNr = $value;
	}
	public function getProtimePersNr() {
		return $this->protimePersNr;
	}
}
