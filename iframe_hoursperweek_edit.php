<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/iframe.php', $connection_settings);
$oPage->setTitle('Timecard | Hours per week (edit)');
$oPage->setContent(createHoursperweekEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createHoursperweekEditContent() {
	global $protect, $dbhandleTimecard, $connection_settings;

	$ret = '';

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_form/class_form.inc.php");

	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_decimal.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");

	$oDb = new class_db($connection_settings, 'timecard');
	$oForm = new class_form($connection_settings, $oDb);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM HoursPerWeek WHERE ID=[FLD:ID] '
		, 'table' => 'HoursPerWeek'
		, 'inserttable' => 'HoursPerWeek'
		, 'primarykey' => 'ID'
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$id = $_GET["ID"];
	$id = trim($id);
	$id = class_website_protection::get_left_part($id);
	if ( $id < 0 || $id == '' ) {
		die('No user id');
	}
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'Employee'
		, 'fieldlabel' => 'Employee'
		, 'onNew' => $id
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'year'
		, 'fieldlabel' => 'Year'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => date("Y")
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'startmonth'
		, 'fieldlabel' => 'Month (start)'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => 1
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'endmonth'
		, 'fieldlabel' => 'Month (end)'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => 12
		)));

	$oForm->add_field( new class_field_decimal ( array(
		'fieldname' => 'hoursperweek'
		, 'fieldlabel' => 'Hours per week'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => 38
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'isdeleted'
		, 'fieldlabel' => 'isdeleted'
		)));

	// calculate form
	$ret .= $oForm->generate_form();

	return $ret;
}
?>