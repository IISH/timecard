<?php
require_once dirname(__FILE__) . "/class_calendar.inc.php";

function getTablePostfix( $year ) {
	$postfix = '';

	switch ( $year ) {
		case 2009:
		case 2010:
		case 2011:
		case 2012:
		case 2013:
			$postfix = "_" . $year;
			break;
	}

	return $postfix;
}

function c( $c) {
	return convertSpreadsheatColumnNumberToColumnCharacter($c);
}

function rc( $r, $c) {
	return convertSpreadsheatColumnNumberToColumnCharacter($c) . $r;
}

function convertSpreadsheatColumnNumberToColumnCharacter($i, $choices = "ABCDEFGHIJKLMNOPQRSTUVWXYZ") {
	$len = strlen($choices);

	$mod = ($i-1) % $len;
	if ( $i - ($mod+1) > 0 ) {
		$rest = ($i - ($mod+1))/$len;
		$retval = convertSpreadsheatColumnNumberToColumnCharacter($rest, $choices) . substr($choices, $mod, 1);
	} else {
		$retval = substr($choices, $mod, 1);
	}

	return $retval;
}

function hoursLeft_formatNumber($value, $decimal = 1, $show_zero = false) {
	$ret = '';

	if ( $value == '' ) {
		$value = 0;
	}

	if ( $value != 0 || $show_zero ) {
		$ret = number_format($value, $decimal, ',', '.');
	}

	return $ret;
}

function convertToJiraUrl( $jira_issue_nr ) {
	$ret = '';
	$separator = '';

	$jira_url_browse = Settings::get('jira_url_browse');

	$jira_issue_nr = trim($jira_issue_nr);

	$arr = explode(' ', $jira_issue_nr);
	foreach ( $arr as $url ) {
		$ret .= $separator . "<a href=\"$jira_url_browse$url\" target=\"_blank\">$url</a>";
		$separator = ' ';
	}

	return $ret;
}

function goBackTo() {
	$ret = '';
/*
	$backurl = getBackUrl();
	if ( $backurl != '' ) {
		$backurllabel = getAndProtectBackurlLabel();
		if ( $backurllabel == '' ) {
			$backurllabel = "&laquo; Go back";
		} else {
			$backurllabel = "&laquo; Go back to " . $backurllabel;
		}
		$ret = "<div class=\"goBackTo\"><a href=\"$backurl\">$backurllabel</a></div>";
	}
*/
	return $ret;
}

function getDepartmentEmployeesRibbon($currentlySelectedEmployee, $year) {
	global $oWebuser, $settings;

	$selected_employee = "Select an employee";

	$prev = '';
	$next = '';

	$ret = "
<b>Selected employee:</b><br>
<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
	<td colspan=2 align=\"center\">::SELECTEDEMPLOYEE::</td>
</tr>
<tr>
	<td style=\"text-align:left;padding-left:20px;\">::PREV::</td>
	<td style=\"text-align:right;padding-right:20px;\" align=\"right\">::NEXT::</td>
</tr>
</table>
<br>
<b>Employees: </b><br>";

	$oProtimeUser = new class_protime_user($oWebuser->getProtimeId(), $settings);
	$allowedDepartments = array_merge(array($oProtimeUser->getDepartmentId()), $oWebuser->getDepartmentHeadExtraRightsOnDepartments());
	$allowedUsers = $oWebuser->getDepartmentHeadExtraRightsOnUsers();
	foreach ( getListOfUsersActiveInSpecificYearAndInDepartment($year, $allowedDepartments, $allowedUsers) as $user ) {
		if ( $currentlySelectedEmployee->getTimecardId() == $user["id"] ) {
			$ret .= "<b>";
			$selected_employee = trim($user["firstname"] . ' ' . verplaatsTussenvoegselNaarBegin($user["lastname"]));
			$prev = $user['prev'];
			$next = $user['next'];
		}

		$current_employee = trim($user["firstname"] . ' ' . verplaatsTussenvoegselNaarBegin($user["lastname"]));
		if ( $current_employee == '' ) {
			$current_employee = trim($user["longcode"]);
		}

		$ret .= "<a href=\"" . GetModifyReturnQueryString("?", "eid", $user["id"]) . "\" title=\"" . $current_employee . "\">" . $current_employee . "</a>";

		if ( $currentlySelectedEmployee->getTimecardId() == $user["id"] ) {
			$ret .= "</b>";
		}

		$ret .= "<br>";
	}

	if ( $currentlySelectedEmployee->getTimecardId() == -1 ) {
		$selected_employee = 'all employees';
	}

	$ret = str_replace("::SELECTEDEMPLOYEE::", $selected_employee, $ret);

	// prev
	if ( $prev != '' ) {
		$ret = str_replace('::PREV::', '<a href="' . GetModifyReturnQueryString("?", "eid", $prev) . '">&laquo; prev</a>', $ret);
	} else {
		$ret = str_replace('::PREV::', '&laquo; prev', $ret);
	}

	// next
	if ( $next != '' ) {
		$ret = str_replace('::NEXT::', '<a href="' . GetModifyReturnQueryString("?", "eid", $next) . '">next &raquo;</a>', $ret);
	} else {
		$ret = str_replace('::NEXT::', 'next &raquo;', $ret);
	}

	return $ret;
}

