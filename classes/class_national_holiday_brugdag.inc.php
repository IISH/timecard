<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_national_holiday_brugdag {
	private $year;
	private $low = array();

	function __construct( $year ) {
		$this->year = $year;
		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		$query = "SELECT * FROM Feestdagen WHERE datum LIKE '{$this->year}-%' AND isdeleted=0 ORDER BY datum ASC ";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $r) {

			$dayOfWeek = $r["DAYNR"];
			if ( $dayOfWeek == '7' ) {
				$dayOfWeek = '0';
			}

			$this->low[] = array('date' => $r["datum"], 'description' => $r['omschrijving'], 'vooreigenrekening' => $r['vooreigenrekening'], 'dayofweek' => $dayOfWeek);
		}
	}

	public function getAll() {
		return $this->low;
	}

	public function getBrugdagen() {
		$arr = array();

		foreach ( $this->low as $item ) {
			if ( $item['vooreigenrekening'] == 1 ) {
				$arr[] = $item;
			}
		}

		return $arr;
	}

	public function getNationalHolidays() {
		$arr = array();

		foreach ( $this->low as $item ) {
			if ( $item['vooreigenrekening'] == 0 ) {
				$arr[] = $item;
			}
		}

		return $arr;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\nProtime #: " . $this->oEmployee->getProtimeId() . "\n";
	}
}
