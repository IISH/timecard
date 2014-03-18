<?php 
require_once "classes/start.inc.php";

$date = class_datetime::get_date($protect);
$originalDate = class_datetime::get_date($protect, "pd");
$oCalendar = new class_calendar();

$s = trim($_GET["s"]);
$s = str_replace( array( 'http://', 'https://', 'ftp://', 'ftps://', '<script', '</script>', '<', '>'), ' ', $s);
$s = trim($s);

$q = trim($_GET["q"]);
$q = str_replace( array( 'http://', 'https://', 'ftp://', 'ftps://', '<script', '</script>', '<', '>'), ' ', $q);
$q = trim($q);

$calendar = $oCalendar->getCalendar($date, $originalDate, $s, $q);

echo $calendar;
?>