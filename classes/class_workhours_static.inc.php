<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_workhours_static {

	public static function getWorkhoursPerEmployeeGroupedFromTill($projectid, $startdate, $enddate) {
		global $settings, $dbConn;

		$arr = array();

		$query = "SELECT Employee, SUM(TimeInMinutes) AS AantalMinuten FROM Workhours INNER JOIN vw_Employees ON Workhours.Employee = vw_Employees.ID WHERE WorkCode=$projectid AND DateWorked>='$startdate' AND DateWorked<='$enddate' GROUP BY Employee ORDER BY FIRSTNAME, NAME ";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {
			$arrPerson = array();
			$arrPerson["employee"] = new class_employee($r["Employee"], $settings);
			$arrPerson["timeinminutes"] = $r["AantalMinuten"];
			$arr[] = $arrPerson;
		}

		return $arr;
	}

	public static function getWorkhoursPerEmployeeGroupedMonth($projectid, $year_month) {
		global $settings, $dbConn;

		$arr = array();

		$query = "SELECT Employee, SUM(TimeInMinutes) AS AantalMinuten FROM Workhours INNER JOIN vw_Employees ON Workhours.Employee = vw_Employees.ID WHERE WorkCode=$projectid AND DateWorked LIKE '${year_month}-%' GROUP BY Employee ORDER BY FIRSTNAME, NAME ";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {
			$arrPerson = array();
			$arrPerson["employee"] = new class_employee($r["Employee"], $settings);
			$arrPerson["timeinminutes"] = $r["AantalMinuten"];
			$arr[] = $arrPerson;
		}

		return $arr;
	}
}
