<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.dailyautomaticadditions'));
$oPage->setTitle('Timecard | Daily automatic additions');
$oPage->setContent(createShortcutsContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createShortcutsContent() {
	global $settings, $oWebuser, $protect, $databases;

	// get design
	$design = new class_contentdesign("page_dailyautomaticadditions_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_time_double_field.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_time_single_field.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oForm = new class_form($settings, $oDb);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM DailyAutomaticAdditions WHERE ID=[FLD:ID] AND employee=' . $oWebuser->getTimecardId() . ' AND isdeleted=0 '
		, 'table' => 'DailyAutomaticAdditions'
		, 'primarykey' => 'ID'
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => 'Internal no.'
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'employee'
		, 'fieldlabel' => 'Employee'
		, 'onNew' => $oWebuser->getTimecardId()
		)));

	// 
	if ( $protect->request_positive_number_or_empty('get', "ID") == '' || $protect->request_positive_number_or_empty('get', "ID") == '0' ) {
		$currentValueOnNew = '';
	} else {
		$currentValueOnNew = ' OR ID=[CURRENTVALUE] ';
	}
	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'workcode'
		, 'fieldlabel' => 'Project'
		, 'xxxquery' => 'SELECT ID, Concat(Projectnummer, \' \', Description) AS ProjectNumberName FROM Workcodes WHERE ( isdisabled = 0 AND show_in_selectlist = 1 AND (lastdate IS NULL OR lastdate = \'\' OR lastdate >= \'' . date("Y-m-d") . '\') ) ' . $currentValueOnNew . ' ORDER BY Projectnummer, Description '
		, 'query' => 'SELECT ID, Concat(Projectnummer, \' \', Description) AS ProjectNumberName FROM Workcodes WHERE ( isdisabled = 0 AND (lastdate IS NULL OR lastdate = \'\' OR lastdate >= \'' . date("Y-m-d") . '\') ) ' . $currentValueOnNew . ' ORDER BY Projectnummer, Description '
		, 'id_field' => 'ID'
		, 'description_field' => 'ProjectNumberName'
		, 'empty_value' => '0'
		, 'required' => 1
		, 'show_empty_row' => true
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'ratio'
		, 'fieldlabel' => 'Minutes'
		, 'required' => 1
		, 'onNew' => 456
		, 'style' => 'width:425px;'
		)));

	$oForm->add_field( new class_field_textarea ( array(
		'fieldname' => 'description'
		, 'fieldlabel' => 'Description'
		, 'class' => 'resizable'
		, 'style' => 'width:425px;height:70px;'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'first_date'
		, 'fieldlabel' => 'Start date (yyyy-mm-dd)'
		, 'required' => 1
		, 'onNew' => date("Y-m-d")
		, 'style' => 'width:425px;'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'last_date'
		, 'fieldlabel' => 'Last date (yyyy-mm-dd)'
		, 'required' => 0
		, 'onNew' => ''
		, 'style' => 'width:425px;'
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'isenabled'
		, 'fieldlabel' => 'Enabled?'
		, 'onNew' => '1'
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'isdeleted'
		, 'fieldlabel' => 'Deleted?'
		, 'onNew' => '0'
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
