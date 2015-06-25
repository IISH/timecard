<?php
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/class_tcdatetime.inc.php";
require_once dirname(__FILE__) . "/_misc_functions.inc.php";

class class_refresh_employee_hours_per_week {
	private $oEmployee;
	private $year;
	private $databases;
	private $totalHoursPerWeek;
	private $totalHoursPerWeekText;
	private $isNew;

	// TODOEXPLAIN
	function class_refresh_employee_hours_per_week( $oEmployee, $year ) {
		global $databases;
		$this->databases = $databases;

		$this->oEmployee = $oEmployee;
		$this->year = $year;

		$this->isNew = true;
	}

	// TODOEXPLAIN
	public function refresh( $force_refresh = false ) {

		// get last refresh time
		$lastRefreshTime = $this->getLastRefreshTime();
		if ( $lastRefreshTime == '' ) {
			$this->isNew = true;
		} else {
			$this->isNew = false;
		}

		// if time stamp differs with time stamp template then it is too old and should be refreshed
		if ( $lastRefreshTime != date( class_settings::getSetting("timeStampRefreshLowPriority") ) ) {
			$force_refresh = true;
		}

		if ( $force_refresh ) {
			// get current total hours per week
			$oEmployeeHoursPerDayStarting = new class_employee_hours_per_day_starting($this->oEmployee, $this->year);
			$this->totalHoursPerWeek = $oEmployeeHoursPerDayStarting->getCurrentTotalHoursPerWeek();

			// get text for all hours per week until (including) the first one of a previous year
			$text = '';
			foreach ( $oEmployeeHoursPerDayStarting->getStartDayTotals( true ) as $element ) {
				$text .= "Starting " . $element['date'] . ' : ' . hoursLeft_formatNumber($element['minutes']/60.0,1,true) . " hours per week\n";
			}

			$this->totalHoursPerWeekText = $text;

			$this->saveRecord();
		}
	}

	// TODOEXPLAIN
	private function saveRecord() {
		if ( $this->isNew ) {
			$this->newRecord();
		} else {
			$this->updateRecord();
		}
	}

	// TODOEXPLAIN
	private function newRecord() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$curtime = date( class_settings::getSetting("timeStampRefreshLowPriority") );
		$totalHoursPerWeekText = addslashes($this->totalHoursPerWeekText);

		$query = "
INSERT INTO Employee_Cache_Hours_Per_Week (
	EmployeeID
	, year
	, last_refresh
	, hours_per_week
	, hours_per_week_text
) VALUES (
	{$this->oEmployee->getTimecardId()}
	, {$this->year}
	, '{$curtime}'
	, {$this->totalHoursPerWeek}
	, '{$totalHoursPerWeekText}'
)";

		$result = mysql_query($query, $oConn->getConnection());
	}

	// TODOEXPLAIN
	private function updateRecord() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$curtime = date( class_settings::getSetting("timeStampRefreshLowPriority") );
		$totalHoursPerWeekText = addslashes($this->totalHoursPerWeekText);

		$query = "
UPDATE Employee_Cache_Hours_Per_Week
SET
	last_refresh = '{$curtime}'
	, hours_per_week = {$this->totalHoursPerWeek}
	, hours_per_week_text = '{$totalHoursPerWeekText}'
WHERE EmployeeID = {$this->oEmployee->getTimecardId()} AND year = {$this->year}";

		$result = mysql_query($query, $oConn->getConnection());
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\Employee Timecard #: " . $this->oEmployee->getTimecardId() . "\n";
	}

	// TODOEXPLAIN
	public function getLastRefreshTime() {
		$lastRefreshTime = '';

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Employee_Cache_Hours_Per_Week WHERE EmployeeID=" . $this->oEmployee->getTimecardId() . " AND year=" . $this->year;

		$result = mysql_query($query, $oConn->getConnection());
		if ($row = mysql_fetch_assoc($result)) {
			$lastRefreshTime = $row['last_refresh'];
		}
		mysql_free_result($result);

		return $lastRefreshTime;
	}
}

class class_employee_hours_per_day_starting {
	private $oEmployee;
	private $last_year;
	private $arr = array();
	private $startDayTotals = array();