function getEmployeesRibbon($currentlySelectedEmployee, $year, $hide_all_employees_choice = 0) {
	//global $oEmployee;

	$selected_employee = "Select an employee";

	$prev = '';
	$next = '';

	$ret = "
<b>Selected employee:</b><br>
<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
<tr>
	<td colspan=2 align=\"center\">::SELECTEDEMPLOYEE::</td>
</tr>
<tr>
	<td style=\"text-align:left;padding-left:20px;\">::PREV::</td>
	<td style=\"text-align:right;padding-right:20px;\" align=\"right\">::NEXT::</td>
</tr>
</table>
<br>
<b>Employees: </b><br>";

	// if all (employees) show also link for 'all'
	if ( !$hide_all_employees_choice ) {
		$ret .= "<a href=\"" . GetModifyReturnQueryString("?", "eid", "-1") . "\">all employees</a><br>";
	}

	foreach ( getListOfUsersActiveInSpecificYear($year) as $user ) {
		if ( $currentlySelectedEmployee->getTimecardId() == $user["id"] ) {
			$ret .= "<b>";
			$selected_employee = trim($user["firstname"] . ' ' . verplaatsTussenvoegselNaarBegin($user["lastname"]));
			$prev = $user['prev'];
			$next = $user['next'];
		}

		$current_employee = trim($user["firstname"] . ' ' . verplaatsTussenvoegselNaarBegin($user["lastname"]));
		if ( $current_employee == '' ) {
			$current_employee = trim($user["longcode"]);
		}

		$ret .= "<a href=\"" . GetModifyReturnQueryString("?", "eid", $user["id"]) . "\" title=\"" . $current_employee . "\">" . $current_employee . "</a>";

		if ( $currentlySelectedEmployee->getTimecardId() == $user["id"] ) {
			$ret .= "</b>";
		}

		$ret .= "<br>";
	}

	if ( $currentlySelectedEmployee->getTimecardId() == -1 ) {
		$selected_employee = 'all employees';
	}

	$ret = str_replace("::SELECTEDEMPLOYEE::", $selected_employee, $ret);

	// prev
	if ( $prev != '' ) {
		$ret = str_replace('::PREV::', '<a href="' . GetModifyReturnQueryString("?", "eid", $prev) . '">&laquo; prev</a>', $ret);
	} else {
		$ret = str_replace('::PREV::', '&laquo; prev', $ret);
	}

	// next
	if ( $next != '' ) {
		$ret = str_replace('::NEXT::', '<a href="' . GetModifyReturnQueryString("?", "eid", $next) . '">next &raquo;</a>', $ret);
	} else {
		$ret = str_replace('::NEXT::', 'next &raquo;', $ret);
	}

	return $ret;
}

function getListOfUsersActiveInSpecificYearAndInDepartment($year, $arrDepartments, $arrUsers) {
	global $dbConn;

	if ( count($arrDepartments) == 0 ) {
		$arrDepartments[] = '0';
	}

	if ( count($arrUsers) == 0 ) {
		$arrUsers[] = '0';
	}

	$ret = array();
	$last_id = '';
	$query_users = "
SELECT vw_Employees.*
FROM vw_Employees
	INNER JOIN protime_curric ON vw_Employees.ProtimePersNr = protime_curric.PERSNR
WHERE 
	firstyear<=" . $year . " 
	AND lastyear>=" . $year . " 
	AND is_test_account=0 
	AND ( 
			protime_curric.DEPART IN (" . implode(", ", $arrDepartments) . ")
			OR protime_curric.PERSNR IN (" . implode(", ", $arrUsers) . ")
		)
ORDER BY longcode
";

	$item = array();

	$stmt = $dbConn->prepare($query_users);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row_users) {
		if ( $last_id != '' ) {
			$item["next"] = $row_users["ID"];
			$ret[] = $item;
			$item = array();
		}
		$item["id"] = $row_users["ID"];
		$item["firstname"] = $row_users["FIRSTNAME"];
		$item["lastname"] = $row_users["NAME"];
		$item["longcode"] = $row_users["LongCode"];
		$item["prev"] = $last_id;
		$last_id = $row_users["ID"];
	}
	$ret[] = $item;

	return $ret;
}

function getListOfUsersActiveInSpecificYear($year) {
	global $dbConn;

	$ret = array();
	$item = array();
	$last_id = '';
	$query_users = "SELECT * FROM vw_Employees WHERE firstyear<=" . $year . " AND lastyear>=" . $year . " AND is_test_account=0 ORDER BY longcode ";
	$stmt = $dbConn->prepare($query_users);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row_users) {
		if ( $last_id != '' ) {
			$item["next"] = $row_users["ID"];
			$ret[] = $item;
			$item = array();
		}
		$item["id"] = $row_users["ID"];
		$item["firstname"] = $row_users["FIRSTNAME"];
		$item["lastname"] = $row_users["NAME"];
		$item["longcode"] = $row_users["LongCode"];
		$item["prev"] = $last_id;
		$last_id = $row_users["ID"];
	}
	$ret[] = $item;

	return $ret;
}

function fillTemplate($template, $data) {
	foreach ( $data as $a => $b ) {
		$template = str_replace('{' . $a . '}', $b, $template);
	}

	return $template;
}

function protectFilename( $fname ) {
	$dangerousChars = array(' ', ',', '?', '!');
	$fname = str_replace($dangerousChars, '_', $fname);

	while ( strpos($fname, '__') !== false ) {
		$fname = str_replace('__','_', $fname);
	}

	return $fname;
}

function getAndProtectSearch($field = 's') {
	$s = ( isset( $_GET[$field] ) ? $_GET[$field] : '' );
	$s = str_replace(array('?', "~", "`", "#", "$", "%", "^", "'", "\"", "(", ")", "<", ">", ":", ";", "*", "\n"), ' ', $s);

	while ( strpos($s, '  ') !== false ) {
		$s = str_replace('  ',' ', $s);
	}

	$s = trim($s);
	$s = substr($s, 0, 20);

	return $s;
}

function getAndProtectBackurlLabel() {
	global $protect;

	if ( !isset($_GET["backurllabel"]) ) {
		$_GET["backurllabel"] = '';
	}

	$retval = trim($_GET["backurllabel"]);

	$retval = str_replace('<', ' ', $retval);
	$retval = str_replace('>', ' ', $retval);

	$retval = trim($retval);

	$retval = $protect->get_left_part($retval);

	return $retval;
}

