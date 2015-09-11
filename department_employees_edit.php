<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/iframe.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('finad.employees'));
$oPage->setTitle('Timecard | Employee (edit)');
$oPage->setContent(createEmployeesEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createEmployeesEditContent() {
	global $protect, $settings, $oWebuser, $databases;

	// get design
	$design = new class_contentdesign("page_department_employees_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
//	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
//	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
//	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
//	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oForm = new class_form($settings, $oDb);

	$oForm->set_form( array(
		'query' => "SELECT * FROM DepartmentEmployee WHERE ID=[FLD:ID] "
		, 'table' => 'DepartmentEmployee'
		, 'primarykey' => 'ID'
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'DepartmentID'
		, 'fieldlabel' => 'Department'
		, 'onNew' => $protect->request_positive_number_or_empty('get', "DepID")
		)));

	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'EmployeeID'
		, 'fieldlabel' => 'Employee'
		, 'query' => "SELECT ID, CONCAT(RTRIM(LTRIM(FIRSTNAME)), ' ', RTRIM(LTRIM(NAME)), ' (#', ID, IF(is_test_account=1, ', testaccount', ''), ')') AS FULLNAME FROM vw_Employees WHERE isdisabled=0 ORDER BY FIRSTNAME, NAME "

		, 'id_field' => 'ID'
		, 'description_field' => 'FULLNAME'

		, 'empty_value' => '0'
		, 'required' => 1
		, 'show_empty_row' => true
		, 'onNew' => '0'
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'isdeleted'
		, 'fieldlabel' => 'Delete?'
		, 'onNew' => '0'
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
