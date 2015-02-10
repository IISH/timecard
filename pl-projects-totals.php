<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() || $oWebuser->isProjectLeader() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pl.projects'));
$oPage->setTitle('Timecard | Project totals');
$oPage->setContent(createProjectContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createProjectContent() {
	global $settings, $databases, $oWebuser, $protect;

	// get design
	$design = new class_contentdesign("page_pl_projecttotals");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	$projectId = $protect->request_positive_number_or_empty('get', 'ID');
	$oProject = new class_project( $projectId );

	$year = $protect->request_positive_number_or_empty('get', 'y');
	if ( $year == '' ) {
		$year = date("Y");
	}
	$month = $protect->request_positive_number_or_empty('get', 'm');
	if ( $month == '' ) {
		$month = date("m");
	}

	if ( $year > date("Y")  ) {
		$year = date("Y");
		$month = date("m");
	} else {
	}

	$year = (int)$year;
	$month = (int)$month;

	$today = "*";
	$prev = "&laquo;";
	$next = "&raquo;";
	$template = "<a href=\"?ID={ID}&y={y}&m={m}\">{label}</a>";

	// PREVIOS MONTH
	$prevMonth = $month-1;
	if ( $prevMonth < 1 ) {
		$prevYear = $year-1;
		$prevMonth = 12;
	} else {
		$prevYear = $year;
	}

	//
	$dataPrev['label'] = $prev;
	$dataPrev['ID'] = $oProject->getId();
	$dataPrev['y'] = $prevYear;
	$dataPrev['m'] = $prevMonth;
	$prev = fillTemplate($template, $dataPrev);

	// NEXT MONTH
	$nextMonth = $month+1;
	if ( $nextMonth > 12 ) {
		$nextYear = $year+1;
		$nextMonth = 1;
	} else {
		$nextYear = $year;
	}

	//
	$dataNext['label'] = $next;
	$dataNext['ID'] = $oProject->getId();
	$dataNext['y'] = $nextYear;
	$dataNext['m'] = $nextMonth;
	$next = fillTemplate($template, $dataNext);

	// CURRENT DATE
	$dataToday['label'] = $today;
	$dataToday['ID'] = $oProject->getId();
	$dataToday['y'] = (int)date("Y");
	$dataToday['m'] = (int)date("m");
	$today = fillTemplate($template, $dataToday);

	//
	$oDate = new class_date($year, $month, 1);

	$ret .= "Project: " . $oProject->getDescription() . "<br>";
	$ret .= "Project number: " . $oProject->getProjectnumber() . "<br>";
	$ret .= "Project leader: " . $oProject->getProjectleader() . "<br>";
	$ret .= "Month: " . $prev . " " . $today . " " . $next . " " . $oDate->get("F Y") . "<br>";

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}