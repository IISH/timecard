<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('finad.feestdagen'));
$oPage->setTitle('Timecard | National holidays');
$oPage->setContent(createFeestdagenContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createFeestdagenContent() {
	global $settings, $oWebuser, $databases;

	// get design
	$design = new class_contentdesign("page_nationalholidays");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => 'SELECT * FROM Feestdagen WHERE 1=1 AND isdeleted=0 AND ( datum >= \'' . date('Y-m-d') . '\' AND datum <= \'' . (date('Y')+1) . '-01-10\' )'
		, 'count_source_type' => 'query'
		, 'order_by' => 'datum ASC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_date ( array(
		'fieldname' => 'datum'
		, 'fieldlabel' => 'Date'
		, 'format' => 'D j F Y'
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'omschrijving'
		, 'fieldlabel' => 'Description'
		)));

/*
	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'vooreigenrekening'
		, 'fieldlabel' => 'For own account'
		, 'show_different_values' => 1
		, 'different_true_value' => 'yes'
		, 'different_false_value' => 'no'
		)));
*/
	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
