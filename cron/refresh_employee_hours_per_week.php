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
if ( trim( $cron_key ) != Settings::get('cron_key') ) {
	die('Error: Incorrect cron key...');
}

// save cron start run
SyncInfo::save($path_parts['filename'], 'start', date("Y-m-d H:i:s"));

//
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";

// loop through all enabled employees
echo 'Employee#: ';
$separator = '';
foreach ( class_employee::getListOfAllHoursLeftEmployees() as $oUser ) {
	echo $separator . $oUser->getTimecardId();

	// save current state
	SyncInfo::save($path_parts['filename'], 'counter', $oUser->getTimecardId());

	$oRefresh = new class_refresh_employee_hours_per_week( $oUser, date("Y") );
	$oRefresh->refresh(true);

	$separator = ', ';

	flush();
}
echo " <br>\n";

// save cron end run
SyncInfo::save($path_parts['filename'], 'end', date("Y-m-d H:i:s"));

//
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