	// TODOEXPLAIN
	function class_employee_hours_per_day_starting( $oEmployee, $last_year ) {
		global $databases;
		$this->databases = $databases;

		$this->oEmployee = $oEmployee;
		$this->last_year = $last_year;

		$this->initValues();
	}

	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// OLD QUERY
		// exclude datefrom starting a year in the future
		$query = "
SELECT PROTIME_LNK_CURRIC_PROFILE.DATEFROM, PROTIME_CYC_DP.DAYNR, PROTIME_DAYPROG.NORM
FROM PROTIME_LNK_CURRIC_PROFILE
	LEFT JOIN PROTIME_CYC_DP ON PROTIME_LNK_CURRIC_PROFILE.PROFILE = PROTIME_CYC_DP.CYCLIQ
	LEFT JOIN PROTIME_DAYPROG ON PROTIME_CYC_DP.DAYPROG = PROTIME_DAYPROG.DAYPROG
WHERE PROFILETYPE = '4'
	AND PERSNR = '" . $this->oEmployee->getProtimeId() . "'
	AND PROTIME_LNK_CURRIC_PROFILE.DATEFROM < '" . ($this->last_year+1)  . "'
	AND PROTIME_CYC_DP.DAYNR <= 7
ORDER BY DATEFROM DESC, CAST(PROTIME_CYC_DP.DAYNR AS UNSIGNED) ASC
";
		// nieuwe query naar aanleiding van wisselende week roosters
		$query = "
SELECT PROTIME_LNK_CURRIC_PROFILE.DATEFROM, MOD(CAST(PROTIME_CYC_DP.DAYNR AS UNSIGNED),7) AS DAG, SUM(PROTIME_DAYPROG.NORM)/count(*) AS HOEVEEL
FROM PROTIME_LNK_CURRIC_PROFILE
	LEFT JOIN PROTIME_CYC_DP ON PROTIME_LNK_CURRIC_PROFILE.PROFILE = PROTIME_CYC_DP.CYCLIQ
	LEFT JOIN PROTIME_DAYPROG ON PROTIME_CYC_DP.DAYPROG = PROTIME_DAYPROG.DAYPROG
WHERE PROFILETYPE = '4'
	AND PERSNR = '" . $this->oEmployee->getProtimeId() . "'
	AND PROTIME_LNK_CURRIC_PROFILE.DATEFROM < '" . ($this->last_year+1)  . "'
GROUP BY PROTIME_LNK_CURRIC_PROFILE.DATEFROM, MOD(CAST(PROTIME_CYC_DP.DAYNR AS UNSIGNED),7)
ORDER BY PROTIME_LNK_CURRIC_PROFILE.DATEFROM DESC, MOD(CAST(PROTIME_CYC_DP.DAYNR AS UNSIGNED),7) ASC
";

		$lastDate = '';
		$total = 0;

		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {

			// convert date format
			$oTCDate = new TCDateTime();
			$oTCDate->setFromString($row['DATEFROM'], "Ymd");
			$date = $oTCDate->getToString("Y-m-d");

			//
//			$dayOfWeek = $row['DAYNR'];
//			$minutes = $row['NORM'];
			$dayOfWeek = $row['DAG'];
			$minutes = $row['HOEVEEL'];

			if ( $lastDate != '' && $lastDate != $date ) {
				// save in grouped array
				$this->startDayTotals[] = array( 'date' => $lastDate, 'minutes' => $total );
				$total = 0;
			}

			$this->arr[] = array( 'date' => $date, 'dayOfWeek' => $dayOfWeek, 'minutes' => $minutes );
			$total += $minutes;
			$lastDate = $date;
		}

		if ( $lastDate != '' ) {
			// save in grouped array
			$this->startDayTotals[] = array( 'date' => $lastDate, 'minutes' => $total );
		}

		mysql_free_result($result);
	}

	public function getCurrentTotalMinutesPerWeek() {
		$lastDate = '';
		$total = 0;

		foreach ( $this->arr as $element ) {
			if ( $lastDate == '' || $lastDate == $element['date'] ) {
				$total += $element['minutes'];
				$lastDate = $element['date'];
			}
		}

		return $total;
	}

	public function getCurrentTotalHoursPerWeek() {
		return $this->getCurrentTotalMinutesPerWeek()/60.0;
	}

	public function getStartDayTotals( $only_recent = false ) {
		$arr = array();

		foreach ( $this->startDayTotals as $element ) {
			$arr[] = $element;

			if ( $only_recent ) {
				if ( $element['date'] < $this->last_year ) {
					break;
				}
			}
		}

		return $arr;
	}
}
