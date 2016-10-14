<?php
die('disabled by gcu');

class class_hoursperweek {
	private $databases;
	private $hours;
	private $startmonth;
	private $endmonth;

	function __construct($id, $settings) {
		global $databases;
		$this->databases = $databases;

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// reset values
		$query = "SELECT * FROM HoursPerWeek WHERE ID=" . $id . " ";
		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {
			$this->hours = $row["hoursperweek"];
			$this->startmonth = $row["startmonth"];
			$this->endmonth = $row["endmonth"];
		}
		mysql_free_result($result);
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
