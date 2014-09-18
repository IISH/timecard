<?php
die('disabled by gcu');

require_once "../classes/start.inc.php";

// connection to the database
$dbhandlePT = mssql_connect($settings["protime_server"], $settings["protime_user"], $settings["protime_password"]) or die("Couldn't connect to SQL Server on: " . $settings["protime_server"]);

// select a database to work with
$selectedPT = mssql_select_db($settings["protime_database"], $dbhandlePT) or die("Couldn't open database " . $settings["protime_database"]);

$oWebuser->checkLoggedIn();

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);
?>
<?php echo date('Y-m-d H:i:s'); ?><br>
<br>
<hr>
<?php 
$result = mssql_query("SELECT * FROM sysobjects where xtype = 'U' ORDER BY name ");
while ( $row = mssql_fetch_array($result) ) {
?>
<b><?php echo $row["name"]; ?></b><br>
<table border="1" cellspacing="0" cellpadding="1">
<tr>
<?php 
	// show field names
	$resultFields = mssql_query("SELECT * FROM information_schema.columns WHERE TABLE_NAME = '" . $row["name"] . "' ORDER by ORDINAL_POSITION ");
	$arrFields = array();
	while ( $rowFields = mssql_fetch_array($resultFields) ) {
		if ( $rowFields["COLUMN_NAME"] != 'LASTMOD' ) {
		array_push($arrFields, $rowFields["COLUMN_NAME"]);
?>
	<td><b><?php echo $rowFields["COLUMN_NAME"]; ?></b></td>
<?php
		}
	}
	mssql_free_result($resultFields);

	$top = ' TOP 10 ';
	$where = '';
	$order = '';

	if ( in_array("PERSNR", $arrFields) ) {
		$top = ' TOP 50 ';

		if ( $where != '' ) {
			$where .= ' AND ';
		}
//		$where .= ' PERSNR=37 ';
		$where .= ' PERSNR=202 ';
	}

	if ( in_array("BOOKDATE", $arrFields) ) {
		$top = ' TOP 1000 ';

		if ( $where != '' ) {
			$where .= ' AND ';
		}
		$where .= " BOOKDATE LIKE '" . date("Y") . "%' ";

		if ( $order != '' ) {
			$order .= ' , ';
		}
		$order .= ' BOOKDATE DESC ';
	}

	if ( $where != '' ) {
		$where = ' WHERE ' . $where;
	}

	if ( $order != '' ) {
		$order = ' ORDER BY ' . $order;
	}
?>
</tr>
<?php 
	// show data
	$resultData = mssql_query("SELECT $top * FROM " . $row["name"] . " $where $order ");
	while ( $rowData = mssql_fetch_array($resultData) ) {
?>
<tr>
<?php 
		foreach ( $arrFields as $field ) {
?>
	<td><?php echo substr($rowData[$field], 0, 50); ?></td>
<?php 
		}
?>
</tr>
<?php 
	}
	mssql_free_result($resultData);
	unset($arrFields);
?>
</table>
<br>
<hr>
<?php 
}
mssql_free_result($result);
mssql_close($dbhandlePT);
