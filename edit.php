<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$date = class_datetime::get_date($protect);
$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('timecard.day'));
$oPage->setTitle('Timecard | Day (edit)');

if ( $oDate->get("Y-m-d") < $oWebuser->getAllowAdditionsStartingDate() ) {
	$oPage->setContent( '<div class="youcannot">' . Settings::get('error_cannot_modify_legacy_contact_fa') . ' (error: 256985)</div>' );
} elseif ( class_datetime::is_future( $oDate ) ) {
	$oPage->setContent( '<div class="youcannot">' . Settings::get('error_cannot_add_in_the_future') . '</div>' );
} else {
	$oPage->setContent(createDayEditContent( $date ));
}

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createDayEditContent( $date ) {
	global $protect;

	//
	$shortcutTemplate = $protect->request_positive_number_or_empty('get', 'template');
	$oShortcutTemplate = new class_shortcut( $shortcutTemplate );

	// get design
	$design = new class_contentdesign("page_edit");

	// add header
	$ret = $design->getHeader();

	//
	$ret .= getUserDayEdit( $date, $oShortcutTemplate );

	// AUTO SAVE
	if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
		if ( $oShortcutTemplate->getOnNewAutoSave() == '1' ) {
			if ( $protect->request_positive_number_or_empty('get', "ID") == '' || $protect->request_positive_number_or_empty('get', "ID") == '0' ) {
				$ret .= "
<script type=\"text/javascript\">
<!--
doc_submit('saveclose')
// -->
</script>
";
			}
		}
	}

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}

	function getUserDayEdit( $date, $oShortcutTemplate ) {
		global $settings, $oWebuser, $oDate, $protect, $databases, $dbConn;

		// get 'on new' project id from shortcut template
		$onNew["project"] = $oShortcutTemplate->getWorkCode();
		// if no 'on new' project id, try to get it from url
		if ( $onNew["project"] == 0 ) {
			$onNew["project"] = $protect->request_positive_number_or_empty('get', "p");
		}

		// get 'on new' time from shortcut template
		$onNew["time"] = $oShortcutTemplate->getTimeInMinutes();

		// achterhaal hoeveel op de betreffende dag is gewerkt
		// bereken hoeveel minuten er nog 'over' zijn
		// Bij dagen in de toekomst kan niet uitgerekend worden hoeveel men op die dagen zal werken ;-) dus bij deze dagen wordt geen rekening gehouden met hoeveel minuten er nog over.
		$oEmployee = new class_employee( $oWebuser->getTimecardId(), $settings );
		$protime_day_total = $oEmployee->getProtimeDayTotal($date);

		if ( $protime_day_total > 0 ) {
			$vandaagGewerkt = advancedSingleRecordSelectMysql('default', "Workhours", "AANTAL", "Employee=" . $oWebuser->getTimecardId() . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%'" , "SUM(TimeInMinutes) AS AANTAL");
			if ( $vandaagGewerkt["aantal"] == '' ) {
				$vandaagGewerkt["aantal"] = 0;
			}
			$over = $protime_day_total - $vandaagGewerkt["aantal"];
			if ( $over < 0 ) {
				$over = 0;
			}

			if ( $onNew["time"] > $over ) {
				$onNew["time"] = $over;
			} else {
				if ( $onNew["time"] > (7*60) ) {
					$onNew["time"] = $over;
				}
			}
		}

		$id = $protect->request_positive_number_or_empty('get', "ID");
		if ( $id == '' ) {
			$id = 0;
		}
		$oWh = new class_workhours( $id );

		require_once("./classes/class_form/workhours_class_form.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_date.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_list.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_remark.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_time_double_field.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_time_single_field.inc.php");
		require_once("./classes/class_form/fieldtypes/class_field_time_free_input_field.inc.php");

		// 
		$oForm = new workhours_class_form($settings, $dbConn);

		$oForm->set_form( array(
			'query' => 'SELECT * FROM Workhours WHERE ID=[FLD:ID] AND Employee=' . $oWebuser->getTimecardId() . ' AND protime_absence_recnr=0 '
			, 'table' => 'Workhours'
			, 'primarykey' => 'ID'
			));

		// required !!!
		$oForm->add_field( new class_field_hidden ( array(
			'fieldname' => 'ID'
			, 'fieldlabel' => 'Internal no.'
			)));

		$oForm->add_field( new class_field_hidden ( array(
			'fieldname' => 'Employee'
			, 'fieldlabel' => 'Employee'
			, 'onNew' => $oWebuser->getTimecardId()
			)));

		$oForm->add_field( new class_field_date ( array(
			'fieldname' => 'DateWorked'
			, 'fieldlabel' => 'Date'
			, 'required' => 1
			, 'size' => 10
			, 'onNew' => $date["y"] . "-" . $date["m"] . "-" . $date["d"]
			, 'readonly' => 1
			)));

		// 
		if ( $protect->request_positive_number_or_empty('get', "ID") == '' || $protect->request_positive_number_or_empty('get', "ID") == '0' ) {
			$currentValueOnNew = '';
		} else {
			$currentValueOnNew = ' OR ID=[CURRENTVALUE] ';
		}

		if ( $oWebuser->getSortProjectsOnName() == 1 ) {
			$projectQuery = 'SELECT ID, Concat(Description, IFNULL( CONCAT(\' (\', Projectnummer, \')\'), \'\') ) AS ProjectNumberName FROM Workcodes WHERE ( isdisabled = 0 AND (lastdate IS NULL OR lastdate = \'\' OR lastdate >= \'' . $oDate->get("Y-m-d") . '\') ) ' . $currentValueOnNew . ' ORDER BY Description, Projectnummer ';
		} else {
			$projectQuery = 'SELECT ID, Concat(IFNULL(Projectnummer,\'\'), \' \', Description) AS ProjectNumberName FROM Workcodes WHERE ( isdisabled = 0 AND (lastdate IS NULL OR lastdate = \'\' OR lastdate >= \'' . $oDate->get("Y-m-d") . '\') ) ' . $currentValueOnNew . ' ORDER BY Projectnummer, Description ';
		}
		$oForm->add_field( new class_field_list ( $settings, array(
			'fieldname' => 'WorkCode'
			, 'fieldlabel' => 'Project'
			, 'query' => $projectQuery
			, 'id_field' => 'ID'
			, 'description_field' => 'ProjectNumberName'
			, 'empty_value' => '0'
			, 'required' => 1
			, 'show_empty_row' => true
			, 'onNew' => $onNew["project"]
			)));

		// single select field, double select field or free input field
		if ( $oWebuser->getHoursdoublefield() == 2 ) {

			$oForm->add_field( new class_field_time_free_input_field ( array(
				'fieldname' => 'TimeInMinutes'
				, 'fieldlabel' => 'Time (h:mm)'
				, 'required' => 0
				, 'onNew' => $onNew["time"]
				, 'placeholder' => '0:00'
				, 'style' => 'width:60px;'
			)));

		} elseif ( $oWebuser->getHoursdoublefield() == 1 ) {

			$oForm->add_field( new class_field_time_double_field ( array(
				'fieldname' => 'TimeInMinutes'
				, 'fieldlabel' => 'Time (h:mm)'
				, 'required' => 0
				, 'possible_hour_values' => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10")
				, 'possible_minute_values' => array("00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55")
				, 'onNew' => $onNew["time"]
				)));

		} else {

			$oForm->add_field( new class_field_time_single_field ( array(
				'fieldname' => 'TimeInMinutes'
				, 'fieldlabel' => 'Time (h:mm)'
				, 'required' => 0
				, 'possible_hour_values' => array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10")
				, 'possible_minute_values' => array("00", "05", "10", "15", "20", "25", "30", "35", "40", "45", "50", "55")
				, 'onNew' => $onNew["time"]
				)));

		}

		if ( $oWh->getDailyAutomaticAdditionId() > 0 ) {
			$oForm->add_field( new class_field_bit ( array(
				'fieldname' => 'fixed_time'
				, 'fieldlabel' => 'Fixed time?'
				, 'onNew' => '0'
				)));
		}

		if ( $oWebuser->getShowJiraField() ) {
			$oForm->add_field( new class_field_string ( array(
				'fieldname' => 'jira_issue_nr'
				, 'fieldlabel' => 'JIRA issue #'
				, 'style' => 'width:425px;'
				)));
		}

		$oForm->add_field( new class_field_textarea ( array(
			'fieldname' => 'WorkDescription'
			, 'fieldlabel' => 'Description'
			, 'class' => 'resizable'
			, 'style' => 'width:425px;height:70px;'
			, 'onNew' => $oShortcutTemplate->getWorkDescription()
			)));

		$oForm->add_field( new class_field_hidden ( array(
			'fieldname' => 'isdeleted'
			, 'fieldlabel' => 'Delete?'
			, 'onNew' => '0'
			)));

		if ( $id == 0 && $oShortcutTemplate->getId() > 0 && $oShortcutTemplate->getExtraExplanation() != '' ) {
			$oForm->add_field( new class_field_remark ( array(
				'onNew' => '<i>' . $oShortcutTemplate->getExtraExplanation() . '</i>'
				, 'fieldlabel' => 'Explanation'
				)));
		}

		// generate form
		$retval = $oForm->generate_form();

		return $retval;
	}
