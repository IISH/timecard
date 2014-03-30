<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

$date = class_datetime::get_date($protect);

$oEmployee = new class_employee($protect->request('get', 'eid'), $settings);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('administrator.quartertotals'));
$oPage->setTitle('Timecard | Admin Quarter Totals');
$oPage->setContent(createAdminQuarterContent( $date ));

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAdminQuarterContent( $date ) {
	global $settings, $oEmployee;

	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getQuarterRibbon( 'Quarter Totals: ' );

	//
	$ret .= getEmployeesRibbon($date["y"], 0);

	//
	if ( $oEmployee->getTimecardId() != '' ) {
		$ret .= getQuarterTotals( $date, $oEmployee->getTimecardId(), 'admin_' );
	}

	return $ret;
}
?>