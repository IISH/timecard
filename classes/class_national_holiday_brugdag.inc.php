<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_national_holiday_brugdag {
	private $databases;
	private $year;
	private $low = array();

	// TODOEXPLAIN
	function class_national_holiday_brugdag( $year ) {
		global $databases;
		$this->databases = $databases;

		$this->year = $year;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT * FROM Feestdagen WHERE datum LIKE '{$this->year}-%' AND isdeleted=0 ORDER BY datum ASC ";

			$res = mysql_query($query, $oConn->getConnection());
			while ($r = mysql_fetch_assoc($res)) {

				$dayOfWeek = $r["DAYNR"];
				if ( $dayOfWeek == '7' ) {
					$dayOfWeek = '0';
				}

				$this->low[] = array('date' => $r["datum"], 'description' => $r['omschrijving'], 'vooreigenrekening' => $r['vooreigenrekening'], 'dayofweek' => $dayOfWeek);
			}
			mysql_free_result($res);
	}

	// TODOEXPLAIN
	public function getAll() {
		return $this->low;
	}

	// TODOEXPLAIN
	public function getBrugdagen() {
		$arr = array();

		foreach ( $this->low as $item ) {
			if ( $item['vooreigenrekening'] == 1 ) {
				$arr[] = $item;
			}
		}

		return $arr;
	}

	// TODOEXPLAIN
	public function getNationalHolidays() {
		$arr = array();

		foreach ( $this->low as $item ) {
			if ( $item['vooreigenrekening'] == 0 ) {
				$arr[] = $item;
			}
		}

		return $arr;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\nProtime #: " . $this->oEmployee->getProtimeId() . "\n";
	}
}
