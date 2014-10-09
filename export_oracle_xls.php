<?php 
require_once "classes/start.inc.php";

// check authentication
$oWebuser->checkLoggedIn();

// check authorisation
if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// get protime worklocations
$protime_worklocations = class_protime_worklocation::getProtimeWorklocations();

//
date_default_timezone_set('Europe/London');

// check output file
$output = strtolower(substr($protect->request('get', "output"), 0, 10));
if ( !in_array( $output, array('xls', 'xlsx', 'json') ) ) {
	$output = 'debug';
}

//
$year = substr($protect->request_positive_number_or_empty('get', "year"), 0, 4);
$month = substr($protect->request_positive_number_or_empty('get', "month"), 0, 2);
if ( $year == '' || $month == '' || $year < (date("Y")-1) || $month < 1 || $month > 12 ) {
	die('Error 54289654. Go back to <a href="export_oracle.php">view</a>');
}

$firstrow = 1;
$firstcolumn = 0;
$fnaam = protectFilename('export-for-oracle-' . $year . '-' . str_pad( $month, 2, '0', STR_PAD_LEFT)) . ".xlsx";

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

$query = "
SELECT vw_Employees.*, Workcodes.Projectnummer, Workcodes.Description AS Projectname, SUM(Workhours.TimeInMinutes) AS AantalMinuten
FROM Workhours
	INNER JOIN Workcodes ON Workhours.Workcode = Workcodes.ID
	INNER JOIN vw_Employees ON Workhours.Employee = vw_Employees.ID
WHERE Workhours.DateWorked LIKE '" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%'
AND Workhours.isdeleted = 0
AND Workcodes.Projectnummer LIKE '320-%'
AND vw_Employees.is_test_account = 0
GROUP BY vw_Employees.REGISTERNR, vw_Employees.FIRSTNAME, vw_Employees.NAME, vw_Employees.WORKLOCATION, Workcodes.Projectnummer, Workcodes.Description
HAVING SUM(Workhours.TimeInMinutes) > 0
ORDER BY vw_Employees.REGISTERNR, vw_Employees.WORKLOCATION, vw_Employees.NAME, vw_Employees.FIRSTNAME, Workcodes.Projectnummer, Projectname
";

// calculate data
$data = array();

$data[] = array(
	'Projectnummer'			// column 1
	, 'Organisatienaam'		// column 2
	, 'Taaknummer'			// column 3
	, 'Kostensoort'			// column 4
	, 'Werknemersnummer'	// column 5
	, 'Aantaluur'			// column 6
	, 'Toelichting'			// column 7
	, 'Werknemer'			// column 8
	, 'Projectnaam'			// column 9
	);

$col_taaknummer = '1';	// always 1
$col_maand = date("F Y", mktime (0, 0, 0, $month, 1, $year));

$oConn->connect();
$result = mysql_query($query, $oConn->getConnection());
while ($row = mysql_fetch_assoc($result)) {

	$col_projectnummer = '';
	$col_organisatienaam = '';
	$col_kostensoort = '';
	$col_knawpersnr = '';
	$col_hoeveeltijd = '';
	$col_werknemernaam = '';
	$col_projectnaam = '';

	if ( trim($row["Projectnummer"]) != '' ) {
		$col_projectnummer = '="' . trim($row["Projectnummer"]) . '"';
	}

	if ( trim($row["WORKLOCATION"]) != '' && trim($row["WORKLOCATION"]) != '0') {
		if ( isset($protime_worklocations[$row["WORKLOCATION"]]) ) {
			$short1 = $protime_worklocations[$row["WORKLOCATION"]]->getShort1();
			$col_organisatienaam = '="' . trim($short1) . '"';
		}
	}

	if ( trim($row["REGISTERNR"]) != '' ) {
		$col_knawpersnr = '="' . trim($row["REGISTERNR"]) . '"';
	}

	if ( $row["AantalMinuten"] > 0 ) {
		$col_hoeveeltijd = $row["AantalMinuten"]/60;
	} else {
		$col_hoeveeltijd = 0;
	}

	$wname = trim($row["FULLNAME"]);
	if ( $wname != '' ) {
		$col_werknemernaam = $wname;
	}

	if ( trim($row["Projectname"]) != '' ) {
		$col_projectnaam = trim($row["Projectname"]);
	}

	$data[] = array(
		$col_projectnummer
		, $col_organisatienaam
		, $col_taaknummer
		, $col_kostensoort
		, $col_knawpersnr
		, $col_hoeveeltijd
		, $col_maand
		, $col_werknemernaam
		, $col_projectnaam
		);

}

mysql_free_result($result);


// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

if ( $output == 'debug'  ) {

	echo "<pre>";
	print_r($data);
	echo "</pre>";

} elseif ( $output == 'json' ) {

	echo json_encode($data);

} elseif ( $output == 'xlsx' || $output == 'xls' ) {

	// create excel file
	require_once 'PHPExcel/PHPExcel.php';
	require_once('PHPExcel/PHPExcel/IOFactory.php');
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("IISG")
								 ->setLastModifiedBy("IISG");
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	// write data
	$row = $firstrow;
	foreach ( $data as $datarecord ) {
		$column = $firstcolumn;
		foreach ( $datarecord as $dataitem ) {
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $dataitem);
			$column++;
		}
		$row++;
	}

	// send to browser
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"" . $fnaam . "\"");
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;

} else {
	die('Unknown output type: ' . $output);
}
