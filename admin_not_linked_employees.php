<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.not_linked_employees'));
$oPage->setTitle('Timecard | Not Linked Employees');
$oPage->setContent(createEmployeesContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createEmployeesContent() {
	global $settings, $databases;

	// get design
	$design = new class_contentdesign("page_admin_not_linked_employees");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_integer.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => 'SELECT * FROM vw_Employees WHERE 1=1 AND isdisabled=0 AND ProtimePersNr=0 '
		, 'count_source_type' => 'query'
		, 'order_by' => 'FIRSTNAME, NAME, LongCode, ID DESC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'add_new_url' => "employees_edit.php?ID=0&backurl=[BACKURL]"
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'FIRSTNAME'
		, 'fieldlabel' => 'First name'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'FIRSTNAME'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'NAME'
		, 'fieldlabel' => 'Last name'
		, 'if_no_value' => '-no value-'
		, 'href' => 'employees_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'NAME'
						, 'type' => 'string'
						, 'size' => 10
						)
					)
				)
		)));

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
