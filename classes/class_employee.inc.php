<?php 
// modified: 2012-12-27

class class_employee {
    private $timecard_id = 0;
    private $settings;
    private $protime_id = 0;
    private $kamerbezetting_id = 0;
    private $hoursdoublefield = '';
    private $is_disabled = 0;
    private $lastname = '';
    private $firstname = '';
    private $hoursperweek = 0;
    private $daysperweek = 0;
    private $authorisation = array();

	// TODOEXPLAIN
	function class_employee($timecard_id, $settings) {
		if ( $timecard_id == '' || $timecard_id < -1 ) {
			$timecard_id = 0;
		}

		$this->timecard_id = $timecard_id;
		$this->settings = $settings;

		if ( $timecard_id > 0 ) {
			$this->getTimecardValues();
		}
	}

	// TODOEXPLAIN
	function getTimecardValues() {
		global $dbhandleTimecard;

		// 
		$query_project = "SELECT * FROM Employees WHERE ID=" . $this->timecard_id;

		$resultReset = mysql_query($query_project, $dbhandleTimecard);
		if ($row_project = mysql_fetch_assoc($resultReset)) {

			$this->is_disabled = $row_project["is_disabled"];
			$this->protime_id = $row_project["ProtimePersNr"];
			$this->lastname = $row_project["LastName"];
			$this->firstname = $row_project["FirstName"];
			$this->hoursperweek = $row_project["hoursperweek"];
			$this->daysperweek = $row_project["daysperweek"];

			$this->hoursdoublefield = $row_project["HoursDoubleField"];
			if ( $this->hoursdoublefield != 1 && $this->hoursdoublefield != -1 ) {
				$this->hoursdoublefield = 1;
			}

			// 
			$queryAuthorisation = "SELECT * FROM Employee_Authorisation WHERE EmployeeID=" . $this->timecard_id;
			$resultAuthorisation = mysql_query($queryAuthorisation, $dbhandleTimecard);
			while ($rowAuthorisation = mysql_fetch_assoc($resultAuthorisation)) {
				$this->authorisation[] = $rowAuthorisation["authorisation"];
			}
			mysql_free_result($resultAuthorisation);


		}
		mysql_free_result($resultReset);
	}

	// TODOEXPLAIN
	function getAuthorisation() {
		return $this->authorisation ;
	}

