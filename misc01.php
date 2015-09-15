<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasReportsAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('exports.misc'));
$oPage->setTitle('Timecard | Miscellaneous');
$oPage->setContent(createExportOracleContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createExportOracleContent() {
	global $protect;

	$ret = '<h2>Misc 01 (Booked on Deparmtent)</h2><br>';

	$year = substr($protect->request_positive_number_or_empty('get', "y"), 0, 4);
	$month = substr($protect->request_positive_number_or_empty('get', "m"), 0, 2);
	if ( $year == '' || $year > date("Y") || $year < date("Y")-2 ) {
		$year = date("Y");
	}
	if ( $month == '' || $month < 0 || $month > 12) {
		$month = date("m");
	}
	$month = substr('0' . $month, -2);

	$d = new TCDateTime();
	$d->setFromString($year . '-' . $month . '-01', 'Y-m-d');

	$prevMonth = $d->getPrevMonth();
	$nextMonth = $d->getNextMonth();

	$ret .= '<a href="?y=' . $prevMonth->format("Y") . '&m=' . $prevMonth->format('m') . '">&laquo; prev</a>';
	$ret .= ' ' .  $d->get()->format("F Y") . ' ';
	$ret .= '<a href="?y=' . $nextMonth->format("Y") . '&m=' . $nextMonth->format('m') . '">next &raquo;</a><br><br>';

	$ret .= class_misc::convertArrayToHtmlTable(class_misc01::getMisc01( $d->get()->format("Y-m")));

	return $ret;
}
