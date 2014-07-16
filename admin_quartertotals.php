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
	$ribbon = getEmployeesRibbon($date["y"], 0);

	//
	$content = '';
	if ( $oEmployee->getTimecardId() != '' ) {
		$content = getQuarterTotals( $date, $oEmployee->getTimecardId(), 'admin_' );
	}

	$template = "<table border=\"0\"><tr><td valign=\"top\">::LEFT::</td><td valign=\"top\">::RIGHT::</td></tr></table>";
	$template =  str_replace("::LEFT::", $ribbon, $template);
	$template =  str_replace("::RIGHT::", $content, $template);

	$ret .= $template;

	return $ret;
}
?>