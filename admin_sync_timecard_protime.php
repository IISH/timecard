<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

$oEmployee = new class_employee($protect->request('get', 'eid'), $settings);

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

$oEmployee->syncTimecardProtimeMonthInformation( $oDate );

require_once "classes/_db_disconnect.inc.php";

// redirect back
$url = "admin_quartertotals.php?d=" . $oDate->get("Ymd") . "&eid=" . $oEmployee->getTimecardId();
Header("location: " . $url);
die("Go to <a href=\"$url\">quarter totals</a>");