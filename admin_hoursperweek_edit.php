<?php
die('deprecated. contact gcu');

require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.urenperweek'));
$oPage->setTitle('Timecard | Hours per week (edit)');
$oPage->setContent(createHoursperweekEditContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createHoursperweekEditContent() {
	global $settings, $protect, $databases, $dbConn;

	// get design
	$design = new class_contentdesign("page_admin_hoursperweek_edit");

	// add header
	$ret = $design->getHeader();

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_decimal.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");

	$oForm = new class_form($settings, $dbConn);

	$oForm->set_form( array(
		'query' => 'SELECT * FROM HoursPerWeek WHERE ID=[FLD:ID] '
		, 'table' => 'HoursPerWeek'
		, 'primarykey' => 'ID'
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	$q = "SELECT Employees.ID, CONCAT( RTRIM( LTRIM( IFNULL(protime_curric.FIRSTNAME,'') ) ) , ' ', RTRIM( LTRIM( IFNULL(protime_curric.NAME,'') ) ), ' (#', Employees.ID, IF(Employees.is_test_account=1, ', testaccount', ''), ')' ) AS FULLNAME
FROM Employees
	LEFT JOIN protime_curric ON Employees.ProtimePersNr = protime_curric.PERSNR
	LEFT JOIN protime_worklocation ON protime_curric.WORKLOCATION = protime_worklocation.LOCATIONID
WHERE is_test_account=0
	AND ( isdisabled=0 OR Employees.ID=" . $protect->request('get', 'ID') . " )
ORDER BY FULLNAME ";

	$oForm->add_field( new class_field_list ( $settings, array(
		'fieldname' => 'Employee'
		, 'fieldlabel' => 'Employee'
		, 'query' => $q
		, 'id_field' => 'ID'
		, 'description_field' => 'FULLNAME'
		, 'empty_value' => '0'
		, 'required' => 1
		, 'show_empty_row' => true
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'year'
		, 'fieldlabel' => 'Year'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => date("Y")
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'startmonth'
		, 'fieldlabel' => 'Month (start)'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => 1
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'endmonth'
		, 'fieldlabel' => 'Month (end)'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => 12
		)));

	$oForm->add_field( new class_field_decimal ( array(
		'fieldname' => 'hoursperweek'
		, 'fieldlabel' => 'Hours per week'
		, 'required' => 1
		, 'style' => 'width:200px;'
		, 'onNew' => 38
		)));

	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'isdeleted'
		, 'fieldlabel' => 'isdeleted'
		)));

	// generate form
	$ret .= $oForm->generate_form();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
