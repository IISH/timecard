<?php
require_once dirname(__FILE__) . "/../classes/start.inc.php";
$path_parts['filename'] = 'daily_maintenance';

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
} else {
	// if run CLI
	foreach ($argv as $arg) {
		$e = explode("=", $arg, 2);
		// if 2 elemenents (part before, part after equal sign)
		if ( count($e) == 2 ) {
			// only if not already set
			if ( !isset($_GET[$e[0]]) ) {
				$_GET[$e[0]] = $e[1];
			}
		}
	}
	if ( isset($_GET["cron_key"]) ) {
		$cron_key = $_GET["cron_key"];
	}
}
if ( trim( $cron_key ) != Settings::get('cron_key') ) {
	die('Error: Incorrect cron key...');
}

// save cron start run
SyncInfo::save($path_parts['filename'], 'start', date("Y-m-d H:i:s"));

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
		SyncInfo::save($path_parts['filename'], 'counter', $oUser->getTimecardId() . " - " . $oDate->get("Y-m"));

		echo ' ' . (int)$oDate->get("m");
		$oUser->syncTimecardProtimeMonthInformation( $oDate );
		flush();
	}

	echo " <br>\n";
}

// save cron end run
SyncInfo::save($path_parts['filename'], 'end', date("Y-m-d H:i:s"));

//
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
