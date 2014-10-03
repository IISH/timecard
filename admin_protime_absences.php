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
$oPage->setTitle('Timecard | Absences');
$oPage->setContent(createAbsencesContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAbsencesContent() {
	global $settings, $databases;

	// get design
	$design = new class_contentdesign("page_admin_protime_absences");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => "
SELECT vw_ProtimeAbsences.protime_absence_id, vw_ProtimeAbsences.SHORT_1 AS protime_description, Workcodes.Description AS timecard_description
FROM vw_ProtimeAbsences
	LEFT OUTER JOIN Workcodes ON vw_ProtimeAbsences.workcode_id = Workcodes.ID "
		, 'count_source_type' => 'query'
		, 'order_by' => 'protime_description '
		, 'anchor_field' => 'protime_absence_id'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'protime_description'
		, 'fieldlabel' => 'Protime absence'
		, 'href' => 'admin_protime_absences_edit.php?protime_absence_id=[FLD:protime_absence_id]&backurl=[BACKURL]'
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

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
