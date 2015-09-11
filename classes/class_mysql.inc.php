<?php
class class_mysql {
	private $server;
	private $user;
	private $password;
	private $database;
	private $conn;

	function __construct($database) {
		$this->server = $database["host"];
		$this->user = $database["username"];
		$this->password = $database["password"];
		$this->database = $database["database"];
	}

	function connect() {
		$this->conn = mysql_connect($this->server, $this->user, $this->password);
		if ( !$this->conn ) {
			die('Error: 174154 - Could not connect to MySql server: ' . $this->server . "\n" . mysql_error());
		}

		// connect to database
		mysql_select_db($this->database, $this->conn);

		return 1;
	}

	function close() {
		@mysql_close($this->conn);
	}

	function getConnection() {
		return $this->conn;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\nserver: " . $this->server . "\nuser: " . $this->user . "\ndatabase: " . $this->database . "\n";
	}
}