function debug($text = "", $extra = '') {
    echo "<font color=red>";
	if ( is_array($text) ) {
		echo "<pre>" . date("H:i:s ") . " (" . microtime(true) . ") " . $extra;
		print_r($text);
		echo " +</pre><br>";
	} else {
		echo date("H:i:s ") . " (" . microtime(true) . ") " . $extra . $text . " +<br>";
	}
    echo "</font>";
}

function get_current_url() {
	$backurl = $_SERVER["QUERY_STRING"];
	if ( $backurl <> "" ) {
		$backurl = "?" . $backurl;
	}
	$backurl = $_SERVER["SCRIPT_NAME"] . $backurl;

	return $backurl;
}

function achterhaalQuarterLabel($quarter, $format = 'M') {
	// format: M - 3 char month
	// format: F - full month
	if ( $format != 'M' && $format != 'F' ) {
		$format = 'M';
	}

	return date($format, mktime(0, 0, 0, ($quarter-1)*3+1, 1, 2010)) . " - " . date($format, mktime(0, 0, 0, (($quarter-1)*3)+3, 1, 2010)) . " ";
}

function RemoveFromQueryString($tekst, $field) {
	$retval = '';

	$qstring = $tekst;

	$qstring = str_replace('&amp;', '__amp;', $qstring);

	$querystring_argument_array = explode('&', $qstring);

	$separator = '';

	foreach ( $querystring_argument_array as $querystring_argument_field => $querystring_argument_value ) {

		$querystring_argument_value2 = explode('=', $querystring_argument_value, 2);

		$value0 = $querystring_argument_value2[0];
		$value1 = $querystring_argument_value2[1];

		if ( $value0 != '' ) {
			if ( $value1 != '' ) {

				$value1 = str_replace("__amp;", "&amp;", $value1);

				if ( $field != $value0 ) {
					$retval .= $separator . $value0 . "=" . urldecode($value1);
					$separator = '&';
				}

			}
		}
	}

	return $retval;
}

function GetModifyReturnQueryString($pre_character, $field, $value) {
	$retval = '';

	$qstring = $_SERVER["QUERY_STRING"];
	$qstring = str_replace('&amp;', '__amp;', $qstring);

	$querystring_argument_array = explode('&', $qstring);

	$separator = '';
	$found_new_field = 0;

	foreach ( $querystring_argument_array as $querystring_argument_field => $querystring_argument_value ) {

		$querystring_argument_value2 = explode('=', $querystring_argument_value, 2);

		$value0 = $querystring_argument_value2[0];
		if ( count($querystring_argument_value2) > 1 ) {
			$value1 = $querystring_argument_value2[1];
		} else {
			$value1 = '';
		}

		if ( $value0 != '' ) {
			if ( $value1 != '' ) {

				$value1 = str_replace("__amp;", "&amp;", $value1);

				if ( $field == $value0 ) {
					if ( $value != '' ) {
						$retval .= $separator . $value0 . "=" . urldecode($value);
						$separator = '&';
					}
					$found_new_field = 1;
				} else {
					$retval .= $separator . $value0 . "=" . urldecode($value1);
					$separator = '&';
				}

			}
		}
	}

	if ( $found_new_field == 0 ) {
		if ( $field != '' ) {
			if ( $value != '' ) {
				$retval .= $separator . $field . "=" . urldecode($value);
			}
		}
	}

	if ( $retval != '' ) {
		$retval = $pre_character . $retval;
	}

	return $retval;
}

function vorigeVolgendeJaar($date, $richting, $urlDescription = '') {
	$original_date = $date;

	if ( $richting == '-' ) {
		$date["y"] -= 1;
		$label = '&laquo; ' . $urlDescription;
	} else if ( $richting == '+' ) {
		$date["y"] += 1;
		$label = $urlDescription . ' &raquo;';
	} else {
		$date["y"] = date("Y");
		$date["m"] = date("m");
		$date["d"] = date("d");
//		$label = $date["y"];
		$label = 'current year';
	}

	// 
	$date = class_datetime::check_date($date);

	$d = mktime(0, 0, 0, $date["m"], $date["d"], $date["y"]);

	$date["y"] = date("Y", $d);
	$date["m"] = date("m", $d);
	$date["d"] = date("d", $d);

	$only_lable = 0;
	if ( $richting == '' ) {
		if ( date("Y") == $original_date["y"] ) {
			$only_lable = 1;
		}
	}

	if ( $only_lable == 1 ) {
		$retval = $label;
	} else {
		$alt = 'go to ' . date("Y", mktime(0, 0, 0, $date["m"], $date["d"], $date["y"]));
		$retval = '<a href="' . GetModifyReturnQueryString('?', 'd', $date["Ymd"]) . '" alt="' . $alt . '" title="' . $alt . '">' . $label . '</a>';
	}

	return $retval;
}

function achterhaalQuarter($date) {
	$retval = 0;

	switch ( $date["m"] ) {
		case 1:
		case 2:
		case 3:
			$retval = 1;
			break;
		case 4:
		case 5:
		case 6:
			$retval = 2;
			break;
		case 7:
		case 8:
		case 9:
			$retval = 3;
			break;
		case 10:
		case 11:
		case 12:
			$retval = 4;
			break;
	}

	return $retval;
}

function advancedRecordDelete($db, $table, $criterium, $test = 0 ) {
	global $databases;

	$advQuery = "DELETE FROM " . $table;

	if ( $criterium != '' ) {
		$advQuery .= " WHERE " . $criterium . " ";
	} else {
		die('No criterium. Cannot execute because it will delete everything.');
	}

	if ( $test == 1 ) {
		// test
		//debug($advQuery);
	} else {
		$oConn = new class_pdo($databases[$db]);

		// run
		//debug($advQuery, "advancedRecordDelete: ");
		$stmt = $oConn->getConnection()->prepare($advQuery);
		$stmt->execute();
	}
}

