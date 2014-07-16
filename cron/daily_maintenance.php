<?php
require_once "../classes/start.inc.php";
//require_once "/home/www/timecard.iisg.nl/public_html/v6/classes/start.inc.php";
//$path_parts = pathinfo($_SERVER["SCRIPT_FILENAME"]);
$path_parts['filename'] = 'daily_maintenance';

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
}
if ( trim( $cron_key ) != class_settings::getSetting('cron_key') ) {
	die('Error: Incorrect cron key...');
}

// save cron start run
class_settings::saveSetting('cron_' . $path_parts['filename'] . '_start', date("Y-m-d H:i:s"));

//
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";

// create array of months that have to synced (prev, current and next two months)
$arrMonths = array();
for ( $i = -1; $i <= 2; $i++ ) {
	$month = mktime(0, 0, 0, date('m')+$i, date('d'), date('Y'));
	$arrMonths[] = new class_date( date("Y", $month), date("m", $month), date("d", $month) );
}

// loop through all enabled employees
foreach ( class_employee::getListOfEnabledAndLinkedEmployees() as $oUser ) {
	echo 'Employee#: ' . $oUser->getTimecardId() . ' - month:';

	foreach ( $arrMonths as $oDate ) {
		// save current state
		class_settings::saveSetting('cron_' . $path_parts['filename'] . '_state', $oUser->getTimecardId() . " - " . $oDate->get("Y-m"));

		echo ' ' . (int)$oDate->get("m");
		$oUser->syncTimecardProtimeMonthInformation( $oDate );
		flush();
	}

	echo " <br>\n";
}

// save cron end run
class_settings::saveSetting('cron_' . $path_parts['filename'] . '_end', date("Y-m-d H:i:s"));

//
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
