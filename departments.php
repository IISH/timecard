<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
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

// TODOEXPLAIN
function createDepartmentsContent() {
	global $settings;

	// get design
	$design = new class_contentdesign("page_departments");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_button.inc.php");

	$oDb = new class_mysql($settings, 'timecard');
	$oView = new class_view($settings, $oDb);

	$oView->set_view( array(
		'query' => "SELECT * FROM Departments WHERE 1=1 AND isdisabled=0 AND isdeleted=0 "
		, 'count_source_type' => 'query'
		, 'order_by' => 'name, ID DESC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));
/*
	$oView->add_field( new class_field_button ( array(
		'buttonlabel' => '(Users)'
		, 'href' => 'departments_users.php?ID=[FLD:ID]&backurl=[BACKURL]'
		)));

	$oView->add_field( new class_field_button ( array(
		'buttonlabel' => '(Edit)'
		, 'href' => 'departments_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		)));
*/
	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'name'
		, 'fieldlabel' => 'Department'
		, 'if_no_value' => '-no value-'
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

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
