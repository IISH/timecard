<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

// sync Timecard Protime
syncTimecardProtimeMonth($oWebuser->getTimecardId(), $oWebuser->getProtimeId(), $oDate);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('timecard.month'));
$oPage->setTitle('Timecard | Month');
$oPage->setContent(createMonthContent( $date ));

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createMonthContent( $date ) {
	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getMonthRibbon();

	//
	$ret .= getUserMonth( $date );

	return $ret;
}

	// TODOEXPLAIN
	function getUserMonth( $date ) {
		global $settings, $dbhandleTimecard, $oWebuser, $oDate;

		$ret = '';

		require_once("./classes/class_db.inc.php");
		require_once("./classes/class_view/class_view.inc.php");

		require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
		require_once("./classes/class_view/fieldtypes/class_field_time.inc.php");
		require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");

		$oDb = new class_db($settings, 'timecard');
		$oView = new class_view($settings, $oDb);

		// if legacy, then no edit link
		if ( class_datetime::is_legacy( $oDate ) ) {
			$add_new_url = '';
		} else {
			$add_new_url = "edit.php?ID=0&d=" . $oDate->get("Ymd") . "&backurl=[BACKURL]";
		}

		$oView->set_view( array(
			'query' => 'SELECT * FROM vw_hours2011_user WHERE Employee=' . $oWebuser->getTimecardId() . ' AND DateWorked LIKE \'' . $oDate->get("Y-m") . '-%\' '
			, 'count_source_type' => 'query'
			, 'order_by' => 'DateWorked, Description, TimeInMinutes DESC '
			, 'anchor_field' => 'ID'
			, 'viewfilter' => true
			, 'calculate_total' => array('nrofcols' => 5, 'totalcol' => 4, 'field' => 'TimeInMinutes')
			, 'add_new_url' => $add_new_url
			, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
			, 'extra_hidden_viewfilter_fields' => '<input type="hidden" name="d" value="' . $date["Ymd"] . '">'
			));

		$oView->add_field( new class_field_date ( array(
			'fieldname' => 'DateWorked'
			, 'fieldlabel' => 'Date'
			, 'format' => 'D j F'
			, 'nobr' => true
			, 'href' => 'day.php?d=[FLD:yyyymmdd]&backurl=[BACKURL]&backurllabel=Month'
			, 'href_alttitle' => 'Go to day'
			)));

		// if legacy, then no edit link
		$href = '';
		if ( !class_datetime::is_legacy( $oDate ) ) {
			$href = 'edit.php?ID=[FLD:ID]&d=' . $oDate->get("Ymd") . '&backurl=[BACKURL]';
		}

		$oView->add_field( new class_field_string ( array(
			'fieldname' => 'Description'
			, 'fieldlabel' => 'Project'
			, 'href' => $href
			, 'href_alttitle' => 'Edit hours'
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
			, 'view_max_length' => 35
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
					, "showelsevalue" => "<a alt=\"Imported from Protime\" title=\"Imported from Protime\" class=\"PT\">(PT)</a>"
				)
			)));

		// calculate and show view
		// QUICK AND DIRTY CONCAT/REPLACE
		$list = $oView->generate_view() . "___";

		$oEmployee = new class_employee( $oWebuser->getTimecardId(), $settings );
		$protime_month_total = $oEmployee->getProtimeMonthTotal($date, 'max');

		// show protime month total
		$ptime = "<tr><td><b>Total (protime):&nbsp;</td><td></td><td></td><td><b>" . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $protime_month_total ) . "</b></td></tr>";

		// QUICK AND DIRTY CONCAT/REPLACE
		$list = str_ireplace("</table>___", $ptime . "</table>", $list);

		$ret .= $list;

		return $ret;
	}

?>