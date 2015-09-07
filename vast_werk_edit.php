<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasDepartmentAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('fl.vastwerk'));
$oPage->setTitle('Timecard | Vast werk (edit)');
$oPage->setContent(createVastWerkContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createVastWerkContent() {
	global $protect, $settings, $oWebuser, $databases;

	// get design
	$design = new class_contentdesign("page_vastwerk_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_decimal.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_static_string_list.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oForm = new class_form($settings, $oDb);

	$oForm->set_form( array(
		'query' => "SELECT * FROM VastWerk WHERE ID=[FLD:ID] "
		, 'table' => 'VastWerk'
		, 'primarykey' => 'ID'
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'EmployeeID'
		, 'fieldlabel' => 'Name'
		, 'query' => "SELECT ID, CONCAT(RTRIM(LTRIM(FIRSTNAME)), ' ', RTRIM(LTRIM(NAME)), ' (#', ID, IF(is_test_account=1, ', testaccount', ''), ')') AS FULLNAME FROM vw_Employees WHERE isdisabled=0 AND is_test_account=0 ORDER BY FIRSTNAME, NAME "

		, 'id_field' => 'ID'
		, 'description_field' => 'FULLNAME'

		, 'empty_value' => '0'
		, 'required' => 1
		, 'show_empty_row' => true
		, 'onNew' => '0'
		)));

	$yearChoices = array();
	for ( $i = 0; $i <= 4; $i++ ) {
		$yearChoices[] = date("Y")+$i;
	}
	$oForm->add_field( new class_field_static_string_list ( array(
		'fieldname' => 'year'
		, 'fieldlabel' => 'Year'
		, 'choices' => $yearChoices
		, 'required' => 1
		)));

	$periodChoices = array();
	for ( $i = 1; $i <= 12; $i++ ) {
		$periodChoices[] = 'M' . $i;
	}
	for ( $i = 1; $i <= 4; $i++ ) {
		$periodChoices[] = 'Q' . $i;
	}
	$periodChoices[] = 'Y';
	$oForm->add_field( new class_field_static_string_list ( array(
		'fieldname' => 'period'
		, 'fieldlabel' => 'Period'
		, 'choices' => $periodChoices
		, 'required' => 1
		)));

	$oForm->add_field( new class_field_decimal ( array(
		'fieldname' => 'hours'
		, 'fieldlabel' => 'Hours'
		, 'size' => 6
		, 'required' => 1
		)));

	$oForm->add_field( new class_field_textarea ( array(
		'fieldname' => 'description'
		, 'fieldlabel' => 'Description'
		, 'xxxsize' => 6
		, 'required' => 1
		, 'class' => 'resizable'
		, 'style' => 'width:350px;height:60px;'
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'isdeleted'
		, 'fieldlabel' => 'Is deleted?'
		, 'XXXonNew' => '0'
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
