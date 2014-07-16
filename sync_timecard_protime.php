<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

$oWebuser->syncTimecardProtimeMonthInformation( $oDate );

require_once "classes/_db_disconnect.inc.php";

// redirect back
$url = "quartertotals.php?d=" . $oDate->get("Ymd");
Header("location: " . $url);
die("Go to <a href=\"$url\">quarter totals</a>");