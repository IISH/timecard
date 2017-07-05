<?php
die('deprecated. contact gcu');

require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/iframe.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.departments'));
$oPage->setTitle('Timecard | Departments');
$oPage->setContent(createDepartmentsContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createDepartmentsContent() {
	global $settings, $protect, $databases;

	// get design
	$design = new class_contentdesign("page_department_employees");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => "SELECT * FROM vw_Employees INNER JOIN DepartmentEmployee ON vw_Employees.ID=DepartmentEmployee.EmployeeID WHERE DepartmentEmployee.DepartmentID=[FLD:ID] and DepartmentEmployee.isdeleted=0 "
		, 'count_source_type' => 'query'
		, 'order_by' => 'FIRSTNAME, NAME, vw_Employees.ID DESC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		, 'add_new_url' => "department_employees_edit.php?ID=0&DepID=" . $protect->request_positive_number_or_empty('get', "ID") . "&backurl=[BACKURL]"
		, 'show_view_header' => false
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'FIRSTNAME'
		, 'fieldlabel' => 'First name'
		, 'if_no_value' => '-no value-'
		, 'href' => 'department_employees_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'NAME'
		, 'fieldlabel' => 'Last name'
		, 'if_no_value' => '-no value-'
		, 'href' => 'department_employees_edit.php?ID=[FLD:ID]&DepID=[FLD:DepartmentID]&backurl=[BACKURL]'
		)));

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
