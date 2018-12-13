<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";
require_once "class_project.inc.php";

class class_project_static {
	public static function getEnabledProjectsWithAProjectleader() {
		global $dbConn;

		$arr = array();

		$query = "SELECT Workcodes.ID FROM Workcodes INNER JOIN vw_Employees ON Workcodes.projectleader = vw_Employees.ID WHERE Workcodes.isdisabled=0 AND vw_Employees.isdisabled=0 AND Workcodes.projectleader>0 ";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = new class_project($row["ID"]);
		}

		return $arr;
	}
}