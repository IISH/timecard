<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_shortcut {
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

	function __construct($id) {
		global $settings;
		$this->settings = $settings;

		if ( trim($id) == '' || $id < 0) {
			$id = 0;
		}

		$this->id = $id;

		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		if ( $this->getId() > 0 ) {
			$query = "SELECT * FROM UserCreatedQuickAdds WHERE ID=" . $this->getId();

			$stmt = $dbConn->prepare($query);
			$stmt->execute();
			if ( $r = $stmt->fetch() ) {
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
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getWorkCode() {
		return $this->workCode;
	}

	public function getWorkDescription() {
		return $this->workDescription;
	}

	public function getTimeInMinutes() {
		return $this->timeInMinutes;
	}

	public function getOnNewAutoSave() {
		return $this->onNewAutoSave;
	}

	public function getExtraExplanation() {
		return $this->extraExplanation;
	}

	public function getDepartment() {
		return $this->department;
	}

	public function getEmployee() {
		return $this->oEmployee;
	}

	public function getIsVisible() {
		return $this->isVisible;
	}

	public function getIsDeleted() {
		return $this->isDeleted;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\nShortcut ID: " . $this->getId() . "\n";
	}
}