	// TODOEXPLAIN
	function hasInOutTimeAuthorisation() {
		return ( in_array( 'inouttime', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function hasAdminAuthorisation() {
		return ( in_array( 'admin', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function hasProtimeAuthorisation() {
		return ( in_array( 'protime', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function hasFaAuthorisation() {
		return ( in_array( 'fa', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function hasPresentAuthorisation() {
		return ( in_array( 'present', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function hasAbsenceAuthorisation() {
		return ( in_array( 'absence', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function hasExportsAuthorisation() {
		return ( in_array( 'reports', $this->getAuthorisation() ) ) ? true : false ;
	}

	// TODOEXPLAIN
	function isDisabled() {
		return $this->is_disabled;
	}

	// TODOEXPLAIN
	function getTimecardId() {
		return $this->timecard_id;
	}

	// TODOEXPLAIN
	function getProtimeId() {
		return $this->protime_id;
	}

	// TODOEXPLAIN
	function isLoggedIn() {
		$ret = false;

		if ( $this->timecard_id > 0 ) {
			$ret = true;
		}

		return $ret;
	}

	// TODOEXPLAIN
	function checkLoggedIn() {
		global $protect;

		if ( $this->timecard_id == 0 ) {
			Header("Location: login.php?burl=" . URLencode($protect->getShortUrl()));
			die("go to <a href=\"login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		} else {
			$this->ifDisabledGoToLogout();
		}
	}

	// TODOEXPLAIN
	function ifDisabledGoToLogout() {
		if ( $this->isDisabled() ) {
			Header("Location: logout.php?m=disabled");
			die('go to: <a href="logout.php?m=disabled">logout</a>');
		}
	}

	// TODOEXPLAIN
	function getHoursdoublefield() {
		return $this->hoursdoublefield;
	}

	// TODOEXPLAIN
	function getLastname() {
		return $this->lastname;
	}

	// TODOEXPLAIN
	function getLastFirstname() {
		return $this->lastname . ', ' . $this->firstname;
	}

	// TODOEXPLAIN
	function getFirstLastname() {
		return $this->firstname . ' ' . $this->lastname;
	}

	// TODOEXPLAIN
	function getFirstname() {
		return $this->firstname;
	}

	// TODOEXPLAIN
	function getHoursperweek() {
		return $this->hoursperweek;
	}

	// TODOEXPLAIN
	function getDaysperweek() {
		return $this->daysperweek;
	}

	// TODOEXPLAIN
	function calculateVacationHours() {
		global $dbhandleProtime;
		$retval = '';

		if ( $this->getProtimeId() != '0' ) {

			$vakantie = advancedSingleRecordSelectMssql(
						$dbhandleProtime
						, "P_LIMIT"
						, array("BEGIN_VAL", "END_VAL", "BOOKDATE")
						, "PERSNR=" . $this->getProtimeId() . " AND EXEC_ORDER=2 "
						, '*'
						, "BOOKDATE DESC"
					);

			$end_val = $vakantie["end_val"];
			if ( $end_val != '' ) {
				$bookdate = $vakantie["bookdate"];
				$bookdate = substr($bookdate, 0, 4) . "-" . substr($bookdate, 4, 2) . "-" . substr($bookdate, 6, 2);
				$retval .= number_format( $end_val/60, 2 ) . " hours <i>(processed until: " . $bookdate . ")</i>";
			} else {
				$retval .= "<i>(no days off found)</i><br>";
			}

		}

		return $retval;
	}

	// TODOEXPLAIN
	function findTimecardIdUsingProtimeId($protime_id) {
		global $dbhandleTimecard;

		$val = 0;

		if ( $protime_id > 0 ) {

			// search in protime database
			$record = advancedSingleRecordSelectMysql(
					$dbhandleTimecard
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

	// TODOEXPLAIN
	function getProtimeMonthTotal($date, $type='max') {
		$retval = 0;

		for ( $i=1; $i<=31; $i++ ) {
			$date["d"] = substr('0' . $i, -2);
			$tmp = $this->getProtimeDayTotal($date, $type);
			$retval += $tmp;
		}

		return $retval;
	}

	// TODOEXPLAIN
	function getProtimeDayTotal($date) {
		$protime_id = $this->getProtimeId();

		$protime_day_total = $this->getProtimeDayTotalPart($protime_id, $date, 'weekpres1');
		$protime_day_total_extra = $this->getProtimeDayTotalPart($protime_id, $date, 'extra');
		if ( $protime_day_total_extra < 0 ) {
			$protime_day_total += -($protime_day_total_extra);
		}

		return $protime_day_total;
	}

	// TODOEXPLAIN
	function getProtimeDayTotalPart($protime_id, $date, $type) {
		global $dbhandleProtime;

		$retval = 0;

		if ( $protime_id != '' && $protime_id != '0' ) {

			$oDate = new class_date( $date["y"], $date["m"], $date["d"] );

			// 
			$hours = advancedSingleRecordSelectMssql(
					$dbhandleProtime
					, "PR_MONTH"
					, array("PERSNR", "BOOKDATE", "PREST", "RPREST", "WEEKPRES1", "EXTRA")
					, "PERSNR=" . $protime_id . " AND BOOKDATE='" . $oDate->get("Ymd") . "' "
				);

			// returneer opgegeven veld
			$retval = $hours[$type];
		}

		return $retval;
	}

	function getHoursPerWeek2($year) {
		global $dbhandleTimecard;

		$arr = array();

		// reset values
		$query = "SELECT ID FROM HoursPerWeek WHERE year=" . $year . " AND Employee=" . $this->getTimecardId() . " AND isdeleted=0 ORDER BY startmonth ";
		$result = mysql_query($query, $dbhandleTimecard);
		while ($row = mysql_fetch_assoc($result)) {
			$oHoursPerWeek = new class_hoursperweek($row["ID"], $this->settings);
			$arr[] = $oHoursPerWeek;
		}
		mysql_free_result($result);

		return $arr;
	}

	function getVacationHours() {
		global $dbhandleProtime;

		$ret = 0;

		if ( $this->getProtimeId() != '0' ) {

			$vakantie = advancedSingleRecordSelectMssql(
						$dbhandleProtime
						, "P_LIMIT"
						, array("BEGIN_VAL", "END_VAL", "BOOKDATE")
						, "PERSNR=" . $this->getProtimeId() . " AND EXEC_ORDER=2 "
						, '*'
						, "BOOKDATE DESC"
					);

			$end_val = $vakantie["end_val"];
			if ( $end_val != '' ) {
				$ret = $end_val/60;
			} else {
				$ret = 0;
			}

		}

		return $ret;
	}

	function getFavourites( $type ) {
		global $dbhandleTimecard;

		$ids = array();
		$ids[] = '0';

		$query = "SELECT * FROM EmployeeFavourites WHERE TimecardID=" . $this->getTimecardId() . ' AND type=\'' . $type . '\' ';

		$result = mysql_query($query, $dbhandleTimecard);
		while ( $row = mysql_fetch_array($result) ) {
			$ids[] = $row["ProtimeID"];
		}
		mysql_free_result($result);

		return $ids;
	}

	function getTimecardDayTotals( $year, $month ) {
		global $dbhandleTimecard;

		$ret = array();

		$query = 'SELECT *, DAY(DateWorked) AS CURRENTDAY FROM vw_hours2011_user WHERE Employee=' . $this->getTimecardId() . ' AND DateWorked LIKE \'' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-%\' AND protime_absence_recnr>=0 ORDER BY DateWorked ';
		$result = mysql_query($query, $dbhandleTimecard);
		while ($row = mysql_fetch_assoc($result)) {
			if ( !isset( $ret[ $row["CURRENTDAY"] ] ) ) {
				$ret[ $row["CURRENTDAY"]+0 ] = 0;
			}
			$ret[ $row["CURRENTDAY"]+0 ] += $row["TimeInMinutes"];
		}
		mysql_free_result($result);

		return $ret;
	}


	// TODOEXPLAIN
	function getEerderNaarHuisDayTotals( $year, $month ) {
		global $dbhandleTimecard;

		$ret = array();

		// achterhaal 
		$query = "SELECT TimeInMinutes, DAY(DateWorked) AS CURRENTDAY FROM Workhours WHERE Employee=" . $this->getTimecardId() . " AND DateWorked LIKE '" . $year . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-%' AND protime_absence_recnr=-1 ORDER BY DateWorked ";

		$result = mysql_query($query, $dbhandleTimecard);
		while ( $row = mysql_fetch_array($result) ) {
			if ( !isset( $ret[ $row["CURRENTDAY"] ] ) ) {
				$ret[ $row["CURRENTDAY"]+0 ] = 0;
			}
			$ret[ $row["CURRENTDAY"]+0 ] = $row["TimeInMinutes"];
		}
		mysql_free_result($result);

		return $ret;
	}

	// TODOEXPLAIN
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

	// TODOEXPLAIN
	function getProtimeDayTotalsPart($protime_id, $yyyymm, $type) {
		global $dbhandleProtime;

		$ret = array();

		if ( $protime_id != '' && $protime_id != '0' ) {

			$query = "SELECT PERSNR, BOOKDATE, PREST, RPREST, WEEKPRES1, EXTRA FROM PR_MONTH WHERE PERSNR=" . $protime_id . " AND LEFT(BOOKDATE, 6)=" . $yyyymm;

			$result = mssql_query($query, $dbhandleProtime);
			while ( $row = mssql_fetch_array($result) ) {
				$currentday = substr($row["BOOKDATE"], -2)+0;
				if ( !isset( $ret[ $currentday ] ) ) {
					$ret[ $currentday ] = 0;
				}
				$ret[ $currentday ] = $row[ strtoupper($type) ];
			}
			mssql_free_result($result);

		}

		return $ret;
	}
}
?>