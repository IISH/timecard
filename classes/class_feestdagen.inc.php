<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";
require_once "class_feestdag.inc.php";

class class_feestdagen {

	public static function getNationalHolidays() {
		global $dbConn;
		$arr = array();

		$query = "SELECT * FROM Feestdagen WHERE datum>=" . date("Y") . " AND isdeleted=0 ORDER BY datum ";
		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[] = new class_feestdag( $row["ID"] );
		}

		return $arr;
	}
}
