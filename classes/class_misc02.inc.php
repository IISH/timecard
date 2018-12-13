<?php
class class_misc02 {

	public static function getMisc02() {
		global $dbConn;

		$ret = array();

		$ret[] = array('Name', 'User Rights');

		//
		$query = "SELECT LongCodeKnaw, authorisation
FROM Employee_Authorisation
INNER JOIN Employees ON Employee_Authorisation.EmployeeID = Employees.ID
ORDER BY authorisation, LongCodeKnaw
";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret[] = array($row["LongCodeKnaw"], $row["authorisation"] );
		}

		return $ret;
	}
}
