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
$oPage->setTab($menuList->findTabNumber('misc.feestdagen'));
$oPage->setTitle('Timecard | National holidays (edit)');
$oPage->setContent(createFeestdagenEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createFeestdagenEditContent() {
	global $protect, $dbhandleTimecard, $connection_settings;

	$ret = "<h2>National holidays (edit)</h2>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_form/class_form.inc.php");

	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");

	$oDb = new class_db($connection_settings, 'timecard');
	$oForm = new class_form($connection_settings, $oDb);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM Feestdagen WHERE ID=[FLD:ID] '
		, 'table' => 'Feestdagen'
		, 'inserttable' => 'Feestdagen'
		, 'primarykey' => 'ID'
		));

	// verplicht !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'datum'
		, 'fieldlabel' => 'Date (yyyy-mm-dd)'
		, 'required' => 1
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'omschrijving'
		, 'fieldlabel' => 'Description'
		, 'required' => 1
		)));

	$oForm->add_field( new class_field_bit ( array(
		'fieldname' => 'vooreigenrekening'
		, 'fieldlabel' => 'Voor eigen rekening'
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'isdeleted'
		, 'fieldlabel' => 'isdeleted'
		)));

	// calculate form
	$ret .= $oForm->generate_form();

	return $ret;
}
?>