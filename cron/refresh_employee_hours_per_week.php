<?php
require_once "../classes/start.inc.php";
$path_parts['filename'] = 'refresh_cache_employee_hours_per_week';

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

// loop through all enabled employees
echo 'Employee#: ';
$separator = '';
foreach ( class_employee::getListOfAllHoursLeftEmployees() as $oUser ) {
	echo $separator . $oUser->getTimecardId();

	// save current state
	class_settings::saveSetting('cron_' . $path_parts['filename'] . '_state', $oUser->getTimecardId());

	$oRefresh = new class_refresh_employee_hours_per_week( $oUser, date("Y") );
	$oRefresh->refresh(true);

	$separator = ', ';

	flush();
}
echo " <br>\n";

// save cron end run
class_settings::saveSetting('cron_' . $path_parts['filename'] . '_end', date("Y-m-d H:i:s"));

//
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
