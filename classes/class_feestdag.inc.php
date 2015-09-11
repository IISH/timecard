<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_feestdag {
	private $id;
	private $date;
	private $description;
	private $vooreigenrekening;
	private $isdeleted;

	function class_feestdag($id) {
		$this->id = $id;
		$this->date = '';
		$this->description = '';
		$this->vooreigenrekening = 0;
		$this->isdeleted = 0;

		$this->initValues();
	}

	private function initValues() {
		global $databases;

		$oConn = new class_mysql($databases['default']);
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

	public function getId() {
		return $this->id;
	}

	public function getDate() {
		return $this->date;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getVooreigenrekening() {
		return $this->vooreigenrekening;
	}

	public function getIsdeleted() {
		return $this->isdeleted;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n#: " . $this->id . "\n";
	}
}
