<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_employee {
	private $timecard_id = 0;
	private $databases;
	private $protime_id = 0;
	private $hoursdoublefield = '';
	private $isdisabled = 0;
	private $lastname = '';
	private $firstname = '';
	private $hoursperweek = 0;
	private $daysperweek = 0;
	private $authorisation = array();
	private $show_jira_field = false;
	private $allow_additions_starting_date = '';
	private $projects = array();
	private $department = '';
	private $sortProjectsOnName = 0;

	function class_employee($timecard_id, $settings) {
		global $databases;

		if ( $timecard_id == '' || $timecard_id < -1 ) {
			$timecard_id = 0;
		}

		$this->timecard_id = $timecard_id;
		$this->databases = $databases;

		if ( $timecard_id > 0 ) {
			$this->getTimecardValues();
		}
	}

	function getTimecardValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		//
		$query_project = "SELECT * FROM vw_Employees WHERE ID=" . $this->timecard_id;

		$resultReset = mysql_query($query_project, $oConn->getConnection());
		if ($row_project = mysql_fetch_assoc($resultReset)) {

			$this->isdisabled = $row_project["isdisabled"];
			$this->protime_id = $row_project["ProtimePersNr"];
			$this->lastname = $row_project["NAME"];
			$this->firstname = $row_project["FIRSTNAME"];
			$this->hoursperweek = $row_project["hoursperweek"];
			$this->daysperweek = $row_project["daysperweek"];
			$this->allow_additions_starting_date = $row_project["allow_additions_starting_date"];
			$this->department = $row_project["Department"];

			if ( $row_project["show_jira_field"] == 1 ) {
				$this->show_jira_field = true;
			}

			$this->hoursdoublefield = $row_project["HoursDoubleField"];
			if ( $this->hoursdoublefield != 1 && $this->hoursdoublefield != -1 ) {
				$this->hoursdoublefield = 1;
			}

			$this->sortProjectsOnName = $row_project["sort_projects_on_name"];
			if ( $this->sortProjectsOnName != 1 && $this->sortProjectsOnName != -1 ) {
				$this->sortProjectsOnName = -1;
			}

			// AUTHORISATION
			$queryAuthorisation = "SELECT * FROM Employee_Authorisation WHERE EmployeeID=" . $this->timecard_id;
			$resultAuthorisation = mysql_query($queryAuthorisation, $oConn->getConnection());
			while ($rowAuthorisation = mysql_fetch_assoc($resultAuthorisation)) {
				$this->authorisation[] = $rowAuthorisation["authorisation"];
			}
			mysql_free_result($resultAuthorisation);

			// PROJECTS
			$queryProjects = "SELECT * FROM Workcodes WHERE projectleader=" . $this->timecard_id . " ORDER BY Description";
			$resultProjects = mysql_query($queryProjects, $oConn->getConnection());
			while ($rowProjects = mysql_fetch_assoc($resultProjects)) {
				$this->projects[] = $rowProjects["ID"];
			}
			mysql_free_result($resultProjects);
		}
		mysql_free_result($resultReset);
	}

	function getAuthorisation() {
		return $this->authorisation ;
	}

	function getAllowAdditionsStartingDate() {
		return $this->allow_additions_starting_date ;
	}

	function hasAdminAuthorisation() {
		return ( in_array( 'admin', $this->getAuthorisation() ) ) ? true : false ;
	}

	function isProjectLeader() {
		return ( count($this->projects) > 0 );
	}

	function hasFaAuthorisation() {
		return ( in_array( 'fa', $this->getAuthorisation() ) ) ? true : false ;
	}

	function hasDepartmentAuthorisation() {
		return ( in_array( 'department', $this->getAuthorisation() ) ) ? true : false ;
	}

	function hasReportsAuthorisation() {
		return ( in_array( 'reports', $this->getAuthorisation() ) ) ? true : false ;
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

	public function getDepartmentId() {
		return $this->department;
	}

	public function getProtimeId() {
		return $this->protime_id;
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

	function calculateVacationHours() {
		$retval = '';

		if ( $this->getProtimeId() != '0' ) {

			$vakantie = advancedSingleRecordSelectMysql(
						'default'
						, "PROTIME_P_LIMIT"
						, array("BEGIN_VAL", "END_VAL", "BOOKDATE")
						, "PERSNR=" . $this->getProtimeId() . " AND YEARCOUNTER=1 AND LIM_PERIODE = 6 "
						, '*'
						, "BOOKDATE DESC"
					);

			$end_val = $vakantie["end_val"];
			if ( $end_val != '' ) {
				$bookdate = $vakantie["bookdate"];
				$bookdate = substr($bookdate, 0, 4) . "-" . substr($bookdate, 4, 2) . "-" . substr($bookdate, 6, 2);
				$retval .= number_format( $end_val/60, 2, ',', '.' ) . " hours <i>(processed until: " . $bookdate . "*)</i>";
			} else {
				$retval .= "<i>(no vacation days found)</i><br>";
			}

		}

		return $retval;
	}

	function findTimecardIdUsingProtimeId($protime_id) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

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
		$ret = array();

		$protime_id = $this->getProtimeId();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// reset values
		$query = "SELECT SUBSTR(BOOKDATE, 1, 10) AS BOOKDATUM, WEEKPRES1, EXTRA
FROM PROTIME_PR_MONTH
WHERE PERSNR=" . $protime_id . " AND BOOKDATE LIKE '" . $date["y"] . str_pad( $date["m"], 2, '0', STR_PAD_LEFT) . "%'
GROUP BY SUBSTR(BOOKDATE, 1, 10)
";

		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {

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
		mysql_free_result($result);

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
					, "PROTIME_PR_MONTH"
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
		$ret = 0;

		if ( $this->getProtimeId() != '0' ) {

			$vakantie = advancedSingleRecordSelectMysql(
				'default'
				, "PROTIME_P_LIMIT"
				, array("BEGIN_VAL", "END_VAL", "BOOKDATE")
				, "PERSNR=" . $this->getProtimeId() . " AND YEARCOUNTER=1 "
				, '*'
				, "BOOKDATE DESC"
			);

			$ret = $vakantie["end_val"];

			if ( ret == '' ) {
				$ret = 0;
			}

			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT SUM(ABSENCE_VALUE) AS SOM FROM PROTIME_P_ABSENCE  WHERE PERSNR=" . $this->getProtimeId() . " AND ABSENCE IN ( 12 ) AND BOOKDATE LIKE '$year%' AND BOOKDATE > '{$vakantie["bookdate"]}' ";
			$result = mysql_query($query, $oConn->getConnection());
			if ( $row = mysql_fetch_array($result) ) {
				$ret -= $row["SOM"];
			}
			mysql_free_result($result);
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
						, "PROTIME_P_LIMIT"
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
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$ids = array();
		$ids[] = '0';

		$query = "SELECT * FROM EmployeeFavourites WHERE TimecardID=" . $this->getTimecardId() . ' AND type=\'' . $type . '\' ';

		$result = mysql_query($query, $oConn->getConnection());
		while ( $row = mysql_fetch_array($result) ) {
			$ids[] = $row["ProtimeID"];
		}
		mysql_free_result($result);

		return $ids;
	}

	function getTimecardDayTotals( $year, $month ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$ret = array();

		$query = 'SELECT *, DAY(DateWorked) AS CURRENTDAY FROM vw_hours_user WHERE Employee=' . $this->getTimecardId() . ' AND DateWorked LIKE \'' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-%\' AND protime_absence_recnr>=0 ORDER BY DateWorked ';
		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {
			if ( !isset( $ret[ $row["CURRENTDAY"] ] ) ) {
				$ret[ $row["CURRENTDAY"]+0 ] = 0;
			}
			$ret[ $row["CURRENTDAY"]+0 ] += $row["TimeInMinutes"];
		}
		mysql_free_result($result);

		return $ret;
	}


	function getEerderNaarHuisDayTotals( $year, $month ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$ret = array();

		// achterhaal 
		$query = "SELECT TimeInMinutes, DAY(DateWorked) AS CURRENTDAY FROM Workhours WHERE Employee=" . $this->getTimecardId() . " AND DateWorked LIKE '" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%' AND protime_absence_recnr=-1 ORDER BY DateWorked ";

		$result = mysql_query($query, $oConn->getConnection());
		while ( $row = mysql_fetch_array($result) ) {
			if ( !isset( $ret[ $row["CURRENTDAY"] ] ) ) {
				$ret[ $row["CURRENTDAY"]+0 ] = 0;
			}
			$ret[ $row["CURRENTDAY"]+0 ] = $row["TimeInMinutes"];
		}
		mysql_free_result($result);

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
			$arrExtras[$a] += $b;
		}

		return $arrExtras;
	}

	function getProtimeDayTotalsPart($protime_id, $yyyymm, $type) {
		global $databases;

		$ret = array();

		if ( $protime_id != '' && $protime_id != '0' ) {

			$query = "SELECT PERSNR, BOOKDATE, PREST, RPREST, WEEKPRES1, EXTRA FROM PROTIME_PR_MONTH WHERE PERSNR=" . $protime_id . " AND LEFT(BOOKDATE, 6)=" . $yyyymm;
			$oTc = new class_mysql($databases['default']);
			$oTc->connect();

			$result = mysql_query($query, $oTc->getConnection());
			while ( $row = mysql_fetch_array($result) ) {
				$currentday = (int)(substr($row["BOOKDATE"], -2));
				if ( !isset( $ret[ $currentday ] ) ) {
					$ret[ $currentday ] = 0;
				}
				$ret[ $currentday ] = $row[ strtoupper($type) ];
			}
			mysql_free_result($result);

		}

		return $ret;
	}

	function getAllDailyAdditions() {
		$arr = array();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// TODOTODO
		$query = "
SELECT DailyAutomaticAdditions.ID FROM DailyAutomaticAdditions
	INNER JOIN Workcodes ON DailyAutomaticAdditions.workcode = Workcodes.ID
WHERE employee=::USER::
  AND DailyAutomaticAdditions.isdeleted=0
  AND Workcodes.isdisabled=0
ORDER BY Workcodes.Description
";

		$query = str_replace('::USER::', $this->timecard_id, $query);

		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[] = new class_dailyaddition($row["ID"]);
			}
			mysql_free_result($result);

		}

		return $arr;
	}

	function getEnabledDailyAdditions( $oDate ) {
		$arr = array();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// TODOTODO: add first_date and last_date controle
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

		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[] = new class_dailyaddition($row["ID"]);
			}
			mysql_free_result($result);

		}

		return $arr;
	}

	function getTotalWeightOfEnabledDailyAdditions() {
		$total = 0;

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

			// TODOTODO
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

		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$daa = new class_dailyaddition($row["ID"]);
				$total += $daa->getRatio();
			}
			mysql_free_result($result);

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
		// don't do if legacy, or date in the future
		//if ( $oDate->get("Y-m") < Settings::get("oldest_modifiable_daa_month") || $oDate->get("Y-m-d") >= date("Y-m-d") ) {
		if ( class_datetime::is_legacy( $oDate ) || $oDate->get("Y-m-d") >= date("Y-m-d") ) {
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
	}

	function getTimecardDayTotal( $oDate ) {
		$hoursTotal = 0;

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = 'SELECT * FROM vw_hours_user WHERE Employee=' . $this->getTimecardId() . ' AND DateWorked="' . $oDate->get("Y-m-d") . '" AND protime_absence_recnr>=0 ';
		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {
			$hoursTotal += $row["TimeInMinutes"];
		}

		$eerderNaarHuisTotal = getEerderNaarHuisDayTotal($this->getTimecardId(), $oDate);

		return $hoursTotal+$eerderNaarHuisTotal;
	}

	function setZeroNoneFixedDaa( $oDate ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "UPDATE Workhours SET TimeInMinutes=0 WHERE Employee=" . $this->getTimecardId() . " AND DateWorked=\"" . $oDate->get("Y-m-d") . "\" AND isdeleted=0 AND daily_automatic_addition_id>0 AND fixed_time=0 AND protime_absence_recnr>=0 AND TimeInMinutes<>0 ";
		$result = mysql_query($query, $oConn->getConnection());

		return;
	}

	public static function getListOfDaaEmployees() {
		global $settings, $databases;

		$ret = array();

		$query = "
SELECT ID
FROM `Employees`
WHERE `isdisabled`=0
AND `ProtimePersNr`>0
		AND `ID` IN (
				SELECT DISTINCT `employee`
				FROM `DailyAutomaticAdditions`
				WHERE `isenabled`=1 AND `isdeleted`=0 AND `ratio`>0
			)
";

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$result = mysql_query($query, $oConn->getConnection());

		while ($row = mysql_fetch_assoc($result)) {
			$ret[] = new class_employee($row["ID"], $settings);
		}
		mysql_free_result($result);

		return $ret;
	}

	public static function getListOfEnabledAndLinkedEmployees() {
		global $settings, $databases;

		$ret = array();

		$query = "
SELECT ID
FROM `Employees`
WHERE `isdisabled`=0
AND `ProtimePersNr`>0
";

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$result = mysql_query($query, $oConn->getConnection());

		while ($row = mysql_fetch_assoc($result)) {
			$ret[] = new class_employee($row["ID"], $settings);
		}
		mysql_free_result($result);

		return $ret;
	}

	public static function getListOfAllHoursLeftEmployees() {
		global $settings, $databases;

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

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$result = mysql_query($query, $oConn->getConnection());

		while ($row = mysql_fetch_assoc($result)) {
			$ret[] = new class_employee($row["ID"], $settings);
		}
		mysql_free_result($result);

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
				, "PROTIME_CURRIC"
				, array("EMAIL")
				, "PERSNR=" . $this->getProtimeId()
			);

			$retval = $res["email"];
		}

		return $retval;
	}
}
