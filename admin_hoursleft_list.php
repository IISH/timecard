<?php 
//
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() && !$oWebuser->hasDepartmentAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

//
$s = getAndProtectSearch();

$retval = '';

$oConn2 = new class_mysql($databases['default']);
$oConn2->connect();
$favIds = '0';
$queryFav = "SELECT * FROM EmployeeFavourites WHERE TimecardID=" . $oWebuser->getTimecardId() . ' AND type=\'hoursleft\' ';
$resultFav = mysql_query($queryFav, $oConn2->getConnection());
while ( $rowFav = mysql_fetch_array($resultFav) ) {
	$favIds .= ',' . $rowFav["ProtimeID"];
}
mysql_free_result($resultFav);

// CRITERIUM
$queryCriterium = '';
$to_short = 0;
if ( $s == '' ) {
	// no search
	// use favourites
	$queryCriterium = 'AND ID IN (' . $favIds . ') ';
} else {
	$to_short = strlen(str_replace(' ', '', $s)) < 3;
	if ( $to_short == 1 ) {
		// search
		$queryCriterium = ' AND 1=0 ';
	} else {
		// search
		$queryCriterium = Generate_Query(array("NAME", "FIRSTNAME"), explode(' ', $s));
	}
}

$selectedMonth = trim(substr($_GET["m"],0,2));
if ( $selectedMonth == '' ) {
	$selectedMonth = date("m");
}

$selectedYear = trim(substr($_GET["y"],0,4));
if ( $selectedYear == '' ) {
	$selectedYear = date("Y");
}

