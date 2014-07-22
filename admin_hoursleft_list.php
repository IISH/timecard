<?php 
//
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

//
$s = getAndProtectSearch();

$retval = '';

$oConn2 = new class_mysql($settings, 'timecard');
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
	global $settings;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$ret = '';

	$year = trim($_GET["y"]);
	$curyear = (int)date("Y");
	if ( $year == '' ) {
		$year = date("Y");
	}

	$month = trim($_GET["m"]);
	$curmonth = (int)date("m");
	if ( $month == '' ) {
		$month = date("m");
	}

	// calculate number of holidays until end of year
	$nrOfHolidays = 0;
	$queryHolidays = "SELECT COUNT(*) AS aantal FROM Feestdagen WHERE datum LIKE '" . $year . "%' AND datum>='" . date("Y-m-d") . "' AND isdeleted=0 ";
	$resultHolidays = mysql_query($queryHolidays, $oConn->getConnection());
	if ( $rowHolidays = mysql_fetch_array($resultHolidays) ) {
		$nrOfHolidays = $rowHolidays["aantal"];
	}
	mysql_free_result($resultHolidays);
	//

	$oConn->connect();
	// loop employees
	$querySelect = "SELECT ID FROM vw_Employees WHERE 1=1 and is_test_account=0 " . $queryCriterium . " ORDER BY NAME, FIRSTNAME ";
//debug( $querySelect );
	$resultSelect = mysql_query($querySelect, $oConn->getConnection());

	$arrEmployees = array();
	while ( $rowSelect = mysql_fetch_assoc($resultSelect) ) {
		$oEmployee = new class_employee($rowSelect["ID"], $settings);
		$arrEmployees[] = $oEmployee;
	}

	if ( count($arrEmployees) > 0 ) {
		$ret .= "<h2>Hours left " . $year . '-' . substr('0'.$month,-2) . " until end of year</h2>
<br>
<table border=1>
	<tr>
		<th>Name</th>
		<th>Hours&nbsp;per&nbsp;week</th>
		<th>Year total</th>
		<th>Year total (76%)</th>
		<th>Until end of year total</th>
		<th>Vacation left</th>
		<th>Nat. hol. left</th>
		<th>(Max. transfer)</th>
		<th>Left</th>
		<th>Left (76%)</th>
	</tr>
";

	for ( $i = 0; $i < count($arrEmployees); $i++ ) {
		$oEmployee = $arrEmployees[$i];

		$hoursPerWeekText = '';
		$yearTotal = 0;
		$endyearTotal = 0;
		$natHoliday = 0;
		$maxMeenemen = 0;
		$left = 0;

		// NAME
		$ret .= "\t<tr>\n";
		$ret .= "\t\t<td valign=top width=\"200px\">";
		$tmp = "<div id=\"divAddRemove" . $oEmployee->getTimecardId() . "\" style=\"display:inline;\" >::ADDREMOVE::</div> ";
		//
		if ( strpos(',' . $favIds . ',', ',' . $oEmployee->getTimecardId() . ',') !== false ) {
			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $oEmployee->getTimecardId() . ', \'r\');" alt="Stop following this person" title="Stop following this person" class="nolink"><img src="images/minus-sign.png" border=0></a>', $tmp);
		} else {
			$tmp = str_replace('::ADDREMOVE::', '<a href="#" onClick="addRemove(' . $oEmployee->getTimecardId() . ', \'a\');" alt="Start following this person" title="Start following this person" class="nolink"><img src="images/plus-sign.png" border=0></a>', $tmp);
		}

		$ret .= $tmp;

		$ret .= "<a href=\"employees_edit.php?ID=" . $oEmployee->getTimecardId() . "&backurl=" . urlencode(get_current_url()) . "\">" . $oEmployee->getLastFirstname() . "</a>";
		$ret .= "\t\t</td>\n";

		$arrHoursPerWeek = $oEmployee->getHoursPerWeek2($year);
		if ( count( $arrHoursPerWeek ) > 0 ) {
			$separator = '';
			for ( $y = 0; $y < count($arrHoursPerWeek); $y++ ) {
				$startmonth = $arrHoursPerWeek[$y]->getStartmonth();
				$endmonth = $arrHoursPerWeek[$y]->getEndmonth();
				$hourspw = $arrHoursPerWeek[$y]->getHours();

				$hoursPerWeekText .= $separator . $startmonth . '-' . $endmonth . ': ' . $hourspw . ' hpw';
				$separator = '<br>';

				for ( $k = $startmonth; $k <= $endmonth; $k++ ) {
					$yearTotal += ($hourspw * 4.333333333);

					if ( $k >= $month ) {
						$endyearTotal += ($hourspw * 4.333333333);
					}

					// calculate national holidays until end of year
					// national holidays * hoursperweek/daysperweek
					$natHoliday = $nrOfHolidays * $hourspw/5;

					// you are allowed to take two weeks to next year
					$maxMeenemen = 2 * $hourspw;
				}
			}
		}

		// HOURS PER WEEK
		$ret .= "\t\t<td valign=top>\n";
		$ret .= $hoursPerWeekText;
		$ret .= "\t\t</td>\n";

		// YEAR TOTAL
		$ret .= "\t\t<td valign=top>\n";
		$ret .= hoursLeft_formatNumber($yearTotal, 1);
		$ret .= "\t\t</td>\n";

		// YEAR TOTAL 76
		$ret .= "\t\t<td valign=top>\n";
		$ret .= hoursLeft_formatNumber($yearTotal * class_settings::getSetting("rule76"), 1);
		$ret .= "\t\t</td>\n";

		// END YEAR TOTAL
		$ret .= "\t\t<td valign=top>\n";
		$ret .= hoursLeft_formatNumber($endyearTotal, 1);
		$ret .= "\t\t</td>\n";

		// VACATION
		$vacationLeft = $oEmployee->getVacationHours();
		$ret .= "\t\t<td valign=top>\n";
		$ret .= hoursLeft_formatNumber($vacationLeft, 1);
		$ret .= "\t\t</td>\n";

		// NATIONAL HOLIDAYS LEFT
		$ret .= "\t\t<td valign=top>\n";
		$ret .= hoursLeft_formatNumber($natHoliday, 1);
		$ret .= "\t\t</td>\n";

		// MAX MEENEMEN
		$maxMeenemenText = hoursLeft_formatNumber($maxMeenemen, 1);
		if ( $maxMeenemenText != '' ) {
			$maxMeenemenText = '(' . $maxMeenemenText . ')';
		}
		$ret .= "\t\t<td valign=top>\n";
		$ret .= $maxMeenemenText;
		$ret .= "\t\t</td>\n";

		//
		if ( $yearTotal > 0 ) {
			$left = $endyearTotal - $vacationLeft - $natHoliday;
		}

		// LEFT
		$ret .= "\t\t<td valign=top>\n";
		$ret .= hoursLeft_formatNumber($left, 1);
		$ret .= "\t\t</td>\n";

		// LEFT 76
		$ret .= "\t\t<td valign=top>\n";
		$ret .= hoursLeft_formatNumber($left * class_settings::getSetting("rule76"), 1);
		$ret .= "\t\t</td>\n";

		$ret .= "\t</tr>\n";
	}
	$ret .= "</table>\n";

	}

	return $ret;
}

// TODOEXPLAIN
function hoursLeft_formatNumber($value, $decimal) {
	$ret = '';

	if ( $value != 0 ) {
		$ret = number_format($value, $decimal, ',', '.');
	}

	return $ret;
}
?>