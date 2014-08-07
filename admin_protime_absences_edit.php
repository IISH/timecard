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
$oPage->setTab($menuList->findTabNumber('misc.protimeabsenties'));
$oPage->setTitle('Timecard | Absences (edit)');
$oPage->setContent(createAbsencesContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAbsencesContent() {
	global $settings;

	// get design
	$design = new class_contentdesign("page_admin_protime_absences_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");

	$oDb = new class_mysql($settings, 'timecard');
	$oForm = new class_form($settings, $oDb);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM vw_ProtimeAbsences WHERE protime_absence_id=[FLD:protime_absence_id] '
		, 'table' => 'ProtimeAbsenceWorkcode'
		, 'primarykey' => 'protime_absence_id'
		, 'disallow_delete' => 1
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'protime_absence_id'
		, 'fieldlabel' => 'ID'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'SHORT_1'
		, 'fieldlabel' => 'Protime description (Dutch)'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'SHORT_2'
		, 'fieldlabel' => 'Protime description (English)'
		)));

	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'workcode_id'
		, 'fieldlabel' => 'Timecard workcode'
		, 'query' => 'SELECT ID, Description FROM Workcodes ORDER BY Description '
		, 'id_field' => 'ID'
		, 'description_field' => 'Description'
		, 'empty_value' => '0'
		, 'required' => 0
		, 'show_empty_row' => true
		, 'onNew' => 0
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
