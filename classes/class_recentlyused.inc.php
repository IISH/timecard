<?php 
// modified: 2012-12-02

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";
require_once "class_date.inc.php";

class class_recentlyused {
	private $user;
	private $settings;
	private $oDate;

	// TODOEXPLAIN
	function class_recentlyused($user, $settings, $oDate) {
		if ( $user == '' ) {
			$user = 0;
		}
		$this->user = $user;
		$this->settings = $settings;
		$this->oDate = $oDate;
	}

	// TODOEXPLAIN
	function getRecentlyUsed() {
		$arr = array();

		$oConn = new class_mysql($this->settings, 'timecard');
		$oConn->connect();

		$query = "SELECT Workcodes.ID, Workcodes.Description
FROM Workhours INNER JOIN Workcodes on Workhours.WorkCode=Workcodes.ID
WHERE YEAR(Workhours.DateWorked)>=::CURRENTYEAR:: AND Workhours.Employee=::USERID:: 
AND Workcodes.isdisabled = 0
AND ( Workcodes.lastdate IS NULL OR Workcodes.lastdate = '' OR Workcodes.lastdate >= '" . $this->oDate->get("Y-m-d") . "' )
GROUP BY Workcodes.ID, Workcodes.Description
ORDER BY Workcodes.Description ";
//AND Workcodes.show_in_selectlist = 1

		$query = str_replace('::CURRENTYEAR::', date("Y"), $query);
		$query = str_replace('::USERID::', $this->user, $query);

		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[] = $this->createItem($row);
			}
			mysql_free_result($result);

		}

		return $arr;
	}

	function createItem( $row ) {
		$item = array();

		$item["id"] = $row["ID"];
		$item["description"] = $row["Description"];

		return $item;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\nuser: " . $this->user . "\ndate: " . $this->oDate->get("Y-m-d") . "\n";
	}
}
