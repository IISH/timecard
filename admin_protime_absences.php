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
$oPage->setTitle('Timecard | Absences');
$oPage->setContent(createAbsencesContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAbsencesContent() {
	global $connection_settings;

	$ret = "<h2>Absences</h2>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_view/class_view.inc.php");

	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");

	$oDb = new class_db($connection_settings, 'timecard');
	$oView = new class_view($connection_settings, $oDb);

	$oView->set_view( array(
		'query' => 'SELECT ProtimeAbsences.ID, ProtimeAbsences.description_nl AS protime_description, Workcodes2011.Description AS timecard_description FROM ProtimeAbsences LEFT OUTER JOIN Workcodes2011 ON ProtimeAbsences.workcode_id = Workcodes2011.ID '
		, 'count_source_type' => 'query'
		, 'order_by' => 'protime_description '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'protime_description'
		, 'fieldlabel' => 'Protime absence'
		, 'href' => 'admin_protime_absences_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'protime_description'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'timecard_description'
		, 'fieldlabel' => 'Timecard absence'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'timecard_description'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	// calculate and show view
	$ret .= $oView->generate_view();

	$ret .= "<span class=\"comment\"><br />Protime absenties die niet gekoppeld zijn aan een Timecard absentie worden niet automatisch geimporteerd en dus ook niet meegeteld.<br />De gebruiker moet voor deze absenties zelf de uren invoeren.<br />Voor 'Dienstreis', 'Werk buiten IISG' en 'Werk thuis' mag niet gekoppeld worden aan een timecard absentie, deze absenties moeten door de gebruiker zelf ingevoerd worden.</span>";

	return $ret;
}
?>