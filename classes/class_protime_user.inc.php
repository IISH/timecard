<?php 
class class_protime_user {
	private $protime_id = 0;
	private $settings;
	private $firstname = '';
	private $lastname = '';
	private $email = '';
	private $department_id = '';

	function __construct($protime_id, $settings) {
		if ( $protime_id == '' || $protime_id < -1 ) {
			$protime_id = 0;
		}

		$this->protime_id = $protime_id;
		$this->settings = $settings;

		if ( $protime_id > 0 ) {
			$this->getProtimeValues();
		}
	}

	function getProtimeValues() {
		global $dbConn;

		// reset values
		$query = "SELECT * FROM protime_curric WHERE PERSNR=" . $this->protime_id;
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		if ( $row = $stmt->fetch() ) {
			$this->lastname = $row["NAME"];
			$this->firstname = $row["FIRSTNAME"];
			$this->email = $row["EMAIL"];
			$this->department_id = $row["DEPART"];
		}
	}

	function getId() {
		return $this->protime_id;
	}

	function getFirstname() {
		return $this->firstname;
	}

	function getLastname() {
		return $this->lastname;
	}

	function getEmail() {
		return $this->email;
	}

	public function getDepartmentId() {
		return $this->department_id;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->protime_id . "\n";
	}
}
