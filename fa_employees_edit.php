<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('finad.employees'));
$oPage->setTitle('Timecard | Employee (edit)');
$oPage->setContent(createEmployeesEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createEmployeesEditContent() {
	global $protect, $dbhandleTimecard, $connection_settings, $dbhandleProtime, $oWebuser;

	$oUser = new class_employee( $protect->request_positive_number_or_empty('get', 'ID') , $connection_settings );
	syncProtimeAndTimecardEmployeeData( $oUser->getTimecardId(), $oUser->getProtimeId() );

	$ret = "<h2>Employee (edit)</h2>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_form/employee_class_form.inc.php");

	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list_mssql.inc.php");

	$oDb = new class_db($connection_settings, 'timecard');
	$oForm = new employee_class_form($connection_settings, $oDb);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM Employees WHERE ID=[FLD:ID] '
		, 'table' => 'Employees'
		, 'inserttable' => 'Employees'
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
			, 'fieldlabel' => 'SA/2X login'
			, 'required' => 1
			, 'size' => 35
			)));
	} else {
		$oForm->add_field( new class_field_readonly ( array(
			'fieldname' => 'LongCode'
			, 'fieldlabel' => 'SA/2X login'
			)));
	}

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'LastName'
		, 'fieldlabel' => 'Last name'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'FirstName'
		, 'fieldlabel' => 'First name'
		)));

	$oForm->add_field( new class_field_list_mssql ( $connection_settings, array(
		'fieldname' => 'ProtimePersNr'
		, 'fieldlabel' => 'Protime link'
		, 'query' => "SELECT PERSNR, RTRIM(LTRIM(NAME)) + ', ' + RTRIM(LTRIM(FIRSTNAME)) AS FULLNAME FROM CURRIC WHERE 1=1 ORDER BY NAME, FIRSTNAME "
		, 'dbhandle' => $dbhandleProtime
		, 'rdbms' => 'mssql'

		, 'id_field' => 'PERSNR'
		, 'description_field' => 'FULLNAME'

		, 'empty_value' => '0'
		, 'required' => 0
		, 'show_empty_row' => true
		, 'onNew' => '0'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'KnawPersNr'
		, 'fieldlabel' => 'KNAW Pers. #'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'AfdelingsNummer'
		, 'fieldlabel' => 'Department'
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'is_test_account'
		, 'fieldlabel' => 'Is test account?'
		, 'required' => 0
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'last_user_login'
		, 'fieldlabel' => 'Last login'
		)));

	// calculate form
	$ret .= $oForm->generate_form();

	return $ret;
}
?>