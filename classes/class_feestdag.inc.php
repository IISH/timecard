<?php 
// modified: 2012-12-02

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_feestdag {
	private $id;
	private $date;
	private $description;
	private $vooreigenrekening;
	private $isdeleted;

	// TODOEXPLAIN
	function class_feestdag($id) {
		$this->id = $id;
		$this->date = '';
		$this->description = '';
		$this->vooreigenrekening = 0;
		$this->isdeleted = 0;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		global $settings;

		$oConn = new class_mysql($settings, 'timecard');
		$oConn->connect();

		$query = "SELECT * FROM Feestdagen WHERE ID=" . $this->getId();

		$res = mysql_query($query, $oConn->getConnection());
		if ($r = mysql_fetch_assoc($res)) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
			$this->vooreigenrekening = $r["vooreigenrekening"];
			$this->isdeleted = $r["isdeleted"];
		}
		mysql_free_result($res);
	}

	// TODOEXPLAIN
	public function getId() {
		return $this->id;
	}

	// TODOEXPLAIN
	public function getDate() {
		return $this->date;
	}

	// TODOEXPLAIN
	public function getDescription() {
		return $this->description;
	}

	// TODOEXPLAIN
	public function getVooreigenrekening() {
		return $this->vooreigenrekening;
	}

	// TODOEXPLAIN
	public function getIsdeleted() {
		return $this->isdeleted;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}
}
