<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";
require_once "class_project.inc.php";

class class_project_static {
	public static function getEnabledProjectsWithAProjectleader() {
		global $databases;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$arr = array();

		$query = "SELECT Workcodes.ID FROM Workcodes INNER JOIN vw_Employees ON Workcodes.projectleader = vw_Employees.ID WHERE Workcodes.isdisabled=0 AND vw_Employees.isdisabled=0 AND Workcodes.projectleader>0 ";
		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$arr[] = new class_project($r["ID"]);
		}

		return $arr;
	}
}