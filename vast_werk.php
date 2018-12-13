<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasDepartmentAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('projects.vastwerk'));
$oPage->setTitle('Timecard | Vast werk');
$oPage->setContent(createVastWerkContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createVastWerkContent() {
	global $settings, $databases, $dbConn;

	// get design
	$design = new class_contentdesign("page_vastwerk");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");

	$oView = new class_view($settings, $dbConn);

	$oView->set_view( array(
		'query' => "SELECT * FROM vw_VastWerk WHERE isdeleted=0 AND year>=" . date("Y")
		, 'count_source_type' => 'query'
		, 'order_by' => 'FULLNAME, year, period, description, hours '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		, 'add_new_url' => "vast_werk_edit.php?ID=0&backurl=[BACKURL]"
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'FULLNAME'
		, 'fieldlabel' => 'Naam'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'FULLNAME'
							, 'type' => 'string'
							, 'size' => 10
						)
					)
				)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'description'
		, 'fieldlabel' => 'Description'
		, 'href' => 'vast_werk_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'description'
							, 'type' => 'string'
							, 'size' => 10
						)
					)
				)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'year'
		, 'fieldlabel' => 'Year'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'year'
							, 'type' => 'string'
							, 'size' => 10
						)
					)
				)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'period'
		, 'fieldlabel' => 'Period'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'period'
							, 'type' => 'string'
							, 'size' => 10
						)
					)
				)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'hours'
		, 'fieldlabel' => 'Hours'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'hours'
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
