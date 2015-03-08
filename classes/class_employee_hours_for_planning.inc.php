<?php 
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_employee_hours_for_planning {
	private $settings;

	private $oEmployee;
	private $year;
	private $workMonthTotals = array();
	private $nationalHolidayMonthTotals = array();
	private $brugdagMonthTotals = array();
	private $number_of_nationalholidays = array();
	private $number_of_brugdagen = array();

	// TODOEXPLAIN
	function class_employee_hours_for_planning( $oEmployee, $year ) {
		global $databases;
		$this->databases = $databases;

		$this->oEmployee = $oEmployee;
		$this->year = $year;

		$this->initValues( true );
	}

	// TODOEXPLAIN
	public function getWorkValue($yyyy_mm) {
		if ( isset($this->workMonthTotals[$yyyy_mm]) ) {
			return $this->workMonthTotals[$yyyy_mm];
		}

		return 0;
	}

	// TODOEXPLAIN
	public function getNationalHolidayValue($yyyy_mm) {
		if ( isset($this->nationalHolidayMonthTotals[$yyyy_mm]) ) {
			return $this->nationalHolidayMonthTotals[$yyyy_mm];
		}

		return 0;
	}

	// TODOEXPLAIN
	public function getBrugdagValue($yyyy_mm) {
		if ( isset($this->brugdagMonthTotals[$yyyy_mm]) ) {
			return $this->brugdagMonthTotals[$yyyy_mm];
		}

		return 0;
	}

	// TODOEXPLAIN
	private function initValues( $recursive = false ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Employee_Cache_Planning WHERE EmployeeId=" . $this->oEmployee->getTimecardId() . " AND yearmonth LIKE '" . $this->year . "-%' ORDER BY yearmonth ";

		$res = mysql_query($query, $oConn->getConnection());
		$recordsFound = 0;
		$areAllLastRefreshOkay = true;
		while ($r = mysql_fetch_assoc($res)) {
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

			if ( date(class_settings::getSetting("timeStampRefreshLowPriority")) != $r['last_refresh'] ) {
				$areAllLastRefreshOkay = false;
			}
		}
		mysql_free_result($res);

//		$areAllLastRefreshOkay = false; // TODO VERWIJDEREN REGEL

		if ( $recursive && ( $recordsFound < 12 || !$areAllLastRefreshOkay ) ) {
			$oRefresh = new class_refresh_employee_hours_for_planning($this->oEmployee, $this->year);
			$oRefresh->refresh( false ); // TODO SET THIS LINE
//			$oRefresh->refresh( true ); // TODO remove this line

			//
			$this->initValues( false );
		}
	}

	// TODOEXPLAIN
	public function getTimecardId() {
		return $this->timecard_id;
	}

	// TODOEXPLAIN
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
