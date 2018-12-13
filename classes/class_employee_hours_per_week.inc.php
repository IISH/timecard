<?php

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_employee_hours_per_week {
	private $oEmployee;
	private $year;
	private $hours_per_week;
	private $hours_per_week_text;
	private $last_refresh;

	function __construct( $oEmployee, $year ) {
		$this->oEmployee = $oEmployee;
		$this->year = $year;
		$this->initValues( true );
	}

	public function getHoursPerWeek() {
		return $this->hours_per_week;
	}

	public function getHoursPerWeekText() {
		return $this->hours_per_week_text;
	}

	public function getLastRefresh() {
		return $this->last_refresh;
	}

	private function initValues( $recursive = false ) {
		global $dbConn;

		$isRecordFound = false;

		$query = "SELECT * FROM Employee_Cache_Hours_Per_Week WHERE EmployeeID={$this->oEmployee->getTimecardId()} AND year={$this->year}";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		if ( $row = $stmt->fetch() ) {
			$this->hours_per_week = $row['hours_per_week'];
			$this->hours_per_week_text = $row['hours_per_week_text'];
			$this->last_refresh = $row['last_refresh'];

			$isRecordFound = true;
		}

		if ( $recursive && ( !$isRecordFound || date(Settings::get("timeStampRefreshLowPriority")) != $this->last_refresh ) ) {
			$oRefresh = new class_refresh_employee_hours_per_week($this->oEmployee, $this->year);
			$oRefresh->refresh( false );

			//
			$this->initValues( false );
		}
	}

	public function refresh( $force_refresh = false ) {
		$oRefresh = new class_refresh_employee_hours_per_week($this->oEmployee, $this->year);
		$oRefresh->refresh( $force_refresh );

		//
		$this->initValues( false );
	}
}