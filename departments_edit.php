<?php 
require_once "classes/start.inc.php";
require_once("classes/class_misc.inc.php");

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.departments'));
$oPage->setTitle('Timecard | Department (edit)');
$oPage->setContent(createDepartmentsEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createDepartmentsEditContent() {
	global $protect, $settings, $oWebuser;

	$oMisc = new class_misc();

	// get design
	$design = new class_contentdesign("page_departments_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_iframe.inc.php");

	$oDb = new class_mysql($settings, 'timecard');
	$oForm = new class_form($settings, $oDb);

	$oForm->set_form( array(
		'query' => "SELECT * FROM Departments WHERE ID=[FLD:ID] "
		, 'table' => 'Departments'
		, 'primarykey' => 'ID'
		, 'disallow_delete' => 1
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'name'
		, 'fieldlabel' => 'Department'
		, 'required' => 1
		, 'size' => 35
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'isdisabled'
		, 'fieldlabel' => 'Is disabled?'
		, 'required' => 0
		)));

	$oForm->add_field( new class_field_iframe ( array(
		'fieldname' => 'FRM_EMPLOYEES'
		, 'fieldlabel' => 'Employees'
		, 'src' => $oMisc->PlaceURLParametersInQuery('department_employees.php?ID=[FLD:ID]&backurl=[BACKURL]')
		, 'style' => 'width: 500px; height: 200px; border: 1px #AAAAAA solid;'
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
