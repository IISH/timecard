<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_workhours {
	private $settings;
	private $dbConn;

	private $id;
	private $employeeId;
	private $dateWorked;
	private $workCode;
	private $workDescription;
	private $isDeleted;
	private $timeInMinutes;
	private $daily_automatic_addition_id;
	private $is_time_fixed;

	function __construct($id) {
		global $dbConn;
		$this->dbConn = $dbConn;

		$this->id = $id;
		$this->employeeId = 0;
		$this->dateWorked = '';
		$this->workCode = 0;
		$this->workDescription = '';
		$this->isDeleted = 0;
		$this->timeInMinutes = 0;
		$this->daily_automatic_addition_id = NULL;
		$this->is_time_fixed = 0;

		$this->initValues();
	}

	private function initValues() {
		if ( $this->getId() > 0 ) {
			$query = "SELECT * FROM Workhours WHERE ID=" . $this->getId();

			$stmt = $this->dbConn->prepare($query);
			$stmt->execute();
			if ( $r = $stmt->fetch() ) {
				$this->employeeId = $r["Employee"];
				$this->dateWorked = $r["DateWorked"];
				$this->workCode = $r["WorkCode"];
				$this->workDescription = $r["WorkDescription"];
				$this->isDeleted = $r["isdeleted"];
				$this->timeInMinutes = $r["TimeInMinutes"];
				$this->daily_automatic_addition_id = $r["daily_automatic_addition_id"];
				$this->is_time_fixed = $r["fixed_time"];
			}
		}
	}

	function save() {
		if ( $this->id == 0 ) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	private function update() {
		// update record
		$query = "UPDATE Workhours
		SET Employee = ::EID::
		, DateWorked = '::DATE::'
		, WorkCode = ::WORKID::
		, WorkDescription = '::WORKDESCRIPTION::'
		, isdeleted = ::ISDELETED::
		, TimeInMinutes = ::TIMEINMINUTES::
		, daily_automatic_addition_id = ::DAAID::
		, fixed_time = ::ISTIMEFIXED::
		WHERE ID=::ID::";

		$query = str_replace("::ID::", $this->getId(), $query);
		$query = str_replace("::EID::", $this->employeeId, $query);
		$query = str_replace("::DATE::", $this->dateWorked, $query);
		$query = str_replace("::WORKID::", $this->workCode, $query);
		$query = str_replace("::WORKDESCRIPTION::", $this->workDescription, $query);
		$query = str_replace("::ISDELETED::", $this->isDeleted, $query);
		$query = str_replace("::TIMEINMINUTES::", $this->timeInMinutes, $query);
		$query = str_replace("::DAAID::", $this->daily_automatic_addition_id, $query);
		$query = str_replace("::ISTIMEFIXED::", $this->is_time_fixed, $query);

		$stmt = $this->dbConn->prepare($query);
		$stmt->execute();
	}

	function delete() {
		if ( $this->getId() > 0 ) {
			$query = "DELETE FROM Workhours WHERE ID=::ID:: ";

			$query = str_replace("::ID::", $this->getId(), $query);

			$stmt = $this->dbConn->prepare($query);
			$stmt->execute();
		}
	}

	private function insert() {
		// insert new record
		$query = "INSERT INTO Workhours (Employee, DateWorked, WorkCode, WorkDescription, isdeleted, TimeInMinutes, daily_automatic_addition_id, fixed_time) VALUES (::EID::, '::DATE::', ::WORKID::, '::WORKDESCRIPTION::', ::ISDELETED::, ::TIMEINMINUTES::, ::DAAID::, ::ISTIMEFIXED:: ) ";
		$query = str_replace("::EID::", $this->employeeId, $query);
		$query = str_replace("::DATE::", $this->dateWorked, $query);
		$query = str_replace("::WORKID::", $this->workCode, $query);
		$query = str_replace("::WORKDESCRIPTION::", $this->workDescription, $query);
		$query = str_replace("::ISDELETED::", $this->isDeleted, $query);
		$query = str_replace("::TIMEINMINUTES::", $this->timeInMinutes, $query);
		$query = str_replace("::DAAID::", $this->daily_automatic_addition_id, $query);
		$query = str_replace("::ISTIMEFIXED::", $this->is_time_fixed, $query);

		$stmt = $this->dbConn->prepare($query);
		$stmt->execute();

		// set new id
		$id = $this->dbConn->lastInsertId();
		$this->setId( $id );
	}

	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		$this->id = $id;
	}

	public function getEmployeeId() {
		return $this->employeeId;
	}

	public function setEmployeeId( $id ) {
		$this->employeeId = $id;
	}

	public function getDateWorked() {
		return $this->dateWorked;
	}

	public function setDateWorked( $date ) {
		$this->dateWorked = $date;
	}

	public function getWorkCode() {
		return $this->workCode;
	}

	public function setWorkCode( $code ) {
		$this->workCode = $code;
	}

	public function getWorkDescription() {
		return $this->workDescription;
	}

	public function setWorkDescription( $description ) {
		$this->workDescription = $description;
	}

	public function getIsDeleted() {
		return $this->isDeleted;
	}

	public function setIsDeleted( $code ) {
		$this->isDeleted = $code;
	}

	public function getTimeInMinutes() {
		return $this->timeInMinutes;
	}

	public function setTimeInMinutes( $minutes ) {
		$this->timeInMinutes = $minutes;
	}

	public function getDailyAutomaticAdditionId() {
		return $this->daily_automatic_addition_id;
	}

	public function setDailyAutomaticAdditionId( $id ) {
		$this->daily_automatic_addition_id = $id;
	}

	public function getIsTimeFixed() {
		return $this->is_time_fixed;
	}

	public function setIsTimeFixed( $value ) {
		$this->is_time_fixed = $value;
	}

	public static function findDaaRecord( $employeeId, $daaId, $oDate ) {
		global $dbConn;

		$recordId = 0;

		$query = "SELECT ID FROM Workhours WHERE Employee=" . $employeeId->getTimecardId() . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND daily_automatic_addition_id=" . $daaId;
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
			$recordId = $r["ID"];
		}

		return new class_workhours($recordId);
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}
}
