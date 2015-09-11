<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$switch = $protect->request_only_characters_or_numbers_or_empty('get', "s");
if ( !in_array( $switch, array('tif', 'pso', 'jira') ) ) {
	die('Error 832341: Unknown switch: ' . $switch);
}

switch ( $switch ) {
	case "tif": // time input format
		$field = 'HoursDoubleField';
		break;
	case "pso": // projects sorting order
		$field = 'sort_projects_on_name';
		break;
	case "jira": // show jira field
		$field = 'show_jira_field';
		break;
	default:
		die('Error 742564: Unknown switch: ' . $switch);
}

$query_update = "UPDATE Employees SET $field=$field*(-1) WHERE ID=" . $oWebuser->getTimecardId();
$result_update = mysql_query($query_update, $oConn->getConnection());

require_once "classes/_db_disconnect.inc.php";

$backurl = "aboutme.php";
Header("location: " . $backurl);
die('go <a href="' . $backurl . '">back</a>');