<?php 
// modified: 2012-12-27

class class_hoursperweek {
    private $connection_settings;
    private $hours;
    private $startmonth;
    private $endmonth;

	// TODOEXPLAIN
	function class_hoursperweek($id, $connection_settings) {
		global $dbhandleTimecard;

		$this->connection_settings = $connection_settings;

		// reset values
		$query = "SELECT * FROM HoursPerWeek WHERE ID=" . $id . " ";
		$result = mysql_query($query, $dbhandleTimecard);
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
}
