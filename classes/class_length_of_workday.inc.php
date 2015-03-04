<?php
// modified: 2015-03-04

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class LengthOfWorkday {
	private $settings;

	private $protime_id;
	private $low = array();

	// TODOEXPLAIN
	function LengthOfWorkday($protime_id) {
		global $databases;
		$this->databases = $databases;

		$this->protime_id = $protime_id;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		if ( $this->getId() > 0 ) {
			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT PROTIME_LNK_CURRIC_PROFILE.DATEFROM, PROTIME_CYC_DP.DAYNR, PROTIME_DAYPROG.NORM
FROM PROTIME_LNK_CURRIC_PROFILE
	LEFT JOIN PROTIME_CYC_DP
		ON PROTIME_LNK_CURRIC_PROFILE.PROFILE = PROTIME_CYC_DP.CYCLIQ
	LEFT JOIN PROTIME_DAYPROG
		ON PROTIME_CYC_DP.DAYPROG = PROTIME_DAYPROG.DAYPROG
WHERE PROFILETYPE='4'
			AND PERSNR = '" . $this->protime_id . "'
ORDER BY DATEFROM DESC, PROTIME_CYC_DP.DAYNR ASC";

			$res = mysql_query($query, $oConn->getConnection());
			while ($r = mysql_fetch_assoc($res)) {
				$this->low[] = array('datefrom' => $r["DATEFROM"], 'daynr' => $r["DAYNR"], 'norm' => $r["NORM"]);
			}
			mysql_free_result($res);
		}
	}

	// TODOEXPLAIN
	public function getProtimeId() {
		return $this->protime_id;
	}

	// TODOEXPLAIN
	public function getLengthOfWorkday( $yyyymm,  $weekday) {
		return 0;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\nprotime #: " . $this->protime_id . "\n";
	}
}
