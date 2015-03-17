<?php
require_once dirname(__FILE__) . "/class_calendar.inc.php";

// TODOEXPLAIN
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

// TODOEXPLAIN
function c( $c) {
	return convertSpreadsheatColumnNumberToColumnCharacter($c);
}

// TODOEXPLAIN
function rc( $r, $c) {
	return convertSpreadsheatColumnNumberToColumnCharacter($c) . $r;
}

// TODOEXPLAIN
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

// TODOEXPLAIN
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

// TODOEXPLAIN
function fixCharErrors( $text ) {
	$text = str_replace("Ã«", "&euml;", $text);
	$text = str_replace("ë", "&euml;", $text);

	return $text;
}

// TODOEXPLAIN
function convertToJiraUrl( $jira_issue_nr ) {
	$ret = '';
	$separator = '';

	$jira_url_browse = class_settings::getSetting('jira_url_browse');

	$jira_issue_nr = trim($jira_issue_nr);

	$arr = explode(' ', $jira_issue_nr);
	foreach ( $arr as $url ) {
		$ret .= $separator . "<a href=\"$jira_url_browse$url\" target=\"_blank\">$url</a>";
		$separator = ' ';
	}

	return $ret;
}

// TODOEXPLAIN
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

// TODOEXPLAIN
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

// TODOEXPLAIN
function getListOfUsersActiveInSpecificYear($year) {
	global $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$ret = array();
	$last_id = '';
	$query_users = "SELECT * FROM vw_Employees WHERE ( ( firstyear<=" . $year . " AND lastyear>=" . $year . ") OR isdisabled=0 ) AND is_test_account=0 ORDER BY FIRSTNAME, NAME ";
	$result_users = mysql_query($query_users, $oConn->getConnection());
	$item = array();
	while ($row_users = mysql_fetch_assoc($result_users)) {
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
	mysql_free_result($result_users);

	return $ret;
}

// TODOEXPLAIN
function fillTemplate($template, $data) {
	foreach ( $data as $a => $b ) {
		$template = str_replace('{' . $a . '}', $b, $template);
	}

	return $template;
}

// TODOEXPLAIN
function protectFilename( $fname ) {
	$dangerousChars = array(' ', ',', '?', '!');
	$fname = str_replace($dangerousChars, '_', $fname);

	while ( strpos($fname, '__') !== false ) {
		$fname = str_replace('__','_', $fname);
	}

	return $fname;
}

// TODOEXPLAIN
function getAndProtectSearch($field = 's') {
	$s = $_GET[$field];
	$s = str_replace(array('?', "~", "`", "#", "$", "%", "^", "'", "\"", "(", ")", "<", ">", ":", ";", "*", "\n"), ' ', $s);

	while ( strpos($s, '  ') !== false ) {
		$s = str_replace('  ',' ', $s);
	}

	$s = trim($s);
	$s = substr($s, 0, 20);

	return $s;
}

// TODOEXPLAIN
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

// TODOEXPLAIN
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

// TODOEXPLAIN: OUD VERHUISD NAAR CLASS_WEB_PROTECTION
function get_current_url() {
	$backurl = $_SERVER["QUERY_STRING"];
	if ( $backurl <> "" ) {
		$backurl = "?" . $backurl;
	}
	$backurl = $_SERVER["SCRIPT_NAME"] . $backurl;

	return $backurl;
}

// TODOEXPLAIN
function achterhaalQuarterLabel($quarter, $format = 'M') {
	// format: M - 3 char month
	// format: F - full month
	if ( $format != 'M' && $format != 'F' ) {
		$format = 'M';
	}

	return date($format, mktime(0, 0, 0, ($quarter-1)*3+1, 1, 2010)) . " - " . date($format, mktime(0, 0, 0, (($quarter-1)*3)+3, 1, 2010)) . " ";
}

// TODOEXPLAIN
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

// TODOEXPLAIN
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
		$value1 = $querystring_argument_value2[1];

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

// TODOEXPLAIN
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

// TODOEXPLAIN
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

// TODOEXPLAIN
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
		$oConn = new class_mysql($databases[$db]);
		$oConn->connect();

		// run
		//debug($advQuery, "advancedRecordDelete: ");
		$resultAdvUpdate = mysql_query($advQuery, $oConn->getConnection());
	}
}

