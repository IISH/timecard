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
$oPage->setTab($menuList->findTabNumber('finad.worklocations'));
$oPage->setTitle('Timecard | Work locations');
$oPage->setContent(createWorkLocationsContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createWorkLocationsContent() {
	global $settings, $databases;

	// get design
	$design = new class_contentdesign("page_worklocation");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => 'SELECT * FROM protime_worklocation WHERE 1=1 '
		, 'count_source_type' => 'query'
		, 'order_by' => 'SHORT_1, LOCATIONID ASC '
		, 'anchor_field' => 'LOCATIONID'
		, 'viewfilter' => false
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));
/*
	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'LOCATIONID'
		, 'fieldlabel' => '#'
		)));
*/
	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'SHORT_1'
		, 'fieldlabel' => 'Work locations'
		)));

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
