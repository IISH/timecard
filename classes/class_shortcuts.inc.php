<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";
require_once "class_date.inc.php";

class class_shortcuts {
	private $oTimecardUser;
	private $settings;
	private $oDate;
	private $oProtimeUser;

	function __construct($oTimecardUser, $settings, $oDate) {
		$this->oTimecardUser = $oTimecardUser;
		$this->settings = $settings;
		$this->oDate = $oDate;

		$this->oProtimeUser = new class_protime_user($this->oTimecardUser->getProtimeId(), $this->settings);
	}

	function getEnabledShortcuts( $type ) {
		global $dbConn;

		$arr = array();

		//
		$query = "SELECT Workcodes.Description, UserCreatedQuickAdds.ID, UserCreatedQuickAdds.Employee, UserCreatedQuickAdds.WorkCode, UserCreatedQuickAdds.WorkDescription, UserCreatedQuickAdds.isvisible, UserCreatedQuickAdds.isdeleted, UserCreatedQuickAdds.TimeInMinutes, UserCreatedQuickAdds.onNewAutoSave, UserCreatedQuickAdds.extra_explanation
FROM UserCreatedQuickAdds INNER JOIN Workcodes ON UserCreatedQuickAdds.WorkCode = Workcodes.ID
WHERE ::CRITERIUM::
AND UserCreatedQuickAdds.isvisible = 1 AND UserCreatedQuickAdds.isdeleted = 0 AND Workcodes.isdisabled = 0
AND (
( Workcodes.isdisabled = 0 AND (Workcodes.lastdate IS NULL OR Workcodes.lastdate = '' OR Workcodes.lastdate >= '" . $this->oDate->get("Y-m-d") . "') )
)
ORDER BY Workcodes.Description, UserCreatedQuickAdds.WorkDescription, UserCreatedQuickAdds.TimeInMinutes DESC ";

		if ( $type == 'user' ) {
			$criterium = " UserCreatedQuickAdds.Employee = ::USER:: ";
		} elseif ( $type == 'department' ) {
			$criterium = " ( UserCreatedQuickAdds.Employee IS NULL AND UserCreatedQuickAdds.Department = ::DEPARTMENT:: ) ";
		} else {
			$criterium = "";
		}

		$query = str_replace('::CRITERIUM::', $criterium, $query);
		$query = str_replace('::USER::', $this->oTimecardUser->getTimecardId(), $query);

		//
		$department = $this->oProtimeUser->getDepartmentId();
		if ( $department == '' ) {
			$department = 0;
		}
		$query = str_replace('::DEPARTMENT::', $department, $query);

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = $this->createItem($row);
		}

		return $arr;
	}

	function getAllShortcuts() {
		global $dbConn;

		$arr = array();

		//
		$query = "SELECT vw_UserCreatedQuickAdds.ID, vw_UserCreatedQuickAdds.Description, vw_UserCreatedQuickAdds.WorkCode, vw_UserCreatedQuickAdds.TimeInMinutes, vw_UserCreatedQuickAdds.onNewAutoSave, vw_UserCreatedQuickAdds.WorkDescription, vw_UserCreatedQuickAdds.isvisible, vw_UserCreatedQuickAdds.Projectnummer, vw_UserCreatedQuickAdds.extra_explanation
FROM vw_UserCreatedQuickAdds INNER JOIN Workcodes ON vw_UserCreatedQuickAdds.WorkCode = Workcodes.ID
WHERE vw_UserCreatedQuickAdds.Employee=::USER::
AND (
( Workcodes.isdisabled = 0 AND (Workcodes.lastdate IS NULL OR Workcodes.lastdate = '' OR Workcodes.lastdate >= '" . $this->oDate->get("Y-m-d") . "') )
)
ORDER BY Projectnummer, Description, TimeInMinutes DESC, WorkDescription ";

		$query = str_replace('::USER::', $this->oTimecardUser->getTimecardId(), $query);
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
		$item["projectname"] = $row["Description"];
		$item["projectnr"] = $row["WorkCode"];
		$item["minutes"] = $row["TimeInMinutes"];
		$item["autosave"] = $row["onNewAutoSave"];
		$item["description"] = $row["WorkDescription"];
		$item["isvisible"] = $row["isvisible"];
		$item["projectnummer"] = ( isset($row["Projectnummer"]) ? $row["Projectnummer"] : '' );
		$item["extra_explanation"] = $row["extra_explanation"];

		return $item;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\nuser id: " . $this->oTimecardUser->getTimecardId() . "\ndate: " . $this->oDate->get("Y-m-d") . "\n";
	}
}
