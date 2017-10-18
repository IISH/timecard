<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
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
	$design = new class_contentdesign("page_employees_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oForm = new class_form($settings, $oDb);

	$oForm->set_form( array(
		'query' => "SELECT * FROM vw_Employees WHERE ID=[FLD:ID] "
		, 'table' => 'Employees'
		, 'primarykey' => 'ID'
		, 'disallow_delete' => 1
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	if ( $oWebuser->hasAdminAuthorisation() ) {
		$oForm->add_field( new class_field_string ( array(
			'fieldname' => 'LongCode'
			, 'fieldlabel' => 'SA login (firstname.lastname)'
			, 'required' => 1
			, 'size' => 35
			)));

		$oForm->add_field( new class_field_string ( array(
			'fieldname' => 'LongCodeKnaw'
			, 'fieldlabel' => 'KNAW login (FirstnameL)'
			, 'required' => 1
			, 'size' => 35
			)));
	} else {
		$oForm->add_field( new class_field_readonly ( array(
			'fieldname' => 'LongCode'
			, 'fieldlabel' => 'SA login'
			)));

		$oForm->add_field( new class_field_readonly ( array(
			'fieldname' => 'LongCodeKnaw'
			, 'fieldlabel' => 'KNAW login'
			)));
	}

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'FIRSTNAME'
		, 'fieldlabel' => 'First name'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'NAME'
		, 'fieldlabel' => 'Last name'
		)));

	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'ProtimePersNr'
		, 'fieldlabel' => 'Protime link'
		, 'query' => "SELECT PERSNR, CONCAT(RTRIM(LTRIM(FIRSTNAME)), ' ', RTRIM(LTRIM(NAME))) AS FULLNAME FROM protime_curric WHERE 1=1 ORDER BY FIRSTNAME, NAME "

		, 'id_field' => 'PERSNR'
		, 'description_field' => 'FULLNAME'

		, 'empty_value' => '0'
		, 'required' => 0
		, 'show_empty_row' => true
		, 'onNew' => '0'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'allow_additions_starting_date'
		, 'fieldlabel' => 'Allow additions starting (yyyy-mm-dd)'
		, 'size' => 35
		, 'onNew' => date("Y-m-01")
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'REGISTERNR'
		, 'fieldlabel' => 'KNAW #'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'SHORT_1'
		, 'fieldlabel' => 'Work location'
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'export_to_oracle'
		, 'fieldlabel' => 'Export to Oracle?'
		, 'onNew' => 1
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'isdisabled'
		, 'fieldlabel' => 'Check if disabled?'
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'is_test_account'
		, 'fieldlabel' => 'Check if test account?'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'last_user_login'
		, 'fieldlabel' => 'Last login'
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
