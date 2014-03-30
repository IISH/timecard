<?php 
//phpinfo();
//die();

require_once "classes/start_no_session_start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

date_default_timezone_set('Europe/London');

$output = strtolower(substr($protect->request('get', "output"), 0, 10));
if ( !in_array( $output, array('xls', 'xlsx', 'json') ) ) {
	$output = 'debug';
}

$year = substr($protect->request_positive_number_or_empty('get', "year"), 0, 4);
$month = substr($protect->request_positive_number_or_empty('get', "month"), 0, 2);
if ( $year == '' || $month == '' || $year < (date("Y")-1) || $month < 1 || $month > 12 ) {
	die('Error 54289654. Go back to <a href="export_oracle.php">view</a>');
}

$firstrow = 1;
$firstcolumn = 0;
$fnaam = protectFilename('export-for-oracle-' . $year . '-' . substr('0' . $month, -2)) . ".xlsx";

// connection to the database
$dbhandleTimecard = mysql_connect($settings["timecard_server"], $settings["timecard_user"], $settings["timecard_password"]) or die("Couldn't connect to SQL Server on: " . $settings["timecard_server"]);
$selectedTimecard = mysql_select_db($settings["timecard_database"], $dbhandleTimecard) or die("Couldn't open database " . $settings["timecard_database"]);

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

$query = "
SELECT Employees.KnawPersNr, Employees.FirstName, Employees.LastName, Employees.AfdelingsNummer, Workcodes2011.Projectnummer, Workcodes2011.Description AS Projectname, SUM(Workhours.TimeInMinutes) AS AantalMinuten 
FROM Workhours 
	INNER JOIN Workcodes2011 ON Workhours.Workcode = Workcodes2011.ID 
	INNER JOIN Employees ON Workhours.Employee = Employees.ID 
WHERE Workhours.DateWorked LIKE '" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%'
AND Workhours.isdeleted = 0
AND Workcodes2011.Projectnummer LIKE '320-%'
AND Employees.is_test_account = 0 
GROUP BY Employees.KnawPersNr, Employees.FirstName, Employees.LastName, Employees.AfdelingsNummer, Workcodes2011.Projectnummer, Workcodes2011.Description 
HAVING SUM(Workhours.TimeInMinutes) > 0 
ORDER BY Employees.KnawPersNr, Employees.AfdelingsNummer, Employees.LastName, Employees.FirstName, Workcodes2011.Projectnummer, Projectname
";
//YEAR(Workhours.DateWorked) = " . $year . "
//AND MONTH(Workhours.DateWorked) = " . $month . "

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

$result = mysql_query($query, $dbhandleTimecard);
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

	if ( trim($row["AfdelingsNummer"]) != '' ) {
		$col_organisatienaam = '="' . trim($row["AfdelingsNummer"]) . '"';
	}

	if ( trim($row["KnawPersNr"]) != '' ) {
		$col_knawpersnr = '="' . trim($row["KnawPersNr"]) . '"';
	}

	if ( $row["AantalMinuten"] > 0 ) {
		$col_hoeveeltijd = $row["AantalMinuten"]/60;
	} else {
		$col_hoeveeltijd = 0;
	}

	$wname = trim($row["FirstName"] . ' ' . $row["LastName"]);
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
?>