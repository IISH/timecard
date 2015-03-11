<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

//if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) ) {
//	echo "You are not authorized to access this page.<br>";
//	die('Go to <a href="index.php">timecard home</a>');
//}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('projects.projects_year'));
$oPage->setTitle('Timecard | Project hours - Year overview');
$oPage->setContent(createProjectContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createProjectContent() {
	global $settings, $databases, $oWebuser;

	// get design
	$design = new class_contentdesign("page_projects_year_admin");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");

	$oDb = new class_mysql($databases['default']);
	$oView = new class_view($settings, $oDb);

	//
	$oView->set_view( array(
		'query' => "SELECT Workcodes.*, vw_Employees.FULLNAME  FROM Workcodes LEFT JOIN vw_Employees ON Workcodes.projectleader = vw_Employees.ID WHERE 1=1 "
		, 'count_source_type' => 'query'
		, 'order_by' => 'Workcodes.Description, Workcodes.ID DESC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'Description'
		, 'fieldlabel' => 'Project'
		, 'if_no_value' => '-no value-'
		, 'href' => 'projects-year-totals.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
			'labelfilterseparator' => '<br>'
			, 'filter' => array (
				array (
					'fieldname' => 'Description'
					, 'type' => 'string'
					, 'size' => 10
					)
				)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'Projectnummer'
		, 'fieldlabel' => 'Project number'
		, 'viewfilter' => array(
			'labelfilterseparator' => '<br>'
			, 'filter' => array (
					array (
						'fieldname' => 'Projectnummer'
						, 'type' => 'string'
						, 'size' => 10
					)
				)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'lastdate'
		, 'fieldlabel' => 'End date'
		, 'viewfilter' => array(
			'labelfilterseparator' => '<br>'
			, 'filter' => array (
					array (
						'fieldname' => 'lastdate'
						, 'type' => 'string'
						, 'size' => 10
					)
				)
			)
		)));

	// show project leader only if admin or FinAdm
	// don't show if projectleader
	if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() ) {
		$oView->add_field( new class_field_string ( array(
			'fieldname' => 'FULLNAME'
			, 'fieldlabel' => 'Project leader'
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
	}

	// generate view
	$ret .= $oView->generate_view();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}