<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";
require_once "class_date.inc.php";

class class_recentlyused {
	private $user;
	private $oDate;

	function __construct($user, $settings, $oDate) {
		if ( $user == '' ) {
			$user = 0;
		}
		$this->user = $user;
		$this->oDate = $oDate;
	}

	function getRecentlyUsed() {
		global $dbConn;

		$arr = array();

		$query = "SELECT Workcodes.ID, Workcodes.Description
FROM Workhours INNER JOIN Workcodes on Workhours.WorkCode=Workcodes.ID
WHERE YEAR(Workhours.DateWorked)>=::CURRENTYEAR:: AND Workhours.Employee=::USERID:: 
AND Workcodes.isdisabled = 0
AND ( Workcodes.lastdate IS NULL OR Workcodes.lastdate = '' OR Workcodes.lastdate >= '" . $this->oDate->get("Y-m-d") . "' )
GROUP BY Workcodes.ID, Workcodes.Description
ORDER BY Workcodes.Description ";

		$query = str_replace('::CURRENTYEAR::', date("Y"), $query);
		$query = str_replace('::USERID::', $this->user, $query);

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = $this->createItem($row);
		}

		return $arr;
	}

	private function createItem( $row ) {
		$item = array();

		$item["id"] = $row["ID"];
		$item["description"] = $row["Description"];

		return $item;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\nuser: " . $this->user . "\ndate: " . $this->oDate->get("Y-m-d") . "\n";
	}
}
