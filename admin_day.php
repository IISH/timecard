<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

// sync Timecard Protime
$oEmployee = new class_employee($protect->request('get', 'eid'), $settings);
$oEmployee->syncTimecardProtimeDayInformation($oDate);

// create webpage
$oPage = new class_page('design/page_admin.php', $settings);
if ( $oEmployee->getTimecardId() == -1 || $oEmployee->getTimecardId() == '' ) {
	$oPage->removeSidebar();
}
$oPage->setTab($menuList->findTabNumber('administrator.day'));
$oPage->setTitle('Timecard | Admin Day');
$oPage->setContent(createAdminDayContent( $date ) . getCheckedInCheckedOut($oEmployee->getProtimeId(), $date["Ymd"]));
$oPage->setLeftMenu( getEmployeesRibbon($oEmployee, $date["y"]) );

// add shortcuts and recently used
if ( $date["y"] >= "2013" ) {
	$oPage->setShortcuts(getAdminShortcuts($oEmployee->getTimecardId(), $oDate, $settings));
	$oPage->setRecentlyUsed(getAdminRecentlyUsed($oEmployee->getTimecardId(), $oDate, $settings));
}

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAdminDayContent( $date ) {
	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getDayRibbon();

	//
	$ret .= goBackTo();

	//
	$ret .= getAdminDay( $date );

	return $ret;
}

