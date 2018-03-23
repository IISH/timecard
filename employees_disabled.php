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
$oPage->setTab($menuList->findTabNumber('finad.employees'));
$oPage->setTitle('Timecard | Employees (disabled)');
$oPage->setContent(createEmployeesContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createEmployeesContent() {
	global $settings, $databases, $dbConn;

	// get design
	$design = new class_contentdesign("page_employees_disabled");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oView = new class_view($settings, $dbConn);

	$oView->set_view( array(
		'query' => "SELECT * FROM vw_Employees WHERE isdisabled=1 OR lastyear<" . date("Y")
		, 'count_source_type' => 'query'
		, 'order_by' => 'LongCode, ID DESC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		, 'href' => 'employees_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'ID'
						, 'type' => 'string'
						, 'size' => 2
						)
					)
				)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'FIRSTNAME'
		, 'fieldlabel' => 'First name'
		, 'if_no_value' => '-'
		, 'href' => 'employees_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'FIRSTNAME'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'NAME'
		, 'fieldlabel' => 'Last name'
		, 'viewfilter' => array(
				'labelfilterseparator' => '<br>'
				, 'filter' => array (
						array (
							'fieldname' => 'NAME'
						, 'type' => 'string'
						, 'size' => 10
						)
					)
				)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'LongCode'
		, 'fieldlabel' => 'IISG SA login'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'LongCode'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'LongCode'
		, 'fieldlabel' => 'IISG SA login'
		, 'viewfilter' => array(
			'labelfilterseparator' => '<br>'
			, 'filter' => array (
					array (
						'fieldname' => 'LongCode'
						, 'type' => 'string'
						, 'size' => 10
					)
				)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'REGISTERNR'
		, 'fieldlabel' => 'KNAW #'
		, 'viewfilter' => array(
							'labelfilterseparator' => '<br>'
							, 'filter' => array (
												array (
													'fieldname' => 'REGISTERNR'
													, 'type' => 'string'
													, 'size' => 10
												)
											)
							)
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'is_test_account'
		, 'fieldlabel' => 'Test?'
		, 'show_different_values' => 1
		, 'different_true_value' => 'test'
		, 'different_false_value' => ''
	)));
/*
	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'SHORT_1'
		, 'fieldlabel' => 'Work location'
        , 'viewfilter' => array(
                'labelfilterseparator' => '<br>'
            , 'filter' => array (
                    array (
                        'fieldname' => 'SHORT_1'
                    , 'type' => 'string'
                    , 'size' => 10
                    )
                )
            )
		)));
*/
	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'allow_additions_starting_date'
		, 'fieldlabel' => 'Allow additions'
		, 'viewfilter' => array(
			'labelfilterseparator' => '<br>'
			, 'filter' => array (
				array (
					'fieldname' => 'allow_additions_starting_date'
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