function advancedRecordInsert($db, $table, $fields, $test = 0 ) {
	global $databases;

	$advQuery = "INSERT INTO " . $table . " ";

	$tot_fields = '';
	$tot_values = '';

	if ( is_array($fields) ) {
		$separator = '';
		foreach ($fields as $a => $b) {
			if ( is_array($b) ) {
				foreach ($b as $c => $d) {
					$tot_fields .= $separator . $c;
					$tot_values .= $separator . $d;

					$separator = ", ";
				}
			} else {
				$tot_fields .= $separator . $a;
				$tot_values .= $separator . $b;
			}
		}
	}

	$advQuery .= " ( " . $tot_fields . " ) VALUES ( " . $tot_values . " ) ";

	if ( $test == 1 ) {
		// test
		//debug($advQuery);
	} else {
		$oConn = new class_pdo($databases[$db]);

		// run
		//debug($advQuery, "advancedRecordInsert: ");
		$stmt = $oConn->getConnection()->prepare($advQuery);
		$stmt->execute();

	}
}

function advancedRecordUpdate($db, $table, $fields, $criterium, $test = 0 ) {
	global $databases;

	$advQuery = "UPDATE " . $table . " SET ";

	if ( is_array($fields) ) {
		$separator2 = '';

		foreach ($fields as $a => $b) {
			$separator = '';
			$advQuery .= $separator2;

			if ( is_array($b) ) {
				foreach ($b as $c => $d) {
					$advQuery .= $separator . $c . "=" . $d;
					$separator = ", ";
				}
			} else {
//echo "+<br>";
				$advQuery .= $a . "=" . $b;
			}
			$separator2 = ", ";
		}
	}

	if ( $criterium != '' ) {
		$advQuery .= " WHERE " . $criterium . " ";
	}

	if ( $test == 1 ) {
		// test
		echo $advQuery . "+<br>";
	} else {
		$oConn = new class_pdo($databases[$db]);

		// run
		//debug($advQuery, "advancedRecordUpdate: ");
		$stmt = $oConn->getConnection()->prepare($advQuery);
		$stmt->execute();
	}
}

function advancedSingleRecordSelectMysql($db, $table, $fields, $criterium, $fieldselect = '', $order_by = '' ) {
	global $databases;

	$retval = array();

	$oConn = new class_pdo($databases[$db]);

	if ( $fieldselect == '' || $fieldselect == '*' ) {
		$fieldselect = implode(', ', $fields);
	}

	$advSelect = "SELECT " . $fieldselect . " FROM " . $table;
	$retval["__is_record_found"] = '0';

	if ( $criterium != '' ) {
		$advSelect .= " WHERE " . $criterium . " ";
	}

	if ( $order_by != '' ) {
		$advSelect .= " ORDER BY " . $order_by . " ";
	}

	$stmt = $oConn->getConnection()->prepare($advSelect);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $rowSelect) {
		$retval["__is_record_found"] = '1';
		if ( is_array($fields) ) {
			foreach ($fields as $a) {
				$retval[strtolower($a)] = $rowSelect[$a];
			}
		} else {
			$retval[strtolower($fields)] = $rowSelect[$fields];
		}
		break;
	}

	return $retval;
}

function updateLastUserLogin($userid) {
	advancedRecordUpdate(
			'default'
			, "Employees"
			, array("last_user_login" => "'" . date("Y-m-d H:i:s") . "'", 'isdisabled' => '0')
			, "ID=" . $userid
		);
}

function getEmployeeIdByLoginName($loginName) {
	global $dbConn;

	$retval["id"] = '0';

	if ( $loginName != '' && $loginName != '-'  ) {

		$query = "SELECT ID FROM Employees WHERE LongCode='" . addslashes($loginName) . "' OR LongCodeKnaw='" . addslashes($loginName) . "' ORDER BY ID DESC ";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$retval["id"] = $row["ID"];
		}

	}

	return $retval;
}

