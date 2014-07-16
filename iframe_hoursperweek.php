<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/iframe.php', $settings);
$oPage->setTitle('Timecard | Hours per week');
$oPage->setContent(createHoursperweekContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createHoursperweekContent() {
	global $settings;

	$id = $_GET["ID"];
	$id = trim($id);
	$id = class_website_protection::get_left_part($id);
	if ( $id < 0 || $id == '' ) {
		$id = 0;
	}

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_decimal.inc.php");

	$oDb = new class_mysql($settings, 'timecard');
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => 'SELECT Employee, HoursPerWeek.ID, HoursPerWeek.year, HoursPerWeek.startmonth, HoursPerWeek.endmonth, HoursPerWeek.hoursperweek FROM HoursPerWeek WHERE 1=1 AND Employee=' . $id . ' AND HoursPerWeek.isdeleted=0 AND HoursPerWeek.year>=' . (date('Y')-1) . ' '
		, 'count_source_type' => 'query'
		, 'order_by' => 'HoursPerWeek.year DESC, HoursPerWeek.startmonth ASC, HoursPerWeek.endmonth ASC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => false
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'year'
		, 'fieldlabel' => 'Year'
		, 'if_no_value' => '-no value-'
		, 'href' => 'iframe_hoursperweek_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		)));

	$oView->add_field( new class_field_integer ( array(
		'fieldname' => 'startmonth'
		, 'fieldlabel' => 'Start month'
		)));

	$oView->add_field( new class_field_integer ( array(
		'fieldname' => 'endmonth'
		, 'fieldlabel' => 'End month'
		)));

	$oView->add_field( new class_field_decimal ( array(
		'fieldname' => 'hoursperweek'
		, 'fieldlabel' => 'Hours per week'
		)));

	// generate view
	$ret = $oView->generate_view();

	return $ret;
}
?>