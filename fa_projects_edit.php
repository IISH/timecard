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
$oPage->setTab($menuList->findTabNumber('finad.projecten'));
$oPage->setTitle('Timecard | Project (edit)');
$oPage->setContent(createProjectEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createProjectEditContent() {
	global $protect, $connection_settings;

	$ret = "<h2>Project (edit)</h2>";

	$id = $protect->request_positive_number_or_empty('get', "ID");
	if ( $id == '' ) {
		$id = '0';
	}

	$pid = $protect->request_positive_number_or_empty('get', "PID");
	if ( $pid == '' ) {
		$pid = '0';
	}

	if ( $id == '' || $id == '0' ) {
		$extra_project_filter = ' AND ParentID=0 ';
	} else {
		$extra_project_filter = ' AND ( ParentID=0 OR ID=' . $pid . ' ) ';
	}

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_form/class_form.inc.php");

	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_static_string_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");

	$oDb = new class_db($connection_settings, 'timecard');
	$oForm = new class_form($connection_settings, $oDb);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM Workcodes2011 WHERE ID=[FLD:ID] '
		, 'table' => 'Workcodes2011'
		, 'inserttable' => 'Workcodes2011'
		, 'primarykey' => 'ID'
		, 'disallow_delete' => 1
		));

	// verplicht !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$oForm->add_field( new class_field_list ( $connection_settings, array(
		'fieldname' => 'ParentID'
		, 'fieldlabel' => 'Menu Parent'
		, 'query' => 'SELECT ID, Description FROM Workcodes2011 WHERE 1=1 ' . $extra_project_filter . ' ORDER BY Description '

		, 'id_field' => 'ID'
		, 'description_field' => 'Description'

		, 'empty_value' => '0'
		, 'required' => 0
		, 'show_empty_row' => true
		, 'onNew' => $pid
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'Projectnummer'
		, 'fieldlabel' => 'Projectnumber'
		, 'required' => 0
		, 'onNew' => ''
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'Description'
		, 'fieldlabel' => 'Project'
		, 'required' => 1
		, 'onNew' => ''
		)));

	$oForm->add_field( new class_field_textarea ( array(
		'fieldname' => 'ExtraComment'
		, 'fieldlabel' => 'Description'
		, 'class' => 'resizable'
		, 'style' => 'width:425px;height:70px;'
		)));

//	$oForm->add_field( new class_field_bit ( array(
//		'fieldname' => 'show_separate_in_reports'
//		, 'fieldlabel' => 'Show separate in exports'
//		, 'onNew' => '0'
//		)));

//	$oForm->add_field( new class_field_bit ( array(
//		'fieldname' => 'oracle_export'
//		, 'fieldlabel' => 'Oracle Export?'
//		, 'onNew' => '1'
//		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'show_in_selectlist'
		, 'fieldlabel' => 'Show in Select List'
		, 'onNew' => '1'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'enddate'
		, 'fieldlabel' => 'End date (yyyy-mm-dd)'
		, 'size' => 10
		)));

	$oForm->add_field( new class_field_static_string_list ( array(
		'fieldname' => 'isdisabled'
		, 'fieldlabel' => 'Is disabled / Hide?'
		, 'choices' => array( array('0', 'no'), array('1', 'yes') )
		)));

	// calculate form
	$ret .= $oForm->generate_form();

	return $ret;
}
?>