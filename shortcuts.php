<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.myshortcuts'));
$oPage->setTitle('Timecard | My shortcuts');
$oPage->setContent( createShortcutsList( $oWebuser->getTimecardId() ) );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createShortcutsList( $userid ) {
	global $date, $connection_settings, $oWebuser, $template;

	// 
	$ret = "<h3>My shortcuts</h3>
&nbsp; &nbsp; &nbsp;<input type=\"button\" class=\"button\" name=\"cancelButton\" value=\"Add new\" onClick=\"open_page('shortcuts_edit.php?ID=0&backurl=" . urlencode(get_current_url()) . "');\"><br>
";

	$records = '';

	$oDate = new class_date( date("Y"), date("m"), date("d") );

	// 2011 - is the database structure version year
	$oShortcuts = new class_shortcuts($oWebuser->getTimecardId(), 2011, $connection_settings, $oDate);

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
		$recordTemplate = fillTemplate($template["settings"]["shortcuts"]["records"], $data);

		$records .= $recordTemplate;
	}

	$tableTemplate = fillTemplate($template["settings"]["shortcuts"]["table"], array("records" => $records));

	$ret .= $tableTemplate;

	return $ret;
}
?>