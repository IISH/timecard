<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.dailyautomaticadditions'));
$oPage->setTitle('Timecard | Daily automatic additions');
$oPage->setContent( createShortcutsList() );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createShortcutsList() {
	global $oWebuser;

	// get design
	$design = new class_contentdesign("page_dailyautomaticadditions");

	// add header
	$ret = $design->getHeader();

	$records = '';
	$totalWeightOfEnabledDailyAddittions = $oWebuser->getTotalWeightOfEnabledDailyAdditions();
	if ( $totalWeightOfEnabledDailyAddittions == 0 ) {
		$totalWeightOfEnabledDailyAddittions = 1; // set at least 1, cannot divide by zero
	}

	foreach ( $oWebuser->getAllDailyAdditions() as $dailyaddition) {
		if ( $dailyaddition->getIsEnabled() != 1 ) {
			$data["strike_start"] = '<strike>';
			$data["strike_end"] = '</strike>';
			$data["ratio_extra"] = '';
		} else {
			$data["strike_start"] = '';
			$data["strike_end"] = '';
			$data["ratio_extra"] = '';
//			$data["ratio_extra"] = ' / ' . $totalWeightOfEnabledDailyAddittions . ' = ' . number_format(100.0*$dailyaddition->getRatio()/$totalWeightOfEnabledDailyAddittions,2) . '%';
		}

		$data["url"] = "dailyautomaticadditions_edit.php?ID=" . $dailyaddition->getId() . "&backurl=" . urlencode(get_current_url());
		$data["projectname"] = trim($dailyaddition->getWorkcodeProjectnumber() . ' ' . $dailyaddition->getWorkcodeDescription());
		$data["ratio"] = $dailyaddition->getRatio();
		$data["description"] = htmlspecialchars($dailyaddition->getDescription());
		$data["firstdate"] = $dailyaddition->getFirstDate();
		$data["lastdate"] = $dailyaddition->getLastDate();

		// fill template
		$recordTemplate = fillTemplate($design->getRecords(), $data);

		$records .= $recordTemplate;
	}

	$ret .= fillTemplate(
		$design->getContent()
		, array(
			"records" => $records
		, 'onclickurl' => "dailyautomaticadditions_edit.php?ID=0&backurl=" . urlencode(get_current_url())
		)
	);

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
?>