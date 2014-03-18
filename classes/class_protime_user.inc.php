<?php 
// version: 2013-09-24

class class_protime_user {
	var $protime_id = 0;
	var $connection_settings;
	var $firstname = '';
	var $lastname = '';

	// TODOEXPLAIN
	function class_protime_user($protime_id, $connection_settings) {
		if ( $protime_id == '' || $protime_id < -1 ) {
			$protime_id = 0;
		}

		$this->protime_id = $protime_id;
		$this->connection_settings = $connection_settings;

		if ( $protime_id > 0 ) {
			$this->getProtimeValues();
		}
	}

	// TODOEXPLAIN
	function getProtimeValues() {
		global $dbhandleProtime;

		// reset values
		$query = "SELECT * FROM CURRIC WHERE PERSNR=" . $this->protime_id;
		$resultReset = mssql_query($query, $dbhandleProtime);
		if ($row = mssql_fetch_assoc($resultReset)) {

			$this->lastname = $row["NAME"];
			$this->firstname = $row["FIRSTNAME"];

		}
		mssql_free_result($resultReset);
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
}
?>