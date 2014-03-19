<?php 
require_once "classes/class_calendar.inc.php";

// modified: 2012-12-27

// TODOEXPLAIN
function getStatusColor( $persnr, $date ) {
	global $dbhandleProtime;
	$retval = array();

	//
	$status_color = 'background-color:#C62431;color:white;';
	$status_text = '';
	$status_alt = '';

	// achterhaal 'present' status
	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM BOOKINGS WHERE PERSNR=" . $persnr . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY REC_NR ";
	$result = mssql_query($query, $dbhandleProtime);
	$status = 0;
	$found = 0;
	$aanwezig = 0;
	while ( $row = mssql_fetch_array($result) ) {
		$found = 1;
		$status++;

		if ( $status == 1 ) {
			// green cell
			$status_color = 'background-color:green;color:white;';
			$status_alt .= 'In: ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);
			$aanwezig = 1;
		} else {
			// red cell
			$status_color = 'background-color:#C62431;color:white;';
			$status_alt .= ' - Out: ' . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]) . "\n";
			$aanwezig = 0;
		}
		$status_text = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]);

		$status = $status % 2;
	}
	mssql_free_result($result);

	$status_alt = trim($status_alt);

	// als status nog leeg
	// dan betekent dat dat de persoon vandaag nog niet ingeklokt heeft
	// misschien omdat de persoon op vakantie is
	if ( $status_text == '' && $found == 0 ) {
		$query = "SELECT ABSENCE.SHORT_1 FROM P_ABSENCE INNER JOIN ABSENCE ON P_ABSENCE.ABSENCE = ABSENCE.ABSENCE WHERE (P_ABSENCE.PERSNR = " . $persnr . ") AND (P_ABSENCE.BOOKDATE = '" . $date . "') ";
		$result = mssql_query($query, $dbhandleProtime);
		$status_separator = '';
		while ( $row = mssql_fetch_array($result) ) {
			$status_text .= $status_separator . $row["SHORT_1"];
			$status_separator = ', ';
		}
		mssql_free_result($result);
	}

	$retval["aanwezig"] = $aanwezig;
	$retval["status_text"] = $status_text;
	$retval["status_color"] = $status_color;
	$retval["status_alt"] = $status_alt;

	return $retval;
}

// TODOEXPLAIN
function goBackTo() {
	$ret = '';

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

	return $ret;
}

// TODOEXPLAIN
function getEmployeesRibbon($year, $all = 0) {
	global $date, $oEmployee, $protect, $oPage, $dbhandleTimecard, $connection_settings;

	$ret = "
<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">
<tr>
	<td><b>Employee: </b> ";

 	$selected_employee = "Please select an employee...";

	$separator = ' &nbsp; ';

	// if all (employees) show also link for 'all'
	if ( $all == 1 ) {
		$ret .= $separator . "<a href=\"" . GetModifyReturnQueryString("?", "eid", "-1") . "\">all employees</a>";
	}

	foreach ( getListOfUsersActiveInSpecificYear($date["y"]) as $user ) {

		$ret .= $separator;

		if ( $oEmployee->getTimecardId() == $user["id"] ) {
			$ret .= "<b>";
			$selected_employee = trim($user["lastname"] . ', ' . $user["firstname"]);
		}

		$current_employee = trim($user["lastname"] . ', ' . $user["firstname"]);

		$ret .= "<a href=\"" . GetModifyReturnQueryString("?", "eid", $user["id"]) . "\" alt=\"" . $current_employee . "\" title=\"" . $current_employee . "\">" . $current_employee . "</a>";

		if ( $oEmployee->getTimecardId() == $user["id"] ) {
			$ret .= "</b>";
		}
	}

	if ( $oEmployee->getTimecardId() == -1 ) {
		$selected_employee = 'all employees';
	}

	$ret .= "
	</td>
</tr>
<tr>
	<td><b>Selected employee:</b> &nbsp; &nbsp; " . $selected_employee . "
";

	$ret .= "
	</td>
</tr>
</table>
<br>
";

	return $ret;
}

