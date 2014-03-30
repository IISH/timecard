<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.feestdagen'));
$oPage->setTitle('Timecard | National holidays');
$oPage->setContent(createFeestdagenContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createFeestdagenContent() {
	global $settings, $oWebuser;

	$ret = "<h2>National holidays</h2>";

	require_once("./classes/class_db.inc.php");
	require_once("./classes/class_view/class_view.inc.php");

	require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oDb = new class_db($settings, 'timecard');
	$oView = new class_view($settings, $oDb);

	$add_new_url = '';
	if ( $oWebuser->hasAdminAuthorisation() ) {
		$add_new_url = "feestdagen_edit.php?ID=0&backurl=[BACKURL]";
	}

	$oView->set_view( array(
		'query' => 'SELECT * FROM Feestdagen WHERE 1=1 AND isdeleted=0 AND datum >= \'' . date('Y') . '\''
		, 'count_source_type' => 'query'
		, 'order_by' => 'datum ASC '
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
		, 'add_new_url' => $add_new_url
		));

	$oView->add_field( new class_field_date ( array(
		'fieldname' => 'datum'
		, 'fieldlabel' => 'Date'
		, 'if_no_value_value' => '-no value-'
		, 'format' => 'D j F Y'
		)));

	if ( $oWebuser->hasAdminAuthorisation() ) {
		$oView->add_field( new class_field_string ( array(
			'fieldname' => 'omschrijving'
			, 'fieldlabel' => 'Description'
			, 'href' => 'feestdagen_edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
			)));
	} else {
		$oView->add_field( new class_field_string ( array(
			'fieldname' => 'omschrijving'
			, 'fieldlabel' => 'Description'
			)));
	}

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'vooreigenrekening'
		, 'fieldlabel' => 'For own account'
		, 'show_different_values' => 1
		, 'different_true_value' => 'yes'
		, 'different_false_value' => ''
		)));

	// calculate and show view
	$ret .= $oView->generate_view();

	return $ret;
}
?>