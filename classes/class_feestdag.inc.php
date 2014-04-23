<?php 
// modified: 2012-12-02

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__DIR__) . "/sites/default/settings.php";
require_once "class_db.inc.php";

class class_feestdag {
    private $id;
    private $date;
    private $description;

	// TODOEXPLAIN
	function class_feestdag($id) {
		$this->id = $id;
		$this->date = '';
		$this->description = '';

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		global $dbhandleTimecard;

		$query = "SELECT * FROM Feestdagen WHERE ID=" . $this->getId();

		$res = mysql_query($query, $dbhandleTimecard);
		if ($r = mysql_fetch_assoc($res)) {
			$this->date = $r["datum"];
			$this->description = $r["omschrijving"];
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
}
