<?php 
// version: 2013-02-08

class class_calendar {

	// TODOEXPLAIN
	function class_calendar() {
	}

	// TODOEXPLAIN
	function getCalendar($date, $originalDate, $scriptname, $querystring) {
		$querystring = RemoveFromQueryString($querystring, "d");
		$querystring = RemoveFromQueryString($querystring, "s");

		$t_month["1"] = 'January';
		$t_month["2"] = 'February';
		$t_month["3"] = 'March';
		$t_month["4"] = 'April';
		$t_month["5"] = 'May';
		$t_month["6"] = 'June';
		$t_month["7"] = 'July';
		$t_month["8"] = 'August';
		$t_month["9"] = 'September';
		$t_month["10"] = 'October';
		$t_month["11"] = 'November';
		$t_month["12"] = 'December';

		$retval = "
<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\" width=\"135px\" style=\"background-color: white; border: 1px solid #AAAAAA;\">
<tr>
	<td align=\"center\" colspan=\"1\">::GOTOTODAY::</td>
	<td align=\"center\" colspan=\"5\">::YEAR::</td>
	<td align=\"center\" colspan=\"1\"></td>
</tr>
<tr>
	<td align=\"center\" colspan=\"7\"><nobr><a class=\"nolink\" href=\"#\" onClick=\"::PREVIOUS::\">&laquo;</a>&nbsp;::MAAND::&nbsp;<a class=\"nolink\" href=\"#\" onClick=\"::NEXT::\">&raquo;</a></nobr></td>
</tr>
<tr>
	<td class=\"calendar_header\"><a class=\"nolink calendar_weekday\" alt=\"Monday\" title=\"Monday\">M</a></td>
	<td class=\"calendar_header\"><a class=\"nolink calendar_weekday\" alt=\"Tuesday\" title=\"Tuesday\">T</a></td>
	<td class=\"calendar_header\"><a class=\"nolink calendar_weekday\" alt=\"Wednesday\" title=\"Wednesday\">W</a></td>
	<td class=\"calendar_header\"><a class=\"nolink calendar_weekday\" alt=\"Thursday\" title=\"Thursday\">T</a></td>
	<td class=\"calendar_header\"><a class=\"nolink calendar_weekday\" alt=\"Friday\" title=\"Friday\">F</a></td>
	<td class=\"calendar_header\"><a class=\"nolink calendar_weekend\" alt=\"Saturday\" title=\"Saturday\">S</a></td>
	<td class=\"calendar_header\"><a class=\"nolink calendar_weekend\" alt=\"Sunday\" title=\"Sunday\">S</a></td>
</tr>
::DAYS::
</table>
";

		$templateDays = "
<tr>
	::DAG1::
	::DAG2::
	::DAG3::
	::DAG4::
	::DAG5::
	::DAG6::
	::DAG0::
</tr>
";

		$templateWeekdayWeekend = array(
				'werkdag_notselected' => '<td class="calendar_align"><a href="::URL::" class="nolink calendar_weekday">::DAG::</a></td>'
				, 'werkdag_selected' => '<td class="calendar_align"><a href="::URL::" class="nolink calendar_weekday selectedday">::DAG::</a></td>'
				, 'weekend_notselected' => '<td class="calendar_align"><a href="::URL::" class="nolink calendar_weekend">::DAG::</a></td>'
				, 'weekend_selected' => '<td class="calendar_align"><b><a href="::URL::" class="nolink calendar_weekend selectedday">::DAG::</a></b></td>'
				, 'empty_field' => '<td></td>'
			);

		// + + + + + + + + + + + + + + + + +

		// select year
		$select = "<select name=\"selectYear\" onchange=\"tcRefreshCalendar(" . "this.value" . "+'" . $date["m"] . $date["d"] . "', '" . class_datetime::formatDateAsString($originalDate) . "'); return false;\">\n";
		// q&d fix
		$select = str_replace('?&d=', '?d=', $select);
		$begin_jaar = 2009;
		$eind_jaar = date("Y");
		if ( $date["y"] < $begin_jaar ) {
			$begin_jaar = $date["y"];
		}
		if ( $date["y"] > $eind_jaar ) {
			$eind_jaar = $date["y"];
		}

		for ( $i=$begin_jaar; $i<=$eind_jaar; $i++) {
			$go2month = substr("0" . $i, -2);
			$tmpSelect = "<option value=\"$i\" ::SELECTED::>" . $i . "</option>\n";
			if ( $i == $date["y"] ) {
				$tmpSelect = str_replace("::SELECTED::", "SELECTED", $tmpSelect);
			} else {
				$tmpSelect = str_replace("::SELECTED::", "", $tmpSelect);
			}
			$select .= $tmpSelect;
		}
		$select .= "</select>";
		$retval = str_replace("::YEAR::", $select, $retval);

// + + + + + + + + + + + + + + + + +

		// select month
		$select = "<select name=\"selectMonth\" onchange=\"tcRefreshCalendar('" . $date["y"] . "'+" . "this.value" . "+'" . $date["d"] . "', '" . class_datetime::formatDateAsString($originalDate) . "'); return false;\">\n";
		// q&d fix
		$select = str_replace('?&d=', '?d=', $select);
		for ( $i=1; $i<=12; $i++) {
			$go2month = substr("0" . $i, -2);
			$tmpSelect = "<option value=\"$go2month\" ::SELECTED::>" . $t_month["$i"] . "</option>\n";
			if ( $i == $date["m"] ) {
				$tmpSelect = str_replace("::SELECTED::", "SELECTED", $tmpSelect);
			} else {
				$tmpSelect = str_replace("::SELECTED::", "", $tmpSelect);
			}
			$select .= $tmpSelect;
		}
		$select .= "</select>";
		$retval = str_replace("::MAAND::", $select, $retval);

// + + + + + + + + + + + + + + + + +

		// maak calendar
		$oD = new class_date($date["y"], $date["m"]);
		$maxdays = $oD->getNumberOfDaysInMonth();

		$currentDayInMonth = date("w", mktime(0, 0, 0, $date["m"], 1, $date["y"]));

		$tmpDays = $templateDays;

		for ( $i=1; $i<=$maxdays; $i++) {

			$tmp = $this->Create_Dag($i, $currentDayInMonth, $templateWeekdayWeekend, $date, $scriptname, $querystring, $originalDate);
			$tmpDays = str_replace("::DAG" . $currentDayInMonth . "::", $tmp, $tmpDays);

			if ( $currentDayInMonth == 0 ) {
				$days .= $tmpDays;
				$tmpDays = $templateDays;
			}

			$currentDayInMonth++;
			if ( $currentDayInMonth > 6 ) {
				$currentDayInMonth = 0;
			}
		}

		$days .= $tmpDays;

		// wis lege velden
		for ( $i=0; $i<=6; $i++) {
			$days = str_replace("::DAG$i::", $templateWeekdayWeekend["empty_field"], $days);
		}

		$retval = str_replace("::DAYS::", $days, $retval);

// + + + + + + + + + + + + + + + + +

		// previous
		$prev = $date["m"]-1;
		$prev_year = $date["y"];
		if ( $prev < 1 ) {
			$prev = 12;
			$prev_year--;
		}
		$prevScript = "tcRefreshCalendar('" . $prev_year . substr("0" . $prev, -2) . substr("0" . $date["d"], -2) . "', '" . class_datetime::formatDateAsString($originalDate) . "');return false;";
		$retval = str_replace("::PREVIOUS::", $prevScript, $retval);

		// next
		$next = $date["m"]+1;
		$next_year = $date["y"];
		if ( $next > 12 ) {
			$next = 1;
			$next_year++;
		}
		$nextScript = "tcRefreshCalendar('" . $next_year . substr("0" . $next, -2) . substr("0" . $date["d"], -2) . "', '" . class_datetime::formatDateAsString($originalDate) . "');return false;";
		$retval = str_replace("::NEXT::", $nextScript, $retval);

		// + + + + + + + + + + + + + + + + +

		// go home
		// alleen als niet huidige dag een sterretje tonen
		if ( date("Ymd") != $date["Ymd"] ) {
			$retval = str_replace("::GOTOTODAY::", "<a class=\"nolink\" href=\"::SCRIPT_NAME::\" alt=\"Go to today\" title=\"Go to today\">*</a>", $retval);
		} else {
			$retval = str_replace("::GOTOTODAY::", "", $retval);
		}

		// + + + + + + + + + + + + + + + + +

		$retval = str_replace("::SCRIPT_NAME::", $scriptname . '?' . $querystring, $retval);

		return $retval;
	}

	// TODOEXPLAIN
	function Create_Dag($dag, $dayInWeek, $templates, $date, $scriptname, $querystring, $originalDate) {
		$welke_template = '';

		if ( $dayInWeek == 0 || $dayInWeek == 6 ) {
			// weekend
			$welke_template = "weekend_";
		} else {
			// werkdag
			$welke_template = "werkdag_";
		}

		if ( $dag != $date["d"] || ( $date["Ym"] . substr("0" . $dag,-2) ) != $originalDate["Ymd"] ) {
			$welke_template .= "notselected";
		} else {
			$welke_template .= "selected";
		}

		$retval = $templates[$welke_template];

		$retval = str_replace("::DAG::", $dag, $retval);

// TODOTODOTODO
		$tmpQueryString = $querystring;
		$tmpQueryString .= '&d=' . $date["Ym"] . substr("0" . $dag,-2);
		$tmpQueryString = removeLeftChar($tmpQueryString, array('?', '&'));
		$retval = str_replace("::URL::", $scriptname . '?' . $tmpQueryString, $retval);

		return $retval;
	}
}
?>