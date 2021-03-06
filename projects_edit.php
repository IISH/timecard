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
$oPage->setTab($menuList->findTabNumber('finad.projects'));
$oPage->setTitle('Timecard | Project (edit)');
$oPage->setContent(createProjectEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createProjectEditContent() {
	global $protect, $settings, $databases, $dbConn;

	// get design
	$design = new class_contentdesign("page_projects_edit");

	// add header
	$ret = $design->getHeader();

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

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_static_string_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");

	$oForm = new class_form($settings, $dbConn);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM Workcodes WHERE ID=[FLD:ID] '
		, 'table' => 'Workcodes'
		, 'primarykey' => 'ID'
		, 'disallow_delete' => 1
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'Projectnummer'
		, 'fieldlabel' => 'Projectnumber'
		, 'required' => 0
		, 'onNew' => ''
		, 'style' => 'width:425px;'
		, 'convertEmptyToNull' => 1
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'Description'
		, 'fieldlabel' => 'Project'
		, 'required' => 1
		, 'onNew' => ''
		, 'style' => 'width:425px;'
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'hours_estimated'
		, 'fieldlabel' => 'Hours estimated'
		, 'required' => 1
		, 'onNew' => '0'
		, 'style' => 'width:425px;'
		)));

	$oForm->add_field( new class_field_textarea ( array(
		'fieldname' => 'ExtraComment'
		, 'fieldlabel' => 'Description'
		, 'class' => 'resizable'
		, 'style' => 'width:425px;height:70px;'
		)));

	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'projectleader'
		, 'fieldlabel' => 'Project leader'
		, 'query' => "SELECT ID, CONCAT(RTRIM(LTRIM(FIRSTNAME)), ' ', RTRIM(LTRIM(NAME)), ' (#', ID, IF(is_test_account=1, ', testaccount', ''), ')') AS FULLNAME FROM vw_Employees WHERE isdisabled=0 AND FIRSTNAME IS NOT NULL AND NAME IS NOT NULL ORDER BY FIRSTNAME, NAME "
		, 'id_field' => 'ID'
		, 'description_field' => 'FULLNAME'
		, 'empty_value' => '0'
		, 'required' => 0
		, 'show_empty_row' => true
		, 'onNew' => '0'
		)));

	$oForm->add_field( new class_field_static_string_list ( array(
		'fieldname' => 'enable_weekly_report_mail'
		, 'fieldlabel' => 'Send weekly mail report?'
		, 'onNew' => '1'
		, 'choices' => array( array('1', 'yes'), array('0', 'no') )
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'lastdate'
		, 'fieldlabel' => 'End date (yyyy-mm-dd)'
		, 'size' => 10
		)));

	$oForm->add_field( new class_field_static_string_list ( array(
		'fieldname' => 'isdisabled'
		, 'fieldlabel' => 'Is disabled / Hide?'
		, 'choices' => array( array('0', 'no'), array('1', 'yes') )
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
