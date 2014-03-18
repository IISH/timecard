<?php 
// version: 2012-11-30

class class_db {
	var $m_server;
	var $m_user;
	var $m_password;
	var $m_database;
	var $conn;

	// TODOEXPLAIN
	function class_db($connection_settings, $prefix = "timecard") {
		$this->m_server = $connection_settings[$prefix . "_server"];
		$this->m_user = $connection_settings[$prefix . "_user"];
		$this->m_password = $connection_settings[$prefix . "_password"];
		$this->m_database = $connection_settings[$prefix . "_database"];
	}

	// TODOEXPLAIN
	function connect() {
		$this->conn = mysql_connect($this->m_server, $this->m_user, $this->m_password);
		if ( !$this->conn ) {
			die('Error: 100 - Could not connect: ' . mysql_error());
		}

		// connect to database
		mysql_select_db($this->m_database, $this->conn);

		return 1;
	}

	// TODOEXPLAIN
	function disconnect() {
		mysql_close($this->conn);
	}

	function connection() {
		return $this->conn;
	}
}
?>