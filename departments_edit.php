<?php
die('deprecated. contact gcu');

require_once "classes/start.inc.php";
require_once("classes/class_misc.inc.php");

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
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

function createDepartmentsEditContent() {
	global $protect, $settings, $oWebuser, $databases, $dbConn;

	$oMisc = new class_misc();

	// get design
	$design = new class_contentdesign("page_departments_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_iframe.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_remark.inc.php");

	$oForm = new class_form($settings, $dbConn);

	$oForm->set_form( array(
		'query' => "SELECT * FROM Departments WHERE ID=[FLD:ID] "
		, 'table' => 'Departments'
		, 'primarykey' => 'ID'
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

	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'head'
		, 'fieldlabel' => 'Head'
		, 'query' => "SELECT ID, CONCAT(RTRIM(LTRIM(FIRSTNAME)), ' ', RTRIM(LTRIM(NAME)), ' (#', ID, IF(is_test_account=1, ', testaccount', ''), ')') AS FULLNAME FROM vw_Employees WHERE isdisabled=0 ORDER BY FIRSTNAME, NAME "

		, 'id_field' => 'ID'
		, 'description_field' => 'FULLNAME'

		, 'empty_value' => '0'
		, 'required' => 0
		, 'show_empty_row' => true
		, 'onNew' => '0'
		)));

	$oForm->add_field( new class_field_static_string_list ( array(
		'fieldname' => 'enable_weekly_report_mail'
		, 'fieldlabel' => 'Send weekly mail report?'
		, 'onNew' => '1'
		, 'choices' => array( array('1', 'yes'), array('0', 'no') )
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'isenabled'
		, 'fieldlabel' => 'Is enabled?'
		, 'onNew' => '1'
		)));

	if ( $_GET["ID"] != '' && $_GET["ID"] != '0' ) {
		$oForm->add_field( new class_field_iframe ( array(
			'fieldname' => 'FRM_EMPLOYEES'
			, 'fieldlabel' => 'Employees'
			, 'src' => $oMisc->PlaceURLParametersInQuery('department_employees.php?ID=[FLD:ID]&backurl=[BACKURL]')
			, 'style' => 'width: 500px; height: 200px; border: 1px #AAAAAA solid;'
			)));
	} else {
		$oForm->add_field( new class_field_remark ( array(
			'onNew' => 'You have to save the department first before you can add employees to it.'
			, 'fieldlabel' => 'Employees'
			)));
	}

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