function getAddEmployeeToTimecard($longcode) {
	global $protect, $dbConn;

	$retval["id"] = '0';

	if ( strpos($longcode, '.') !== false ) {
		// IISG
		$query = "SELECT ID, LongCode FROM Employees WHERE LongCode='" . addslashes($longcode) . "' ORDER BY ID DESC ";
	} else {
		// KNAW
		$query = "SELECT ID, LongCode FROM Employees WHERE LongCodeKnaw='" . addslashes($longcode) . "' ORDER BY ID DESC ";
	}

	$stmt = $dbConn->prepare($query);
	$stmt->execute();

	// get ID of employee
	if ( $row = $stmt->fetch() ) {
		$retval["id"] = $row["ID"];
	} else {
		//
		$a = new TCDateTime();
		$allow_additions_starting_date = $a->getFirstDate()->format("Y-m-d");
		$year = date("Y");

		$created_on = date("Y-m-d H:i:s");

		if ( strpos($longcode, '.') !== false ) {
			// IISG
			// everyone with a IISG account is allowed to use timecard
			// so without any problems we can add the user to the database

			// insert new record in Employees database
			$queryInsert = "INSERT INTO Employees (LongCode, firstyear, lastyear, allow_additions_starting_date, created_on) VALUES ('" . addslashes($longcode) . "', $year, $year, '$allow_additions_starting_date', '$created_on') ";
			$stmt = $dbConn->prepare($queryInsert);
			$stmt->execute();

			// get the id of the last created document
			$stmt = $dbConn->prepare($query);
			$stmt->execute();
			if ( $row2 = $stmt->fetch() ) {
				$retval["id"] = $row2["ID"];
			}

			// send mail to admin to check the data
			$newUserBody = "A new timecard user has registered (" . $longcode . ").
Go to website and check the user(s) without a protime link
https://intranet.bb.huc.knaw.nl/timecard/admin_not_linked_employees.php
- click on an user
- enter the users KNAW login
- select user's name in the Protime field
- and save the record
(that's all.)
After that you can close the Jira call.";
			$protect->send_email( Settings::get("email_new_employees_to"), "IISG Timecard - new user added (" . $longcode . ")", $newUserBody );

		} else {
			// KNAW
			// not overyone with a KNAW account is allowed to use the timecard application
			// do not add users automatically to the database
			// send mail to admin to check the data
			$newUserBody = "An unknown KNAW employee has tried to login in timecard (" . $longcode . ").
If this user should be authorized to use timecard, please add him/her via:
https://intranet.bb.huc.knaw.nl/timecard/employees.php
- click on 'Add employee'
- enter the users SA login
- enter the users KNAW login
- select user's name in the Protime field
- and save the record
(that's all.)
After that you can close the Jira call.";
			$protect->send_email( Settings::get("email_new_employees_to"), "IISG Timecard - blocked unknown KNAW employee (" . $longcode . ")", $newUserBody );

			//
			$_SESSION["timecard"]["id"] = 0;
			die('Error: You are not authorized to use this application. Please send an email to: servicedesk at social history services dot org.');
		}
	}

	return $retval;
}

function getCheckedInCheckedOut($protimeid, $date = '') {
	global $dbConn;

	if ( $date == '' ) {
		$date = date("Ymd");
	}
	$date = substr($date, 0, 8);

	$retval = "
<div class=\"checkedInOut\">
<table>
<tr>
	<td colspan=2><b>Checked in/out</b></td>
</tr>
<tr>
	<td><b>In</b></td>
	<td><b>Out</b></td>
</tr>
";

	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM protime_bookings WHERE PERSNR=" . $protimeid . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY BOOKTIME ";

	$status = 0;
	$found = 0;
	$template = "<tr><td>::IN::</td><td>::OUT::</td></tr>";
	$inout = $template;

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$status++;
		$found++;

		if ( $status == 1 ) {
			$inout = $template;
			$inout = str_replace('::IN::', class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]), $inout);
		} else {
			$inout = str_replace('::OUT::', class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]), $inout);
			$retval .= $inout;
			$inout = '';
		}

		$status = $status % 2;
	}

	if ( $inout != '' ) {
		$inout = str_replace('::IN::', '-', $inout);
		$inout = str_replace('::OUT::', '-', $inout);
		$retval .= $inout;
	}

	$retval .= "</table></div>";

	// clear if nothing found
	if ( $found == 0 ) {
		$retval = '';
	}

	return $retval;
}

function addAndRemoveAbsentiesInTimecard($timecard_id, $protime_id, $oDate) {
	global $dbConn;

	if ( $oDate->get("Y") < Settings::get("oldest_modifiable_year") ) {
		return;
	}

	// create a semicolon separated string of all absences used in this current day
	// string will be used in later stadium to remove all 'leftover' absences
	$timecard_absenties = ';';
	$query = "SELECT * FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr>0 ";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$timecard_absenties .= $row["protime_absence_recnr"] . ";";
	}

	// doorloop Protime absenties en voeg/update toe aan Timecard
	$query2 = "
SELECT protime_p_absence.REC_NR, protime_p_absence.ABSENCE_VALUE, vw_ProtimeAbsences.workcode_id
FROM protime_p_absence
	INNER JOIN vw_ProtimeAbsences ON protime_p_absence.ABSENCE = vw_ProtimeAbsences.protime_absence_id
WHERE protime_p_absence.PERSNR = " . $protime_id . "
	AND protime_p_absence.BOOKDATE = '" . $oDate->get("Ymd") . "'
";

	$stmt = $dbConn->prepare($query2);
	$stmt->execute();
	$result2 = $stmt->fetchAll();
	foreach ($result2 as $row2) {

		if ( $row2["workcode_id"] != '' && $row2["workcode_id"] != '0' && $row2["workcode_id"] != '-1' ) {
			if ( strpos($timecard_absenties, ";" . $row2["REC_NR"] . ";") !== false ) {
				// update
				advancedRecordUpdate(
						'default'
						, "Workhours"
						, array(
								array("WorkCode" => $row2["workcode_id"])
								, array("WorkDescription" => "''")
								, array("TimeInMinutes" => $row2["ABSENCE_VALUE"])
							)
						, "Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=" . $row2["REC_NR"]
						, 0
					);

				//
				$timecard_absenties = str_replace(";" . $row2["REC_NR"] . ";", ";", $timecard_absenties);
			} else {
				// insert
				advancedRecordInsert(
					'default'
					, "Workhours"
					, array(
							array("Employee" => $timecard_id)
							, array("DateWorked" => "'" . $oDate->get("Y") . "-" . $oDate->get("m") . "-" . $oDate->get("d") . "'")
							, array("WorkCode" => $row2["workcode_id"])
							, array("WorkDescription" => "''")
							, array("TimeInMinutes" => $row2["ABSENCE_VALUE"])
							, array("protime_absence_recnr" => $row2["REC_NR"])
						)
					, 0
					);

			}
		}
	}

	// delete 'leftover' absences in specified day
	$timecard_absenties = str_replace(";", " ", $timecard_absenties);
	$timecard_absenties = trim($timecard_absenties);
	$timecard_absenties = str_replace(" ", ",", $timecard_absenties);
	if ( $timecard_absenties != '' ) {
		$queryDelete = "DELETE FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr IN (" . $timecard_absenties . ") ";
		$stmt = $dbConn->prepare($queryDelete);
		$stmt->execute();
	}
}

function getEerderNaarHuisMonthTotal($timecard_id, $oDate) {
	global $dbConn;

	$eerderWeg = 0;
	$query = "SELECT SUM(TimeInMinutes) AS TOTMINUTES FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m") . "-%' AND protime_absence_recnr=-1 ";
	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$eerderWeg += $row["TOTMINUTES"];
	}

	return $eerderWeg;
}

function getEerderNaarHuisGroupedByDay($timecard_id, $oDate) {
	global $dbConn;

	$eerderWeg = array();

	// achterhaal
	$query = "SELECT SUBSTR(DateWorked, 1, 10) AS WORKDATE, SUM(TimeInMinutes) AS TOTMINUTES
FROM Workhours
WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-") . "%' AND protime_absence_recnr=-1
GROUP BY SUBSTR(DateWorked, 1, 10) ";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$eerderWeg[$row["WORKDATE"]] = $row["TOTMINUTES"];
	}

	return $eerderWeg;
}

