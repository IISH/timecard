<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasDepartmentAuthorisation() && count( $oWebuser->getDepartmentHeadExtraRightsOnDepartments() ) == 0  && count( $oWebuser->getDepartmentHeadExtraRightsOnUsers() ) == 0 ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

$oEmployee = new class_employee($protect->request('get', 'eid'), $settings);

// create webpage
$oPage = new class_page('design/page_admin.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('department.quarter'));
$oPage->setTitle('Timecard | Department Quarter');
$oPage->setContent(createAdminQuarterContent( $date ));
$oPage->setLeftMenu( getDepartmentEmployeesRibbon( $oEmployee, $date["y"] ) );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createAdminQuarterContent( $date ) {
	//
	$oPrevNext = new class_prevnext($date);
	$ret = $oPrevNext->getQuarterRibbon();

	//
	$ret .= getAdminQuarter( $date );

	return $ret;
}
	function getAdminQuarter( $date ) {
		global $settings, $oEmployee, $oDate, $databases, $dbConn;

		if ( $oEmployee->getTimecardId() != '' ) {
			require_once("./classes/class_view/class_view.inc.php");
			require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
			require_once("./classes/class_view/fieldtypes/class_field_time.inc.php");
			require_once("./classes/class_view/fieldtypes/class_field_date.inc.php");

			$oView = new class_view($settings, $dbConn);

			$oPrevNext = new class_prevnext($date);
			$extra_month_criterium = $oPrevNext->getExtraMonthCriterium();

			if ( $oEmployee->getTimecardId() == -1 ) {
				$tmp_query = 'SELECT * FROM vw_hours_admin WHERE DateWorked LIKE \'' . $oDate->get("Y") . '-%\' ' . $extra_month_criterium . ' ';
			} else {
				$tmp_query = 'SELECT * FROM vw_hours_admin WHERE Employee=' . $oEmployee->getTimecardId() . ' AND DateWorked LIKE \'' . $oDate->get("Y") . '-%\' ' . $extra_month_criterium . ' ';
			}

			$oView->set_view( array(
				'query' => $tmp_query
				, 'count_source_type' => 'query'
				, 'order_by' => 'DateWorked, Description, TimeInMinutes DESC '
				, 'anchor_field' => 'ID'
				, 'viewfilter' => true
				, 'calculate_total' => array('nrofcols' => 7, 'totalcol' => 5, 'field' => 'TimeInMinutes')
				, 'table_parameters' => ' cellspacing="0" cellpadding="0" border="0" '
				, 'extra_hidden_viewfilter_fields' => '<input type="hidden" name="d" value="' . $date["Ymd"] . '"><input type="hidden" name="eid" value="' . $oEmployee->getTimecardId() . '">'
				));

			$oView->add_field( new class_field_date ( array(
				'fieldname' => 'DateWorked'
				, 'fieldlabel' => 'Date'
				, 'format' => 'D j F'
				, 'nobr' => true
				, 'href' => 'department_day.php?eid=[FLD:Employee]&d=[FLD:yyyymmdd]&backurl=[BACKURL]&backurllabel=Quarter+(all empl.)'
				)));

			if ( $oEmployee->getTimecardId() == -1 ) {
				$oView->add_field( new class_field_string ( array(
					'fieldname' => 'LongCodeKnaw'
					, 'fieldlabel' => 'Employee'
					, 'viewfilter' => array(
										'labelfilterseparator' => '<br>'
										, 'filter' => array (
															array (
																'fieldname' => 'LongCodeKnaw'
																, 'type' => 'string'
																, 'size' => 10
															)
														)
										)
					, 'nobr' => true
					)));
			} else {
				$oView->add_field( new class_field_string ( array(
					'fieldname' => 'LongCodeKnaw'
					, 'fieldlabel' => 'Employee'
					)));
			}

			// if legacy, then no edit link
			$href = '';
			if (  $oDate->get("Y-m-d") >= $oEmployee->getAllowAdditionsStartingDate() ) {
				$href = 'department_edit.php?ID=[FLD:ID]&d=' . $oDate->get("Ymd") . '&backurl=[BACKURL]';
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

			$oView->add_field( new class_field_string ( array(
				'fieldname' => 'daily_automatic_addition_id'
				, 'fieldlabel' => ''
				, 'show_different_value' => array(
					"value" => ""
					, "showvalue" => ""
					, "showelsevalue" => "<a title=\"Daily automatic addition\" class=\"PT\">(DAA)</a>"
					)
				)));

			// generate view
			$ret = $oView->generate_view();
		}

		return $ret;
	}

?>
