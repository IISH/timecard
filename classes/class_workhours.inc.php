<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_workhours {
	private $settings;

	private $id;
	private $employeeId;
	private $dateWorked;
	private $workCode;
	private $workDescription;
	private $isDeleted;
	private $timeInMinutes;
	private $daily_automatic_addition_id;
	private $is_time_fixed;

	// TODOEXPLAIN
	function class_workhours($id) {
		global $databases;
		$this->databases = $databases;

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

	// TODOEXPLAIN
	private function initValues() {
		if ( $this->getId() > 0 ) {
			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT * FROM Workhours WHERE ID=" . $this->getId();

			$res = mysql_query($query, $oConn->getConnection());
			if ($r = mysql_fetch_assoc($res)) {
				$this->employeeId = $r["Employee"];
				$this->dateWorked = $r["DateWorked"];
				$this->workCode = $r["WorkCode"];
				$this->workDescription = $r["WorkDescription"];
				$this->isDeleted = $r["isdeleted"];
				$this->timeInMinutes = $r["TimeInMinutes"];
				$this->daily_automatic_addition_id = $r["daily_automatic_addition_id"];
				$this->is_time_fixed = $r["fixed_time"];
			}
			mysql_free_result($res);
		}
	}

	// TODOEXPLAIN
	function save() {
		if ( $this->id == 0 ) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	// TODOEXPLAIN
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

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		mysql_query($query, $oConn->getConnection());
	}

	// TODOEXPLAIN
	function delete() {
		if ( $this->getId() > 0 ) {
			$query = "DELETE FROM Workhours WHERE ID=::ID:: ";

			$query = str_replace("::ID::", $this->getId(), $query);

			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			mysql_query($query, $oConn->getConnection());
		}
	}

	// TODOEXPLAIN
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

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		mysql_query($query, $oConn->getConnection());

		// set new id
		// todo
		$id = mysql_insert_id();
		$this->setId( $id );
	}

	// TODOEXPLAIN
	public function getId() {
		return $this->id;
	}

	// TODOEXPLAIN
	public function setId( $id ) {
		$this->id = $id;
	}

	// TODOEXPLAIN
	public function getEmployeeId() {
		return $this->employeeId;
	}

	// TODOEXPLAIN
	public function setEmployeeId( $id ) {
		$this->employeeId = $id;
	}

	// TODOEXPLAIN
	public function getDateWorked() {
		return $this->dateWorked;
	}

	// TODOEXPLAIN
	public function setDateWorked( $date ) {
		$this->dateWorked = $date;
	}

	// TODOEXPLAIN
	public function getWorkCode() {
		return $this->workCode;
	}

	// TODOEXPLAIN
	public function setWorkCode( $code ) {
		$this->workCode = $code;
	}

	// TODOEXPLAIN
	public function getWorkDescription() {
		return $this->workDescription;
	}

	// TODOEXPLAIN
	public function setWorkDescription( $description ) {
		$this->workDescription = $description;
	}

	// TODOEXPLAIN
	public function getIsDeleted() {
		return $this->isDeleted;
	}

	// TODOEXPLAIN
	public function setIsDeleted( $code ) {
		$this->isDeleted = $code;
	}

	// TODOEXPLAIN
	public function getTimeInMinutes() {
		return $this->timeInMinutes;
	}

	// TODOEXPLAIN
	public function setTimeInMinutes( $minutes ) {
		$this->timeInMinutes = $minutes;
	}

	// TODOEXPLAIN
	public function getDailyAutomaticAdditionId() {
		return $this->daily_automatic_addition_id;
	}

	// TODOEXPLAIN
	public function setDailyAutomaticAdditionId( $id ) {
		$this->daily_automatic_addition_id = $id;
	}

	// TODOEXPLAIN
	public function getIsTimeFixed() {
		return $this->is_time_fixed;
	}

	// TODOEXPLAIN
	public function setIsTimeFixed( $value ) {
		$this->is_time_fixed = $value;
	}

	// TODOEXPLAIN
	public static function findDaaRecord( $employeeId, $daaId, $oDate ) {
		global $settings, $databases;
		$recordId = 0;

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$query = "SELECT ID FROM Workhours WHERE Employee=" . $employeeId->getTimecardId() . " AND DateWorked LIKE '" . $oDate->get("Y-m-d") . "%' AND daily_automatic_addition_id=" . $daaId;

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$recordId = $r["ID"];
		}
		mysql_free_result($res);

		return new class_workhours($recordId);
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}
}
