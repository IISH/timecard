<?php
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/class_tcdatetime.inc.php";
require_once dirname(__FILE__) . "/_misc_functions.inc.php";

class class_refresh_employee_hours_per_week {
	private $oEmployee;
	private $year;
	private $totalHoursPerWeek;
	private $totalHoursPerWeekText;
	private $isNew;

	function __construct( $oEmployee, $year ) {
		$this->oEmployee = $oEmployee;
		$this->year = $year;

		$this->isNew = true;
	}

	public function refresh( $force_refresh = false ) {

		// get last refresh time
		$lastRefreshTime = $this->getLastRefreshTime();
		if ( $lastRefreshTime == '' ) {
			$this->isNew = true;
		} else {
			$this->isNew = false;
		}

		// if time stamp differs with time stamp template then it is too old and should be refreshed
		if ( $lastRefreshTime != date( Settings::get("timeStampRefreshLowPriority") ) ) {
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

	private function saveRecord() {
		if ( $this->isNew ) {
			$this->newRecord();
		} else {
			$this->updateRecord();
		}
	}

	private function newRecord() {
		global $dbConn;

		$curtime = date( Settings::get("timeStampRefreshLowPriority") );
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

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
	}

	private function updateRecord() {
		global $dbConn;

		$curtime = date( Settings::get("timeStampRefreshLowPriority") );
		$totalHoursPerWeekText = addslashes($this->totalHoursPerWeekText);

		$query = "
UPDATE Employee_Cache_Hours_Per_Week
SET
	last_refresh = '{$curtime}'
	, hours_per_week = {$this->totalHoursPerWeek}
	, hours_per_week_text = '{$totalHoursPerWeekText}'
WHERE EmployeeID = {$this->oEmployee->getTimecardId()} AND year = {$this->year}";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\Employee Timecard #: " . $this->oEmployee->getTimecardId() . "\n";
	}

	public function getLastRefreshTime() {
		global $dbConn;

		$lastRefreshTime = '';

		$query = "SELECT * FROM Employee_Cache_Hours_Per_Week WHERE EmployeeID=" . $this->oEmployee->getTimecardId() . " AND year=" . $this->year;

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		if ( $row = $stmt->fetch() ) {
			$lastRefreshTime = $row['last_refresh'];
		}

		return $lastRefreshTime;
	}
}

class class_employee_hours_per_day_starting {
	private $oEmployee;
	private $last_year;
	private $arr = array();
	private $startDayTotals = array();

	function __construct( $oEmployee, $last_year ) {
		$this->oEmployee = $oEmployee;
		$this->last_year = $last_year;

		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		// probleem erhan
		// nieuwe query naar aanleiding van wisselende week roosters
		$query = "
SELECT protime_lnk_curric_profile.DATEFROM, MOD(CAST(protime_cyc_dp.DAYNR AS UNSIGNED),7) AS DAG, SUM(protime_dayprog.NORM)/count(*) AS HOEVEEL
FROM protime_lnk_curric_profile
	LEFT JOIN protime_cyc_dp ON protime_lnk_curric_profile.PROFILE = protime_cyc_dp.CYCLIQ
	LEFT JOIN protime_dayprog ON protime_cyc_dp.DAYPROG = protime_dayprog.DAYPROG
WHERE PROFILETYPE = '4'
	AND PERSNR = '" . $this->oEmployee->getProtimeId() . "'
	AND protime_lnk_curric_profile.DATEFROM < '" . ($this->last_year+1)  . "'
GROUP BY protime_lnk_curric_profile.DATEFROM, MOD(CAST(protime_cyc_dp.DAYNR AS UNSIGNED),7)
ORDER BY protime_lnk_curric_profile.DATEFROM DESC, MOD(CAST(protime_cyc_dp.DAYNR AS UNSIGNED),7) ASC
";

		$lastDate = '';
		$total = 0;

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {

			// convert date format
			$oTCDate = new TCDateTime();
			$oTCDate->setFromString($row['DATEFROM'], "Ymd");
			$date = $oTCDate->getToString("Y-m-d");

			//
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
