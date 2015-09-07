<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_department {
	private $databases;

	private $id;
	private $name;
	private $head;
	private $oHead;
	private $enable_weekly_report_mail;
	private $isenabled;
	private $isdeleted;

	// TODOEXPLAIN
	function class_department($id) {
		global $databases;
		$this->databases = $databases;

		$this->id = $id;
		$this->name = '';
		$this->head = '';
		$this->oHead = null;
		$this->enable_weekly_report_mail = 0;
		$this->isenabled = 1;
		$this->isdeleted = 0;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		if ( $this->getId() > 0 ) {
			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT * FROM Departments WHERE ID=" . $this->getId();

			$res = mysql_query($query, $oConn->getConnection());
			if ($r = mysql_fetch_assoc($res)) {
				$this->name = $r["name"];
				$this->head = $r["head"];
				$this->enable_weekly_report_mail = $r["enable_weekly_report_mail"];
				$this->isenabled = $r["isenabled"];
				$this->isdeleted = $r["ideleted"];
			}
			mysql_free_result($res);
		}
	}

	// TODOEXPLAIN
	public function getId() {
		return $this->id;
	}

	// TODOEXPLAIN
	public function getName() {
		return $this->name;
	}

	// TODOEXPLAIN
	public function getEnableweeklyreportmail() {
		return ( $this->enable_weekly_report_mail == 1 ? true : false);
	}

	// TODOEXPLAIN
	public function isEnabled() {
		return ( $this->isenabled == 1 ? true : false);
	}

	// TODOEXPLAIN
	public function isDeleted() {
		return ( $this->isdeleted == 1 ? true : false);
	}

	// TODOEXPLAIN
	public function getHead() {
		if ( $this->head == '' || $this->head == '0' ) {
			return null;
		} else {
			return new class_employee($this->head, $this->settings);
		}
	}

	// TODOEXPLAIN
	public function getEmployees() {
		$ret = array();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM DepartmentEmployee INNER JOIN vw_Employees ON DepartmentEmployee.EmployeeID=vw_Employees.ID WHERE DepartmentID=" . $this->id . " AND isdeleted=0 ORDER BY vw_Employees.FIRSTNAME, vw_Employees.NAME ";
		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$ret[] = new class_employee($r["EmployeeID"], $this->settings);
		}
		mysql_free_result($res);

		return $ret;
	}

	// TODOEXPLAIN
	public function getEmployeesAndHours($startdate, $enddate) {
		$ret = array();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "
SELECT DepartmentEmployee.EmployeeID, SUM(Workhours.TimeInMinutes) AS TIMEINMIN
FROM DepartmentEmployee
	INNER JOIN vw_Employees ON DepartmentEmployee.EmployeeID=vw_Employees.ID
	LEFT JOIN Workhours ON DepartmentEmployee.EmployeeID=Workhours.Employee
		AND ( Workhours.DateWorked IS NULL OR ( Workhours.DateWorked >= '$startdate' AND Workhours.DateWorked <= '$enddate'  ) )
WHERE DepartmentEmployee.DepartmentID=" . $this->id . "
	AND DepartmentEmployee.isdeleted=0
GROUP BY DepartmentEmployee.EmployeeID
ORDER BY vw_Employees.FIRSTNAME, vw_Employees.NAME
";
		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$arr = array();
			$arr["employee"] = new class_employee($r["EmployeeID"], $this->settings);
			$arr["timeinminutes"] = $r["TIMEINMIN"];
			$ret[] = $arr;
		}
		mysql_free_result($res);

		return $ret;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\nDepartment: " . $this->getName() . "\n";
	}
}
