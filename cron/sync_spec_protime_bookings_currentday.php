<?php
require_once "../classes/start.inc.php";

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
$sync->setSourceTable("BOOKINGS");
$sync->setSourceCriterium(" BOOKDATE >= '" . date("Ymd") . "' ");
$sync->setTargetTable("SPEC_PROTIME_BOOKINGS_CURRENTDAY");
$sync->setPrimaryKey("REC_NR");
$sync->addFields( array("REC_NR", "PERSNR", "BOOKDATE", "BOOK_ORIG", "BOOKTIME", "BOOKTYPE", "CCABS", "TERMINAL", "USER_ID", "COMMENTS", "REQUEST", "CALCBOOKTIME") );
class_settings::saveSetting('cron_' . $sync->getTargetTable() . '_start', date("Y-m-d H:i:s"), $sync->getTargetTable() . "_syncinfo");
$sync->doSync();

//
echo "<br>Rows inserted/updated: " . $sync->getCounter() . "<br>";

// remove old records
$query = "DELETE FROM " . $sync->getTargetTable() . " WHERE BOOKDATE<'" . date("Ymd") . "' ";
$oConn = new class_mysql($databases['default']);
$oConn->connect();
$result = mysql_query($query, $oConn->getConnection());

// save sync last run
class_settings::saveSetting('cron_' . $sync->getTargetTable() . '_end', date("Y-m-d H:i:s"), $sync->getTargetTable() . "_syncinfo");
class_settings::saveSetting('cron_last_insert_id_' . $sync->getTargetTable(), $sync->getLastInsertId(), $sync->getTargetTable() . "_syncinfo");

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
