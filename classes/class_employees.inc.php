<?php 
// version: 2012-12-27

class class_employees {
	var $connection_settings;

	// TODOEXPLAIN
	function class_employees($connection_settings) {
		$this->connection_settings = $connection_settings;
	}

	function getListOfDiEmployees( $year ) {
		global $dbhandleTimecard;

		$arr = array();

		// reset values
		$query = "SELECT ID FROM Employees WHERE isdisabled=0 OR lastyear>=" . $year . " ORDER BY LastName, FirstName ";
		$result = mysql_query($query, $dbhandleTimecard);
		while ($row = mysql_fetch_assoc($result)) {
			$oEmployee = new class_employee($row["ID"], $this->connection_settings);
			$arr[] = $oEmployee;
		}
		mysql_free_result($result);

		return $arr;
	}
}
