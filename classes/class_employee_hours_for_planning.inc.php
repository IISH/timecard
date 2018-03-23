<?php 
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_employee_hours_for_planning {
	private $settings;

	private $oEmployee;
	private $year;
	private $workMonthTotals = array();
	private $nationalHolidayMonthTotals = array();
	private $brugdagMonthTotals = array();
	private $number_of_nationalholidays = array();
	private $number_of_brugdagen = array();

	function __construct( $oEmployee, $year ) {
		$this->oEmployee = $oEmployee;
		$this->year = $year;
		$this->initValues( true );
	}

	public function getWorkValue($yyyy_mm) {
		if ( isset($this->workMonthTotals[$yyyy_mm]) ) {
			return $this->workMonthTotals[$yyyy_mm];
		}

		return 0;
	}

	public function getNationalHolidayValue($yyyy_mm) {
		if ( isset($this->nationalHolidayMonthTotals[$yyyy_mm]) ) {
			return $this->nationalHolidayMonthTotals[$yyyy_mm];
		}

		return 0;
	}

	public function getBrugdagValue($yyyy_mm) {
		if ( isset($this->brugdagMonthTotals[$yyyy_mm]) ) {
			return $this->brugdagMonthTotals[$yyyy_mm];
		}

		return 0;
	}

	private function initValues( $recursive = false ) {
		global $dbConn;
		$query = "SELECT * FROM Employee_Cache_Planning WHERE EmployeeId=" . $this->oEmployee->getTimecardId() . " AND yearmonth LIKE '" . $this->year . "-%' ORDER BY yearmonth ";

		$recordsFound = 0;
		$areAllLastRefreshOkay = true;

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {
			$recordsFound++;

			$total_work = 0;
			$total_nationalholiday = 0;
			$total_brugdag = 0;

			for ( $i = 1; $i <= 31; $i++ ) {
				$total_work += $r["work".$i];
				$total_nationalholiday += $r["nationalholiday".$i];
				$total_brugdag += $r["brugdag".$i];
			}

			$this->workMonthTotals[$r["yearmonth"]] = $total_work;
			$this->nationalHolidayMonthTotals[$r["yearmonth"]] = $total_nationalholiday;
			$this->brugdagMonthTotals[$r["yearmonth"]] = $total_brugdag;

			$this->number_of_nationalholidays[$r["yearmonth"]] = $r['number_of_nationalholidays'];
			$this->number_of_brugdagen[$r["yearmonth"]] = $r['number_of_brugdagen'];

			if ( date(Settings::get("timeStampRefreshLowPriority")) != $r['last_refresh'] ) {
				$areAllLastRefreshOkay = false;
			}
		}

		if ( $recursive && ( $recordsFound < 12 || !$areAllLastRefreshOkay ) ) {
			$oRefresh = new class_refresh_employee_hours_for_planning($this->oEmployee, $this->year);
			$oRefresh->refresh( false ); // TODO SET THIS LINE

			//
			$this->initValues( false );
		}
	}

	public function getTimecardId() {
		return $this->timecard_id;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\ntimecard #: " . $this->oEmployee->getTimecardId() . "\n";
	}

	public function getNumberOfNationalHolidays( $yyyy_mm ) {
		if ( isset($this->number_of_nationalholidays[$yyyy_mm]) ) {
			return $this->number_of_nationalholidays[$yyyy_mm];
		}

		return 0;
	}

	public function getNumberOfBrugdagen( $yyyy_mm ) {
		if ( isset($this->number_of_brugdagen[$yyyy_mm]) ) {
			return $this->number_of_brugdagen[$yyyy_mm];
		}

		return 0;
	}
}
