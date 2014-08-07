<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

// sync Timecard Protime
$oWebuser->syncTimecardProtimeDayInformation($oDate);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->setTab($menuList->findTabNumber('timecard.day'));
$oPage->setTitle('Timecard | Day');
$oPage->setContent(createDayContent( $date ) . getCheckedInCheckedOut($oWebuser->getProtimeId(), $date["Ymd"]) );

// add shortcuts and recently used
if ( $date["y"] >= "2013" ) {
	$oPage->setShortcuts(getUserShortcuts($oWebuser->getTimecardId(), $oDate, $settings));
	$oPage->setRecentlyUsed(getUserRecentlyUsed($oWebuser->getTimecardId(), $oDate, $settings));
}

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createDayContent( $date ) {
	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getDayRibbon();

	//
	$ret .= goBackTo();

	$ret .= getUserDay( $date );

	return $ret;
}

// TODOEXPLAIN
function getUserDay( $date ) {
	global $settings, $oWebuser, $oDate;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$ret = '';

	// hide add new button
	if ( class_datetime::is_legacy( $oDate ) ) {
		$ret .= '<span class="youcannot">You cannot enter legacy data.</span><br><br>';
	} elseif ( class_datetime::is_future( $oDate ) && $oWebuser->getTimecardId() != 1 ) {
		$ret .= '<span class="youcannot">(You cannot enter hours in the future.)</span><br><br>';
	} else {
		$ret .= "
<table>
<tr>
	<td colspan=\"2\">
 		&nbsp; &nbsp; &nbsp;
		<input type=\"button\" class=\"button\" name=\"addNewButton\" value=\"Add new\" onClick=\"javascript:open_page('edit.php?ID=0&d=" . $date["Ymd"] . "&backurl=" . urlencode(get_current_url()) . "');\">
 	</td>
</tr>
</table>
";
	}

	$ret .= "
<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">

<form name=\"filterform\" type=\"get\">
<input type=\"hidden\" name=\"filter\" value=\"\">
<input type=\"hidden\" name=\"\" value=\"\">
	<tr>
		<TH align=\"left\"><a class=\"nolink\">Project</a>&nbsp;</TH>
		<TH align=\"left\"><a class=\"nolink\">Description</a>&nbsp;</TH>
		<TH align=\"left\"><a class=\"nolink\">Time</a>&nbsp;</TH>
		<TH align=\"left\">&nbsp;</TH>
";

	if ( $oWebuser->getShowJiraField() ) {
		$ret .= "		<TH align=\"left\"><a class=\"nolink\">Jira</a>&nbsp;</TH>
";
	}
	$ret .= "
	</tr>
</form>
";

	$timecard_deeltotaal = 0;

	$query = 'SELECT * FROM vw_hours_user WHERE Employee=' . $oWebuser->getTimecardId() . ' AND DateWorked="' . $oDate->get("Y-m-d") . '" AND protime_absence_recnr>=0 ORDER BY Description, TimeInMinutes DESC ';
	$result = mysql_query($query, $oConn->getConnection());
	while ($row = mysql_fetch_assoc($result)) {
		$timecard_deeltotaal += $row["TimeInMinutes"];
		$description = $row["WorkDescription"];
		if ( strlen($description) > 35 ) {
			$description = substr($description, 0, 35) . "...";
		}
		$description = htmlspecialchars($description);
		$protime_absence_recnr = $row["protime_absence_recnr"];
		$daily_automatic_addition_id = $row["daily_automatic_addition_id"];
		$jira_issue_nr = $row["jira_issue_nr"];

		$protime_label = '';

	$ret .= "
	<tr><A NAME=\"" . $row["ID"] . "\"></A>
";
		if ( $protime_absence_recnr != 0 ) {
			$protime_label = '<a alt="Imported from Protime" title="Imported from Protime" class="PT">(PT)</a>';
			$ret .= "
		<TD class=\"recorditem\"><nobr>" . $row["Description"] . "</nobr></td>
";
		} else {
			if ( $daily_automatic_addition_id != '' && $daily_automatic_addition_id != '0') {
				$protime_label = '<a alt="Daily automatic addition" title="Daily automatic addition" class="PT">(DAA)</a>';
			} elseif ( true ) {

			}

			// if legacy, then no edit link
			if ( class_datetime::is_legacy( $oDate ) ) {
				$ret .= "
		<TD class=\"recorditem\"><nobr>" . $row["Description"] . "</nobr></td>
";
			} else {
				$ret .= "
		<TD class=\"recorditem\"><nobr><A HREF=\"edit.php?ID=" . $row["ID"] . "&d=" . $date["Ymd"] . "&backurl=" . urlencode(get_current_url()) . "\" title=\"Edit hours\">" . $row["Description"] . "</a></nobr></td>
";
			}

		}

	$ret .= "
		<TD class=\"recorditem\">" . $description . "</td>
		<TD class=\"recorditem\">" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["TimeInMinutes"]) . "</td>
";

	$ret .= "
		<TD class=\"recorditem\">" . $protime_label . "</td>
";

	if ( $oWebuser->getShowJiraField() ) {
		$ret .= "		<TD class=\"recorditem\">" . convertToJiraUrl($jira_issue_nr) . "</td>
";
	}

	$ret .= "	</tr>
";
	}
	mysql_free_result($result);

	$dagvakantie = getEerderNaarHuisDayTotal($oWebuser->getTimecardId(), $oDate);
	if ( $dagvakantie > 0 ) {
		$ret .= "
	<tr><td colspan=\"5\"><hr></td></tr>
	<tr>
		<td colspan=\"2\"><i>Subtotal:</i></td>
		<td><i>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $timecard_deeltotaal ) . "</i></td>
	</tr>
	<tr><td colspan=\"2\"><i>Department - Leave (eerder weg):</i></td><td><i>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $dagvakantie ) . "</i></td><td><a title=\"Imported from Protime\" class=\"PT\">(PT)</a></td></tr>