// TODOEXPLAIN
function getAdminDay( $date ) {
	global $settings, $oEmployee, $oDate;

	$oConn = new class_mysql($settings, 'timecard');
	$oConn->connect();

	$ret = '';

	if ( $oEmployee->getTimecardId() != '' ) {

		if ( $oEmployee->getTimecardId() == -1 ) {
			// MULTIPLE USER

			require_once("./classes/class_view/class_view.inc.php");
			require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
			require_once("./classes/class_view/fieldtypes/class_field_time.inc.php");

			$oDb = new class_mysql($settings, 'timecard');
			$oView = new class_view($settings, $oDb);

			// if legacy, then no edit link
			$add_new_url = '';
			if ( !class_datetime::is_legacy( $oDate ) ) {
			} else {
				$add_new_url = "admin_edit.php?ID=0&d=" . $oDate->get("Ymd") . "&eid=" . $oEmployee->getTimecardId() . "&backurl=[BACKURL]";
			}

			$oView->set_view( array(
				'query' => 'SELECT * FROM vw_hours_admin WHERE DateWorked LIKE "' . $oDate->get("Y-m-d") . '%" '
				, 'count_source_type' => 'query'
				, 'order_by' => 'Description, TimeInMinutes DESC '
				, 'anchor_field' => 'ID'
				, 'viewfilter' => true
				, 'calculate_total' => array('nrofcols' => 6, 'totalcol' => 4, 'field' => 'TimeInMinutes')
				, 'add_new_url' => $add_new_url
				, 'table_parameters' => ' cellspacing="0" cellpadding="2" border="0" '
				, 'extra_hidden_viewfilter_fields' => '<input type="hidden" name="d" value="' . $date["Ymd"] . '"><input type="hidden" name="eid" value="' . $oEmployee->getTimecardId() . '">'
				));

			$oView->add_field( new class_field_string ( array(
				'fieldname' => 'LongCode'
				, 'fieldlabel' => 'Employee'
				, 'viewfilter' => array(
									'labelfilterseparator' => '<br>'
									, 'filter' => array (
														array (
															'fieldname' => 'LongCode'
															, 'type' => 'string'
															, 'size' => 10
														)
														)
									)
				, 'nobr' => true
				)));

			// if legacy, then no edit link
			$href = '';
			if ( !class_datetime::is_legacy( $oDate ) ) {
				$href = 'admin_edit.php?ID=[FLD:ID]&d=' . $oDate->get("Ymd") . '&backurl=[BACKURL]';
			}

			$oView->add_field( new class_field_string ( array(
				'fieldname' => 'Description'
				, 'fieldlabel' => 'Project'
				, 'href' => $href
				, 'no_href_if' => array(
						"field" => "protime_absence_recnr"
						, "operator" => "<>"
						, "value" => "0"
					)
				, 'viewfilter' => array(
									'labelfilterseparator' => '<br>'
									, 'filter' => array (
														array (
															'fieldname' => 'Description'
															, 'type' => 'string'
															, 'size' => 10
														)
													)
									)
				, 'nobr' => true
				)));

			$oView->add_field( new class_field_string ( array(
				'fieldname' => 'WorkDescription'
				, 'fieldlabel' => 'Description'
				, 'view_max_length' => 30
				, 'view_max_length_extension' => '...'
				, 'viewfilter' => array(
									'labelfilterseparator' => '<br>'
									, 'filter' => array (
														array (
															'fieldname' => 'WorkDescription'
															, 'type' => 'string'
															, 'size' => 10
														)
													)
									)
					)));

			$oView->add_field( new class_field_time ( array(
				'fieldname' => 'TimeInMinutes'
				, 'fieldlabel' => 'Time'
				)));

			$oView->add_field( new class_field_string ( array(
				'fieldname' => 'protime_absence_recnr'
				, 'fieldlabel' => ''
				, 'show_different_value' => array(
						"value" => "0"
						, "showvalue" => ""
						, "showelsevalue" => "<a title=\"Imported from Protime\" class=\"PT\">(PT)</a>"
					)
				)));

			// generate view
			$ret .= $oView->generate_view();

			return $ret;
		} else {

			// SINGLE USER
			if ( !class_datetime::is_legacy( $oDate ) ) {
				$ret .= "
<table>
<tr>
	<td colspan=\"2\">
 		&nbsp; &nbsp; &nbsp;
		<input type=\"button\" class=\"button\" name=\"addNewButton\" value=\"Add new\" onClick=\"javascript:open_page('admin_edit.php?ID=0&d=" . $date["Ymd"] . "&eid=" . $oEmployee->getTimecardId() . "&backurl=" . urlencode(get_current_url()) . "');\">
		&nbsp;&nbsp;&nbsp;
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
		<TH align=\"left\"><a class=\"nolink\">Project</a></TH>
		<TH align=\"left\"><a class=\"nolink\">Description</a></TH>
		<TH align=\"left\"><a class=\"nolink\">Time</a></TH>
	</tr>
</form>
";

			$timecard_deeltotaal = 0;

			$query = 'SELECT * FROM vw_hours_admin WHERE Employee=' . $oEmployee->getTimecardId() . ' AND DateWorked LIKE "' . $oDate->get("Y-m-d") . '%" AND protime_absence_recnr>=0 ORDER BY Description, TimeInMinutes DESC ';

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
				$protime_label = '';

				$ret .= "
	<tr><A NAME=\"" . $row["ID"] . "\"></A>
";

				if ( $protime_absence_recnr != 0 ) {
					$protime_label = '<a title="Imported from Protime" class=\"PT\">(PT)</a>';
					$ret .= "
		<TD class=\"recorditem\"><nobr>" . $row["Description"] . "</nobr></td>
";
				} else {
					if ( $daily_automatic_addition_id != '' && $daily_automatic_addition_id != '0') {
						$protime_label = '<a title="Daily automatic addition" class="PT">(DAA)</a>';
					} elseif ( true ) {

					}

					// if legacy, then no edit link
					if ( class_datetime::is_legacy( $oDate ) ) {
						$ret .= "
		<TD class=\"recorditem\"><nobr>" . $row["Description"] . "</nobr></td>
";
					} else {
						$ret .= "
		<TD class=\"recorditem\"><nobr><A HREF=\"admin_edit.php?ID=" . $row["ID"] . "&d=" . $date["Ymd"] . "&eid=" . $oEmployee->getTimecardId() . "&backurl=" . urlencode(get_current_url()) . "\" title=\"Edit hours\">" . $row["Description"] . "</a></nobr></td>
";
					}
				}

				$ret .= "
		<TD class=\"recorditem\">" . $description . "</td>
		<TD class=\"recorditem\">" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["TimeInMinutes"]) . "</td>
		<TD class=\"recorditem\">
";

				$ret .= "
		</td>
		<TD class=\"recorditem\">" . $protime_label . "</td>
	</tr>
";

			}
			mysql_free_result($result);

			$dagvakantie = getEerderNaarHuisDayTotal($oEmployee->getTimecardId(), $oDate);

			$ret .= "
	<tr><td colspan=\"5\"><hr></td></tr>
";

			$ret .= "
	<tr>
		<td colspan=\"2\"><i>Subtotal:</i></td>
		<td><i>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $timecard_deeltotaal ) . "</i></td>
	</tr>
";

			$ret .= "
	<tr>
		<td colspan=\"2\"><i>Department - Leave (eerder weg):</i></td>
		<td><i>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $dagvakantie ) . "</i></td>
		<td></td>
		<td><a title=\"Imported from Protime\" class=\"PT\">(PT)</a></td>
	</tr>