// TODOEXPLAIN
function getListOfUsersActiveInSpecificYear($year) {
	global $dbhandleTimecard;

	$ret = array();

	$query_users = "SELECT * FROM Employees WHERE firstyear<=" . $year . " AND lastyear>=" . $year . " AND is_test_account=0 ORDER BY lastname, firstname ";
	$result_users = mysql_query($query_users, $dbhandleTimecard);
	while ($row_users = mysql_fetch_assoc($result_users)) {
		$item = array();
		$item ["id"] = $row_users["ID"];
		$item ["firstname"] = $row_users["FirstName"];
		$item ["lastname"] = $row_users["LastName"];
		$item ["longcode"] = $row_users["LongCode"];
		$ret[] = $item;
	}
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
function createTelephoneArray($arrTel, $tel, $explode = 1) {
	if ( $explode == 1 ) {
		$tel = cleanUpTelephone($tel);
	} else {
		if ( strlen($tel) >= 3 ) {
			if ( substr($tel, 0, 3) == '06 ' ) {
				$tel = '06-' . substr($tel, -strlen($tel)+3);
			}
		}
	}

	if ( $tel != '' ) {
		$arr = explode(',', $tel);

		foreach ( $arr as $a ) {
			if ( trim($a) != '' ) {
				array_push($arrTel, trim($a));
			}
		}
	}

	return $arrTel;
}

// TODOEXPLAIN
function cleanUpTelephone($telephone) {
	$retval = $telephone;

	// remove some dirty data from telephone
	$retval = str_replace(array('.', ',', '/', "(", ")", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"), ' ', $retval);

	// 
	while ( strpos($retval, '  ') !== false ) {
		$retval = str_replace('  ',' ', $retval);
	}
	$retval = trim($retval);

	// ad comma between the telephones
	$retval = str_replace(' ', ', ', $retval);

	return $retval;
}

// TODOEXPLAIN
function debug($text = "") {
	if ( is_array($text) ) {
		echo "<font color=red>+";
		print_r($text);
		echo "+</font><br>";
	} else {
		echo "<font color=red>+" . $text . "+</font><br>";
	}
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
	$found_new_field = 0;

	foreach ( $querystring_argument_array as $querystring_argument_field => $querystring_argument_value ) {

		$querystring_argument_value2 = explode('=', $querystring_argument_value, 2);

		$value0 = $querystring_argument_value2[0];
		$value1 = $querystring_argument_value2[1];

		if ( $value0 != '' ) {
			if ( $value1 != '' ) {

				$value1 = str_replace("__amp;", "&amp;", $value1);

				if ( $field == $value0 ) {
					$found_new_field = 1;
				} else {
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
	$retval = '';
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
		$label = $date["y"];
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
function advancedRecordDelete($handle, $table, $criterium, $test = 0 ) {
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
		// run
		$resultAdvUpdate = mysql_query($advQuery, $handle);
	}
}

// TODOEXPLAIN
function advancedRecordInsert($handle, $table, $fields, $test = 0 ) {
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
				$tot_fields .= $separator . $c;
				$tot_values .= $separator . $d;
			}
		}
	}

	$advQuery .= " ( " . $tot_fields . " ) VALUES ( " . $tot_values . " ) ";

	if ( $test == 1 ) {
		// test
		//debug($advQuery);
	} else {
		// run
		$resultAdvUpdate = mysql_query($advQuery, $handle);
	}
}

// TODOEXPLAIN
function advancedRecordUpdate($handle, $table, $fields, $criterium, $test = 0 ) {
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
		// run
		$resultAdvUpdate = mysql_query($advQuery, $handle);
	}
}

// TODOEXPLAIN
function advancedSingleRecordSelectMysql($handle, $table, $fields, $criterium, $fieldselect = '*', $order_by = '' ) {
	$retval = array();

	$advSelect = "SELECT " . $fieldselect . " FROM " . $table;
	$retval["__is_record_found"] = '0';

	if ( $criterium != '' ) {
		$advSelect .= " WHERE " . $criterium . " ";
	}

	if ( $order_by != '' ) {
		$advSelect .= " ORDER BY " . $order_by . " ";
	}

	$resultAdvSelect = mysql_query($advSelect, $handle);
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
function advancedSingleRecordSelectMssql($handle, $table, $fields, $criterium, $fieldselect = '*', $order_by = '' ) {
	$retval = array();

	$advSelect = "SELECT " . $fieldselect . " FROM " . $table;
	$retval["__is_record_found"] = '0';

	if ( $criterium != '' ) {
		$advSelect .= " WHERE " . $criterium . " ";
	}

	if ( $order_by != '' ) {
		$advSelect .= " ORDER BY " . $order_by . " ";
	}

	$resultAdvSelect = mssql_query($advSelect, $handle);
	if ($rowSelect = mssql_fetch_assoc($resultAdvSelect)) {
		$retval["__is_record_found"] = '1';
		if ( is_array($fields) ) {
			foreach ($fields as $a) {
				$retval[strtolower($a)] = $rowSelect[$a];
			}
		} else {
			$retval[strtolower($fields)] = $rowSelect[$fields];
		}
	}
	mssql_free_result($resultAdvSelect);

	return $retval;
}

// TODOEXPLAIN
function updateLastUserLogin($userid) {
	global $dbhandleTimecard;

	advancedRecordUpdate(
			$dbhandleTimecard
			, "Employees"
			, array("last_user_login" => "'" . date("Y-m-d H:i:s") . "'")
			, "ID=" . $userid
		);
}

// TODOEXPLAIN
function getAddEmployeeToTimecard($longcode) {
	global $dbhandleTimecard, $connection_settings, $protect, $settings_from_database;

	$retval["id"] = '0';

	$query = "SELECT ID, LongCode FROM Employees WHERE LongCode='" . addslashes($longcode) . "' ORDER BY ID DESC ";
	$result = mysql_query($query, $dbhandleTimecard);

	// get ID of employee
	if ( $row = mysql_fetch_array($result) ) {
		$retval["id"] = $row["ID"];
	} else {
		// insert new record in Employees database
		$queryInsert = "INSERT INTO Employees (LongCode) VALUES ('" . addslashes($longcode) . "') ";
		$resultInsert = mysql_query($queryInsert, $dbhandleTimecard);

		// get the id of the last created document
		$result2 = mysql_query($query, $dbhandleTimecard);
		if ( $row2 = mysql_fetch_array($result2) ) {
			$retval["id"] = $row2["ID"];
		}

		// send mail to admin to check the data
		$newUserBody = "This message is for the IISG IT Department.
A new timecard user has registered.
Go to website and check the users data.
https://timecard.socialhistoryservices.org/fa_employees_edit.php?ID=" . $retval["id"];
		$protect->send_email( $settings_from_database["email_new_employees_to"], "IISG Timecard - new user added", $newUserBody );
	}

	return $retval;
}

// TODOEXPLAIN
function syncProtimeAndTimecardEmployeeData($timecard_id, $protime_id) {
	global $dbhandleTimecard, $dbhandleProtime;

	// 
	$recordProtime = advancedSingleRecordSelectMssql(
			$dbhandleProtime
			, "CURRIC"
			, array("PERSNR", "NAME", "FIRSTNAME", "REGISTERNR", "RREGISTER")
			, "PERSNR=" . $protime_id . " ORDER BY PERSNR DESC "
		);

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	// is er wel wat gevonden in protime?
	// zo niet, skip next part (en laat dus alles zoals in timecard)
	if ( $recordProtime["persnr"] != '' && $recordProtime["persnr"] != '0' ) {

		// synchronize data
		advancedRecordUpdate(
				$dbhandleTimecard
				, "Employees"
				, array(
					array("LastName" => "'" . addslashes(trim($recordProtime["name"])) . "'")
					, array("FirstName" => "'" . addslashes(trim($recordProtime["firstname"])) . "'")
					, array("KnawPersNr" => "'" . addslashes(trim($recordProtime["registernr"])) . "'")
					, array("AfdelingsNummer" => "'" . addslashes(trim($recordProtime["rregister"])) . "'")
				)
				, "ID=" . $timecard_id
				, 0
			);
	}
}

// TODOEXPLAIN
function getCheckedInCheckedOut($protimeid, $date = '') {
	global $dbhandleProtime;
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

	$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM BOOKINGS WHERE PERSNR=" . $protimeid . " AND BOOKDATE='" . $date . "' AND BOOKTIME<>9999 ORDER BY BOOKTIME ";

	$result = mssql_query($query, $dbhandleProtime);
	$status = 0;
	$found = 0;
	$template = "<tr><td>::IN::</td><td>::OUT::</td></tr>";
	$inout = $template;
	while ( $row = mssql_fetch_array($result) ) {
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
	mssql_free_result($result);

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

// TODO
function addAndRemoveAbsentiesInTimecard($timecard_id, $protime_id, $oDate) {
	global $dbhandleTimecard, $dbhandleProtime;

	// achterhaal 
	$timecard_absenties = ';';
	$query = "SELECT * FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr>0 ";
	$result = mysql_query($query, $dbhandleTimecard);
	while ( $row = mysql_fetch_array($result) ) {
		$timecard_absenties .= $row["protime_absence_recnr"] . ";";
	}
	mysql_free_result($result);

	// doorloop Protime absenties en voeg/update toe aan Timecard
	$query2 = "SELECT * FROM P_ABSENCE WHERE PERSNR = " . $protime_id . " AND BOOKDATE = '" . $oDate->get("Ymd") . "' ";

	$result2 = mssql_query($query2, $dbhandleProtime);
	while ( $row2 = mssql_fetch_array($result2) ) {
		$protime_absence_id = $row2["ABSENCE"];
		// 
		$timecard_absence_id = advancedSingleRecordSelectMysql($dbhandleTimecard, "ProtimeAbsences", "workcode_id", "ID=" . $protime_absence_id );

		if ( $timecard_absence_id["workcode_id"] != '' && $timecard_absence_id["workcode_id"] != '0' && $timecard_absence_id["workcode_id"] != '-1' ) {
			if ( strpos($timecard_absenties, ";" . $row2["REC_NR"] . ";") !== false ) {
				// update
				advancedRecordUpdate(
						$dbhandleTimecard
						, "Workhours"
						, array(
								array("WorkCode" => $timecard_absence_id["workcode_id"])
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
					$dbhandleTimecard
					, "Workhours"
					, array(
							array("Employee" => $timecard_id)
							, array("DateWorked" => "'" . $oDate->get("m") . "/" . $oDate->get("d") . "/" . $oDate->get("Y") . "'")
							, array("WorkCode" => $timecard_absence_id["workcode_id"])
							, array("WorkDescription" => "''")
							, array("TimeInMinutes" => $row2["ABSENCE_VALUE"])
							, array("protime_absence_recnr" => $row2["REC_NR"])
						)
					, 0
					);

			}
		}
	}
	mssql_free_result($result2);

	// delete overgebleven 'oude' absenties
	$timecard_absenties = str_replace(";", " ", $timecard_absenties);
	$timecard_absenties = trim($timecard_absenties);
	$timecard_absenties = str_replace(" ", ",", $timecard_absenties);
	if ( $timecard_absenties != '' ) {
		$queryDelete = "DELETE FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr IN (" . $timecard_absenties . ") ";
		mysql_query($queryDelete, $dbhandleTimecard);
	}
}

// TODOEXPLAIN
function getEerderNaarHuisMonthTotal($timecard_id, $oDate) {
	global $dbhandleTimecard;

	$eerderWeg = 0;
	$query = "SELECT SUM(TimeInMinutes) AS TOTMINUTES FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m") . "-%' AND protime_absence_recnr=-1 ";
	$result = mysql_query($query, $dbhandleTimecard);
	if ( $row = mysql_fetch_array($result) ) {
		$eerderWeg = $row["TOTMINUTES"];
	}
	mysql_free_result($result);

	return $eerderWeg;
}

// TODOEXPLAIN
function getEerderNaarHuisDayTotal($timecard_id, $oDate) {
	global $dbhandleTimecard;

	$eerderWeg = 0;

	// achterhaal 
	$query = "SELECT SUM(TimeInMinutes) AS TOTMINUTES FROM Workhours WHERE Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=-1 ";
	$result = mysql_query($query, $dbhandleTimecard);
	if ( $row = mysql_fetch_array($result) ) {
		$eerderWeg = $row["TOTMINUTES"];
	}
	mysql_free_result($result);

	return $eerderWeg;
}

// TODOEXPLAIN
function addEerderNaarHuisInTimecard($timecard_id, $protime_id, $oDate) {
	global $dbhandleTimecard, $dbhandleProtime;

	// 
	$hours = advancedSingleRecordSelectMssql(
			$dbhandleProtime
			, "PR_MONTH"
			, array("PREST", "RPREST", "WEEKPRES1", "EXTRA")
			, "PERSNR=" . $protime_id . " AND BOOKDATE='" . $oDate->get("Ymd") . "' "
		);

	$eerderWeg = (int)($hours["extra"]);
	if ( $eerderWeg < 0 ) {
		$eerderWeg *= -1;

		$zoek = advancedSingleRecordSelectMysql(
				$dbhandleTimecard
				, "Workhours"
				, array("ID", "TimeInMinutes")
				, "Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=-1 "
			);

		if ( $zoek["id"] != '' && $zoek["id"] != '0' ) {
			// update
			advancedRecordUpdate(
					$dbhandleTimecard
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
					$dbhandleTimecard
					, "Workhours"
					, array(
							array("Employee" => $timecard_id)
							, array("DateWorked" => "'" . $oDate->get("m") . "/" . $oDate->get("d") . "/" . $oDate->get("Y") . "'")
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
			$dbhandleTimecard
			, "Workhours"
			, "Employee=" . $timecard_id . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND protime_absence_recnr=-1 "
		);
	}
}

// TODOEXPLAIN
function syncTimecardProtimeYear($timecard_id, $protime_id, $oDate) {
	for ( $i = 1; $i <= 12; $i++ ) {
		$oDate2 = new class_date($oDate->get("Y"), $i, 1);
		syncTimecardProtimeMonth($timecard_id, $protime_id, $oDate2);
	}
}

// TODOEXPLAIN
function syncTimecardProtimeMonth($timecard_id, $protime_id, $oDate) {
	for ( $i = 1; $i <= date("t", mktime(0, 0, 0, (int)( $oDate->get("m") ), (int)( $oDate->get("d") ), (int)( $oDate->get("Y") ) )); $i++ ) {
		$oDate2 = new class_date( $oDate->get("y"), $oDate->get("m"), $i );
		syncTimecardProtimeDay($timecard_id, $protime_id, $oDate2);
	}
}

// TODOEXPLAIN
function syncTimecardProtimeDay($timecard_id, $protime_id, $oDate) {

	if ( $timecard_id != '' && $timecard_id != '0' && $timecard_id != '-1' ) {
		if ( $protime_id != '' && $protime_id != '0' && $protime_id != '-1' ) {

			// inclusief vandaag
			if ( $oDate->get("Y") >= "2011" ) {
				//
				addAndRemoveAbsentiesInTimecard($timecard_id, $protime_id, $oDate);
			}

			// pas vanaf morgen (dus exclusief vandaag)
			if ( $oDate->get("Y") >= "2011" && $oDate->get("Ymd") < date("Ymd") ) {
				//
				addEerderNaarHuisInTimecard($timecard_id, $protime_id, $oDate);
			}
		}
	}
}

// TODOEXPLAIN
function getAbsences($eid) {
	global $dbhandleProtime;
	$ret = '';

	$query = "SELECT TOP 2000 P_ABSENCE.REC_NR, P_ABSENCE.PERSNR, P_ABSENCE.BOOKDATE, P_ABSENCE.ABSENCE_VALUE, P_ABSENCE.ABSENCE_STATUS, ABSENCE.SHORT_1, ABSENCE.ABSENCE FROM P_ABSENCE LEFT OUTER JOIN ABSENCE ON P_ABSENCE.ABSENCE = ABSENCE.ABSENCE WHERE P_ABSENCE.PERSNR=" . $eid . " AND P_ABSENCE.BOOKDATE>='" . date("Ymd") . "' AND ( ABSENCE_VALUE>0 OR SHORT_1 <> 'Vakantie' ) AND P_ABSENCE.ABSENCE NOT IN (6) ORDER BY P_ABSENCE.BOOKDATE, P_ABSENCE.REC_NR ";
	$result = mssql_query($query, $dbhandleProtime);
	$num = mssql_num_rows($result);
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

		while ( $row = mssql_fetch_array($result) ) {

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

	mssql_free_result($result);

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
	global $dbhandleProtime;

	$ret = array();

	$yearMonth = createDateAsString($year, $month);

	$query = "
SELECT P_ABSENCE.REC_NR, P_ABSENCE.PERSNR, P_ABSENCE.BOOKDATE, P_ABSENCE.ABSENCE_VALUE, P_ABSENCE.ABSENCE_STATUS, ABSENCE.SHORT_1, P_ABSENCE.ABSENCE 
FROM P_ABSENCE 
	LEFT OUTER JOIN ABSENCE ON P_ABSENCE.ABSENCE = ABSENCE.ABSENCE 
WHERE P_ABSENCE.PERSNR=" . $eid . " AND P_ABSENCE.BOOKDATE LIKE '" . $yearMonth . "%' AND P_ABSENCE.ABSENCE NOT IN (5, 19) 
AND ( P_ABSENCE.ABSENCE_VALUE>=" . $min_minutes . " OR P_ABSENCE.ABSENCE_VALUE=0 ) 
ORDER BY P_ABSENCE.BOOKDATE, P_ABSENCE.REC_NR 
";

	$result = mssql_query($query, $dbhandleProtime);
	$num = mssql_num_rows($result);
	if ( $num ) {
		while ( $row = mssql_fetch_array($result) ) {
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

	mssql_free_result($result);

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

	$ret .= substr('0' . $month, -2);

	if ( $day != '' ) {
		$ret .= substr('0' . $day, -2);
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
	global $connection_settings, $dbhandleTimecard;

	$oDateOriginal = new class_date( $date["y"], $date["m"], $date["d"] );

	$ret = '<br><table border=0 width="100%"><tr>';
	for ( $monthIterator = $oDateOriginal->getFirstMonthInQuarter(); $monthIterator <= $oDateOriginal->getLastMonthInQuarter(); $monthIterator++ ) {
		$oDate = new class_date( $oDateOriginal->get('Y'), $monthIterator, 1 );

		$ret .= '<td valign=top><table border=1 cellspacing=0 cellpadding=3>';
		$ret .= "<tr><td colspan=3 align=center><strong>" . $oDate->get("F Y") . "</strong></td></tr>";
		$ret .= "<tr><td><strong>Day</strong></td><td><strong>Timecard</strong></td><td><strong>Protime</strong></td></tr>";

		$number_of_days_in_current_month = $oDate->get('t');

		$oEmployee = new class_employee( $userTimecardId, $connection_settings );

		$date2["y"] = $oDate->get('Y');
		$date2["m"] = $oDate->get('n');
		$date2["d"] = 1;
		$timecard_day_totals = $oEmployee->getTimecardDayTotals( $oDate->get('Y'), $oDate->get('n') );
		$dagvakantie2 = $oEmployee->getEerderNaarHuisDayTotals( $oDate->get('Y'), $oDate->get('n') );
		$protime_day_totals = $oEmployee->getProtimeDayTotals(  $oDate->get('Ym') );

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

			// color if difference more then x minutes
			$color_start = "<span class=\"" . ( ( (int)$timecard_day_total - (int)$protime_day_total ) >= 3 || ( (int)$timecard_day_total - (int)$protime_day_total ) <= -3 ? "boldRed" : "" ) . "\">";
			$color_end = '</span>';

			$oCurrentDay = new class_date( $date2["y"], $date2["m"], $i );
			$weekday = $oCurrentDay->get('D j');
			$url = $urlprefix . "day.php?d=" . $oCurrentDay->get('Ymd') . '&eid=' . $userTimecardId . '&backurl=' . urlencode(get_current_url());
			$ret .= "<tr><td><a href=\"$url\">$weekday</a></td><td>$color_start$timecard_day_total_nice$color_end</td><td>$protime_day_total_nice</td></tr>";
		}

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
?>