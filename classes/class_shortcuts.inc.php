<?php 
// modified: 2012-12-02

require_once dirname(__DIR__) . "/sites/default/settings.inc.php";
require_once "class_db.inc.php";

class class_shortcuts {
    private $user;
    private $settings;
    private $oDate;

	// TODOEXPLAIN
	function class_shortcuts($user, $settings, $oDate) {
		if ( $user == '' ) {
			$user = 0;
		}
		$this->user = $user;
		$this->settings = $settings;
		$this->oDate = $oDate;
	}

	// TODOEXPLAIN
	function getEnabledShortcuts() {
		$arr = array();

		$oConn = new class_db($this->settings);
		$oConn->connect();

		// TODOTODO
		$query = "SELECT Workcodes2011.Description, UserCreatedQuickAdds.ID, UserCreatedQuickAdds.Employee, UserCreatedQuickAdds.WorkCode, UserCreatedQuickAdds.WorkDescription, UserCreatedQuickAdds.isvisible, UserCreatedQuickAdds.isdeleted, UserCreatedQuickAdds.TimeInMinutes, UserCreatedQuickAdds.onNewAutoSave
FROM UserCreatedQuickAdds INNER JOIN Workcodes2011 ON UserCreatedQuickAdds.WorkCode = Workcodes2011.ID
WHERE UserCreatedQuickAdds.Employee = ::USER:: AND UserCreatedQuickAdds.isvisible = 1 AND UserCreatedQuickAdds.isdeleted = 0 AND Workcodes2011.isdisabled = 0
AND (
( Workcodes2011.isdisabled = 0 AND Workcodes2011.show_in_selectlist = 1 AND (Workcodes2011.enddate IS NULL OR Workcodes2011.enddate = '' OR Workcodes2011.enddate >= '" . $this->oDate->get("Y-m-d") . "') )
)
ORDER BY Workcodes2011.Description, UserCreatedQuickAdds.TimeInMinutes DESC ";

		$query = str_replace('::USER::', $this->user, $query);

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

	// TODOEXPLAIN
	function getAllShortcuts() {
		$arr = array();

		$oConn = new class_db($this->settings);
		$oConn->connect();

		// TODOTODO
		$query = "SELECT vw_UserCreatedQuickAdds.ID, vw_UserCreatedQuickAdds.Description, vw_UserCreatedQuickAdds.WorkCode, vw_UserCreatedQuickAdds.TimeInMinutes, vw_UserCreatedQuickAdds.onNewAutoSave, vw_UserCreatedQuickAdds.WorkDescription, vw_UserCreatedQuickAdds.isvisible, vw_UserCreatedQuickAdds.Projectnummer
FROM vw_UserCreatedQuickAdds INNER JOIN Workcodes2011 ON vw_UserCreatedQuickAdds.WorkCode = Workcodes2011.ID
WHERE vw_UserCreatedQuickAdds.Employee=::USER::
AND (
( Workcodes2011.isdisabled = 0 AND Workcodes2011.show_in_selectlist = 1 AND (Workcodes2011.enddate IS NULL OR Workcodes2011.enddate = '' OR Workcodes2011.enddate >= '" . $this->oDate->get("Y-m-d") . "') )
)
ORDER BY Projectnummer, Description, TimeInMinutes DESC ";

		$query = str_replace('::USER::', $this->user, $query);

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
		$item["projectname"] = $row["Description"];
		$item["projectnr"] = $row["WorkCode"];
		$item["minutes"] = $row["TimeInMinutes"];
		$item["autosave"] = $row["onNewAutoSave"];
		$item["description"] = $row["WorkDescription"];
		$item["isvisible"] = $row["isvisible"];
		$item["projectnummer"] = $row["Projectnummer"];

		return $item;
	}
}
