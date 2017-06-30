<?php
die('deprecated. contact gcu');

require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();

if ( !isset($tab) ) {
	$tab = '';
}
if (  $tab == 'exports' ) {
	$oPage->setTab($menuList->findTabNumber('exports.projectemployeetotaals'));
} else {
	$oPage->setTab($menuList->findTabNumber('projects.project_hour_totals'));
}
$oPage->setTitle('Timecard | Project hours - Totals');
$oPage->setContent(createProjectContent( $tab ));

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createProjectContent( $tab ) {
	global $settings, $databases, $oWebuser;

	// get design
	$design = new class_contentdesign("page_project_employee_totals");

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
		, 'href' => 'project_totals.php?ID=[FLD:ID]&tab=' . $tab . '&backurl=[BACKURL]'
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
		, 'fieldlabel' => 'Project number&nbsp;'
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
		, 'XXXviewfilter' => array(
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