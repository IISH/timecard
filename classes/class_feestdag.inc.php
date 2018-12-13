<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_feestdag {
	private $id;
	private $date;
	private $description;
	private $vooreigenrekening;
	private $isdeleted;

	function __construct($id) {
		$this->id = $id;
		$this->date = '';
		$this->description = '';
		$this->vooreigenrekening = 0;
		$this->isdeleted = 0;

		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		$query = "SELECT * FROM Feestdagen WHERE ID=" . $this->getId();

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
			$this->vooreigenrekening = $r["vooreigenrekening"];
			$this->isdeleted = $r["isdeleted"];
		}
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
