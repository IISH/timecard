<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_workhours_static {

	// TODOEXPLAIN
	public static function getWorkhoursPerEmployeeGroupedFromTill($projectid, $startdate, $enddate) {
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

	// TODOEXPLAIN
	public static function getWorkhoursPerEmployeeGroupedMonth($projectid, $year_month) {
		global $settings, $databases;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$arr = array();

		$query = "SELECT Employee, SUM(TimeInMinutes) AS AantalMinuten FROM Workhours INNER JOIN vw_Employees ON Workhours.Employee = vw_Employees.ID WHERE WorkCode=$projectid AND DateWorked LIKE '${year_month}-%' GROUP BY Employee ORDER BY FIRSTNAME, NAME ";
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