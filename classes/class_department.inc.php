<?php
// modified: 2014-09-25

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_department {
	private $settings;

	private $id;
	private $name;
	private $head;
	private $oHead;
	private $enable_weekly_report_mail;
	private $isenabled;
	private $isdeleted;

	// TODOEXPLAIN
	function class_department($id) {
		global $settings;
		$this->settings = $settings;

		$this->id = $id;
		$this->name = '';
		$this->head = '';
		$this->oHead = null;
		$this->enable_weekly_report_mail = 0;
		$this->isenabled = 1;
		$this->isdeleted = 0;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		if ( $this->getId() > 0 ) {
			$oConn = new class_mysql($this->settings, 'timecard');
			$oConn->connect();

			$query = "SELECT * FROM Departments WHERE ID=" . $this->getId();

			$res = mysql_query($query, $oConn->getConnection());
			if ($r = mysql_fetch_assoc($res)) {
				$this->name = $r["name"];
				$this->head = $r["head"];
				$this->enable_weekly_report_mail = $r["enable_weekly_report_mail"];
				$this->isenabled = $r["isenabled"];
				$this->isdeleted = $r["ideleted"];
			}
			mysql_free_result($res);
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getEnableweeklyreportmail() {
		return ( $this->enable_weekly_report_mail == 1 ? true : false);
	}

	public function isEnabled() {
		return ( $this->isenabled == 1 ? true : false);
	}

	public function isDeleted() {
		return ( $this->isdeleted == 1 ? true : false);
	}

	public function getHead() {
		if ( $this->head == '' || $this->head == '0' ) {
			return null;
		} else {
			return new class_employee($this->head, $this->settings);
		}
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\nDepartment: " . $this->getName() . "\n";
	}
}