if ( $to_short != 1 ) {
	$retval .= createHoursLeftContent($selectedMonth, $selectedYear, $queryCriterium, $favIds);
}
echo $retval;

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createHoursLeftContent( $selectedMonth, $selectedYear, $queryCriterium, $favIds ) {
	global $settings, $databases;

	$oConn = new class_mysql($databases['default']);
	$oConn->connect();

	$ret = '';

	$year = trim($_GET["y"]);
	if ( $year == '' ) {
		$year = date("Y");
	}

	$month = trim($_GET["m"]);
	if ( $month == '' ) {
		$month = date("m");
	}

//	// calculate number of holidays until end of year
//	$nrOfHolidays = 0;
//	$queryHolidays = "SELECT COUNT(*) AS aantal FROM Feestdagen WHERE datum LIKE '" . $year . "%' AND datum>='" . date("Y-m-d") . "' AND isdeleted=0 ";
//	$resultHolidays = mysql_query($queryHolidays, $oConn->getConnection());
//	if ( $rowHolidays = mysql_fetch_array($resultHolidays) ) {
//		$nrOfHolidays = $rowHolidays["aantal"];
//	}
//	mysql_free_result($resultHolidays);
	//

	$oConn->connect();

	// loop employees
	$querySelect = "SELECT ID FROM vw_Employees WHERE isdisabled=0 AND is_test_account=0 " . $queryCriterium . " ORDER BY FIRSTNAME, NAME ";

	$resultSelect = mysql_query($querySelect, $oConn->getConnection());

	$arrEmployees = array();
	while ( $rowSelect = mysql_fetch_assoc($resultSelect) ) {
		$oEmployee = new class_employee($rowSelect["ID"], $settings);
		$arrEmployees[] = $oEmployee;
	}

	if ( count($arrEmployees) > 0 ) {
		$ret .= "
<a href=\"#\" onclick=\"javascript:hideMonths();return false;\" class=\"button extrabuttonmargin\">Hide months</a> &nbsp; <a href=\"#\" onclick=\"javascript:showMonths();return false;\" class=\"button extrabuttonmargin\">Show months</a>
<!-- &nbsp; <button onclick=\"hidePastMonths();\">Hide past months</button> -->
<br>
<table border=1 id=\"tblHours\" CELLPADDING=\"3\">
	<tr>
		<th>Name</th>
		<th>Hours per week</th>
		<th><a title=\"Exact calculation of\nyear total\">Year total (100%)</a></th>
		<th>Year total (" . (int)(class_settings::getSetting("percentage_rule")*100.0) . "%)</th>
		<th colspan=3>January</th>
		<th colspan=3>February</th>
		<th colspan=3>March</th>
		<th colspan=3 style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;border-right-style: solid;border-right-width: 2px;\">Q1</th>
		<th colspan=3>April</th>
		<th colspan=3>May</th>
		<th colspan=3>June</th>
		<th colspan=3 style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;border-right-style: solid;border-right-width: 2px;\">Q2</th>
		<th colspan=3>July</th>
		<th colspan=3>August</th>
		<th colspan=3>September</th>
		<th colspan=3 style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;border-right-style: solid;border-right-width: 2px;\">Q3</th>
		<th colspan=3>October</th>
		<th colspan=3>November</th>
		<th colspan=3>December</th>
		<th colspan=3 style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;border-right-style: solid;border-right-width: 2px;\">Q4</th>
		<th><a title=\"Not yet booked\nvacation hours\">Vacation hours</a></th>
		<th><a title=\"After deduction of all absences,\nnational holidays, brugdagen\nand not booked vacation days,\nthis are the hours available for projects\">Left (100%)</a></th>
		<th>Left (80%)</th>
	</tr>
";

		$ret .= "
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
";

		for ( $i =1 ; $i <= 4; $i++ ) {
			$ret .= "
		<td align=center><a title=\"{a_title}\">{a}</a></td>
		<td align=center><a title=\"{b_title}\">{b}</a></td>
		<td align=center><a title=\"{c_title}\">{c}</a></td>

		<td align=center><a title=\"{a_title}\">{a}</a></td>
		<td align=center><a title=\"{b_title}\">{b}</a></td>
		<td align=center><a title=\"{c_title}\">{c}</a></td>

		<td align=center><a title=\"{a_title}\">{a}</a></td>
		<td align=center><a title=\"{b_title}\">{b}</a></td>
		<td align=center><a title=\"{c_title}\">{c}</a></td>

		<td align=center style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;\"><a title=\"{a_title}\">{a}</a></td>
		<td align=center style=\"background-color:lightgrey\"><a title=\"{b_title}\">{b}</a></td>
		<td align=center style=\"background-color:lightgrey;border-right-style: solid;border-right-width: 2px;\"><a title=\"{c_title}\">{c}</a></td>
";
		}

		$ret .= "
		<td align=center colspan=3>&nbsp;</td>
	</tr>
";

		//
		$ret = str_replace('{a}', 'TH', $ret);
		$ret = str_replace('{a_title}', 'Total Hours', $ret);

		$ret = str_replace('{b}', 'B', $ret);
//		$ret = str_replace('{b_title}', "Already booked\n- National holidays\n- Brugdagen", $ret);
		$ret = str_replace('{b_title}', "Not work related absences\nand booked 'vast werk'", $ret);

		$ret = str_replace('{c}', 'P', $ret);
		$ret = str_replace('{c_title}', 'Available for projects', $ret);

		// + + + + + + +

		$template = "
	<tr>
		<td valign=top xxxwidth=\"200px\"><nobr>{name}</nobr></td>
		<td align=right><a xxxalt=\"{hours_per_week_text}\" title=\"{hours_per_week_text}\">{hours_per_week}</a></td>
		<td align=right>{year_total_100_percent}</td>
		<td align=right>{year_total_percentage_rule}</td>

		<td align=right>{M1_1}</td>
		<td align=right><a title=\"{M1_2_title}\">{M1_2}</a></td>
		<td align=right style=\"background-color:{M1_3_color};\">{M1_3}</td>

		<td align=right>{M2_1}</td>
		<td align=right><a title=\"{M2_2_title}\">{M2_2}</a></td>
		<td align=right style=\"background-color:{M2_3_color};\">{M2_3}</td>

		<td align=right>{M3_1}</td>
		<td align=right><a title=\"{M3_2_title}\">{M3_2}</a></td>
		<td align=right style=\"background-color:{M3_3_color};\">{M3_3}</td>

		<td align=right style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;\">{Q1_1}</td>
		<td align=right style=\"background-color:lightgrey\"><a title=\"{Q1_2_title}\">{Q1_2}</a></td>
		<td align=right style=\"background-color:{Q1_3_color};border-right-style: solid;border-right-width: 2px;\">{Q1_3}</td>

		<td align=right>{M4_1}</td>
		<td align=right><a title=\"{M4_2_title}\">{M4_2}</a></td>
		<td align=right style=\"background-color:{M4_3_color};\">{M4_3}</td>

		<td align=right>{M5_1}</td>
		<td align=right><a title=\"{M5_2_title}\">{M5_2}</a></td>
		<td align=right style=\"background-color:{M5_3_color};\">{M5_3}</td>

		<td align=right>{M6_1}</td>
		<td align=right><a title=\"{M6_2_title}\">{M6_2}</a></td>
		<td align=right style=\"background-color:{M6_3_color};\">{M6_3}</td>

		<td align=right style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;\">{Q2_1}</td>
		<td align=right style=\"background-color:lightgrey\"><a title=\"{Q2_2_title}\">{Q2_2}</a></td>
		<td align=right style=\"background-color:{Q2_3_color};border-right-style: solid;border-right-width: 2px;\">{Q2_3}</td>

		<td align=right>{M7_1}</td>
		<td align=right><a title=\"{M7_2_title}\">{M7_2}</a></td>
		<td align=right style=\"background-color:{M7_3_color};\">{M7_3}</td>

		<td align=right>{M8_1}</td>
		<td align=right><a title=\"{M8_2_title}\">{M8_2}</a></td>
		<td align=right style=\"background-color:{M8_3_color};\">{M8_3}</td>

		<td align=right>{M9_1}</td>
		<td align=right><a title=\"{M9_2_title}\">{M9_2}</a></td>
		<td align=right style=\"background-color:{M9_3_color};\">{M9_3}</td>

		<td align=right style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;\">{Q3_1}</td>
		<td align=right style=\"background-color:lightgrey\"><a title=\"{Q3_2_title}\">{Q3_2}</a></td>
		<td align=right style=\"background-color:{Q3_3_color};border-right-style: solid;border-right-width: 2px;\">{Q3_3}</td>

		<td align=right>{M10_1}</td>
		<td align=right><a title=\"{M10_2_title}\">{M10_2}</a></td>
		<td align=right style=\"background-color:{M10_3_color};\">{M10_3}</td>

		<td align=right>{M11_1}</td>
		<td align=right><a title=\"{M11_2_title}\">{M11_2}</a></td>
		<td align=right style=\"background-color:{M11_3_color};\">{M11_3}</td>

		<td align=right>{M12_1}</td>
		<td align=right><a title=\"{M12_2_title}\">{M12_2}</a></td>
		<td align=right style=\"background-color:{M12_3_color};\">{M12_3}</td>

		<td align=right style=\"background-color:lightgrey;border-left-style: solid;border-left-width: 2px;\">{Q4_1}</td>
		<td align=right style=\"background-color:lightgrey\"><a title=\"{Q4_2_title}\">{Q4_2}</a></td>
		<td align=right style=\"background-color:{Q4_3_color};border-right-style: solid;border-right-width: 2px;\">{Q4_3}</td>

		<td align=right><a title=\"Not yet booked vacation hours\">{vacation_left}</a></td>
		<td align=right>{left_total_100_percent}</td>
		<td align=right>{left_total_percentage_rule}</td>
	</tr>
";

		for ( $i = 0; $i < count($arrEmployees); $i++ ) {
			$oEmployee = $arrEmployees[$i];

			$oHoursForPlanning = new class_employee_hours_for_planning( $oEmployee, date("Y") );
			$oAbsences = new class_employee_not_work_related_absences( $oEmployee, date("Y") );
			$oVastWerk = new class_employee_vast_werk( $oEmployee, date("Y") );

			$tmp = $template;

			// sterretje
			$tmpDiv = "<div id=\"divAddRemove" . $oEmployee->getTimecardId() . "\" style=\"display:inline;\" >::ADDREMOVE::</div> ";
			if ( strpos(',' . $favIds . ',', ',' . $oEmployee->getTimecardId() . ',') !== false ) {
				$tmpDiv = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $oEmployee->getTimecardId() . ', \'r\');" alt="Stop following this person" title="Stop following this person" class="nolink favourites_on">&#9733;</a>', $tmpDiv);
			} else {
				$tmpDiv = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $oEmployee->getTimecardId() . ', \'a\');" alt="Start following this person" title="Start following this person" class="nolink favourites_off">&#9733;</a>', $tmpDiv);
			}

			// link name
			$nameLink = "<a href=\"employees_edit.php?ID=" . $oEmployee->getTimecardId() . "&backurl=" . urlencode(get_current_url()) . "\">" . $oEmployee->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin( $oEmployee->getLastname() ) . "</a>";

			//
			$monthWorkTotals = array();
			$monthAbsenceTotals = array();
			$monthDifferenceTotals = array();
			$numberOfNationalHolidays = array();
			$numberOfBrugdagen = array();
			$monthTitles = array();
			$vastWerkTotals = array();

			for ( $j = 1; $j <= 12; $j++ ) {
				$monthWorkTotals["$j"] = $oHoursForPlanning->getWorkValue(date("Y") . '-' . substr('0'.$j,-2));
//				$monthAbsenceTotals["$j"] = $hoursForPlanning->getNationalHolidayValue(date("Y") . '-' . substr('0'.$j,-2)) + $hoursForPlanning->getBrugdagValue(date("Y") . '-' . substr('0'.$j,-2));

				$vastWerkTotals["$j"] = $oVastWerk->getMonthTotal( $j );
				$monthAbsenceTotals["$j"] = $oAbsences->getTotalInHoursForSpecifiedMonth( date("Y") . substr('0'.$j,-2) ) + $vastWerkTotals["$j"];

//				$vastWerkTotals["$j"] = $oVastWerk->getMonthTotal( $j );

				$difference = $monthWorkTotals["$j"] - $monthAbsenceTotals["$j"];
				if ( $difference < 0 ) {
//					$difference = 0;
				}
				$monthDifferenceTotals["$j"] = $difference;

//				$numberOfNationalHolidays["$j"] = $hoursForPlanning->getNumberOfNationalHolidays( date("Y") . '-' . substr('0'.$j,-2) );
//				$numberOfBrugdagen["$j"] = $hoursForPlanning->getNumberOfBrugdagen( date("Y") . '-' . substr('0'.$j,-2) );

				//
				$title = '';
				if ( $monthAbsenceTotals["$j"] > 0 ) {
					$title .= $oAbsences->getSummarizationForSpecifiedMonth( date("Y") . substr('0'.$j,-2) );
				}
				if ( $vastWerkTotals["$j"] > 0 ) {
					$title .= 'Vast werk: ' .  hoursLeft_formatNumber($vastWerkTotals["$j"]) . " hours\n";
				}
//				if ( $numberOfNationalHolidays["$j"] > 0 ) {
//					$days = ( $numberOfNationalHolidays["$j"] == 1 ) ? 'day' : 'days';
//					$title .= "- National holiday: " . $numberOfNationalHolidays["$j"] . " $days\n";
//				}
//				if ( $numberOfBrugdagen["$j"] > 0 ) {
//					$days = ( $numberOfBrugdagen["$j"] == 1 ) ? 'day' : 'days';
//					$title .= "- Brugdag: " . $numberOfBrugdagen["$j"] . " $days\n";
//				}
				if ( $title != '' ) {
					$oTmpDate = new TCDateTime();
					$oTmpDate->setFromString(date("Y") . '-' . substr('0'.$j,-2) . "-01", "Y-m-d");
					$title = $oTmpDate->getToString("F") . "\n" . $title . "\n";
				}
				$monthTitles["$j"] = $title;
			}

			$quarterWorkTotals = array();
			$quarterAbsenceTotals = array();
			$quarterDifferenceTotals = array();
			$quarterTitles = array();
			for ( $q = 1; $q <= 4; $q++ ) {
				$quarterWorkTotals["$q"] = $monthWorkTotals[((($q-1)*3)+1).""] + $monthWorkTotals[((($q-1)*3)+2).""] + $monthWorkTotals[((($q-1)*3)+3).""];
				$quarterAbsenceTotals["$q"] = $monthAbsenceTotals[((($q-1)*3)+1).""] + $monthAbsenceTotals[((($q-1)*3)+2).""] + $monthAbsenceTotals[((($q-1)*3)+3).""];

				$difference = $quarterWorkTotals["$q"] - $quarterAbsenceTotals["$q"];
				if ( $difference < 0 ) {
//					$difference = 0;
				}
				$quarterDifferenceTotals["$q"] = $difference;

				$quarterTitles["$q"] = $monthTitles[((($q-1)*3)+1)] . $monthTitles[((($q-1)*3)+2)] . $monthTitles[((($q-1)*3)+3)];
			}

			$yearWorkTotal = $quarterWorkTotals["1"] + $quarterWorkTotals["2"] + $quarterWorkTotals["3"] + $quarterWorkTotals["4"];
			$vacationLeft = $oEmployee->getAmountOfNotPlannedVacationInHours( date("Y") );
			$yearLeftTotal = $quarterDifferenceTotals["1"] + $quarterDifferenceTotals["2"] + $quarterDifferenceTotals["3"] + $quarterDifferenceTotals["4"] - $vacationLeft;

			// hours per week
			$oHoursPerWeek = $oEmployee->getHoursPerWeek3($year);

			// + + + + + + + + + + + + + + + + + + +
			// add values to template

			$tmp = str_replace('{hours_per_week}', hoursLeft_formatNumber( $oHoursPerWeek->getHoursPerWeek() ), $tmp);
			$tmp = str_replace('{hours_per_week_text}', $oHoursPerWeek->getHoursPerWeekText(), $tmp);

			// name link
			$tmp = str_replace('{name}', $tmpDiv . $nameLink, $tmp);

			// months
			for ( $j = 1; $j <= 12; $j++ ) {
				$tmp = str_replace('{M' . $j .'_1}', hoursLeft_formatNumber($monthWorkTotals["$j"]), $tmp);
				$tmp = str_replace('{M' . $j .'_2}', hoursLeft_formatNumber($monthAbsenceTotals["$j"]), $tmp);
				$tmp = str_replace('{M' . $j .'_3}', hoursLeft_formatNumber($monthDifferenceTotals["$j"]), $tmp);

				// title for absence/booked column
				$tmp = str_replace('{M' . $j .'_2_title}', $monthTitles["$j"], $tmp);

				// color for project column
//				if ( $monthDifferenceTotals["$j"] < 0.0 ) {
//					$tmp = str_replace('{M' . $j .'_3_color}', 'yellow', $tmp);
//				} else {
//					$tmp = str_replace('{M' . $j .'_3_color}', 'white', $tmp);
//				}
				$tmp = str_replace('{M' . $j .'_3_color}', getListColor($monthDifferenceTotals["$j"]), $tmp);
			}

			// quarter
			for ( $j = 1; $j <= 4; $j++ ) {
				$tmp = str_replace('{Q' . $j .'_1}', hoursLeft_formatNumber($quarterWorkTotals["$j"]), $tmp);
				$tmp = str_replace('{Q' . $j .'_2}', hoursLeft_formatNumber($quarterAbsenceTotals["$j"]), $tmp);
				$tmp = str_replace('{Q' . $j .'_3}', hoursLeft_formatNumber($quarterDifferenceTotals["$j"]), $tmp);

				// title for absence/booked column
				$tmp = str_replace('{Q' . $j .'_2_title}', $quarterTitles["$j"], $tmp);

				// color for project column
//				if ( $quarterDifferenceTotals["$j"] < 0 ) {
//					$tmp = str_replace('{Q' . $j .'_3_color}', 'yellow', $tmp);
//				} else {
//					$tmp = str_replace('{Q' . $j .'_3_color}', 'lightgrey', $tmp);
//				}
				$tmp = str_replace('{Q' . $j .'_3_color}', getListColor($quarterDifferenceTotals["$j"], 'lightgrey'), $tmp);
			}

			// year
			$tmp = str_replace('{year_total_100_percent}', hoursLeft_formatNumber($yearWorkTotal), $tmp);
			$tmp = str_replace('{year_total_percentage_rule}', hoursLeft_formatNumber($yearWorkTotal * class_settings::getSetting("percentage_rule")), $tmp);
			$tmp = str_replace('{left_total_100_percent}', hoursLeft_formatNumber($yearLeftTotal), $tmp);
			$tmp = str_replace('{left_total_percentage_rule}', hoursLeft_formatNumber($yearLeftTotal * class_settings::getSetting("percentage_rule")), $tmp);

			$tmp = str_replace('{vacation_left}', hoursLeft_formatNumber($vacationLeft), $tmp);

			$ret .= $tmp;
		}

		$ret .= "\t</tr>\n";
		$ret .= "</table>\n";
	}