function getEerderNaarHuisDayTotal($timecard_id, $oDate) {
	global $dbConn;

	$eerderWeg = 0;

	// achterhaal 
	$query = "SELECT SUM(TimeInMinutes) AS TOTMINUTES FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=-1 ";
	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$eerderWeg += $row["TOTMINUTES"];
	}

	return $eerderWeg;
}

function addEerderNaarHuisInTimecardMonth($timecard_id, $protime_id, $oDate) {
	global $dbConn;

	$query = "SELECT BOOKDATE, EXTRA FROM protime_pr_month WHERE PERSNR=" . $protime_id . " AND BOOKDATE LIKE '" . $oDate->get("Ym") . "%' GROUP BY BOOKDATE, EXTRA ";
	$arrExtras = array();
	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$arrExtras[ $row["BOOKDATE"] ] = $row["EXTRA"];
	}

	// eerder naar huis
	for ( $i = 1; $i <= date("t", mktime(0, 0, 0, (int)( $oDate->get("m") ), (int)( $oDate->get("d") ), (int)( $oDate->get("Y") ) )); $i++ ) {
		$oDate2 = new class_date( $oDate->get("y"), $oDate->get("m"), $i );
		if ( $oDate->get("Y") < Settings::get("oldest_modifiable_year") || $oDate2->get("Ymd") >= date("Ymd") ) {
			// break from for loop
			break;
		}

		$eerderWeg = (int)($arrExtras[$oDate2->get("Ymd")]);

		if ( $eerderWeg < 0 ) {
			$eerderWeg *= -1;

			$zoek = advancedSingleRecordSelectMysql(
				'default'
				, "Workhours"
				, array("ID", "TimeInMinutes")
				, "Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate2->get("Y-m-d") . "%' AND protime_absence_recnr=-1 "
			);

			if ( $zoek["id"] != '' && $zoek["id"] != '0' ) {
				// update
				advancedRecordUpdate(
					'default'
					, "Workhours"
					, array(
						array("WorkCode" => 7)
						, array("WorkDescription" => "''")
						, array("TimeInMinutes" => $eerderWeg)
					)
					, "ID=" . $zoek["id"]
					, 0
				);
			} else {
				// insert
				advancedRecordInsert(
					'default'
					, "Workhours"
					, array(
						array("Employee" => $timecard_id)
					, array("DateWorked" => "'" . $oDate2->get("Y") . "-" . $oDate2->get("m") . "-" . $oDate2->get("d") . "'")
					, array("WorkCode" => 7)
					, array("WorkDescription" => "''")
					, array("TimeInMinutes" => $eerderWeg)
					, array("protime_absence_recnr" => "-1")
					)
					, 0
				);
			}
		} else {
			// verwijder (indien nodig) de 'oude' eerder weg
			advancedRecordDelete(
				'default'
				, "Workhours"
				, "Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate2->get("Y-m-d") . "%' AND protime_absence_recnr=-1 "
			);
		}

	}
}

function addEerderNaarHuisInTimecard($timecard_id, $protime_id, $oDate) {
	// add 'eerder naar huis' for dates until (excluding) today
	if ( $oDate->get("Y") < Settings::get("oldest_modifiable_year") || $oDate->get("Ymd") >= date("Ymd") ) {
		return;
	}

	//
	$hours = advancedSingleRecordSelectMysql(
			'default'
			, "protime_pr_month"
			, array("EXTRA")
			, "PERSNR=" . $protime_id . " AND BOOKDATE='" . $oDate->get("Ymd") . "' "
		);

	$eerderWeg = (int)($hours["extra"]);
	if ( $eerderWeg < 0 ) {
		$eerderWeg *= -1;

		$zoek = advancedSingleRecordSelectMysql(
				'default'
				, "Workhours"
				, array("ID", "TimeInMinutes")
				, "Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=-1 "
			);

		if ( $zoek["id"] != '' && $zoek["id"] != '0' ) {
			// update
			advancedRecordUpdate(
					'default'
					, "Workhours"
					, array(
							array("WorkCode" => 7)
							, array("WorkDescription" => "''")
							, array("TimeInMinutes" => $eerderWeg)
						)
					, "ID=" . $zoek["id"]
					, 0
				);
		} else {
			// insert
			advancedRecordInsert(
					'default'
					, "Workhours"
					, array(
							array("Employee" => $timecard_id)
							, array("DateWorked" => "'" . $oDate->get("Y") . "-" . $oDate->get("m") . "-" . $oDate->get("d") . "'")
							, array("WorkCode" => 7)
							, array("WorkDescription" => "''")
							, array("TimeInMinutes" => $eerderWeg)
							, array("protime_absence_recnr" => "-1")
						)
					, 0
				);
		}

	} else {
		// verwijder (indien nodig) de 'oude' eerder weg
		advancedRecordDelete(
			'default'
			, "Workhours"
			, "Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=-1 "
		);
	}
}

