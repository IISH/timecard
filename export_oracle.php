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
$oPage->setTab($menuList->findTabNumber('exports.oracle'));
$oPage->setTitle('Timecard | Exports - Oracle');
$oPage->setContent(createExportOracleContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createExportOracleContent() {
	$ret = "<h2>Exports - Oracle</h2>";

	require_once("./classes/class_db.inc.php");

	$ret .= "
<form name=\"frmOverzicht\" action=\"export_oracle_xls.php\" method=\"get\">
<table>
<tr>
	<td>Year: </td>
	<td>
";

	// checked year/month
	$checkedyear = date("Y");
	$checkedMonth = date("m")-1;
	if ( $checkedMonth == 0 ) {
		$checkedMonth = 12;
		$checkedyear--;
	}

	// show years
	for ($i = (date("Y")-1); $i <= date("Y"); $i++) {
		$ret .= "<input type=\"radio\" name=\"year\" id=\"year\" value=\"" . $i . "\" " . (($i == $checkedyear) ? 'CHECKED' : '') . " > " . $i . ' &nbsp; ';
	}

	$ret .= "</td>
</tr>";

	// show months
	$ret .= "
<tr>
	<td valign=\"top\">Month: </td>
	<td>
";

	for ( $i=1; $i<=12; $i++) {
		if ( $i>1 ) {
			$ret .= ' &nbsp; ';
		}
		$ret .= "<input type=\"radio\" name=\"month\" id=\"month\" value=\"" . $i . "\" " . (($i == $checkedMonth) ? 'CHECKED' : '') . " > " . date("M", mktime(0,0,0,$i,1,date("Y")));
	}

	$ret .= "</td>
</tr>
</tr>";

	// show submit button
	$ret .= "
<tr>
	<td></td><td>&nbsp;<br><input type=\"hidden\" name=\"output\" value=\"xlsx\"><input type=\"submit\" value=\"Create Export\"></td>
</tr>
</table>
</form>
";

	return $ret;
}
?>