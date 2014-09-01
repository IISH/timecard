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
$oPage->setTab($menuList->findTabNumber('finad.projects'));
$oPage->setTitle('Timecard | Projects');
$oPage->setContent(createProjectContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createProjectContent() {
	global $protect;

	$id = $protect->request_positive_number_or_empty('get', "ID");
	if ( $id == '' ) {
		$id = '0';
	}

	$show_all = 0;
	if ( isset( $_GET["show"] ) && $_GET["show"] == 'all' ) {
		$show_all = 1;
	}

	$ret = "
<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">
<tr>
	<td><h2>Projects</h2>
";
	$ret .= "
	</td>
	<td align=\"right\"> &nbsp; 
";

	//???
	if ( $id != '0' ) {
		$backurl= getBackUrl();
		$ret .= "<a href=\"" . $backurl . "\">go back</a>";
	}

	$ret .= "</td>
</tr>
</table>

<style>
table.projects {
	border: 0px solid black;
	border-spacing: 0;
	border-collapse: collapse;
}
th.projects {
	padding: 3px 3px 3px 3px;
	text-align: left;
	border: 1px solid black;
}
td.project0 {
	padding: 3px 3px 3px 3px;
	border: 1px solid black;
}
td.project1 {
	padding: 3px 3px 3px 33px;
	border: 1px solid black;
}
td.project2 {
	padding: 3px 3px 3px 63px;
	border: 1px solid black;
}
td.project3 {
	padding: 3px 3px 3px 93px;
	border: 1px solid black;
}
td.project4 {
	padding: 3px 3px 3px 123px;
	border: 1px solid black;
}
</style>
";

	$ret .= '<br>';
	if ( $show_all == 1 ) {
		$ret .= '<a href="?">[ Hide disabled projects ]</a>';
	} else {
		$ret .= '<a href="?show=all">[ Show all projects ]</a>';
	}

	$current_url_encoded = urlencode(get_current_url());
	$ret .= " &nbsp; &nbsp; &nbsp; <a href=\"projects_edit.php?ID=0&PID=0&backurl=" . $current_url_encoded . "\" class=\"button\">Add new project</a><br><br>";

	$ret .= "<table class=\"projects\">
<tr>
	<th class=\"projects\">Name</th>
	<th class=\"projects\">Project number</th>
	<th class=\"projects\">End date</th>
	<th class=\"projects\">Show in<br>select list</th>
</tr>
";
	$ret .= showProjectTree($id, 0, $show_all);
	$ret .= "</table>";

	return $ret;
}

// TODOEXPLAIN
function showProjectTree($id = 0, $level = 1, $show_all) {
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$ret = '';

	$current_url_encoded = urlencode(get_current_url());

	$cur_level = $level;
	$next_level = $cur_level+1;

	if ( $show_all == 1 ) {
		$query = "SELECT * FROM Workcodes WHERE ParentID=" . $id . " ORDER BY Description ";
	} else {
		$query = "SELECT * FROM Workcodes WHERE ParentID=" . $id . " AND isdisabled=0 ORDER BY Description ";
	}

	$result = mysql_query($query, $oConn->getConnection());
	while ($row = mysql_fetch_assoc($result)) {

		$ret .= "<tr>\n";

		// name
		$ret .= "<td class=\"project" . $level . "\">";
		$isStrike = $row["isdisabled"] == 1 || ( isset( $row["lastdate"] ) && trim($row["lastdate"]) != '' && trim($row["lastdate"]) < date("Ymd") ) || $row["show_in_selectlist"] == 0;
		if ( $isStrike ) {
			$ret .= "<strike>";
		}
		$ret .= "<a name=\"" . $row["ID"] . "\" href=\"projects_edit.php?ID=" . $row["ID"] . "&PID=" . $row["ParentID"] . "&backurl=" . $current_url_encoded . "\">" . trim($row["Description"]) . "</a>";
		if ( $isStrike ) {
			$ret .= "</strike>";
		}
		$ret .= "</td>";

		// projectnummer
		$ret .= "<td class=\"project0\">";
		$ret .= trim($row["Projectnummer"]);
		$ret .= "</td>";

		// End date
		$ret .= "<td class=\"project0\">";
		$lastdate = trim($row["lastdate"]);
		if ( $lastdate != '' ) {
			$oDate = new class_dateasstring( $lastdate );
			$ret .= $oDate->get('j M Y');
		}
		$ret .= "</td>";

		// show in select list
		$ret .= "<td class=\"project0\">";
		$no = '';
		$separator = '';
		$separator2 = ' & ';

		if ( $row["show_in_selectlist"] == 0 ) {
			$no .= $separator . '\'show in select list\' not selected';
			$separator = $separator2;
		}

		if ( $row["isdisabled"] == 1 ) {
			$no .= $separator . 'disabled';
			$separator = $separator2;
		}

		if ( isset( $row["lastdate"] ) && trim($row["lastdate"]) != '' && $row["lastdate"] < date("Ymd") ) {
			$no .= $separator . '\'end date\' passed';
			$separator = $separator2;
		}

		if ( $no != '' ) {
			$ret .= 'no <a title="' . $no . '">(?)</a>';
		} else {
			$ret .= 'yes';
		}

		$ret .= "</td>";

		//
		$ret .= "</tr>\n";

		// recursive
		$ret .= showProjectTree($row["ID"], $next_level, $show_all);
	}
	mysql_free_result($result);

	$oConn->connect();

	return $ret;
}
