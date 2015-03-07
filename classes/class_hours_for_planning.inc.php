<?php 
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class HoursForPlanning {
	private $settings;

	private $oEmployee;
	private $year;
	private $workMonthTotals = array();
	private $absenceMonthTotals = array();

	// TODOEXPLAIN
	function HoursForPlanning($oEmployee, $year) {
		global $databases;
		$this->databases = $databases;

		$this->oEmployee = $oEmployee;
		$this->year = $year;

		$this->checkValues();

		$this->getValues();
	}

	// TODOEXPLAIN
	private function checkValues() {
		for ( $i = 1; $i <= 12; $i++ ) {
			// TODO


		}
	}

	// TODOEXPLAIN
	private function refreshValues() {
		// TODO




	}

	// TODOEXPLAIN
	public function getWorkValue($yyyymm) {
		$work = 0;

		if ( isset($this->workMonthTotals[$yyyymm]) ) {
			$work = $this->workMonthTotals[$yyyymm];
		}

		return $work;
	}

	// TODOEXPLAIN
	public function getAbsenceValue($yyyymm) {
		$work = 0;

		if ( isset($this->absenceMonthTotals[$yyyymm]) ) {
			$work = $this->absenceMonthTotals[$yyyymm];
		}

		return $work;
	}

	// TODOEXPLAIN
	private function getValues() {
			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT * FROM Employee_Planning WHERE EmployeeId=" . $this->oEmployee->getTimecardId() . " AND yearmonth LIKE '" . $this->year . "%' ORDER BY yearmonth ";
			$res = mysql_query($query, $oConn->getConnection());
			while ($r = mysql_fetch_assoc($res)) {
				$total_work = 0;
				for ( $i = 1; $i <= 31; $i++ ) {
					$total_work+= $r["work".$i];
				}
				$tmp = $total_work;

				$this->workMonthTotals[$r["yearmonth"]] = $tmp;
			}
			mysql_free_result($res);
	}

	// TODOEXPLAIN
	public function getTimecardId() {
		return $this->timecard_id;
	}

	// TODOEXPLAIN
	public function getLengthOfWorkday( $yyyymm,  $weekday) {
		return 0;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\ntimecard #: " . $this->oEmployee->getTimecardId() . "\n";
	}
}
