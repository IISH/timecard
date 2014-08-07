<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.myshortcuts'));
$oPage->setTitle('Timecard | Shortcuts');
$oPage->setContent( createShortcutsList() );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createShortcutsList() {
	global $settings, $oWebuser;

	// get design
	$design = new class_contentdesign("page_shortcuts");

	// add header
	$ret = $design->getHeader();

	$oDate = new class_date( date("Y"), date("m"), date("d") );

	$oShortcuts = new class_shortcuts($oWebuser->getTimecardId(), $settings, $oDate);

	$records = '';
	foreach ( $oShortcuts->getAllShortcuts() as $shortcut) {
		if ( $shortcut["isvisible"] != '1' ) {
			$data["strike_start"] = '<strike>';
			$data["strike_end"] = '</strike>';
		} else {
			$data["strike_start"] = '';
			$data["strike_end"] = '';
		}

		$data["url"] = "myshortcuts_edit.php?ID=" . $shortcut["id"] . "&backurl=" . urlencode(get_current_url());
		$data["projectname"] = trim($shortcut["projectnummer"] . ' ' . $shortcut["projectname"]);
		$data["minutes"] = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($shortcut["minutes"]);
		$data["description"] = htmlspecialchars($shortcut["description"]);

		// fill template
		$recordTemplate = fillTemplate($design->getRecords(), $data);

		$records .= $recordTemplate;
	}

	$ret .= fillTemplate(
		$design->getContent()
		, array(
			"records" => $records
		, 'onclickurl' => "myshortcuts_edit.php?ID=0&backurl=" . urlencode(get_current_url())
		)
	);

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