";
	}

	$ret .= "
	<tr><td colspan=\"5\"><hr></td></tr>
	<tr><td colspan=\"2\"><b>Total (Timecard):</b></td><td>
";

	$timecard_day_total = $timecard_deeltotaal+$dagvakantie;
	$oEmployee = new class_employee( $oWebuser->getTimecardId(), $settings );

	$protime_day_total = $oEmployee->getProtimeDayTotal($date);

	$ret .= "
<span class=\"" . ( ( (int)$timecard_day_total - (int)$protime_day_total ) >= 3 || ( (int)$timecard_day_total - (int)$protime_day_total ) <= -3 ? "boldRed" : "bold" ) . "\">" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $timecard_day_total ) . "</span></td></tr>
	<tr><td colspan=\"2\"><b>Total (Protime):</b></td><td><b>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $protime_day_total ) . "</b></td></tr>
</table>
";
	return $ret;
}

// TODOEXPLAIN
function getUserShortcuts($userid, $oDate, $settings) {
	if ( $userid == '' || $userid == '0' || $userid == '-1' ) {
		return;
	}

	// get design
	$design = new class_contentdesign("page_div_shortcuts");

	// add header
	$ret = $design->getHeader();

	$oShortcuts = new class_shortcuts($userid, $settings, $oDate);

	// record
	$records = '';
	foreach ( $oShortcuts->getEnabledShortcuts() as $shortcut) {
		$url = "edit.php?ID=0&d=" . $oDate->get("Ymd") . "&p=" . $shortcut["projectnr"] . "&t=" . $shortcut["minutes"];
		if ( trim($shortcut["autosave"]) == '1' ) {
			$url .= "&autoSave=" . trim($shortcut["autosave"]);
		}
		if ( trim($shortcut["description"]) != '' ) {
			$url .= "&desc=" . urlencode(htmlspecialchars($shortcut["description"]));
		}
		$url .= "&backurl=" . urlencode(get_current_url());
		$shortcut["url"] = $url;

		if ( trim($shortcut["autosave"]) == '1' ) {
			$shortcut["autosave"] = "<a title=\"auto save on new\"><img src=\"images/save.gif\" border=\"0\"></a>";
		} else {
			$shortcut["autosave"] = '';
		}

		if ( trim($shortcut["description"]) != '' ) {
			$shortcut["description"] = "<br><i>" . htmlspecialchars(trim($shortcut["description"])) . "</i>";
		} else {
			$shortcut["description"] = '';
		}

		$shortcut["hourminutes"] = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($shortcut["minutes"]);

		$records .= fillTemplate($design->getRecords(), $shortcut);
	}

	if ( $records != '' ) {
		$ret .= fillTemplate( $design->getContent(), array("records" => $records) );
	}

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}

// TODOEXPLAIN
function getUserRecentlyUsed($userid, $oDate, $settings) {
	if ( $userid == '' || $userid == '0' || $userid == '-1' ) {
		return '';
	}

	// get design
	$design = new class_contentdesign("div_recentlyused");

	// add header
	$ret = $design->getHeader();

	$oRecentlyUsed = new class_recentlyused($userid, $settings, $oDate);

	// records
	$records = '';
	foreach ( $oRecentlyUsed->getRecentlyUsed() as $recentlyUsed) {
		$recentlyUsed["url"] = "edit.php?ID=0&d=" . $oDate->get("Ymd") . "&p=" . $recentlyUsed["id"] . "&backurl=" . urlencode(get_current_url());
		$records .= fillTemplate($design->getRecords(), $recentlyUsed);
	}

	if ( $records != '' ) {
		$ret .= fillTemplate( $design->getContent(), array("records" => $records) );
	}

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
