<?php 
// modified: 2014-06-03

class class_mysql {
	private $server;
	private $user;
	private $password;
	private $database;
	private $conn;

	// TODOEXPLAIN
	function class_mysql($settings, $prefix) {
		$this->server = $settings[$prefix . "_server"];
		$this->user = $settings[$prefix . "_user"];
		$this->password = $settings[$prefix . "_password"];
		$this->database = $settings[$prefix . "_database"];
	}

	// TODOEXPLAIN
	function connect() {
		$this->conn = mysql_connect($this->server, $this->user, $this->password);
		if ( !$this->conn ) {
			die('Error: 174154 - Could not connect to MySql server<br>' . mysql_error());
		}

		// connect to database
		mysql_select_db($this->database, $this->conn);

		return 1;
	}

	// TODOEXPLAIN
	function close() {
		@mysql_close($this->conn);
	}

	// TODOEXPLAIN
	function getConnection() {
		return $this->conn;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\nserver: " . $this->server . "\nuser: " . $this->user . "\ndatabase: " . $this->database . "\n";
	}
}
