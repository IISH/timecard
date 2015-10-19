<?php
class class_misc02 {

	public static function getMisc02() {
		global $databases;

		$ret = array();

		$ret[] = array('Name', 'User Rights');

		$oConn = new class_mysql($databases['default']);
		$success = $oConn->connect();

		//
		$query = "
SELECT LongCode, authorisation
FROM Employee_Authorisation
INNER JOIN Employees ON Employee_Authorisation.EmployeeID = Employees.ID
ORDER BY authorisation, LongCode
";

		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$ret[] = array($row["LongCode"], $row["authorisation"] );
			}
			mysql_free_result($result);
		}

		return $ret;
	}
}
