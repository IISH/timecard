<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.personalinfo'));
$oPage->setTitle('Timecard | About me');
$oPage->setContent(createSettingsPage());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createSettingsPage() {
	global $dbhandleTimecard, $oWebuser;

	$ret = '<h2>About me</h2>';

	$query = "SELECT * FROM Employees WHERE ID=" . $oWebuser->getTimecardId();
	$result = mysql_query($query, $dbhandleTimecard);
	if ($row = mysql_fetch_assoc($result)) {

		$ret .= "
<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
	<td><b>Firstname:</b>&nbsp;</td>
	<td>" . $row["FirstName"] . "</td>
</tr>
<tr>
	<td><b>Lastname:</b>&nbsp;</td>
	<td>" . $row["LastName"] . "</td>
</tr>
<tr>
	<td><b>SA/2X login:</b>&nbsp;</td>
	<td>" . $row["LongCode"] . "</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr>
	<td valign=top><b>Vacation hours left:&nbsp;</b></td>
	<td>" . $oWebuser->calculateVacationHours() . "</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr>
	<td><b>hh:mm format:</b>&nbsp;</td>
	<td valign=top>
";

		$hoursdoublefield = $row["HoursDoubleField"];
		if ( $hoursdoublefield == '1' ) {
			$ret .= 'Double field';
			$switch_to = 'single field';
		} else {
			$ret .= 'Single field';
			$switch_to = 'double field';
		}

		$ret .= " <a href=\"switch_hours.php\">Switch to " . $switch_to . "</a></td>
</tr>
</table>
";

	}
	mysql_free_result($result);

	return $ret;
}
?>