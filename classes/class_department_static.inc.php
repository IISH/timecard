<?php
// modified: 2014-09-19

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";
require_once "class_department.inc.php";

class class_department_static {
	public static function getEnabledDepartmentsWithAHead() {
		global $settings;

		$oConn = new class_mysql($settings, 'timecard');
		$oConn->connect();

		$arr = array();

		$query = "SELECT Departments.ID FROM Departments INNER JOIN vw_Employees ON Departments.head = vw_Employees.ID WHERE Departments.isenabled=1 AND vw_Employees.isdisabled=0 AND Departments.head>0 ";
		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$arr[] = new class_department($r["ID"]);
		}

		return $arr;
	}
}