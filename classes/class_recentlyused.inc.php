<?php 
// modified: 2012-12-02

require_once dirname(__DIR__) . "/sites/default/settings.php";
require_once "class_db.inc.php";

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

		$oConn = new class_db($this->settings, 'timecard');
		$oConn->connect();

		$query = "SELECT Workcodes2011.ID, Workcodes2011.Description 
FROM Workhours INNER JOIN Workcodes2011 on Workhours.WorkCode=Workcodes2011.ID
WHERE YEAR(Workhours.DateWorked)>=::CURRENTYEAR:: AND Workhours.Employee=::USERID:: 
AND Workcodes2011.isdisabled = 0
AND Workcodes2011.show_in_selectlist = 1 
AND ( Workcodes2011.enddate IS NULL OR Workcodes2011.enddate = '' OR Workcodes2011.enddate >= '" . $this->oDate->get("Y-m-d") . "' )
GROUP BY Workcodes2011.ID, Workcodes2011.Description 
ORDER BY Workcodes2011.Description ";

		$query = str_replace('::CURRENTYEAR::', 2014, $query);
		$query = str_replace('::USERID::', $this->user, $query);

		$result = mysql_query($query, $oConn->connection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[] = $this->createItem($row);
			}
			mysql_free_result($result);

		}

		$oConn->disconnect();
		return $arr;
	}

	function createItem( $row ) {
		$item = array();

		$item["id"] = $row["ID"];
		$item["description"] = $row["Description"];

		return $item;
	}
}
