<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.myshortcuts'));
$oPage->setTitle('Timecard | My shortcuts');
$oPage->setContent( createShortcutsList() );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createShortcutsList() {
	global $connection_settings, $oWebuser, $settings_from_database;

	$records = '';

	$oDate = new class_date( date("Y"), date("m"), date("d") );

	$oShortcuts = new class_shortcuts($oWebuser->getTimecardId(), $connection_settings, $oDate);

	foreach ( $oShortcuts->getAllShortcuts() as $shortcut) {
		if ( $shortcut["isvisible"] != '1' ) {
			$data["strike_start"] = '<strike>';
			$data["strike_end"] = '</strike>';
		} else {
			$data["strike_start"] = '';
			$data["strike_end"] = '';
		}

		$data["url"] = "shortcuts_edit.php?ID=" . $shortcut["id"] . "&backurl=" . urlencode(get_current_url());
		$data["projectname"] = trim($shortcut["projectnummer"] . ' ' . $shortcut["projectname"]);
		$data["minutes"] = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($shortcut["minutes"]);
		$data["description"] = htmlspecialchars($shortcut["description"]);

		// fill template
		$recordTemplate = fillTemplate($settings_from_database["page_settings_shortcuts_records"], $data);

		$records .= $recordTemplate;
	}

	return fillTemplate(
			$settings_from_database["page_settings_shortcuts_table"]
			, array(
				"records" => $records
				, 'onclickurl' => "shortcuts_edit.php?ID=0&backurl=" . urlencode(get_current_url())
				)
		);
}
?>