// TODOEXPLAIN
function advancedRecordInsert($db, $table, $fields, $test = 0 ) {
	global $databases;

	$advQuery = "INSERT INTO " . $table . " ";

	$tot_fields = '';
	$tot_values = '';
	$separator = '';

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
		$oConn = new class_mysql($databases[$db]);
		$oConn->connect();

		// run
		//debug($advQuery, "advancedRecordInsert: ");
		$resultAdvUpdate = mysql_query($advQuery, $oConn->getConnection());

	}
}

// TODOEXPLAIN
function advancedRecordUpdate($db, $table, $fields, $criterium, $test = 0 ) {
	global $databases;

	$advQuery = "UPDATE " . $table . " SET ";

	if ( is_array($fields) ) {
		$separator = '';
		foreach ($fields as $a => $b) {
			if ( is_array($b) ) {
				foreach ($b as $c => $d) {
					$advQuery .= $separator . $c . "=" . $d;
					$separator = ", ";
				}
			} else {
				$advQuery .= $a . "=" . $b;
			}
		}
	}

	if ( $criterium != '' ) {
		$advQuery .= " WHERE " . $criterium . " ";
	}

	if ( $test == 1 ) {
		// test
		echo $advQuery . "+<br>";
	} else {
		$oConn = new class_mysql($databases[$db]);
		$oConn->connect();

//debug($advQuery, "advancedRecordUpdate: ");
		// run
		$resultAdvUpdate = mysql_query($advQuery, $oConn->getConnection());
	}
}

// TODOEXPLAIN
function advancedSingleRecordSelectMysql($db, $table, $fields, $criterium, $fieldselect = '', $order_by = '' ) {
	global $databases;

	$retval = array();

	$oConn = new class_mysql($databases[$db]);
	$oConn->connect();

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

//debug($advSelect, 'advancedSingleRecordSelectMysql: ');

	$resultAdvSelect = mysql_query($advSelect, $oConn->getConnection());
	if ($rowSelect = mysql_fetch_assoc($resultAdvSelect)) {
		$retval["__is_record_found"] = '1';
		if ( is_array($fields) ) {
			foreach ($fields as $a) {
				$retval[strtolower($a)] = $rowSelect[$a];
			}
		} else {
			$retval[strtolower($fields)] = $rowSelect[$fields];
		}
	}
	mysql_free_result($resultAdvSelect);

	return $retval;
}

// TODOEXPLAIN
function updateLastUserLogin($userid) {
	advancedRecordUpdate(
			'default'
			, "Employees"
			, array("last_user_login" => "'" . date("Y-m-d H:i:s") . "'", 'isdisabled' => '0')
			, "ID=" . $userid
		);
}

// TODOEXPLAIN
function getEmployeeIdByLongCode($longcode) {
	global $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$retval["id"] = '0';

	$query = "SELECT ID, LongCode FROM Employees WHERE LongCode='" . addslashes($longcode) . "' ORDER BY ID DESC ";
	$result = mysql_query($query, $oConn->getConnection());

	// get ID of employee
	if ( $row = mysql_fetch_array($result) ) {
		$retval["id"] = $row["ID"];
	}

	return $retval;
}