function getAbsences($eid) {
	global $dbConn;

	$ret = '';

	$query = "SELECT TOP 2000 protime_p_absence.REC_NR, protime_p_absence.PERSNR, protime_p_absence.BOOKDATE, protime_p_absence.ABSENCE_VALUE, protime_p_absence.ABSENCE_STATUS, protime_absence.SHORT_1, protime_absence.ABSENCE
FROM protime_p_absence
	LEFT OUTER JOIN protime_absence ON protime_p_absence.ABSENCE = protime_absence.ABSENCE
WHERE protime_p_absence.PERSNR=" . $eid . " AND protime_p_absence.BOOKDATE>='" . date("Ymd") . "' AND ( ABSENCE_VALUE>0 OR SHORT_1 <> 'Vakantie' ) AND protime_p_absence.ABSENCE NOT IN (6) ORDER BY protime_p_absence.BOOKDATE, protime_p_absence.REC_NR ";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	if ( $result ) {

		$ret .= "
<table>
<tr><td>&nbsp;</td></tr>
<tr>
	<td colspan=2><b>Protime/Reception absences</b></td>
</tr>
<tr>
	<td><b>Date</b></td>
	<td><b>Absence</b></td>
	<td><b>Hours</b></td>
</tr>
";

		foreach ($result as $row) {

			$ret .= "
<tr>
	<td>" . class_datetime::formatDatePresentOrNot($row["BOOKDATE"]) . "&nbsp;</td>
	<td>" . $row["SHORT_1"] . "&nbsp;</td>
	<td>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["ABSENCE_VALUE"]) . "</td>
</tr>
";

		}

		$ret .= "</table>";
	}

	return $ret;
}

function removeLeftChar( $haystack, $needle ) {
	do {
		$modified = false;
		if ( is_array($needle) ) {
			foreach ( $needle as $n ) {
				if ( substr( $haystack, 0, 1) == $n ) {
					$haystack = substr($haystack, -(strlen($haystack)-1));
					$modified = true;
				}
			}
		} else {
			if ( substr( $haystack, 0, 1) == $needle ) {
				$haystack = substr($haystack, -(strlen($haystack)-1));
				$modified = true;
			}
		}
	} while ($modified);

	return $haystack;
}

function getAbsencesAndHolidays($eid, $year, $month, $min_minutes = 0) {
	global $dbConn;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$query = "
SELECT protime_p_absence.REC_NR, protime_p_absence.PERSNR, protime_p_absence.BOOKDATE, protime_p_absence.ABSENCE_VALUE, protime_p_absence.ABSENCE_STATUS, protime_absence.SHORT_1, protime_p_absence.ABSENCE
FROM protime_p_absence
	LEFT OUTER JOIN protime_absence ON protime_p_absence.ABSENCE = protime_absence.ABSENCE
WHERE protime_p_absence.PERSNR=" . $eid . " AND protime_p_absence.BOOKDATE LIKE '" . $yearMonth . "%' AND protime_p_absence.ABSENCE NOT IN (5, 19)
AND ( protime_p_absence.ABSENCE_VALUE>=" . $min_minutes . " OR protime_p_absence.ABSENCE_VALUE=0 )
ORDER BY protime_p_absence.BOOKDATE, protime_p_absence.REC_NR
";

	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
// 1 Bijzonder verlof
// 2 Calamiteitenverlof
// 3 Cursus
// 4 dienstreis
// 5 Dokter/Tandarts (uitgefiltered, geen vakantie/afwezigheid)
// 6 feestdag
// 12 vakanties
// 13 Werk buiten IISG
// 15 Ziekte (uitgefiltered, geen vakantie/afwezigheid)
// 16 zorgverlof
// 18 Werk thuis
// 19 compensatie overuren (uitgefiltered, geen vakantie/afwezigheid)
// 22 Verlof
		$ret[] = array( 'date' => $row["BOOKDATE"], 'description' => $row["SHORT_1"] );
	}

	return $ret;
}

//
function Generate_Query($arrField, $arrSearch) {
	$retval = '';
	$separatorBetweenValues = '';

	foreach ( $arrSearch as $value ) {
		$separatorBetweenFields = '';
		$retval .= $separatorBetweenValues . " ( ";
		foreach ( $arrField as $field) {
			$retval .= $separatorBetweenFields . $field . " LIKE '%" . $value . "%' ";
			$separatorBetweenFields = " OR ";
		}
		$retval .= " ) ";
		$separatorBetweenValues = " AND ";
	}

	if ( $retval != '' ) {
		$retval = " AND " . $retval;
	}

	return $retval;
}

function createDateAsString($year, $month, $day = '') {
	$ret = $year;

	$ret .= str_pad( $month, 2, '0', STR_PAD_LEFT);

	if ( $day != '' ) {
		$ret .= str_pad( $day, 2, '0', STR_PAD_LEFT);
	}

	return $ret;
}

function getBackUrl() {
	global $protect;

	$ret = '';

	if ( $ret == '' ) {
		if ( isset( $_GET["parentbackurl"] ) ) {
			$ret = $_GET["parentbackurl"];
		}
	}

	if ( $ret == '' ) {
		if ( isset( $_GET["backurl"] ) ) {
			$ret = $_GET["backurl"];
		}
	}

	if ( $ret == '' ) {
		$scriptNameStrippedEdit = str_replace('_edit', '', $_SERVER['SCRIPT_NAME']);
		if ( $_SERVER['SCRIPT_NAME'] != $scriptNameStrippedEdit ) {
			$ret = $scriptNameStrippedEdit;
		}
	}

	$ret = str_replace('<', ' ', $ret);
	$ret = str_replace('>', ' ', $ret);

	$ret = trim($ret);

	$ret = $protect->get_left_part($ret);

	return $ret;
}

