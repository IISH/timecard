<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('timecard.quartertotals'));
$oPage->setTitle('Timecard | Quarter Totals');
$oPage->setContent(createQuarterContent( $date ));

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createQuarterContent( $date ) {
	global $oWebuser;

	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getQuarterRibbon( 'Quarter Totals: ' );

	//
	$ret .= getQuarterTotals( $date, $oWebuser->getTimecardId(), '' );

	return $ret;
}
?>