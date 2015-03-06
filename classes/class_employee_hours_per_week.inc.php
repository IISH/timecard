<?php
// modified: 2014-06-03

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_employee_hours_per_week {
	private $oEmployee;
	private $databases;
	private $year;
	private $protime_id = 0;
	private $hoursdoublefield = '';
	private $is_disabled = 0;
	private $lastname = '';
	private $firstname = '';
	private $hoursperweek = 0;
	private $daysperweek = 0;
	private $authorisation = array();
	private $show_jira_field = false;
	private $allow_additions_starting_date = '';
	private $projects = array();
	private $hours_per_week = 666;
	private $hours_per_week_text = 'xxx';

	// TODOEXPLAIN
	function class_employee_hours_per_week($oEmployee, $year) {
		global $databases;

//		if ( $timecard_id == '' || $timecard_id < -1 ) {
//			$timecard_id = 0;
//		}

		$this->oEmployee = $oEmployee;
		$this->year = $year;
		$this->databases = $databases;

		$this->initValues();
	}

	public function getHoursPerWeek() {
		return $this->hours_per_week;
	}

	public function getHoursPerWeekText() {
		return $this->hours_per_week_text;
	}

	private function initValues() {

	}
}