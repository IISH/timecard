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
	global $protect, $dbhandleTimecard, $connection_settings;

	$ret = "<h2>Exports - Employee Project totals</h2>";

	require_once("./classes/class_db.inc.php");

	$oDb = new class_db($connection_settings, 'timecard');

	// connection to the database
	$dbhandleTimecard = mysql_connect($connection_settings["timecard_server"], $connection_settings["timecard_user"], $connection_settings["timecard_password"]) or die("Couldn't connect to SQL Server on: " . $connection_settings["timecard_server"]);

	// select a database to work with
	$selectedTimecard = mysql_select_db($connection_settings["timecard_database"], $dbhandleTimecard) or die("Couldn't open database " . $connection_settings["timecard_database"]);

	$selyear = substr($protect->request_positive_number_or_empty('get', "selyear"),0,4);
	if ( $selyear == '' ) {
		$selyear = date("Y");
	}

	$ret .= "
<script type=\"text/javascript\">
<!--
function createOverzicht(id) {
	var monthValue = 0;
	for (var i=0; i < document.frmOverzicht.fldMonth.length; i++) {
		if (document.frmOverzicht.fldMonth[i].checked) {
			monthValue = document.frmOverzicht.fldMonth[i].value;
		}
	}
	var url = '';
	if ( monthValue == '0' ) {
		url = 'admin_euprojecten_overzichten_xls_year.php?id=' + id + '&y=' + " . $selyear . ";
	} else {
		url = 'admin_euprojecten_overzichten_xls_month.php?id=' + id + '&y=' + " . $selyear . " + '&m=' + monthValue;
	}

	var newwindow = window.open(url, '_top');
	if (window.focus) {
		newwindow.focus();
	}
}
// -->
</script>

<form name=\"frmOverzicht\" action=\"\">
<table>
<tr>
	<td>Year: </td>
	<td>
";

	for ($i = (date("Y")-1); $i <= date("Y"); $i++) {
		if ( $i == $selyear ) {
			$ret .= "<a href=\"?selyear=" . $i . "\"><b>" . $i . "</b></a>";
		} else {
			$ret .= "<a href=\"?selyear=" . $i . "\">" . $i . "</a>";
		}
		$ret .= " &nbsp; &nbsp; ";
	}

	$ret .= "</td>
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td valign=\"top\">Month: </td>
	<td>
";

	$checkedMonth = date("m")-1;
	if ( $checkedMonth == 0 ) {
		$checkedMonth = 12;
	}
	for ( $i=1; $i<=12; $i++) {
		if ( $i>1 ) {
			$ret .= ' &nbsp; ';
		}

		$ret .= "<input type=\"radio\" name=\"fldMonth\" id=\"fldMonth\" value=\"" . $i . "\" " . (($i == $checkedMonth) ? 'CHECKED' : '') . " > " . date("M", mktime(0,0,0,$i,1,date("Y")));
	}

	$ret .= "<br><input type=\"radio\" name=\"fldMonth\" id=\"fldMonth\" value=\"0\" " . (('0' == $checkedMonth) ? 'CHECKED' : '') . " > Grouped by Month/Quarter/Year</td>";

	$ret .= "
</tr>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td valign=\"top\">Employee: </td><td>";

 	$selected_employee = "Please select an employee...";

	$separator = '';
	foreach ( getListOfUsersActiveInSpecificYear($selyear) as $user ) {
		$ret .= $separator . '<a href="#" onclick="createOverzicht(' . $user['id'] . ');return false;">' . trim($user["lastname"] .  ', ' . $user["firstname"]) . '</a>';
		$separator = ' &nbsp; ';
	}

	$ret .= "
	</td>
</tr>
</table>
</form>
";

	return $ret;
}
?>