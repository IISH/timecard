<?php
die('deprecated. contact gcu');

class class_hoursperweek {
	private $hours;
	private $startmonth;
	private $endmonth;

	function __construct($id, $settings) {
		global $dbConn;

		// reset values
		$query = "SELECT * FROM HoursPerWeek WHERE ID=" . $id . " ";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$this->hours = $row["hoursperweek"];
			$this->startmonth = $row["startmonth"];
			$this->endmonth = $row["endmonth"];
		}
	}

	function getHours() {
		return $this->hours;
	}

	function getStartmonth() {
		return $this->startmonth;
	}

	function getEndmonth() {
		return $this->endmonth;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}
}
