<?php 
class class_protime_user {
	private $protime_id = 0;
	private $settings;
	private $firstname = '';
	private $lastname = '';
	private $email = '';

	function class_protime_user($protime_id, $settings) {
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
		// reset values
		$query = "SELECT * FROM protime_curric WHERE PERSNR=" . $this->protime_id;
		$resultReset = mysql_query($query);
		if ($row = mysql_fetch_assoc($resultReset)) {

			$this->lastname = $row["NAME"];
			$this->firstname = $row["FIRSTNAME"];
			$this->email = $row["EMAIL"];

		}
		mysql_free_result($resultReset);
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

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->protime_id . "\n";
	}
}
