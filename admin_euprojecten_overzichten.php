<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasExportsAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('exports.euprojecten'));
$oPage->setTitle('Timecard | Exports - Employee Project totals');
$oPage->setContent(createEuProjectenContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createEuProjectenContent() {
	global $protect, $settings_from_database;

	$fields = array();

	require_once("./classes/class_db.inc.php");

	// get selected year from url
	$selyear = substr($protect->request_positive_number_or_empty('get', "selyear"),0,4);
	if ( $selyear == '' ) {
		$selyear = date("Y");
	}

	//
	if ( $selyear < date("Y") ) {
		$checkedMonth = 12;
	} else {
		$checkedMonth = date("m")-1;
		if ( $checkedMonth == 0 ) {
			$checkedMonth = 1;
		}
	}

	//
	$fields["selected_year"] = $selyear;

	//
	$fields["url_year"] = 'admin_euprojecten_overzichten_xls_year.php';
	$fields["url_month"] = 'admin_euprojecten_overzichten_xls_month.php';

	// make html list of years
	$list_of_years = '';
	for ($i = (date("Y")-1); $i <= date("Y"); $i++) {
		if ( $i == $selyear ) {
			$list_of_years .= "<a href=\"?selyear=" . $i . "\"><b>" . $i . "</b></a>";
		} else {
			$list_of_years .= "<a href=\"?selyear=" . $i . "\">" . $i . "</a>";
		}
		$list_of_years .= " &nbsp; &nbsp; ";
	}
	$fields['list_of_years'] = $list_of_years;

	// make html list of months
	$list_of_months = '';
	for ( $i=1; $i<=12; $i++) {
		if ( $i>1 ) {
			$list_of_months .= ' &nbsp; ';
		}
		$list_of_months .= "<input type=\"radio\" name=\"fldMonth\" id=\"fldMonth\" value=\"" . $i . "\" " . (($i == $checkedMonth) ? 'CHECKED' : '') . " > " . date("M", mktime(0,0,0,$i,1,date("Y")));
	}
	$list_of_months .= "<br><input type=\"radio\" name=\"fldMonth\" id=\"fldMonth\" value=\"0\" " . (('0' == $checkedMonth) ? 'CHECKED' : '') . " > Grouped by Month/Quarter/Year</td>";
	$fields['list_of_months'] = $list_of_months;

	// make html list of employees
	$list_of_employees = '';
	$separator = '';
	foreach ( getListOfUsersActiveInSpecificYear($selyear) as $user ) {
		$list_of_employees .= $separator . '<a href="#" onclick="createOverzicht(' . $user['id'] . ');return false;">' . trim($user["lastname"] .  ', ' . $user["firstname"]) . '</a>';
		$separator = '<br>';
	}
	$fields['list_of_employees'] = $list_of_employees;

	// return template with variables replaced by values
	return fillTemplate($settings_from_database["page_admin_euprojecten_overzichten"], $fields);
}
?>