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
	die('We cannot predict the future... Please select current year or a year in the past.');
}
if ( $year < "2000" ) {
	die('No data found...');
}

$output = $protect->request('get', 'o');
$output = trim(substr($output, 0, 10));
if ( !in_array( $output, array("xlsx", "html") ) ) {
	$output = "html";
}

$oProjectTotals = new class_project_totals( $oProject->getId(), $year );

// + + + + + + + + +

// STYLES
$style_top = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'left' )
);

$style_names = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'left' )
	, 'borders' => array(
		'outline' => array(
			'style' => 'thin',
			'color' => array('rgb' => '000000'),
		)
	)
);

$style_header_months = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'center' )
	, 'borders' => array(
		'outline' => array(
			'style' => 'thin',
			'color' => array('rgb' => '000000'),
		)
	)
);

$style_totals = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'right' )
	, 'borders' => array(
		'outline' => array(
			'style' => 'thin',
			'color' => array('rgb' => '000000'),
		)
	)
);

$style_highlight_background_month = array(
	'borders' => array(
		'outline' => array(
			'style' => 'thin',
			'color' => array('rgb' => '000000'),
		)
	)
);

$style_highlight_background_quarter = array(
	'fill' => array(
		'type' => 'solid'
	, 'color' => array('rgb' => 'ffff00')
	)
, 'borders' => array(
		'outline' => array(
			'style' => 'thin',
			'color' => array('rgb' => '000000'),
		)
	)
);

$style_highlight_background_year = array(
	'fill' => array(
		'type' => 'solid'
		, 'color' => array('rgb' => 'd3d3d3')
	)
	, 'borders' => array(
		'outline' => array(
			'style' => 'thin',
			'color' => array('rgb' => '000000'),
		)
	)
);

// + + + + + + + + +

/** PHPExcel */
//require_once 'PHPExcel/PHPExcel.php';
//require_once('PHPExcel/PHPExcel/IOFactory.php');
require_once 'PHPExcel_1.8.0/PHPExcel.php';
require_once('PHPExcel_1.8.0/PHPExcel/IOFactory.php');

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("IISG")
	->setLastModifiedBy("IISG");

$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();

//
if ( $output == 'html' ) {
	$width_name_column = 25;
	$width_month_column = 9;
	$width_quarter_column = 11;
	$width_year_column = 11;
} else {
	$width_name_column = 21;
	$width_month_column = 8;
	$width_quarter_column = 8;
	$width_year_column = 9;
}

//
$c = 1;
$sheet->getColumnDimension( c( $c ) )->setWidth( $width_name_column );
for ( $loop = 1; $loop <= 4; $loop++ ) {
	for ( $m = 1; $m <= 3; $m++ ) {
		$c++;
		$sheet->getColumnDimension( c($c) )->setWidth( $width_month_column );
	}
	$c++;
	$sheet->getColumnDimension( c($c) )->setWidth( $width_quarter_column );
}
$c++;
$sheet->getColumnDimension( c(18) )->setWidth( $width_year_column );

// merge cells
if ( $output == 'html' ) {
	$sheet->mergeCells('B1:R1');
} else {
	$sheet->mergeCells('B1:N1');
	$sheet->mergeCells('O1:R1');
}

$sheet->mergeCells('B2:R2');
$sheet->mergeCells('B3:R3');
$sheet->mergeCells('B4:R4');
$sheet->mergeCells('A5:R5');

$r = 1;
$sheet->SetCellValue( rc($r,1), 'Project:');
$sheet->SetCellValue( rc($r,2), $oProject->getDescription() );
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );

if ( $output != 'html' ) {
	$sheet->SetCellValue(rc($r, 15), 'Print: ' . date("d-m-Y H:i"));
	$sheet->getStyle(rc($r, 15))->getAlignment()->setHorizontal('right');
}

$r++;
$sheet->SetCellValue( rc($r,1), 'Project #:');
$sheet->SetCellValue( rc($r,2), $oProject->getProjectnumber());
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );

$r++;
$sheet->SetCellValue( rc($r,1), 'Project leader:');
$sheet->SetCellValue( rc($r,2), $oProject->getProjectleader()->getFirstLastname());
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );

$r++;
$sheet->SetCellValue( rc($r,1), 'Year:');
$sheet->SetCellValue( rc($r,2), $year);
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );


