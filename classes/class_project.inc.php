<?php
// modified: 2014-09-19

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_project {
	private $settings;

	private $id;
	private $description;
	private $projectnummer;
	private $projectleader;
	private $oProjectleader;
	private $enable_weekly_report_mail;

	// TODOEXPLAIN
	function class_project($id) {
		global $settings;
		$this->settings = $settings;

		$this->id = $id;
		$this->description = 0;
		$this->projectnummer = '';
		$this->projectleader = '';
		$this->oProjectleader = null;
		$this->enable_weekly_report_mail = 0;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		if ( $this->getId() > 0 ) {
			$oConn = new class_mysql($this->settings, 'timecard');
			$oConn->connect();

			$query = "SELECT * FROM Workcodes WHERE ID=" . $this->getId();

			$res = mysql_query($query, $oConn->getConnection());
			if ($r = mysql_fetch_assoc($res)) {
				$this->description = $r["Description"];
				$this->projectnummer = $r["Projectnummer"];
				$this->projectleader = $r["projectleader"];
				$this->enable_weekly_report_mail = $r["enable_weekly_report_mail"];
			}
			mysql_free_result($res);
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getProjectnumber() {
		return $this->projectnummer;
	}

	public function getEnableweeklyreportmail() {
		return ( $this->enable_weekly_report_mail == 1 ? true : false);
	}

	public function getProjectleader() {
		if ( $this->projectleader == '' || $this->projectleader == '0' ) {
			return null;
		} else {
			return new class_employee($this->projectleader, $this->settings);
		}
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\nProject: " . $this->getDescription() . "\nProject number: " . $this->getProjectnumber() . "\n";
	}
}
