<?php
die('Disabled by GCU');

require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.departments'));
$oPage->setTitle('Timecard | Departments');
$oPage->setContent(createDepartmentsContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createDepartmentsContent() {
	global $settings, $databases;

	// get design
	$design = new class_contentdesign("page_departments_disabled");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => "SELECT Departments.ID, Departments.name, vw_Employees.FULLNAME FROM Departments LEFT JOIN vw_Employees ON Departments.head = vw_Employees.ID WHERE Departments.isenabled<>1 AND Departments.isdeleted=0 "
		, 'count_source_type' => 'query'
		, 'order_by' => 'Departments.name, Departments.ID DESC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'name'
		, 'fieldlabel' => 'Department'
		, 'href' => 'departments_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
			'labelfilterseparator' => '<br>'
			, 'filter' => array (
					array (
						'fieldname' => 'name'
						, 'type' => 'string'
						, 'size' => 10
					)
				)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'FULLNAME'
		, 'fieldlabel' => 'Head'
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

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
