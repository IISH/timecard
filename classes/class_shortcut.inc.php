<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_shortcut {
	private $databases;
	private $settings;

	private $id;
	private $employee;
	private $oEmployee;
	private $workCode;
	private $workDescription;
	private $isVisible;
	private $isDeleted;
	private $timeInMinutes;
	private $onNewAutoSave;
	private $extraExplanation;
	private $department;

	// TODOEXPLAIN
	function class_shortcut($id) {
		global $databases, $settings;
		$this->databases = $databases;
		$this->settings = $settings;

		if ( trim($id) == '' || $id < 0) {
			$id = 0;
		}

		$this->id = $id;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		if ( $this->getId() > 0 ) {
			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT * FROM UserCreatedQuickAdds WHERE ID=" . $this->getId();

			$res = mysql_query($query, $oConn->getConnection());
			if ($r = mysql_fetch_assoc($res)) {
				$this->employee = $r["Employee"];
				$this->oEmployee = new class_employee($r["Employee"], $this->settings);
				$this->workCode = $r["WorkCode"];
				$this->workDescription = $r["WorkDescription"];
				$this->isVisible = $r["isvisible"];
				$this->isDeleted = $r["isdeleted"];
				$this->timeInMinutes = $r["TimeInMinutes"];
				$this->onNewAutoSave = $r["onNewAutoSave"];
				$this->extraExplanation = $r["extra_explanation"];
				$this->department = $r["Department"];
			}
			mysql_free_result($res);
		}
	}

	// TODOEXPLAIN
	public function getId() {
		return $this->id;
	}

	// TODOEXPLAIN
	public function getWorkCode() {
		return $this->workCode;
	}

	// TODOEXPLAIN
	public function getWorkDescription() {
		return $this->workDescription;
	}

	// TODOEXPLAIN
	public function getTimeInMinutes() {
		return $this->timeInMinutes;
	}

	// TODOEXPLAIN
	public function getOnNewAutoSave() {
		return $this->onNewAutoSave;
	}

	// TODOEXPLAIN
	public function getExtraExplanation() {
		return $this->extraExplanation;
	}

	// TODOEXPLAIN
	public function getDepartment() {
		return $this->department;
	}

	// TODOEXPLAIN
	public function getEmployee() {
		return $this->oEmployee;
	}

	// TODOEXPLAIN
	public function getIsVisible() {
		return $this->isVisible;
	}

	// TODOEXPLAIN
	public function getIsDeleted() {
		return $this->isDeleted;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\nShortcut ID: " . $this->getId() . "\n";
	}
}
