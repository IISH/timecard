<?php
// modified: 2014-09-19

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_workhours_static {
	public static function getWorkhoursPerEmployeeGrouped($projectid, $startdate, $enddate) {
		global $settings, $databases;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$arr = array();

		$query = "SELECT Employee, SUM(TimeInMinutes) AS AantalMinuten FROM Workhours INNER JOIN vw_Employees ON Workhours.Employee = vw_Employees.ID WHERE WorkCode=$projectid AND DateWorked>='$startdate' AND DateWorked<='$enddate' GROUP BY Employee ORDER BY FIRSTNAME, NAME ";
		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$arrPerson = array();
			$arrPerson["employee"] = new class_employee($r["Employee"], $settings);
			$arrPerson["timeinminutes"] = $r["AantalMinuten"];
			$arr[] = $arrPerson;
		}

		return $arr;
	}
}