";

			$ret .= "
	<tr><td colspan=\"5\"><hr></td></tr>
";

			$timecard_day_total = $timecard_deeltotaal+$dagvakantie;

			$oEmployee = new class_employee( $oEmployee->getTimecardId(), $settings );
			$protime_day_total = $oEmployee->getProtimeDayTotal($date);

			$ret .= "
	<tr><td colspan=\"2\"><b>Total (Timecard):</b></td><td><span class=\"" . ( ( (int)$timecard_day_total - (int)$protime_day_total ) >= 3 || ( (int)$timecard_day_total - (int)$protime_day_total ) <= -3 ? "boldRed" : "bold" ) . "\">
" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $timecard_day_total ) . "
</span></td></tr>";
			$ret .= "
	<tr><td colspan=\"2\"><b>Total (Protime):</b></td><td><b>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $protime_day_total ) . "</b></td></tr>
";

			$ret .= "

</table>
";

		}
	}

	return $ret;
}

	// TODOEXPLAIN
	function getAdminShortcuts($pid, $oDate, $settings) {
		if ( $pid == '' || $pid == '0' || $pid == '-1' ) {
			return '';
		}

		// get design
		$design = new class_contentdesign("page_div_shortcuts");

		// add header
		$ret = $design->getHeader();

		$oShortcuts = new class_shortcuts($pid, $settings, $oDate);

		// records
		$records = '';
		foreach ( $oShortcuts->getEnabledShortcuts() as $shortcut) {
			$url = "admin_edit.php?ID=0&eid=" . $pid . "&d=" . $oDate->get("Ymd") . "&p=" . $shortcut["projectnr"] . "&t=" . $shortcut["minutes"];
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

		// add header
		if ( $records != '' ) {
			$ret .= fillTemplate( $design->getContent(), array("records" => $records) );
		}

		// add footer
		$ret .= $design->getFooter();

		return $ret;
	}

	// TODOEXPLAIN
	function getAdminRecentlyUsed($pid, $oDate, $settings) {
		if ( $pid == '' || $pid == '0' || $pid == '-1' ) {
			return '';
		}

		// get design
		$design = new class_contentdesign("div_recentlyused");

		// add header
		$ret = $design->getHeader();

		$oRecentlyUsed = new class_recentlyused($pid, $settings, $oDate);

		// record
		$records = '';
		foreach ( $oRecentlyUsed->getRecentlyUsed() as $recentlyUsed) {
			$recentlyUsed["url"] = "admin_edit.php?ID=0&eid=" . $pid . "&d=" . $oDate->get("Ymd") . "&p=" . $recentlyUsed["id"] . "&backurl=" . urlencode(get_current_url());

			$records .= fillTemplate($design->getRecords(), $recentlyUsed);
		}

		if ( $records != '' ) {
			$ret .= fillTemplate( $design->getContent(), array("records" => $records) );
		}

		// add footer
		$ret .= $design->getFooter();

		return $ret;
	}

?>