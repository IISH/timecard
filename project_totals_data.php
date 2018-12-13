<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

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

$projectId = $protect->request_positive_number_or_empty('get', 'ID');
//$oProject = new class_project( $projectId, $year );
$oProject = new class_project( $projectId );

$oProjectTotals = new class_project_totals( $oProject->getId(), $year );

//
$number_format = '#,##0.00';

// + + + + + + + + +

// STYLES
$style_top = array(
	'font' => array( 'bold' => true )
	, 'alignment' => array( 'horizontal' => 'left' )
);

$style_top_warning = array(
	'font' => array(
		'bold' => true
		, 'color' => array('rgb' => 'FF0000')
	)
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
require_once 'PHPExcel/PHPExcel.php';
require_once 'PHPExcel/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("IISG")
	->setLastModifiedBy("IISG");

$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->getPageSetup()->setOrientation('landscape');
$sheet->getPageSetup()->setPaperSize(9);
$sheet->getPageSetup()->setFitToPage(true);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

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

// PROJECT NAME
$r = 1;
$sheet->SetCellValue( rc($r,1), 'Project:');
$value = $oProject->getDescription();
if ( $value == '0' ) {
	$value = '-';
}
$sheet->SetCellValue( rc($r,2), $value );
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );

// show 'print' only in excel
if ( $output != 'html' ) {
	$sheet->SetCellValue(rc($r, 15), 'Print: ' . date("d-m-Y H:i"));
	$sheet->getStyle(rc($r, 15))->getAlignment()->setHorizontal('right');
}

// PROJECT NUMBER
$r++;
$sheet->SetCellValue( rc($r,1), 'Project #:');
$sheet->SetCellValue( rc($r,2), $oProject->getProjectnumber());
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );

// PROJECT LEADER
$r++;
$sheet->SetCellValue( rc($r,1), 'Project leader:');
$value = '';
if ( !is_null( $oProject->getProjectleader() ) ) {
	$value = $oProject->getProjectleader()->getFirstLastname();
}
$sheet->SetCellValue( rc($r,2), $value);
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );

// END DATE
if ( trim($oProject->getEnddate()) != '' ) {
	$r++;
	$sheet->SetCellValue( rc($r,1), 'End date:');
	$sheet->SetCellValue( rc($r,2), $oProject->getEnddate());
	$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );
	$sheet->mergeCells('B'.$r.':R'.$r);
}

// YEAR
$r++;
$sheet->SetCellValue( rc($r,1), 'Year:');
$sheet->SetCellValue( rc($r,2), $year);
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );
$sheet->mergeCells('B'.$r.':R'.$r);

// Planned hours
$r++;
$sheet->SetCellValue( rc($r,1), 'Planned hours:');
$sheet->SetCellValue( rc($r,2), $oProject->getEstimatedHours());
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );
$sheet->mergeCells('B'.$r.':R'.$r);

// Booked hours
$r++;
$sheet->SetCellValue( rc($r,1), 'Booked hours:');
$sheet->SetCellValue( rc($r,2), class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($oProject->getBookedMinutes()) . ' (h:mm)');
$sheet->getStyle( rc($r,2) )->applyFromArray( $style_top );
$sheet->mergeCells('B'.$r.':R'.$r);

// Left hours
$r++;
$sheet->SetCellValue( rc($r,1), 'Left hours:');
$sheet->SetCellValue( rc($r,2), class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($oProject->getLeftMinutes()) . ' (h:mm)');
if ( $oProject->getLeftMinutes() >= 0 ) {
	$sheet->getStyle(rc($r, 2))->applyFromArray($style_top);
} else {
	$sheet->getStyle(rc($r, 2))->applyFromArray($style_top_warning);
}
$sheet->mergeCells('B'.$r.':R'.$r);

// empty line (IE9 compatbile)
$r++;
$sheet->SetCellValue( rc($r,1), ' ');
$sheet->mergeCells('A'.$r.':R'.$r);

if ( count($oProjectTotals->getIds()) > 0 ) {

	// HEADER LINE
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

		$employee_name = trim($oEmployee->getFirstLastname());

		// if no name, use login name
		if ( $employee_name == '' || $employee_name == ',' ){
			$employee_name = $oEmployee->getLoginName();
			$employee_name = str_replace('.', ' ', $employee_name);
		}

		// replace special characters by standard characters
		setlocale(LC_ALL, 'en_GB');
		$employee_name = str_replace(array('"', '\''), '', iconv("ISO-8859-1", "US-ASCII//TRANSLIT", $employee_name));

//		$sheet->SetCellValue( rc($r,$c), $oEmployee->getFirstLastname());
		$sheet->SetCellValue( rc($r,$c), $employee_name);
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
				$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode($number_format);
			}
			$c++;
			$sheet->SetCellValue( rc($r, $c), '=SUM(' . rc($r, $c - 3) . ':' . rc($r, $c - 1) . ')');
			$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_quarter);
			$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode($number_format);

		}

		//
		$c++;
		$sheet->SetCellValue( rc($r, $c), '='.rc($r, $c-1).'+'.rc($r, $c-5).'+'.rc($r, $c-9).'+'.rc($r, $c-13) );
		$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_year);
		$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode($number_format);

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
			$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode($number_format);
		}
		$c++;
		$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_quarter);
		$sheet->getStyle( rc($r,$c) )->getFont()->setBold(true);
		$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode($number_format);
	}
	$c++;
	$sheet->getStyle( rc($r,$c) )->applyFromArray($style_highlight_background_year);
	$sheet->getStyle( rc($r,$c) )->getFont()->setBold(true);
	$sheet->getStyle( rc($r,$c) )->getNumberFormat()->setFormatCode($number_format);


} else {

	$r++;
	$sheet->SetCellValue( rc($r, 1), 'No data found for ' . $year );
	$sheet->mergeCells('A'.$r.':R'.$r);

}

//
$filename = $oProject->getDescription() . " (" . $year . ") project totals.xlsx";

// send output to browser
switch ( $output ) {
	case "xlsx":
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
		$objWriter->save('php://output');
		break;
	default:
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
		$objWriter->setUseInlineCss( true );
		//ob_end_clean();
		$objWriter->save('php://output');
}
