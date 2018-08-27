<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_employee {
	private $dbConn;
	private $timecard_id = 0;
	private $protime_id = 0;
	private $hoursdoublefield = -1;
	private $isdisabled = 0;
	private $lastname = '';
	private $firstname = '';
	private $loginname = '';
	private $hoursperweek = 0;
	private $daysperweek = 0;
	private $authorisation = array();
	private $show_jira_field = false;
	private $allow_additions_starting_date = '';
	private $projects = array();
	private $sortProjectsOnName = -1;
	private $extra_rights_on_departments = array();
	private $extra_rights_on_users = array();

	function __construct($timecard_id, $settings) {
		global $dbConn;

		if ( $timecard_id == '' || $timecard_id < -1 ) {
			$timecard_id = 0;
		}

		$this->timecard_id = $timecard_id;

		$this->dbConn = $dbConn;

		if ( $timecard_id > 0 ) {
			$this->getTimecardValues();
		}
	}

	function getTimecardValues() {
		//
		$query_project = "SELECT * FROM vw_Employees WHERE ID=" . $this->timecard_id;
		$stmt = $this->dbConn->prepare($query_project);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row_project) {

			$this->isdisabled = $row_project["isdisabled"];
			$this->protime_id = $row_project["ProtimePersNr"];
			$this->lastname = $row_project["NAME"];
			$this->firstname = $row_project["FIRSTNAME"];
			$this->loginname = $row_project["LongCode"];
			$this->hoursperweek = $row_project["hoursperweek"];
			$this->daysperweek = $row_project["daysperweek"];
			$this->allow_additions_starting_date = $row_project["allow_additions_starting_date"];

			if ( $row_project["show_jira_field"] == 1 ) {
				$this->show_jira_field = true;
			} else {
				$this->show_jira_field = false;
			}

			$this->hoursdoublefield = $row_project["HoursDoubleField"];

			// calculate DepartmentHead Extra Rights On Departments & Users
			$this->calcDepartmentHeadExtraRightsOnDepartments();
			$this->calcDepartmentHeadExtraRightsOnUsers();

			$this->sortProjectsOnName = $row_project["sort_projects_on_name"];
			if ( !in_array($this->sortProjectsOnName, array(0, 1)) ) {
				$this->sortProjectsOnName = -1;
			}

			// AUTHORISATION
			$queryAuthorisation = "SELECT * FROM Employee_Authorisation WHERE EmployeeID=" . $this->timecard_id;
			$stmt = $this->dbConn->prepare($queryAuthorisation);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $rowAuthorisation) {

				$this->authorisation[] = $rowAuthorisation["authorisation"];
			}

			// PROJECTS
			$queryProjects = "SELECT * FROM Workcodes WHERE projectleader=" . $this->timecard_id . " ORDER BY Description";
			$stmt = $this->dbConn->prepare($queryProjects);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $rowProjects) {
				$this->projects[] = $rowProjects["ID"];
			}
		}
	}

	function calcDepartmentHeadExtraRightsOnDepartments() {
		global $dbConn;

		if ( $this->protime_id != 0 ) {
			$query = "SELECT * FROM DepartmentHead_Extra_Rights_on_Departments WHERE PERSNR_Head=" . $this->protime_id;
			$stmt = $dbConn->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$this->extra_rights_on_departments[] = $row["DEPART"];
			}
		}
	}

	function calcDepartmentHeadExtraRightsOnUsers() {
		global $dbConn;

		if ( $this->protime_id != 0 ) {
			$query = "SELECT * FROM DepartmentHead_Extra_Rights_on_Users WHERE PERSNR_Head=" . $this->protime_id;
			$stmt = $dbConn->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$this->extra_rights_on_users[] = $row["PERSNR_Employee"];
			}
		}
	}

	function getAuthorisation() {
		return $this->authorisation;
	}

	function getAllowAdditionsStartingDate() {
		return $this->allow_additions_starting_date ;
	}

	function hasAdminAuthorisation() {
		return ( in_array( 'admin', $this->authorisation ) ) ? true : false ;
	}

	function isProjectLeader() {
		return ( count($this->projects) > 0 );
	}

	function hasFaAuthorisation() {
		return ( in_array( 'fa', $this->authorisation ) ) ? true : false ;
	}

	function hasDepartmentAuthorisation() {
		return ( in_array( 'department', $this->authorisation ) ) ? true : false ;
	}

	function hasReportsAuthorisation() {
		return ( in_array( 'reports', $this->authorisation ) ) ? true : false ;
	}

	function isDisabled() {
		return $this->isdisabled;
	}

	public function getId() {
		return $this->getTimecardId();
	}

	public function getTimecardId() {
		return $this->timecard_id;
	}

	public function getProtimeId() {
		return $this->protime_id;
	}

	public function getLoginName() {
		return $this->loginname;
	}

	function isLoggedIn() {
		$ret = false;

		if ( $this->timecard_id > 0 ) {
			$ret = true;
		}

		return $ret;
	}

	function getShowJiraField() {
		return $this->show_jira_field;
	}

	function checkLoggedIn( $subdir = '' ) {
		global $protect;

		if ( $this->timecard_id == 0 ) {
			Header("Location: " . $subdir . "login.php?burl=" . URLencode($protect->getShortUrl()));
			die("go to <a href=\"" . $subdir . "login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		} else {
			$this->ifDisabledGoToLogout();
		}
	}

	function ifDisabledGoToLogout() {
		if ( $this->isDisabled() ) {
			Header("Location: logout.php?m=disabled");
			die('go to: <a href="logout.php?m=disabled">logout</a>');
		}
	}

	function getHoursdoublefield() {
		return $this->hoursdoublefield;
	}

	function getSortProjectsOnName() {
		return $this->sortProjectsOnName;
	}

	function getLastname() {
		return trim($this->lastname);
	}

	function getLastFirstname() {
		return trim($this->lastname) . ', ' . trim($this->firstname);
	}

	function getFirstLastname() {
		return trim($this->firstname . ' ' . verplaatsTussenvoegselNaarBegin($this->lastname));
	}

	function getFirstname() {
		return trim($this->firstname);
	}

	function getCheckInOut( $yyyymmdd = '' ) {
		global $dbConn;

		$retval = '';

		if ( $yyyymmdd == '' ) {
			$yyyymmdd = date("Ymd");
		}

		$query = "SELECT REC_NR, PERSNR, BOOKDATE, BOOKTIME FROM protime_bookings WHERE PERSNR=" . $this->getProtimeId() . " AND BOOKDATE='" . $yyyymmdd . "' AND BOOKTIME<>9999 ORDER BY BOOKTIME ";

		$status = 0;
		$found = 0;
		$template = "::IN::-::OUT::";
		$inout = $template;
		$separator = '';

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$status++;
			$found++;

			if ( $status == 1 ) {
				$inout = $separator . $template;
				$inout = str_replace('::IN::', class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]), $inout);
			} else {
				$inout = str_replace('::OUT::', class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($row["BOOKTIME"]), $inout);
				$retval .= $inout;
				$inout = '';
				$separator = ', ';
			}

			$status = $status % 2;
		}

		if ( $inout != '' ) {
			$inout = str_replace('::IN::', '...', $inout);
			$inout = str_replace('::OUT::', '...', $inout);
			$retval .= $inout;
		}


		return $retval;
	}

	function getOvertimeInMinutes( $yyyy_mm = '' ) {
		global $settings, $oWebuser;

		$ret = 0;

		if ( $yyyy_mm == '' ) {
			$yyyy_mm = date("Ym");
		}


			$oDate = new class_date( date('Y'), date('m'), 1 );

			$ret .= "<tr><td><strong><font size=\"-2\">Day</font></strong></td><td><strong><font size=\"-2\">Timecard</font></strong></td><td><strong><font size=\"-2\">Protime</font></strong></td><td><font size=\"-2\"><strong>Overtime</strong></font></td></tr>";

			$number_of_days_in_current_month = $oDate->get('t');

//			$oEmployee = new class_employee( $oWebuser->getTimecardId(), $settings );

			$date2["y"] = $oDate->get('Y');
			$date2["m"] = $oDate->get('n');
			$date2["d"] = 1;
			$timecard_day_totals = $this->getTimecardDayTotals( $oDate->get('Y'), $oDate->get('n') );

			$dagvakantie2 = $this->getEerderNaarHuisDayTotals( $oDate->get('Y'), $oDate->get('n') );
			$protime_day_totals = $this->getProtimeDayTotals( $oDate->get('Ym') );
			$protime_day_overtimes = $this->getProtimeDayOvertimes( $oDate->get('Ym') );

			$total_overtime = 0;
			for ( $i = 1; $i <= $number_of_days_in_current_month; $i++ ) {
				$date2["d"] = $i;

				$timecard_day_total = 0;
				if ( isset($timecard_day_totals[$i]) ) {
					$timecard_day_total += $timecard_day_totals[$i];
				}
				if ( isset($dagvakantie2[$i]) ) {
					$timecard_day_total += $dagvakantie2[$i];
				}

				$protime_day_total = 0;
				if ( isset($protime_day_totals[$i]) ) {
					$protime_day_total = $protime_day_totals[$i];
				}

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

				// OVERTIME +/-
				$sign = '';
				if ( $oDate->get('Ym') . substr('0'.$i,-2) < date("Ymd")) {
					$extra = $protime_day_overtimes[$i];
				} else {
					$extra = 0;
				}
				$total_overtime += $extra;

				if ( $extra > 0 ) {
					$sign = '+';
				}

				if ( $extra == 0 ) {
					$extra_nice = '&nbsp;';
				} else {
					$extra_nice =  $sign . class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes( $extra );
				}


				$oCurrentDay = new class_date( $date2["y"], $date2["m"], $i );
				$weekday = $oCurrentDay->get('D j');
				$ret .= "<tr><td><a href=\"\">$weekday</a></td><td>$timecard_day_total_nice</td><td>$protime_day_total_nice</td></tr>";
			}

			$ret = $total_overtime;


		return $ret;
	}

	function getVacationInMinutes() {
		$retval = 0;

		if ( $this->getProtimeId() != '0' ) {

			$vakantie = advancedSingleRecordSelectMysql(
				'default'
				, "protime_p_limit"
				, array("BEGIN_VAL", "END_VAL", "BOOKDATE")
				, "PERSNR=" . $this->getProtimeId() . " AND YEARCOUNTER=1 AND LIM_PERIODE = 6 "
				, '*'
				, "BOOKDATE DESC"
			);

			$end_val = $vakantie["end_val"];
			if ( $end_val != '' ) {
				$retval = $end_val;
			} else {
				$retval = 0;
			}

		}

		return $retval;
	}

	function calculateVacationHoursUntilToday() {
		$vac = 0;
		$overtime = 0;

		$vac = $this->getVacationInMinutes();
		$overtime = $this->getOvertimeInMinutes();

		$holidayFormatted = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($vac + $overtime);
		$overtimeFormatted = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($overtime);

		$retval = $holidayFormatted . ' hours <i>(including ' . $overtimeFormatted . ' hours overtime this month)</i>';

		return $retval;
	}

	function findTimecardIdUsingProtimeId($protime_id) {
		$val = 0;

		if ( $protime_id > 0 ) {

			// search in protime database
			$record = advancedSingleRecordSelectMysql(
					'default'
					, "Employees"
					, array("ID")
					, "ProtimePersNr=" . $protime_id . " "
					, '*'
					, " ID DESC "
				);

			$val = $record["id"];

			if ( $val == '' ) {
				$val = 0;
			}
		}

		$this->timecard_id = $val;

		// get timecard values
		if ( $val > 0 ) {
			$this->getTimecardValues();
		}
	}

	function getProtimeMonthTotal($date) {
		$retval = 0;

		$arr = $this->getProtimeDayTotalGroupedByDay($date);
		foreach ( $arr as $date => $minutes  ) {
			$retval += $minutes;
		}

		return $retval;
	}

	function getProtimeDayTotalGroupedByDay($date) {
		global $dbConn;

		$ret = array();

		$protime_id = $this->getProtimeId();

		// reset values
		$query = "SELECT SUBSTR(BOOKDATE, 1, 10) AS BOOKDATUM, WEEKPRES1, EXTRA
FROM protime_pr_month
WHERE PERSNR=" . $protime_id . " AND BOOKDATE LIKE '" . $date["y"] . str_pad( $date["m"], 2, '0', STR_PAD_LEFT) . "%'
GROUP BY SUBSTR(BOOKDATE, 1, 10), WEEKPRES1, EXTRA
";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {

			$protime_day_total = $row['WEEKPRES1'];
			$protime_day_total_extra = $row['EXTRA'];
			if ( $protime_day_total == '' ) {
				$protime_day_total = 0;
			}
			if ( $protime_day_total_extra == '' ) {
				$protime_day_total_extra = 0;
			}
			if ( $protime_day_total_extra < 0 ) {
				$protime_day_total += -($protime_day_total_extra);
			}

			$ret[$row["BOOKDATUM"]] = $protime_day_total;
		}

		return $ret;
	}

	function getProtimeDayTotal($date) {
		$protime_id = $this->getProtimeId();

		$arr = $this->getProtimeDayTotalPart($protime_id, $date, array('weekpres1', 'extra'));
		$protime_day_total = $arr['weekpres1'];
		$protime_day_total_extra = $arr['extra'];
		if ( $protime_day_total == '' ) {
			$protime_day_total = 0;
		}
		if ( $protime_day_total_extra == '' ) {
			$protime_day_total_extra = 0;
		}
		if ( $protime_day_total_extra < 0 ) {
			$protime_day_total += -($protime_day_total_extra);
		}

		return $protime_day_total;
	}

	function getProtimeDayTotalPart($protime_id, $date, $fields) {
		$retval = array();

		if ( $protime_id != '' && $protime_id != '0' ) {

			$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

			// 
			$hours = advancedSingleRecordSelectMysql(
					'default'
					, "protime_pr_month"
					, array("PERSNR", "BOOKDATE", "PREST", "RPREST", "WEEKPRES1", "EXTRA")
					, "PERSNR=" . $protime_id . " AND BOOKDATE='" . $oDate->get("Ymd") . "' "
				);

			// returneer opgegeven velden
			foreach ( $fields as $field ) {
				$retval[$field] = ( isset($hours[$field]) ? $hours[$field] : '' );
			}
		}

		return $retval;
	}

	function getHoursPerWeek3($year) {
		$oHoursPerWeek = new class_employee_hours_per_week($this, $year);
		if ( date(Settings::get("timeStampRefreshLowPriority")) > $oHoursPerWeek->getLastRefresh() ) {
			$oHoursPerWeek->refresh();
		}

		return $oHoursPerWeek;
	}

	function getAmountOfNotPlannedVacationInMinutes( $year ) {
		global $dbConn;

		$ret = 0;

		if ( $this->getProtimeId() != '0' ) {

			$vakantie = advancedSingleRecordSelectMysql(
				'default'
				, "protime_p_limit"
				, array("BEGIN_VAL", "END_VAL", "BOOKDATE")
				, "PERSNR=" . $this->getProtimeId() . " AND YEARCOUNTER=1 "
				, '*'
				, "BOOKDATE DESC"
			);

			$ret = $vakantie["end_val"];

			if ( ret == '' ) {
				$ret = 0;
			}

			$query = "SELECT SUM(ABSENCE_VALUE) AS SOM FROM protime_p_absence  WHERE PERSNR=" . $this->getProtimeId() . " AND ABSENCE IN ( 12 ) AND BOOKDATE LIKE '$year%' AND BOOKDATE > '{$vakantie["bookdate"]}' ";
			$stmt = $dbConn->prepare($query);
			$stmt->execute();
			if ( $row = $stmt->fetch() ) {
				$ret -= $row["SOM"];
			}
		}

		return $ret;
	}

	public function getAmountOfNotPlannedVacationInHours( $year ) {
		return $this->getAmountOfNotPlannedVacationInMinutes( $year )/60.0;
	}

	function getVacationHours() {
		$ret = array();

		if ( $this->getProtimeId() != '0' ) {

			$vakantie = advancedSingleRecordSelectMysql(
						'default'
						, "protime_p_limit"
						, array("BEGIN_VAL", "END_VAL", "BOOKDATE")
						, "PERSNR=" . $this->getProtimeId() . " AND YEARCOUNTER=1 "
						, '*'
						, "BOOKDATE DESC"
					);

			$end_val = $vakantie["end_val"];
			if ( $end_val != '' ) {
				$ret["value"] = $end_val/60.0;
				$ret["bookdate"] = $vakantie["bookdate"];
			} else {
				$ret["value"] = 0;
				$ret["bookdate"] = '';
			}

		}

		return $ret;
	}

	function getFavourites( $type ) {
		global $dbConn;

		$ids = array();
		$ids[] = '0';

		$query = "SELECT * FROM EmployeeFavourites WHERE TimecardID=" . $this->getTimecardId() . ' AND type=\'' . $type . '\' ';

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ids[] = $row["ProtimeID"];
		}

		return $ids;
	}

	function getTimecardDayTotals( $year, $month ) {
		global $dbConn;

		$ret = array();

		$query = 'SELECT *, DAY(DateWorked) AS CURRENTDAY FROM vw_hours_user WHERE Employee=' . $this->getTimecardId() . ' AND DateWorked LIKE \'' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-%\' AND protime_absence_recnr>=0 ORDER BY DateWorked ';
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			if ( !isset( $ret[ $row["CURRENTDAY"] ] ) ) {
				$ret[ $row["CURRENTDAY"]+0 ] = 0;
			}
			$ret[ $row["CURRENTDAY"]+0 ] += $row["TimeInMinutes"];
		}

		return $ret;
	}


	function getEerderNaarHuisDayTotals( $year, $month ) {
		global $dbConn;

		$ret = array();

		// achterhaal 
		$query = "SELECT TimeInMinutes, DAY(DateWorked) AS CURRENTDAY FROM Workhours WHERE Employee=" . $this->getTimecardId() . " AND DateWorked LIKE '" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%' AND protime_absence_recnr=-1 ORDER BY DateWorked ";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			if ( !isset( $ret[ $row["CURRENTDAY"] ] ) ) {
				$ret[ $row["CURRENTDAY"]+0 ] = 0;
			}
			$ret[ $row["CURRENTDAY"]+0 ] = $row["TimeInMinutes"];
		}

		return $ret;
	}

	function getProtimeDayTotals($yyyymm) {
		$protime_id = $this->getProtimeId();

		$protime_day_total = $this->getProtimeDayTotalsPart($protime_id, $yyyymm, 'weekpres1');
		$protime_day_total_extra = $this->getProtimeDayTotalsPart($protime_id, $yyyymm, 'extra');

		foreach ( $protime_day_total_extra as $a=>$b ) {
			if ( $b < 0 ) {
				$protime_day_total[$a] += -($b);
			}
		}

		return $protime_day_total;
	}

	function getProtimeDayOvertimes($yyyymm) {
		$arrExtras = array();

		$protime_id = $this->getProtimeId();

		$protime_day_total_extra = $this->getProtimeDayTotalsPart($protime_id, $yyyymm, 'extra');

		foreach ( $protime_day_total_extra as $a=>$b ) {
			if ( isset( $arrExtras[$a] ) ) {
				$arrExtras[$a] += $b;
			} else {
				$arrExtras[$a] = $b;
			}
		}

		return $arrExtras;
	}

	function getProtimeDayTotalsPart($protime_id, $yyyymm, $type) {
		global $dbConn;

		$ret = array();

		if ( $protime_id != '' && $protime_id != '0' ) {

			$query = "SELECT PERSNR, BOOKDATE, PREST, RPREST, WEEKPRES1, EXTRA FROM protime_pr_month WHERE PERSNR=" . $protime_id . " AND LEFT(BOOKDATE, 6)=" . $yyyymm;

			$stmt = $dbConn->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$currentday = (int)(substr($row["BOOKDATE"], -2));
				if ( !isset( $ret[ $currentday ] ) ) {
					$ret[ $currentday ] = 0;
				}
				$ret[ $currentday ] = $row[ strtoupper($type) ];
			}
		}

		return $ret;
	}

	function getAllDailyAdditions() {
		global $dbConn;

		$arr = array();

		//
		$query = "
SELECT DailyAutomaticAdditions.ID FROM DailyAutomaticAdditions
	INNER JOIN Workcodes ON DailyAutomaticAdditions.workcode = Workcodes.ID
WHERE employee=::USER::
  AND DailyAutomaticAdditions.isdeleted=0
  AND Workcodes.isdisabled=0
ORDER BY Workcodes.Description
";

		$query = str_replace('::USER::', $this->timecard_id, $query);
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = new class_dailyaddition($row["ID"]);
		}

		return $arr;
	}

	function getEnabledDailyAdditions( $oDate ) {
		global $dbConn;

		$arr = array();

		//
		$query = "
		SELECT DailyAutomaticAdditions.ID FROM DailyAutomaticAdditions
			INNER JOIN Workcodes ON DailyAutomaticAdditions.workcode = Workcodes.ID
		WHERE employee=::USER::
			AND DailyAutomaticAdditions.isdeleted=0
			AND DailyAutomaticAdditions.isenabled=1
			AND DailyAutomaticAdditions.ratio>0
			AND Workcodes.isdisabled=0
			AND DailyAutomaticAdditions.first_date<=\"" . $oDate->get("Y-m-d") . "\"
			AND ( DailyAutomaticAdditions.last_date>=\"" . $oDate->get("Y-m-d") . "\" OR DailyAutomaticAdditions.last_date=\"\"  OR DailyAutomaticAdditions.last_date IS NULL )
		ORDER BY DailyAutomaticAdditions.ratio, Workcodes.Description
		";

		$query = str_replace('::USER::', $this->timecard_id, $query);
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = new class_dailyaddition($row["ID"]);
		}

		return $arr;
	}

	function getTotalWeightOfEnabledDailyAdditions() {
		$total = 0;

		//
		$query = "
		SELECT DailyAutomaticAdditions.ID FROM DailyAutomaticAdditions
			INNER JOIN Workcodes ON DailyAutomaticAdditions.workcode = Workcodes.ID
		WHERE employee=::USER::
		  AND DailyAutomaticAdditions.isdeleted=0
		  AND DailyAutomaticAdditions.isenabled=1
		  AND DailyAutomaticAdditions.ratio>0
		  AND Workcodes.isdisabled=0
		ORDER BY Workcodes.Description
		";

		$query = str_replace('::USER::', $this->timecard_id, $query);
		$stmt = $this->dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$daa = new class_dailyaddition($row["ID"]);
			$total += $daa->getRatio();
		}

		return $total;
	}

	function syncTimecardProtimeDayInformation( $oDate ) {
		$timecard_id = $this->timecard_id;
		$protime_id = $this->protime_id;

		if ( $timecard_id == '' || $timecard_id == '0' || $timecard_id == '-1' ) {
			return;
		}

		if ( $protime_id == '' || $protime_id == '0' || $protime_id == '-1' ) {
			return;
		}

		// add 'absences'
		addAndRemoveAbsentiesInTimecard($timecard_id, $protime_id, $oDate);

		// 'eerder naar huis'
		addEerderNaarHuisInTimecard($timecard_id, $protime_id, $oDate);

		// add 'daily automatic additions'
		$protimeDayData = $this->getProtimeDayTotal( array( 'y' => $oDate->get("Y"), 'm' => $oDate->get("m"), 'd' => $oDate->get("d")) );
		$arrProtimeDayData = array( $oDate->get("Ymd") => $protimeDayData );
		$this->addDailyAutomaticAdditions( $oDate, $arrProtimeDayData );
	}

	function syncTimecardProtimeMonthInformation( $oDate ) {
		$timecard_id = $this->timecard_id;
		$protime_id = $this->protime_id;

		if ( $timecard_id == '' || $timecard_id == '0' || $timecard_id == '-1' ) {
			return;
		}

		if ( $protime_id == '' || $protime_id == '0' || $protime_id == '-1' ) {
			return;
		}

		// add 'absences'
		for ( $i = 1; $i <= date("t", mktime(0, 0, 0, (int)( $oDate->get("m") ), (int)( $oDate->get("d") ), (int)( $oDate->get("Y") ) )); $i++ ) {
			$oDate2 = new class_date( $oDate->get("y"), $oDate->get("m"), $i );
			addAndRemoveAbsentiesInTimecard($timecard_id, $protime_id, $oDate2);
		}

		// eerder naar huis
		addEerderNaarHuisInTimecardMonth($timecard_id, $protime_id, $oDate);

		// add 'daily automatic additions'
		$protimeMonthData = $this->getProtimeDayTotalGroupedByDay( array( 'y' => $oDate->get("Y"), 'm' => $oDate->get("m"), 'd' => $oDate->get("d")) );
		for ( $i = 1; $i <= date("t", mktime(0, 0, 0, (int)( $oDate->get("m") ), (int)( $oDate->get("d") ), (int)( $oDate->get("Y") ) )); $i++ ) {
			$oDate2 = new class_date( $oDate->get("y"), $oDate->get("m"), $i );
			$this->addDailyAutomaticAdditions( $oDate2, $protimeMonthData );
		}
	}

	// add 'daily automatic additions'
	function addDailyAutomaticAdditions( $oDate, $protimeMonthData ) {
		global $dbConn;

		// don't do if date is in the future, or month is closed
		if (
			$oDate->get("Y-m-d") >= date("Y-m-d")
				|| $oDate->get("Y-m-d") < $this->getAllowAdditionsStartingDate()
			) {
			return;
		}

		// get Timecard totals
		$hoursTimecard = $this->getTimecardDayTotal( $oDate );

		// get Protime totals
		$hoursProtime = $protimeMonthData[$oDate->get("Ymd")];

			// calculate difference
		$difference = $hoursProtime - $hoursTimecard;

		// if difference smaller than 3 minutes, exit,
		if ( $difference > -3 && $difference < 3 ) {
			return;
		}

		// reset non fixed daa minuten
		$this->setZeroNoneFixedDaa( $oDate );

		// recalculate difference
		$hoursTimecard = $this->getTimecardDayTotal( $oDate );
		$difference = $hoursProtime - $hoursTimecard;

		if ( $difference < 0 ) {
			return;
		}

		$totalFlexibleRatio = 0;
		$arrFlexible = array();

		// get list of enabled DAA
		$arrEnabledDAA = $this->getEnabledDailyAdditions( $oDate );

		// if no DAA then do nothing
		if ( count($arrEnabledDAA) == 0 ) {
			return;
		}

		foreach ( $arrEnabledDAA as $daa ) {

			$oWorkhours = class_workhours::findDaaRecord($this, $daa->getId(), $oDate);

			if ( $oWorkhours->getId() == 0 ) {
				$totalFlexibleRatio += $daa->getRatio();

				// NEW
				$oWorkhours->setEmployeeId( $this->getTimecardId() );
				$oWorkhours->setDateWorked( $oDate->get("Y-m-d") . " 00:00:00" );
				$oWorkhours->setWorkCode( $daa->getWorkcode() );
				$oWorkhours->setWorkDescription( $daa->getDescription() );
				$oWorkhours->setTimeInMinutes( 0 );
				$oWorkhours->setDailyAutomaticAdditionId( $daa->getId() );
				$oWorkhours->setIsDeleted(0);
				$oWorkhours->save();

				$arrFlexible[] = array('workhours' => $oWorkhours, 'daa' => $daa );
			} else {
				// UPDATE
				if ( $oWorkhours->getIsTimeFixed() == 0 ) {
					$totalFlexibleRatio += $daa->getRatio();

					$oWorkhours->setTimeInMinutes( 0 );
					$oWorkhours->setIsDeleted(0);
					$oWorkhours->save();

					$arrFlexible[] = array('workhours' => $oWorkhours, 'daa' => $daa );
				}
			}
		}

		//
		foreach ( $arrFlexible as $flexible) {
			$newMinutes = round(1.0*$flexible["daa"]->getRatio()/$totalFlexibleRatio*$difference);
			$flexible["workhours"]->setTimeInMinutes( $newMinutes );
			$flexible["workhours"]->save();
		}

		// update lastyear data entered
		$year = $oDate->get("Y");
		$query_lastyear = "UPDATE Employees SET lastyear=" . $year . " WHERE ID=" . $this->timecard_id . " AND lastyear<" . $year;
		$stmt = $dbConn->prepare($query_lastyear);
		$stmt->execute();
	}

	function getTimecardDayTotal( $oDate ) {
		$hoursTotal = 0;

		$query = 'SELECT * FROM vw_hours_user WHERE Employee=' . $this->getTimecardId() . ' AND DateWorked="' . $oDate->get("Y-m-d") . '" AND protime_absence_recnr>=0 ';
		$stmt = $this->dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$hoursTotal += $row["TimeInMinutes"];
		}

		$eerderNaarHuisTotal = getEerderNaarHuisDayTotal($this->getTimecardId(), $oDate);

		return $hoursTotal+$eerderNaarHuisTotal;
	}

	function setZeroNoneFixedDaa( $oDate ) {
		global $dbConn;

		$query = "UPDATE Workhours SET TimeInMinutes=0 WHERE Employee=" . $this->getTimecardId() . " AND DateWorked=\"" . $oDate->get("Y-m-d") . "\" AND isdeleted=0 AND daily_automatic_addition_id>0 AND fixed_time=0 AND protime_absence_recnr>=0 AND TimeInMinutes<>0 ";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();

		return;
	}

	public static function getListOfEnabledAndLinkedEmployees() {
		global $settings, $dbConn;

		$ret = array();

		$query = "
SELECT ID
FROM `Employees`
WHERE `isdisabled`=0
AND `ProtimePersNr`>0
";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret[] = new class_employee($row["ID"], $settings);
		}

		return $ret;
	}

	public static function getListOfAllHoursLeftEmployees() {
		global $settings, $dbConn;

		$ret = array();

		$query = "
SELECT ProtimeID AS ID
FROM `Employees` INNER JOIN EmployeeFavourites
	ON Employees.ID  = EmployeeFavourites.ProtimeID
WHERE `isdisabled`=0
	AND `ProtimePersNr`>0
	AND type = 'hoursleft'
GROUP BY ProtimeID
";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret[] = new class_employee($row["ID"], $settings);
		}

		return $ret;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->timecard_id . "\n";
	}

	function getEmail() {
		$retval = '';

		if ( $this->getProtimeId() != '0' ) {

			$res = advancedSingleRecordSelectMysql(
				'default'
				, "protime_curric"
				, array("EMAIL")
				, "PERSNR=" . $this->getProtimeId()
			);

			$retval = $res["email"];
		}

		return $retval;
	}

	public function getDepartmentHeadExtraRightsOnDepartments() {
		return $this->extra_rights_on_departments;
	}

	public function getDepartmentHeadExtraRightsOnUsers() {
		return $this->extra_rights_on_users;
	}
}