if ( count($oProjectTotals->getIds()) > 0 ) {


	// HEADER LINE
	$r++;
	$r++;
	$sheet->SetCellValue( rc($r,1), 'Employee');
	$sheet->SetCellValue( rc($r,2), 'Jan');
	$sheet->SetCellValue( rc($r,3), 'Feb');
	$sheet->SetCellValue( rc($r,4), 'Mar');
	$sheet->SetCellValue( rc($r,5), 'Q1');
	$sheet->getStyle( rc($r,5) )->applyFromArray($style_highlight_background_quarter);
	$sheet->SetCellValue( rc($r,6), 'Apr');
	$sheet->SetCellValue( rc($r,7), 'May');
	$sheet->SetCellValue( rc($r,8), 'Jun');
	$sheet->SetCellValue( rc($r,9), 'Q2');
	$sheet->getStyle( rc($r,9) )->applyFromArray($style_highlight_background_quarter);
	$sheet->SetCellValue( rc($r,10), 'Jul');
	$sheet->SetCellValue( rc($r,11), 'Aug');
	$sheet->SetCellValue( rc($r,12), 'Sep');
	$sheet->SetCellValue( rc($r,13), 'Q3');
	$sheet->getStyle( rc($r,13) )->applyFromArray($style_highlight_background_quarter);
	$sheet->SetCellValue( rc($r,14), 'Oct');
	$sheet->SetCellValue( rc($r,15), 'Nov');
	$sheet->SetCellValue( rc($r,16), 'Dec');
	$sheet->SetCellValue( rc($r,17), 'Q4');
	$sheet->getStyle( rc($r,17) )->applyFromArray($style_highlight_background_quarter);
	$sheet->SetCellValue( rc($r,18), 'Total');
	$sheet->getStyle( rc($r,18) )->applyFromArray($style_highlight_background_year);

	// set HEADER LINE bold
	$sheet->getStyle( rc($r,1) )->applyFromArray( $style_names );

	for ( $i = 2; $i <= 17; $i++ ) {
		$sheet->getStyle( rc($r,$i) )->applyFromArray( $style_header_months );
	}
	$sheet->getStyle( rc($r,18) )->applyFromArray( $style_totals );

	//
	$r++;
	$firstDataLine = $r;
	$totals = array();
	foreach ( $oProjectTotals->getIds() as $id ) {
		// name
		$c = 1;
		$oEmployee = new class_employee($id, $settings);
		$sheet->SetCellValue( rc($r,$c), $oEmployee->getFirstLastname());
		$sheet->getStyle( rc($r,$c) )->applyFromArray( $style_names );

		// loop quarters
		for ( $loop = 1; $loop <= 4; $loop++ ) {

			// loop months in quarter
			for ($m = 1; $m <= 3; $m++) {
				$c++;
				$value = $oProjectTotals->getHours($id, $year, $m+(($loop-1)*3));
				if ( $value == 0 ) {
					$value = '';
				}
				$sheet->SetCellValue( rc($r, $c), $value);
				$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_month);
				$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode('0.00');
			}
			$c++;
			$sheet->SetCellValue( rc($r, $c), '=SUM(' . rc($r, $c - 3) . ':' . rc($r, $c - 1) . ')');
			$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_quarter);
			$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode('0.00');

		}

		//
		$c++;
		$sheet->SetCellValue( rc($r, $c), '='.rc($r, $c-1).'+'.rc($r, $c-5).'+'.rc($r, $c-9).'+'.rc($r, $c-13) );
		$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_year);
		$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode('0.00');

		//
		$r++;
	}


	// set values totals line
	$sheet->SetCellValue( rc($r,1), 'Month totals');
	for( $c = 2; $c <= 18; $c++ ) {
		$value = '=SUM('.rc($firstDataLine, $c).':'.rc($r-1, $c).')';
		$sheet->SetCellValue( rc($r,$c), $value );
	}


	// set design totals line
	$c = 1;
	$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_month);
	$sheet->getStyle( rc($r,$c) )->getFont()->setBold(true);
	for ( $loop = 1; $loop <= 4; $loop++ ) {
		// loop months in quarter
		for ($m = 1; $m <= 3; $m++) {
			$c++;
			$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_month);
			$sheet->getStyle( rc($r,$c) )->getFont()->setBold(true);
			$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode('0.00');
		}
		$c++;
		$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_quarter);
		$sheet->getStyle( rc($r,$c) )->getFont()->setBold(true);
		$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode('0.00');
	}
	$c++;
	$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_year);
	$sheet->getStyle( rc($r,$c) )->getFont()->setBold(true);
	$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode('0.00');


} else {

	$r++;
	$r++;
	$sheet->SetCellValue( rc($r, 1), 'No data found for ' . $year );
	$sheet->mergeCells('A'.$r.':R'.$r);

}


// send output to browser
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
SELECT LEFT(DateWorked, 7) AS YearMonth, WorkCode AS ProjectId, Employees.ID AS TimecardId, ProtimePersNr, NAME, FIRSTNAME, SUM(TimeInMinutes) AS TOTMIN
FROM Workhours
	INNER JOIN Employees ON Workhours.Employee = Employees.ID
	INNER JOIN PROTIME_CURRIC ON Employees.ProtimePersNr = PROTIME_CURRIC.PERSNR
WHERE WorkCode = {$this->projectId}
AND Workhours.isdeleted = 0
AND DateWorked LIKE '{$this->year}%'
GROUP BY LEFT(DateWorked, 7), WorkCode, Employees.ID, ProtimePersNr, NAME, FIRSTNAME
ORDER BY NAME, FIRSTNAME, LEFT(DateWorked, 7)
";

//		echo $query . ' +<br>';

		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			//
			$item = new class_project_totals_item();
			$item->setYearMonth( $r['YearMonth'] );
			$item->setProjectId( $r['ProjectId'] );
			$item->setTimeInMinutes( $r['TOTMIN'] );
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

	}

	public function getIds() {
		return $this->ids;
	}

	function getHours($timecardId, $year, $month) {
		$totHours = 0;

		foreach ( $this->arr as $item ) {
			if ( $timecardId == $item->getTimecardId() && $year == $item->getYear() && $month == $item->getMonth() ) {
				$totHours += $item->getTimeInHours();
			}
		}

		return $totHours;
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
		$this->year = (int)substr($value, 0, 4);
		$this->month = (int)substr($value, -2);
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