// TODOEXPLAIN
function getAddEmployeeToTimecard($longcode) {
	global $protect, $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$retval["id"] = '0';

	$query = "SELECT ID, LongCode FROM Employees WHERE LongCode='" . addslashes($longcode) . "' ORDER BY ID DESC ";
	$result = mysql_query($query, $oConn->getConnection());

	// get ID of employee
	if ( $row = mysql_fetch_array($result) ) {
		$retval["id"] = $row["ID"];
	} else {
		//
		$a = new TCDateTime();
		$a->subMonth(); // one month back
		$allow_additions_starting_date = $a->getFirstDate()->format("Y-m-d");
		$year = date("Y");

		// insert new record in Employees database
		$queryInsert = "INSERT INTO Employees (LongCode, firstyear, lastyear, allow_additions_starting_date) VALUES ('" . addslashes($longcode) . "', $year, $year, '$allow_additions_starting_date') ";
		$resultInsert = mysql_query($queryInsert, $oConn->getConnection());

		// get the id of the last created document
		$result2 = mysql_query($query, $oConn->getConnection());
		if ( $row2 = mysql_fetch_array($result2) ) {
			$retval["id"] = $row2["ID"];
		}

		// send mail to admin to check the data
		$newUserBody = "This message is for the IISG IT Department.
A new timecard user has registered.
Go to website and check users data.
https://timecard.socialhistoryservices.org/employees_edit.php?ID=" . $retval["id"] . "
- se(lec)t user's name in the Protime field
- and save the record
(that's all.)
After that you can close the Jira call.";
		$protect->send_email( class_settings::getSetting("email_new_employees_to"), "IISG Timecard - new user added", $newUserBody );
	}

	return $retval;
}

// TODOEXPLAIN
function getCheckedInCheckedOut($protimeid, $date = '') {
	global $databases;

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

	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM PROTIME_BOOKINGS WHERE PERSNR=" . $protimeid . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY BOOKTIME ";

	$oTc = new class_mysql($databases['default']);
	$oTc->connect();

	$result = mysql_query($query, $oTc->getConnection());
	$status = 0;
	$found = 0;
	$template = "<tr><td>::IN::</td><td>::OUT::</td></tr>";
	$inout = $template;
	while ( $row = mysql_fetch_array($result) ) {
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
	mysql_free_result($result);

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

// TODOEXPLAIN
function addAndRemoveAbsentiesInTimecard($timecard_id, $protime_id, $oDate) {
	global $databases;

	if ( $oDate->get("Y") < class_settings::getSetting("oldest_modifiable_year") ) {
		return;
	}

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	// create a semicolon separated string of all absences used in this current day
	// string will be used in later stadium to remove all 'leftover' absences
	$timecard_absenties = ';';
	$query = "SELECT * FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr>0 ";
//debug($query);
	$result = mysql_query($query, $oConn->getConnection());
	while ( $row = mysql_fetch_array($result) ) {
		$timecard_absenties .= $row["protime_absence_recnr"] . ";";
	}
	mysql_free_result($result);

	// doorloop Protime absenties en voeg/update toe aan Timecard
	$query2 = "
SELECT PROTIME_P_ABSENCE.REC_NR, PROTIME_P_ABSENCE.ABSENCE_VALUE, vw_ProtimeAbsences.workcode_id
FROM PROTIME_P_ABSENCE
	INNER JOIN vw_ProtimeAbsences ON PROTIME_P_ABSENCE.ABSENCE = vw_ProtimeAbsences.protime_absence_id
WHERE PROTIME_P_ABSENCE.PERSNR = " . $protime_id . "
	AND PROTIME_P_ABSENCE.BOOKDATE = '" . $oDate->get("Ymd") . "'
";

	$result2 = mysql_query($query2, $oConn->getConnection());
	while ( $row2 = mysql_fetch_array($result2) ) {
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
	mysql_free_result($result2);

	$oConn->connect();

	// delete 'leftover' absences in specified day
	$timecard_absenties = str_replace(";", " ", $timecard_absenties);
	$timecard_absenties = trim($timecard_absenties);
	$timecard_absenties = str_replace(" ", ",", $timecard_absenties);
	if ( $timecard_absenties != '' ) {
		$queryDelete = "DELETE FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr IN (" . $timecard_absenties . ") ";
		mysql_query($queryDelete, $oConn->getConnection());
	}
}

// TODOEXPLAIN
function getEerderNaarHuisMonthTotal($timecard_id, $oDate) {
	global $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$eerderWeg = 0;
	$query = "SELECT SUM(TimeInMinutes) AS TOTMINUTES FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m") . "-%' AND protime_absence_recnr=-1 ";
	$result = mysql_query($query, $oConn->getConnection());
	while ( $row = mysql_fetch_array($result) ) {
		$eerderWeg += $row["TOTMINUTES"];
	}
	mysql_free_result($result);

	return $eerderWeg;
}

// TODOEXPLAIN
function getEerderNaarHuisGroupedByDay($timecard_id, $oDate) {
	global $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$eerderWeg = array();

	// achterhaal
	$query = "SELECT SUBSTR(DateWorked, 1, 10) AS WORKDATE, SUM(TimeInMinutes) AS TOTMINUTES FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-") . "%' AND protime_absence_recnr=-1 GROUP BY SUBSTR(DateWorked, 1, 10) ";
	$result = mysql_query($query, $oConn->getConnection());
	while ( $row = mysql_fetch_array($result) ) {
		$eerderWeg[$row["WORKDATE"]] = $row["TOTMINUTES"];
	}
	mysql_free_result($result);

	return $eerderWeg;
}

// TODOEXPLAIN
function getEerderNaarHuisDayTotal($timecard_id, $oDate) {
	global $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$eerderWeg = 0;

	// achterhaal 
	$query = "SELECT SUM(TimeInMinutes) AS TOTMINUTES FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=-1 ";
	$result = mysql_query($query, $oConn->getConnection());
	while ( $row = mysql_fetch_array($result) ) {
		$eerderWeg += $row["TOTMINUTES"];
	}
	mysql_free_result($result);

	return $eerderWeg;
}

// TODOEXPLAIN
function addEerderNaarHuisInTimecardMonth($timecard_id, $protime_id, $oDate) {
	global $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$advSelect = "SELECT BOOKDATE, EXTRA FROM PROTIME_PR_MONTH WHERE PERSNR=" . $protime_id . " AND BOOKDATE LIKE '" . $oDate->get("Ym") . "%' GROUP BY BOOKDATE ";
	//debug($advSelect, 'addEerderNaarHuisInTimecardMonth: ');
	$arrExtras = array();
	$resultAdvSelect = mysql_query($advSelect, $oConn->getConnection());
	while ($rowSelect = mysql_fetch_assoc($resultAdvSelect)) {
		$arrExtras[ $rowSelect["BOOKDATE"] ] = $rowSelect["EXTRA"];
	}
	mysql_free_result($resultAdvSelect);

	// eerder naar huis
	for ( $i = 1; $i <= date("t", mktime(0, 0, 0, (int)( $oDate->get("m") ), (int)( $oDate->get("d") ), (int)( $oDate->get("Y") ) )); $i++ ) {
		$oDate2 = new class_date( $oDate->get("y"), $oDate->get("m"), $i );
		if ( $oDate->get("Y") < class_settings::getSetting("oldest_modifiable_year") || $oDate2->get("Ymd") >= date("Ymd") ) {
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

// TODOEXPLAIN
function addEerderNaarHuisInTimecard($timecard_id, $protime_id, $oDate) {
	global $databases;

	// add 'eerder naar huis' for dates until (excluding) today
	if ( $oDate->get("Y") < class_settings::getSetting("oldest_modifiable_year") || $oDate->get("Ymd") >= date("Ymd") ) {
		return;
	}

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	//
	$hours = advancedSingleRecordSelectMysql(
			'default'
			, "PROTIME_PR_MONTH"
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

// TODOEXPLAIN
function getAbsences($eid) {
	global $databases;

	$ret = '';
	$oTc = new class_mysql($databases['default']);
	$oTc->connect();

	$query = "SELECT TOP 2000 PROTIME_P_ABSENCE.REC_NR, PROTIME_P_ABSENCE.PERSNR, PROTIME_P_ABSENCE.BOOKDATE, PROTIME_P_ABSENCE.ABSENCE_VALUE, PROTIME_P_ABSENCE.ABSENCE_STATUS, PROTIME_ABSENCE.SHORT_1, PROTIME_ABSENCE.ABSENCE
FROM PROTIME_P_ABSENCE
	LEFT OUTER JOIN PROTIME_ABSENCE ON PROTIME_P_ABSENCE.ABSENCE = PROTIME_ABSENCE.ABSENCE
WHERE PROTIME_P_ABSENCE.PERSNR=" . $eid . " AND PROTIME_P_ABSENCE.BOOKDATE>='" . date("Ymd") . "' AND ( ABSENCE_VALUE>0 OR SHORT_1 <> 'Vakantie' ) AND PROTIME_P_ABSENCE.ABSENCE NOT IN (6) ORDER BY PROTIME_P_ABSENCE.BOOKDATE, PROTIME_P_ABSENCE.REC_NR ";
	$result = mysql_query($query, $oTc->getConnection());
	$num = mysql_num_rows($result);
	if ( $num ) {
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

		while ( $row = mysql_fetch_array($result) ) {

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

	mysql_free_result($result);

	return $ret;
}

// TODOEXPLAIN
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

// TODOEXPLAIN
function getAbsencesAndHolidays($eid, $year, $month, $min_minutes = 0) {
	global $databases;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$query = "
SELECT PROTIME_P_ABSENCE.REC_NR, PROTIME_P_ABSENCE.PERSNR, PROTIME_P_ABSENCE.BOOKDATE, PROTIME_P_ABSENCE.ABSENCE_VALUE, PROTIME_P_ABSENCE.ABSENCE_STATUS, PROTIME_ABSENCE.SHORT_1, PROTIME_P_ABSENCE.ABSENCE
FROM PROTIME_P_ABSENCE
	LEFT OUTER JOIN PROTIME_ABSENCE ON PROTIME_P_ABSENCE.ABSENCE = PROTIME_ABSENCE.ABSENCE
WHERE PROTIME_P_ABSENCE.PERSNR=" . $eid . " AND PROTIME_P_ABSENCE.BOOKDATE LIKE '" . $yearMonth . "%' AND PROTIME_P_ABSENCE.ABSENCE NOT IN (5, 19)
AND ( PROTIME_P_ABSENCE.ABSENCE_VALUE>=" . $min_minutes . " OR PROTIME_P_ABSENCE.ABSENCE_VALUE=0 )
ORDER BY PROTIME_P_ABSENCE.BOOKDATE, PROTIME_P_ABSENCE.REC_NR
";

	$oTc = new class_mysql($databases['default']);
	$oTc->connect();

	$result = mysql_query($query, $oTc->getConnection());
	$num = mysql_num_rows($result);
	if ( $num ) {
		while ( $row = mysql_fetch_array($result) ) {
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
	}

	mysql_free_result($result);

	return $ret;
}

//TODOEXPLAIN
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

// TODOEXPLAIN
function createDateAsString($year, $month, $day = '') {
	$ret = $year;

	$ret .= str_pad( $month, 2, '0', STR_PAD_LEFT);

	if ( $day != '' ) {
		$ret .= str_pad( $day, 2, '0', STR_PAD_LEFT);
	}

	return $ret;
}

// TODOEXPLAIN
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

// TODOEXPLAIN
function getQuarterTotals( $date, $userTimecardId, $urlprefix ) {
	global $settings;

	$oDateOriginal = new class_date( $date["y"], $date["m"], $date["d"] );

	$ret = '<table border=0 width="100%"><tr>';
	for ( $monthIterator = $oDateOriginal->getFirstMonthInQuarter(); $monthIterator <= $oDateOriginal->getLastMonthInQuarter(); $monthIterator++ ) {
		$oDate = new class_date( $oDateOriginal->get('Y'), $monthIterator, 1 );

		$syncUrl = $urlprefix . "sync_timecard_protime.php?d=" . $oDate->get("Ymd") . "&eid=" . $userTimecardId;
		$syncLabel = '';
		if ( $oDate->get("Y") >= class_settings::getSetting("oldest_modifiable_year") ) {
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

			$timecard_day_total = $timecard_day_totals[$i] + $dagvakantie2[$i];
			$protime_day_total = $protime_day_totals[$i];

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

// TODOEXPLAIN
function fixBrokenChars($text) {
	return htmlentities($text, ENT_COMPAT | ENT_XHTML, 'ISO-8859-1', true);
}

// TODOEXPLAIN
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

// TODOEXPLAIN
function closeDataEntry($year, $month, $id = 0 ) {
	global $databases;

	// calculate new allow_date
	$a = new TCDateTime();
	$a->setFromString($year . '-' . $month . '-01', 'Y-m-d');
	$a->addMonth(); // add one month
	$allow_date = $a->get()->format("Y-m-d");

	// don't change the allow date if it is in the future (months)
	if ( $allow_date <= date("Y-m-01") ) {
		//
		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		// update records
		$query = "UPDATE Employees SET allow_additions_starting_date = '$allow_date' WHERE allow_additions_starting_date < '$allow_date' ";
		if ( $id > 0 ) {
			$query .= ' AND ID=' . $id;
		}
		$result = mysql_query($query, $oConn->getConnection());
	}
}
