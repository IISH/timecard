<?php
die('deprecated. contact gcu');

require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.urenperweek'));
$oPage->setTitle('Timecard | Hours per week');
$oPage->setContent(createHoursperweekContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createHoursperweekContent() {
	global $settings, $databases;

	// get design
	$design = new class_contentdesign("page_admin_hoursperweek");

	// add header
	$ret = $design->getHeader();

	if ( isset( $_GET["archive"] ) && $_GET["archive"] == 1 ) {
		// show all
		$yearcriterium = '';
		$ret .= '<font size=-2><em><a href="?">Hide archive</a></em></font><br><br>';
	} else {
		// show only last year
		$yearcriterium = 'AND HoursPerWeek.year>=' . date('Y');
		$ret .= '<font size=-2><em><a href="?archive=1">Show archive</a></em></font><br><br>';
	}

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_decimal.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => "SELECT HoursPerWeek.ID, HoursPerWeek.year, HoursPerWeek.startmonth, HoursPerWeek.endmonth, HoursPerWeek.hoursperweek, FULLNAME
				FROM HoursPerWeek 
				INNER JOIN vw_Employees ON HoursPerWeek.Employee=vw_Employees.ID
				WHERE 1=1 AND HoursPerWeek.isdeleted=0 " . $yearcriterium
		, 'count_source_type' => 'query'
		, 'order_by' => 'HoursPerWeek.year DESC, FULLNAME, HoursPerWeek.startmonth ASC, HoursPerWeek.endmonth ASC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'add_new_url' => "admin_hoursperweek_edit.php?ID=0&backurl=[BACKURL]"
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'year'
		, 'fieldlabel' => 'Year'
		, 'if_no_value' => '-no value-'
		, 'href' => 'admin_hoursperweek_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'year'
													, 'type' => 'string'
													, 'size' => 6
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'FULLNAME'
		, 'fieldlabel' => 'Employee'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'FULLNAME'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_integer ( array(
		'fieldname' => 'startmonth'
		, 'fieldlabel' => 'Month (start)'
		)));

	$oView->add_field( new class_field_integer ( array(
		'fieldname' => 'endmonth'
		, 'fieldlabel' => 'Month (end)'
		)));

	$oView->add_field( new class_field_decimal ( array(
		'fieldname' => 'hoursperweek'
		, 'fieldlabel' => 'Hours per week'
		)));

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