//	// OUDE VERSIE
//	if ( count($arrEmployees) > 0 && false ) {
//		$ret .= "
//<br>
//<table border=1>
//	<tr>
//		<th>Name</th>
//		<th>Hours&nbsp;per&nbsp;week</th>
//		<th>Year total (100%)</th>
//		<th>Year total (" . (int)(class_settings::getSetting("percentage_rule")*100.0) . "%)</th>
//		<th>Until end of year total (100%)</th>
//		<th>Vacation left</th>
//		<th>Nat. hol. left</th>
//		<th>(Max. transfer)</th>
//		<th>Left (100%)</th>
//		<th>Left (" . (int)(class_settings::getSetting("percentage_rule")*100.0) . "%)</th>
//	</tr>
//";
//
//	for ( $i = 0; $i < count($arrEmployees); $i++ ) {
//		$oEmployee = $arrEmployees[$i];
//
//		$hoursPerWeekText = '';
//		$yearWorkTotal = 0;
//		$endyearTotal = 0;
//		$natHoliday = 0;
//		$maxMeenemen = 0;
//		$left = 0;
//
//		// NAME
//		$ret .= "\t<tr>\n";
//		$ret .= "\t\t<td valign=top width=\"200px\">";
//		$tmp = "<div id=\"divAddRemove" . $oEmployee->getTimecardId() . "\" style=\"display:inline;\" >::ADDREMOVE::</div> ";
//		//
//		if ( strpos(',' . $favIds . ',', ',' . $oEmployee->getTimecardId() . ',') !== false ) {
//			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $oEmployee->getTimecardId() . ', \'r\');" alt="Stop following this person" title="Stop following this person" class="nolink favourites_on">&#9733;</a>', $tmp);
//		} else {
//			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $oEmployee->getTimecardId() . ', \'a\');" alt="Start following this person" title="Start following this person" class="nolink favourites_off">&#9733;</a>', $tmp);
//		}
//
//		$ret .= $tmp;
//
//		$ret .= "<a href=\"employees_edit.php?ID=" . $oEmployee->getTimecardId() . "&backurl=" . urlencode(get_current_url()) . "\">" . $oEmployee->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin( $oEmployee->getLastname() ) . "</a>";
//		$ret .= "\t\t</td>\n";
//
//		$arrHoursPerWeek = $oEmployee->getHoursPerWeek2($year);
//		if ( count( $arrHoursPerWeek ) > 0 ) {
//			$separator = '';
//			for ( $y = 0; $y < count($arrHoursPerWeek); $y++ ) {
//				$startmonth = $arrHoursPerWeek[$y]->getStartmonth();
//				$endmonth = $arrHoursPerWeek[$y]->getEndmonth();
//				$hourspw = $arrHoursPerWeek[$y]->getHours();
//
//				$hoursPerWeekText .= $separator . $startmonth . '-' . $endmonth . ': ' . hoursLeft_formatNumber($hourspw,1);
//				$separator = '<br>';
//
//				for ( $k = $startmonth; $k <= $endmonth; $k++ ) {
//					$yearWorkTotal += ($hourspw * 4.333333333);
//
//					if ( $k >= $month ) {
//						$endyearTotal += ($hourspw * 4.333333333);
//					}
//
//					// calculate national holidays until end of year
//					// national holidays * hoursperweek/daysperweek
//					$natHoliday = $nrOfHolidays * $hourspw/5;
//
//					// you are allowed to take two weeks to next year
//					$maxMeenemen = 2 * $hourspw;
//				}
//			}
//		}
//
//		// HOURS PER WEEK
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= $hoursPerWeekText;
//		$ret .= "\t\t</td>\n";
//
//		// YEAR TOTAL
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= hoursLeft_formatNumber($yearWorkTotal, 1);
//		$ret .= "\t\t</td>\n";
//
//		// YEAR TOTAL 76
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= hoursLeft_formatNumber(1.0 * $yearWorkTotal * class_settings::getSetting("percentage_rule"), 1);
//		$ret .= "\t\t</td>\n";
//
//		// END YEAR TOTAL
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= hoursLeft_formatNumber($endyearTotal, 1);
//		$ret .= "\t\t</td>\n";
//
//		// VACATION LEFT
//		$arrVacationLeft = $oEmployee->getVacationHours();
//		$vacationLeft = $arrVacationLeft["value"];
//		$vacationLeftBookdate = $arrVacationLeft["bookdate"];
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= hoursLeft_formatNumber($vacationLeft, 1);
//
//		$sterretje = '*';
//		if ( $vacationLeftBookdate != '' && $vacationLeftBookdate < date("Ymd", mktime(0,0,0, date("m")-1, 1, date("Y")) ) ) {
//			$sterretje = '**';
//		}
//
//		$oD = new TCDateTime();
//		$oD->setFromString($vacationLeftBookdate, 'Ymd');
//		$ret .= "<a class=\"nolink\" title=\"Processed until: " . $oD->get()->format("Y-m-d") . "\">$sterretje</a>";
//
//		$ret .= "\t\t</td>\n";
//
//		// NATIONAL HOLIDAYS LEFT
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= hoursLeft_formatNumber($natHoliday, 1);
//		$ret .= "\t\t</td>\n";
//
//		// MAX MEENEMEN
//		$maxMeenemenText = hoursLeft_formatNumber($maxMeenemen, 1);
//		if ( $maxMeenemenText != '' ) {
//			$maxMeenemenText = '(' . $maxMeenemenText . ')';
//		}
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= $maxMeenemenText;
//		$ret .= "\t\t</td>\n";
//
//		//
//		if ( $yearWorkTotal > 0 ) {
//			$left = $endyearTotal - $vacationLeft - $natHoliday;
//		}
//
//		// LEFT
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= hoursLeft_formatNumber($left, 1);
//		$ret .= "\t\t</td>\n";
//
//		// LEFT 76
//		$ret .= "\t\t<td valign=top>\n";
//		$ret .= hoursLeft_formatNumber(1.0 * $left * class_settings::getSetting("percentage_rule"), 1);
//		$ret .= "\t\t</td>\n";
//
//		$ret .= "\t</tr>\n";
//	}
//	$ret .= "</table>\n";
//
//	}

	return $ret;
}

function getListColor( $value, $default_color = 'white' ) {
	$color = $default_color;

	if ( $value < -5 ) {
		$color = 'red';
	} elseif ( $value < 0 ) {
		$color = 'yellow';
	}

	return $color;
}