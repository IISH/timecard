<?php 
// modified: 2012-12-27

class class_employees {
    private $settings;

	// TODOEXPLAIN
	function class_employees($settings) {
		$this->settings = $settings;
	}

	function getListOfDiEmployees( $year ) {
		global $dbhandleTimecard;

		$arr = array();

		// reset values
		$query = "SELECT ID FROM Employees WHERE isdisabled=0 OR lastyear>=" . $year . " ORDER BY LastName, FirstName ";
		$result = mysql_query($query, $dbhandleTimecard);
		while ($row = mysql_fetch_assoc($result)) {
			$oEmployee = new class_employee($row["ID"], $this->settings);
			$arr[] = $oEmployee;
		}
		mysql_free_result($result);

		return $arr;
	}
}
