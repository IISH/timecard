<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.protimeabsenties'));
$oPage->setTitle('Timecard | Absences (edit)');
$oPage->setContent(createAbsencesContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAbsencesContent() {
	global $connection_settings;

	$ret = "<h2>Absences (edit)</h2>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_form/class_form.inc.php");

	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");

	$oDb = new class_db($connection_settings, 'timecard');
	$oForm = new class_form($connection_settings, $oDb);

	$oForm->set_form( array(
		'query' => 'SELECT ID, description_nl, description_en, workcode_id FROM ProtimeAbsences WHERE ID=[FLD:ID] '
		, 'table' => 'ProtimeAbsences'
		, 'inserttable' => 'ProtimeAbsences'
		, 'primarykey' => 'ID'
		, 'disallow_delete' => 1
		));

	// verplicht !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => 'ID'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'description_nl'
		, 'fieldlabel' => 'Protime description (Dutch)'
		)));

	$oForm->add_field( new class_field_readonly ( array(
		'fieldname' => 'description_en'
		, 'fieldlabel' => 'Protime description (English)'
		)));

	$oForm->add_field( new class_field_list ( $connection_settings, array(
		'fieldname' => 'workcode_id'
		, 'fieldlabel' => 'Timecard workcode'
		, 'query' => 'SELECT ID, Description FROM Workcodes2011 ORDER BY Description '
		, 'id_field' => 'ID'
		, 'description_field' => 'Description'
		, 'empty_value' => '0'
		, 'required' => 0
		, 'show_empty_row' => true
		, 'onNew' => 0
		)));

	// calculate form
	$ret .= $oForm->generate_form();

	$ret .= "<span class=\"comment\"><br>Protime absenties die niet gekoppeld zijn aan een Timecard absentie worden niet automatisch geimporteerd en dus ook niet meegeteld.<br>De gebruiker moet voor deze absenties zelf de uren invoeren.</span>";

	return $ret;
}
?>