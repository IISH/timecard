<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.shortcuts'));
$oPage->setTitle('Timecard | Shortcuts');
$oPage->setContent(createShortcutsContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createShortcutsContent() {
	global $settings, $oWebuser, $protect, $databases, $dbConn;

	// get design
	$design = new class_contentdesign("page_shortcuts_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_time_double_field.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_time_single_field.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_time_free_input_field.inc.php");

	$oForm = new class_form($settings, $dbConn);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM UserCreatedQuickAdds WHERE ID=[FLD:ID] AND Employee=' . $oWebuser->getTimecardId() . ' AND isdeleted=0 '
		, 'table' => 'UserCreatedQuickAdds'
		, 'primarykey' => 'ID'
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => 'Internal no.'
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'Employee'
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
		'fieldname' => 'WorkCode'
		, 'fieldlabel' => 'Project'
		, 'query' => 'SELECT ID, Concat(IFNULL(Projectnummer,\'\'), \' \', Description) AS ProjectNumberName FROM Workcodes WHERE ( isdisabled = 0 AND (lastdate IS NULL OR lastdate = \'\' OR lastdate >= \'' . date("Y-m-d") . '\') ) ' . $currentValueOnNew . ' ORDER BY Projectnummer, Description '
		, 'id_field' => 'ID'
		, 'description_field' => 'ProjectNumberName'
		, 'empty_value' => '0'
		, 'required' => 1
		, 'show_empty_row' => true
		)));

	// single select field, double select field or free input field
	if ( $oWebuser->getHoursdoublefield() == 2 ) {

		$oForm->add_field( new class_field_time_free_input_field ( array(
			'fieldname' => 'TimeInMinutes'
			, 'fieldlabel' => 'Time (h:mm)'
			, 'required' => 0
			, 'placeholder' => '0:00'
			, 'style' => 'width:60px;'
			)));

	} elseif ( $oWebuser->getHoursdoublefield() == 1 ) {

		$oForm->add_field( new class_field_time_double_field ( array(
			'fieldname' => 'TimeInMinutes'
			, 'fieldlabel' => 'Time (h:mm)'
			, 'required' => 1
			, 'possible_hour_values' => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10")
			, 'possible_minute_values' => array("00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55")
			)));

	} else {

		$oForm->add_field( new class_field_time_single_field ( array(
			'fieldname' => 'TimeInMinutes'
			, 'fieldlabel' => 'Time (h:mm)'
			, 'required' => 1
			, 'possible_hour_values' => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10")
			, 'possible_minute_values' => array("00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55")
			)));
	}

	$oForm->add_field( new class_field_textarea ( array(
		'fieldname' => 'WorkDescription'
		, 'fieldlabel' => 'Description'
		, 'class' => 'resizable'
		, 'style' => 'width:425px;height:50px;'
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'isvisible'
		, 'fieldlabel' => 'Show shortcut in day page?'
		, 'onNew' => '1'
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'onNewAutoSave'
		, 'fieldlabel' => 'Auto save new entries?'
		, 'onNew' => '0'
		)));

	$oForm->add_field( new class_field_textarea ( array(
		'fieldname' => 'extra_explanation'
		, 'fieldlabel' => 'Extra explanation'
		, 'class' => 'resizable'
		, 'style' => 'width:425px;height:50px;'
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
