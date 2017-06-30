<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasDepartmentAuthorisation() && count( $oWebuser->getDepartmentHeadExtraRightsOnDepartments() ) == 0  && count( $oWebuser->getDepartmentHeadExtraRightsOnUsers() ) == 0 ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

$date = class_datetime::get_date($protect);

$oEmployee = new class_employee($protect->request('get', 'eid'), $settings);

// create webpage
$oPage = new class_page('design/page_admin.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('department.quartertotals'));
$oPage->setTitle('Timecard | Department Quarter Totals');
$oPage->setContent(createAdminQuarterContent( $date ));
$oPage->setLeftMenu( getDepartmentEmployeesRibbon( $oEmployee, $date["y"], 1 ) );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createAdminQuarterContent( $date ) {
	global $oEmployee;

	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getQuarterRibbon( 'Quarter Totals: ' );

	if ( $oEmployee->getTimecardId() < 1 || $oEmployee->getTimecardId() == '' ) {
		$ret .= '<br>Please select an employee...';
	} else {
		$ret .= getQuarterTotals( $date, $oEmployee->getTimecardId(), 'department_' );
	}

	return $ret;
}
