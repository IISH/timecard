<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

$oEmployee = new class_employee($protect->request('get', 'eid'), $connection_settings);

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('administrator.quarter'));
$oPage->setTitle('Timecard | Admin Quarter');
$oPage->setContent(createAdminQuarterContent( $date ));

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createAdminQuarterContent( $date ) {
	global $connection_settings;

	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getQuarterRibbon();

	//
	$ret .= getEmployeesRibbon($date["y"], 1);

	//
	$ret .= getAdminQuarter( $date );

	return $ret;
}

	// TODOEXPLAIN
	function getAdminQuarter( $date ) {
		global $connection_settings, $oEmployee, $oDate;

		if ( $oEmployee->getTimecardId() != '' ) {
			require_once("./classes/class_db.inc.php");
			require_once("./classes/class_view/class_view.inc.php");

			require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
			require_once("./classes/class_view/fieldtypes/class_field_time.inc.php");
			require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");

			$oDb = new class_db($connection_settings, 'timecard');
			$oView = new class_view($connection_settings, $oDb);

			$oPrevNext = new class_prevnext($date);
			$extra_month_criterium = $oPrevNext->getExtraMonthCriterium();

			if ( $oEmployee->getTimecardId() == -1 ) {
				$tmp_query = 'SELECT * FROM vw_hours2011_admin WHERE Year(DateWorked)=' . $date["y"] . $extra_month_criterium . ' AND isdeleted=0 ';
				$nrofcols = 7;
				$totalcol = 5;
			} else {
				$tmp_query = 'SELECT * FROM vw_hours2011_admin WHERE Employee=' . $oEmployee->getTimecardId() . ' AND Year(DateWorked)=' . $date["y"] . $extra_month_criterium . ' AND isdeleted=0 ';
//				$nrofcols = 6;
//				$totalcol = 4;
				$nrofcols = 7;
				$totalcol = 5;
			}

			// if legacy, then no edit link
			$add_new_url = '';
			if ( !class_datetime::is_legacy( $oDate ) ) {
				$add_new_url = "admin_edit.php?ID=0&d=" . $date["y"] . substr("0" . $date["m"], -2) . substr("0" . $date["d"], -2) . "&eid=" . $oEmployee->getTimecardId() . "&backurl=[BACKURL]";
			}

			$oView->set_view( array(
				'query' => $tmp_query
				, 'count_source_type' => 'query'
				, 'order_by' => 'DateWorked, Description, TimeInMinutes DESC '
				, 'anchor_field' => 'ID'
				, 'viewfilter' => true
				, 'calculate_total' => array('nrofcols' => $nrofcols, 'totalcol' => $totalcol, 'field' => 'TimeInMinutes')
				, 'add_new_url' => $add_new_url
				, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
				, 'extra_hidden_viewfilter_fields' => '<input type="hidden" name="d" value="' . $date["Ymd"] . '"><input type="hidden" name="eid" value="' . $oEmployee->getTimecardId() . '">'
				));

			$oView->add_field( new class_field_date ( array(
				'fieldname' => 'DateWorked'
				, 'fieldlabel' => 'Date (m/d)'
				, 'format' => 'D j M'
				, 'nobr' => true
				, 'href' => 'admin_day.php?eid=[FLD:Employee]&d=[FLD:yyyymmdd]&backurl=[BACKURL]&backurllabel=Quarter+(all empl.)'
				, 'href_alttitle' => 'Go to day'
				)));

			if ( $oEmployee->getTimecardId() == -1 ) {
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
			} else {
				$oView->add_field( new class_field_string ( array(
					'fieldname' => 'LongCode'
					, 'fieldlabel' => 'Employee'
					)));
			}

			// if legacy, then no edit link
			$href = '';
			if ( !class_datetime::is_legacy( $oDate ) ) {
				$href = 'admin_edit.php?ID=[FLD:ID]&d=' . $date["y"] . substr("0" . $date["m"], -2) . substr("0" . $date["d"], -2) . '&backurl=[BACKURL]';
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
						, "showelsevalue" => "<a alt=\"Imported from Protime\" title=\"Imported from Protime\" class=\"PT\">(PT)</a>"
					)
				)));

			// calculate and show view
			$ret .= $oView->generate_view();
		}

		return $ret;
	}

?>