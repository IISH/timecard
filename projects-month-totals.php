<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() || $oWebuser->isProjectLeader() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();

if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) {
	$oPage->setTab($menuList->findTabNumber('projects.projects_month'));
} else {
	$oPage->setTab($menuList->findTabNumber('projects.projects_month_pl'));
}

$oPage->setTitle('Timecard | Project Hours - Month overview - Totals');
$oPage->setContent(createProjectContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createProjectContent() {
	global $protect;

	// get design
	$design = new class_contentdesign("page_projects_month_totals");

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

	//
	$year = (int)$year;
	$month = (int)$month;

	$templateUrl = "<a href=\"?ID={ID}&y={y}&m={m}\" title=\"{alt}\">{label}</a>";

	// PREVIOS MONTH
	$prevMonth = $month-1;
	if ( $prevMonth < 1 ) {
		$prevYear = $year-1;
		$prevMonth = 12;
	} else {
		$prevYear = $year;
	}

	//
	$dataPrev['label'] = "&laquo;";
	$dataPrev['ID'] = $oProject->getId();
	$dataPrev['y'] = $prevYear;
	$dataPrev['m'] = $prevMonth;
	$dataPrev['alt'] = 'go to previous month';
	$prev = fillTemplate($templateUrl, $dataPrev);

	// NEXT MONTH
	$nextMonth = $month+1;
	if ( $nextMonth > 12 ) {
		$nextYear = $year+1;
		$nextMonth = 1;
	} else {
		$nextYear = $year;
	}

	//
	$dataNext['label'] = "&raquo;";
	$dataNext['ID'] = $oProject->getId();
	$dataNext['y'] = $nextYear;
	$dataNext['m'] = $nextMonth;
	$dataNext['alt'] = 'go to next month';
	$next = fillTemplate($templateUrl, $dataNext);

	// CURRENT DATE
	$dataToday['label'] = '*';
	$dataToday['ID'] = $oProject->getId();
	$dataToday['y'] = (int)date("Y");
	$dataToday['m'] = (int)date("m");
	$dataToday['alt'] = 'go to current month';
	$today = fillTemplate($templateUrl, $dataToday);

	//
	$oDate = new class_date($year, $month, 1);

	$ret .= "Project: " . $oProject->getDescription() . "<br>\n";
	$ret .= "Project number: " . $oProject->getProjectnumber() . "<br>\n";
	$projectLeaderName = '-';
	if ( $oProject->getProjectleader() != null ) {
		$projectLeaderName = $oProject->getProjectleader()->getFirstLastname();
	}
	$ret .= "Project leader: " . $projectLeaderName . "<br>\n";
	$ret .= "Month: " . $prev . " " . $today . " " . $next . " " . $oDate->get("F Y") . "<br>\n";

	//
	$body = "<br><table>\n";
	$total = 0.0;

	// get list of project workhours for specified period
	$workhours = class_workhours_static::getWorkhoursPerEmployeeGroupedMonth( $oProject->getId(), $year . '-' . substr('0'.$month, -2) );

	// name / hours
	foreach ($workhours as $p) {
		$body .= "<tr>\n\t<td>" . $p["employee"]->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin($p["employee"]->getLastname()) . " </td>";
		$body .= "<td align=right>" . number_format(class_misc::convertMinutesToHours($p["timeinminutes"]),2, ',', '.') . "</td>\n\t<td> hour(s)</td>\n</tr>";

		//
		$total += $p["timeinminutes"];
	}

	$body .= "<tr>\n\t<td><b>Total:</b></td>\n\t<td align=right><b>" . number_format(class_misc::convertMinutesToHours($total),2, ',', '.') . "</b></td>\n\t<td><b> hour(s)</b></td>\n</tr>";
	$body .= "</table>\n";

	$ret .= $body;

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}