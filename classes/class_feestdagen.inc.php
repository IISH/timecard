<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";
require_once "class_feestdag.inc.php";

class class_feestdagen {

	public static function getNationalHolidays() {
		global $databases;
		$arr = array();

		$oConn = new class_mysql($databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Feestdagen WHERE datum>=" . date("Y") . " AND isdeleted=0 ORDER BY datum ";

		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			$arr[] = new class_feestdag( $r["ID"] );
		}
		mysql_free_result($res);

		return $arr;
	}
}
