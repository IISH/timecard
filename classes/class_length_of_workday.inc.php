<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_length_of_workday {
	private $databases;
	private $oEmployee;
	private $low = array();

	// TODOEXPLAIN
	function class_length_of_workday( $oEmployee ) {
		global $databases;
		$this->databases = $databases;

		$this->oEmployee = $oEmployee;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		if ( $this->oEmployee->getProtimeId() > 0 ) {
			$oConn = new class_mysql($this->databases['default']);
			$oConn->connect();

			$query = "SELECT PROTIME_LNK_CURRIC_PROFILE.DATEFROM, PROTIME_CYC_DP.DAYNR, PROTIME_DAYPROG.NORM
FROM PROTIME_LNK_CURRIC_PROFILE
	LEFT JOIN PROTIME_CYC_DP
		ON PROTIME_LNK_CURRIC_PROFILE.PROFILE = PROTIME_CYC_DP.CYCLIQ
	LEFT JOIN PROTIME_DAYPROG
		ON PROTIME_CYC_DP.DAYPROG = PROTIME_DAYPROG.DAYPROG
WHERE PROFILETYPE='4'
			AND PERSNR = '" . $this->oEmployee->getProtimeId() . "'
ORDER BY DATEFROM DESC, PROTIME_CYC_DP.DAYNR ASC";

			$res = mysql_query($query, $oConn->getConnection());
			while ($r = mysql_fetch_assoc($res)) {
				$dayOfWeek = $r["DAYNR"];
				if ( $dayOfWeek == '7' ) {
					$dayOfWeek = '0';
				}

				$this->low[] = array('datefrom' => $r["DATEFROM"], 'daynr' => $dayOfWeek, 'norm' => $r["NORM"]);
			}
			mysql_free_result($res);
		}
	}

	// TODOEXPLAIN
	public function getLengthOfWorkdayInMinutes( $date,  $weekday ) {
		// $date - format yyyymmdd
		// $weekday 0 - sunday, 1 - monday, ..., 6 - saturday
		foreach ( $this->low as $item ) {
			if ( $item['datefrom'] < $date && strval($item['daynr']) == strval($weekday) ) {
				return $item['norm'];
			}
		}

		return 0;
	}

	// TODOEXPLAIN
	public function getLengthOfWorkdayInHours( $date,  $weekday ) {
		return $this->getLengthOfWorkdayInMinutes( $date,  $weekday )/60.0;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\nProtime #: " . $this->oEmployee->getProtimeId() . "\n";
	}
}
