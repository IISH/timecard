<?php 
// modified: 2012-12-27

class class_hoursperweek {
	private $databases;
	private $hours;
	private $startmonth;
	private $endmonth;

	// TODOEXPLAIN
	function class_hoursperweek($id, $settings) {
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

	// TODOEXPLAIN
	function getHours() {
		return $this->hours;
	}

	// TODOEXPLAIN
	function getStartmonth() {
		return $this->startmonth;
	}

	// TODOEXPLAIN
	function getEndmonth() {
		return $this->endmonth;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}
}
