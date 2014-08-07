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
$oPage = new class_page('design/page_admin.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('administrator.quartertotals'));
$oPage->setTitle('Timecard | Admin Quarter Totals');
$oPage->setContent(createAdminQuarterContent( $date ));
$oPage->setLeftMenu( getEmployeesRibbon($oEmployee, $date["y"], 1) );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAdminQuarterContent( $date ) {
	global $oEmployee;

	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getQuarterRibbon( 'Quarter Totals: ' );

	if ( $oEmployee->getTimecardId() < 1 || $oEmployee->getTimecardId() == '' ) {
		$ret .= '<br>Please select an employee...';
	} else {
		$ret .= getQuarterTotals( $date, $oEmployee->getTimecardId(), 'admin_' );
	}

	return $ret;
}
