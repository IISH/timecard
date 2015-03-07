<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";
require_once "class_date.inc.php";

class class_shortcuts {
	private $user;
	private $databases;
	private $oDate;

	// TODOEXPLAIN
	function class_shortcuts($user, $settings, $oDate) {
		global $databases;

		if ( $user == '' ) {
			$user = 0;
		}
		$this->user = $user;
		$this->databases = $databases;
		$this->oDate = $oDate;
	}

	// TODOEXPLAIN
	function getEnabledShortcuts() {
		$arr = array();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// TODOTODO
		$query = "SELECT Workcodes.Description, UserCreatedQuickAdds.ID, UserCreatedQuickAdds.Employee, UserCreatedQuickAdds.WorkCode, UserCreatedQuickAdds.WorkDescription, UserCreatedQuickAdds.isvisible, UserCreatedQuickAdds.isdeleted, UserCreatedQuickAdds.TimeInMinutes, UserCreatedQuickAdds.onNewAutoSave
FROM UserCreatedQuickAdds INNER JOIN Workcodes ON UserCreatedQuickAdds.WorkCode = Workcodes.ID
WHERE UserCreatedQuickAdds.Employee = ::USER:: AND UserCreatedQuickAdds.isvisible = 1 AND UserCreatedQuickAdds.isdeleted = 0 AND Workcodes.isdisabled = 0
AND (
( Workcodes.isdisabled = 0 AND (Workcodes.lastdate IS NULL OR Workcodes.lastdate = '' OR Workcodes.lastdate >= '" . $this->oDate->get("Y-m-d") . "') )
)
ORDER BY Workcodes.Description, UserCreatedQuickAdds.TimeInMinutes DESC ";
//AND Workcodes.show_in_selectlist = 1
		$query = str_replace('::USER::', $this->user, $query);

		$result = mysql_query($query, $oConn->getConnection());
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[] = $this->createItem($row);
			}
			mysql_free_result($result);

		}

		return $arr;
	}

	// TODOEXPLAIN
	function getAllShortcuts() {
		$arr = array();

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		// TODOTODO
		$query = "SELECT vw_UserCreatedQuickAdds.ID, vw_UserCreatedQuickAdds.Description, vw_UserCreatedQuickAdds.WorkCode, vw_UserCreatedQuickAdds.TimeInMinutes, vw_UserCreatedQuickAdds.onNewAutoSave, vw_UserCreatedQuickAdds.WorkDescription, vw_UserCreatedQuickAdds.isvisible, vw_UserCreatedQuickAdds.Projectnummer
FROM vw_UserCreatedQuickAdds INNER JOIN Workcodes ON vw_UserCreatedQuickAdds.WorkCode = Workcodes.ID
WHERE vw_UserCreatedQuickAdds.Employee=::USER::
AND (
( Workcodes.isdisabled = 0 AND (Workcodes.lastdate IS NULL OR Workcodes.lastdate = '' OR Workcodes.lastdate >= '" . $this->oDate->get("Y-m-d") . "') )
)
ORDER BY Projectnummer, Description, TimeInMinutes DESC ";
//AND Workcodes.show_in_selectlist = 1
		$query = str_replace('::USER::', $this->user, $query);

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
		$item["projectname"] = $row["Description"];
		$item["projectnr"] = $row["WorkCode"];
		$item["minutes"] = $row["TimeInMinutes"];
		$item["autosave"] = $row["onNewAutoSave"];
		$item["description"] = $row["WorkDescription"];
		$item["isvisible"] = $row["isvisible"];
		$item["projectnummer"] = $row["Projectnummer"];

		return $item;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\nuser: " . $this->user . "\ndate: " . $this->oDate->get("Y-m-d") . "\n";
	}
}