function getQuarterTotals( $date, $userTimecardId, $urlprefix ) {
	global $settings;

	$oDateOriginal = new class_date( $date["y"], $date["m"], $date["d"] );

	$ret = '<table border=0 width="100%"><tr>';
	for ( $monthIterator = $oDateOriginal->getFirstMonthInQuarter(); $monthIterator <= $oDateOriginal->getLastMonthInQuarter(); $monthIterator++ ) {
		$oDate = new class_date( $oDateOriginal->get('Y'), $monthIterator, 1 );

		$syncUrl = $urlprefix . "sync_timecard_protime.php?d=" . $oDate->get("Ymd") . "&eid=" . $userTimecardId;
		$syncLabel = '';
		if ( $oDate->get("Y") >= Settings::get("oldest_modifiable_year") ) {
			$syncLabel = " <font size=-2><em><a href=\"" . $syncUrl . "\">(sync)</a></em></font>";
		}
		$ret .= '<td valign=top><table border=1 cellspacing=0 cellpadding=3>';
		$ret .= "<tr><td colspan=4 align=center><strong>" . $oDate->get("F Y") . "</strong>$syncLabel</td></tr>";
		$ret .= "<tr><td><strong><font size=\"-2\">Day</font></strong></td><td><strong><font size=\"-2\">Timecard</font></strong></td><td><strong><font size=\"-2\">Protime</font></strong></td><td><font size=\"-2\"><strong>Overtime</strong></font></td></tr>";

		$number_of_days_in_current_month = $oDate->get('t');

		$oEmployee = new class_employee( $userTimecardId, $settings );

		$date2["y"] = $oDate->get('Y');
		$date2["m"] = $oDate->get('n');
		$date2["d"] = 1;
		$timecard_day_totals = $oEmployee->getTimecardDayTotals( $oDate->get('Y'), $oDate->get('n') );

		$dagvakantie2 = $oEmployee->getEerderNaarHuisDayTotals( $oDate->get('Y'), $oDate->get('n') );
		$protime_day_totals = $oEmployee->getProtimeDayTotals( $oDate->get('Ym') );
		$protime_day_overtimes = $oEmployee->getProtimeDayOvertimes( $oDate->get('Ym') );

		$total_overtime = 0;
		for ( $i = 1; $i <= $number_of_days_in_current_month; $i++ ) {
			$date2["d"] = $i;

			$timecard_day_total = 0;
			if ( isset($timecard_day_totals[$i]) ) {
				$timecard_day_total += $timecard_day_totals[$i];
			}
			if ( isset($dagvakantie2[$i]) ) {
				$timecard_day_total += $dagvakantie2[$i];
			}

			$protime_day_total = 0;
			if ( isset( $protime_day_totals[$i] ) ) {
				$protime_day_total = $protime_day_totals[$i];
			}

			// TIMECARD
			if ( $timecard_day_total == 0 && $protime_day_total == 0 ) {
				$timecard_day_total_nice = '&nbsp;';
			} else {
				$timecard_day_total_nice = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $timecard_day_total );
			}

			// PROTIME
			if ( $protime_day_total == 0 ) {
				$protime_day_total_nice = '&nbsp;';
			} else {
				$protime_day_total_nice = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $protime_day_total );
			}

			// OVERTIME +/-
			$sign = '';
			if ( $oDate->get('Ym') . substr('0'.$i,-2) < date("Ymd")) {
				$extra = $protime_day_overtimes[$i];
			} else {
				$extra = 0;
			}
			$total_overtime += $extra;

			if ( $extra > 0 ) {
				$sign = '+';
			}

			if ( $extra == 0 ) {
				$extra_nice = '&nbsp;';
			} else {
				$extra_nice =  $sign . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $extra );
			}

			// color if difference more then x minutes
			$color_start = "<span class=\"" . ( ( (int)$timecard_day_total - (int)$protime_day_total ) >= 3 || ( (int)$timecard_day_total - (int)$protime_day_total ) <= -3 ? "boldRed" : "" ) . "\">";
			$color_end = '</span>';

			$oCurrentDay = new class_date( $date2["y"], $date2["m"], $i );
			$weekday = $oCurrentDay->get('D j');
			$url = $urlprefix . "day.php?d=" . $oCurrentDay->get('Ymd') . '&eid=' . $userTimecardId . '&backurl=' . urlencode(get_current_url());
			$ret .= "<tr><td><a href=\"$url\">$weekday</a></td><td>$color_start$timecard_day_total_nice$color_end</td><td>$protime_day_total_nice</td><td><FONT SIZE=-2>$extra_nice</FONT></td></tr>";
		}

			$ret .= "
		<tr><td colspan=\"3\"><FONT SIZE=-2>Total overtime</font></td><td><FONT SIZE=-2>" . ($total_overtime > 0 ? '+': '' ) . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $total_overtime ) . "</FONT></td></tr>
";

		$ret .= "
		</table>
	</td>";
	}
	$ret .= "
</tr>
</table>
";

	return $ret;
}

function verplaatsTussenvoegselNaarBegin( $text ) {
	$text = trim($text);

	$array = array( ' van den', ' van der', ' van', ' de', ' el' );

	foreach ( $array as $t ) {
		if ( strtolower(substr($text, -strlen($t))) == strtolower($t) ) {
			$text = trim($t . ' ' . substr($text, 0, strlen($text)-strlen($t)));
		}
	}

	return $text;
}

function closeDataEntry($year, $month, $id = 0 ) {
	global $dbConn;

	// calculate new allow_date
	$a = new TCDateTime();
	$a->setFromString($year . '-' . $month . '-01', 'Y-m-d');
	$a->addMonth(); // add one month
	$allow_date = $a->get()->format("Y-m-d");

	// don't change the allow date if it is in the future (months)
	if ( $allow_date <= date("Y-m-01") ) {
		// update records
		$query = "UPDATE Employees SET allow_additions_starting_date = '$allow_date' WHERE allow_additions_starting_date < '$allow_date' ";
		if ( $id > 0 ) {
			$query .= ' AND ID=' . $id;
		}
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
	}
}

function getAllEmployeesLoginnameAndFullname() {
	global $dbConn;

	$retval = array();

	$query = "SELECT LongCodeKnaw, protime_curric.FIRSTNAME, protime_curric.NAME
FROM Employees
	LEFT JOIN protime_curric ON Employees.ProtimePersNr = protime_curric.PERSNR
WHERE LongCodeKnaw IS NOT NULL AND LongCodeKnaw<>'' AND LongCodeKnaw<>'-'
ORDER BY Employees.LongCodeKnaw ";
	$stmt = $dbConn->prepare($query);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$retval[] = array(
			'LongCodeKnaw' => $row['LongCodeKnaw']
			, 'FIRSTNAME' => $row['FIRSTNAME']
			, 'NAME' => $row['NAME']
		);
	}

	return $retval;
}
