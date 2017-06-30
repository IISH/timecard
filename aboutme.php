<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.personalinfo'));
$oPage->setTitle('Timecard | About me');
$oPage->setContent(createSettingsPage());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createSettingsPage() {
	global $settings, $oWebuser, $databases;

	// get design
	$design = new class_contentdesign("page_aboutme");

	// add header
	$ret = $design->getHeader();

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$query = "SELECT * FROM vw_Employees WHERE ID=" . $oWebuser->getTimecardId();
	$result = mysql_query($query, $oConn->getConnection());

	if ($row = mysql_fetch_assoc($result)) {
		$template = $design->getContent();

		$data["firstname"] = $row["FIRSTNAME"];
		$data["lastname"] = $row["NAME"];
		$data["longcode"] = $row["LongCode"];
		$data["hours"] = $oWebuser->calculateVacationHoursUntilToday();
		$data["checkinout"] = $oWebuser->getCheckInOut();

		// add content
		$ret .= fillTemplate($template, $data);
	}
	mysql_free_result($result);

	// add footer
	$ret .= $design->getFooter();

	// + + + + + + + + + + + + + + + + + +

	// PREFERENCES

	// get design
	$design = new class_contentdesign("page_preferences");

	// add header
	$ret .= $design->getHeader();

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$query = "SELECT * FROM vw_Employees WHERE ID=" . $oWebuser->getTimecardId();
	$result = mysql_query($query, $oConn->getConnection());

	if ($row = mysql_fetch_assoc($result)) {
		$template = $design->getContent();

		switch ( $row["HoursDoubleField"] ) {
			case "2":
				$data["tif_source"] = 'Free number field';
				break;
			case "1":
				$data["tif_source"] = 'Double select field';
				break;
			default:
				$data["tif_source"] = 'Single select field';
		}

		if ( $row["sort_projects_on_name"] == '1' ) {
			$data["pso_source"] = 'Project name';
		} else {
			$data["pso_source"] = 'Project number';
		}

		if ( $row["show_jira_field"] == '1' ) {
			$data["jira_source"] = 'Yes';
		} else {
			$data["jira_source"] = 'No';
		}

		// add content
		$ret .= fillTemplate($template, $data);
	}
	mysql_free_result($result);

	return $ret;
}
