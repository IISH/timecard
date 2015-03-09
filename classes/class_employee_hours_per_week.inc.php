<?php

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_employee_hours_per_week {
	private $oEmployee;
	private $databases;
	private $year;
	private $hours_per_week;
	private $hours_per_week_text;
	private $last_refresh;

	// TODOEXPLAIN
	function class_employee_hours_per_week( $oEmployee, $year ) {
		global $databases;

		$this->oEmployee = $oEmployee;
		$this->year = $year;
		$this->databases = $databases;

		// TODO remove refresh
		//$this->refresh( true );

		$this->initValues( true );
	}

	// TODOEXPLAIN
	public function getHoursPerWeek() {
		return $this->hours_per_week;
	}

	// TODOEXPLAIN
	public function getHoursPerWeekText() {
		return $this->hours_per_week_text;
	}

	// TODOEXPLAIN
	public function getLastRefresh() {
		return $this->last_refresh;
	}

	// TODOEXPLAIN
	private function initValues( $recursive = false ) {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$isRecordFound = false;

		$query = "SELECT * FROM Employee_Cache_Hours_Per_Week WHERE EmployeeID={$this->oEmployee->getTimecardId()} AND year={$this->year}";

		$result = mysql_query($query, $oConn->getConnection());
		if ($row = mysql_fetch_assoc($result)) {
			$this->hours_per_week = $row['hours_per_week'];
			$this->hours_per_week_text = $row['hours_per_week_text'];
			$this->last_refresh = $row['last_refresh'];

			$isRecordFound = true;
		}

		if ( $recursive && ( !$isRecordFound || date(class_settings::getSetting("timeStampRefreshLowPriority")) != $this->last_refresh ) ) {
			$oRefresh = new class_refresh_employee_hours_per_week($this->oEmployee, $this->year);
			$oRefresh->refresh( false );

			//
			$this->initValues( false );
		}
	}

	// TODOEXPLAIN
	public function refresh( $force_refresh = false ) {
		$oRefresh = new class_refresh_employee_hours_per_week($this->oEmployee, $this->year);
		$oRefresh->refresh( $force_refresh );

		//
		$this->initValues( false );
	}
}