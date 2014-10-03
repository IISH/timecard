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

// TODOEXPLAIN
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
		$data["hours"] = $oWebuser->calculateVacationHours();

		if ( $row["HoursDoubleField"] == '1' ) {
			$data["source"] = 'Double field';
			$data["target"] = 'single field';
		} else {
			$data["source"] = 'Single field';
			$data["target"] = 'double field';
		}

		// add content
		$ret .= fillTemplate($template, $data);
	}
	mysql_free_result($result);

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
