<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('finad.employees'));
$oPage->setTitle('Timecard | Employees');
$oPage->setContent(createEmployeesContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createEmployeesContent() {
	global $settings;

	$ret = "<h2>Employees</h2>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_view/class_view.inc.php");

	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oDb = new class_db($settings, 'timecard');
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => 'SELECT * FROM Employees WHERE 1=1 '
		, 'count_source_type' => 'query'
		, 'order_by' => 'Lastname, Firstname, LongCode, ID DESC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'add_new_url' => "fa_employees_edit.php?ID=0&backurl=[BACKURL]"
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'LastName'
		, 'fieldlabel' => 'Last name'
		, 'if_no_value_value' => '-no value-'
		, 'href' => 'fa_employees_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'href_alttitle' => 'Edit employee info'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'LastName'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'FirstName'
		, 'fieldlabel' => 'First name'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'FirstName'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'LongCode'
		, 'fieldlabel' => 'SA/2X login'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'LongCode'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'KnawPersNr'
		, 'fieldlabel' => 'KNAW Pers. #'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'KnawPersNr'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'AfdelingsNummer'
		, 'fieldlabel' => 'Department'
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'is_test_account'
		, 'fieldlabel' => 'Test account?'
		, 'show_different_values' => 1
		, 'different_true_value' => 'test'
		, 'different_false_value' => ''
		)));

/*
	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'ProtimePersNr'
		, 'fieldlabel' => 'Protime ID'
		)));
*/
	// calculate and show view
	$ret .= $oView->generate_view();

	return $ret;
}
?>