<?php
require_once "../classes/start.inc.php";

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

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

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n";

// sync
$sync = new class_syncProtimeMysql();
$sync->setSourceTable("PR_MONTH");
$sync->setSourceCriterium(" BOOKDATE>='" . date("Ymd", mktime(0, 0, 0, date("m")-3, 1, date("Y"))) . "' ");
//$sync->setSourceCriterium(" BOOKDATE>='20130101' ");
$sync->setTargetTable("PROTIME_PR_MONTH");
$sync->setPrimaryKey("PR_KEY");
$sync->addFields( array("PR_KEY", "PERSNR", "BOOKDATE", "CYC_DP", "DAYPROG", "NORM", "WORKED", "PREST", "RPREST", "EXTRA", "WEEKPRES1", "WEEKPRES2", "WEEKPRES3", "PAYPERIO_PRES", "BALANCE", "TERMINAL", "FLAGS1", "FLAGS2", "FLAGS3", "FLAGS4", "FLAGS5", "FLAGS6", "FLAGS7", "ABS_CORE", "NROFBREAKS", "BREAKTIME", "CALCULATED", "ACCESSGROUP", "SHIFT", "CYCLIQ", "COSTCENTERGROUP", "COSTBLOCKING", "PP_FUNCTION", "COMMENTS", "CUSTOMER" ) );
class_settings::saveSetting('cron_' . $sync->getTargetTable() . '_start', date("Y-m-d H:i:s"), $sync->getTargetTable() . "_syncinfo");
$sync->doSync();

//
echo "<br>Rows inserted/updated: " . $sync->getCounter() . "<br>";

// save sync last run
class_settings::saveSetting('cron_' . $sync->getTargetTable() . '_end', date("Y-m-d H:i:s"), $sync->getTargetTable() . "_syncinfo");
class_settings::saveSetting('cron_last_insert_id_' . $sync->getTargetTable(), $sync->getLastInsertId(), $sync->getTargetTable() . "_syncinfo");

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
