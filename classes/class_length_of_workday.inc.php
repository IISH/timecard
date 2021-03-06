<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_length_of_workday {
	private $oEmployee;
	private $low = array();

	function __construct( $oEmployee ) {
		$this->oEmployee = $oEmployee;
		$this->initValues();
	}

	private function initValues() {
		global $dbConn;
		if ( $this->oEmployee->getProtimeId() > 0 ) {
			$query = "
SELECT protime_lnk_curric_profile.DATEFROM, MOD(CAST(protime_cyc_dp.DAYNR AS UNSIGNED),7) AS DAG, SUM(protime_dayprog.NORM)/count(*) AS HOEVEEL
FROM protime_lnk_curric_profile
	LEFT JOIN protime_cyc_dp ON protime_lnk_curric_profile.PROFILE = protime_cyc_dp.CYCLIQ
	LEFT JOIN protime_dayprog ON protime_cyc_dp.DAYPROG = protime_dayprog.DAYPROG
WHERE PROFILETYPE='4'
			AND PERSNR = '" . $this->oEmployee->getProtimeId() . "'
GROUP BY protime_lnk_curric_profile.DATEFROM, MOD(CAST(protime_cyc_dp.DAYNR AS UNSIGNED),7)
ORDER BY protime_lnk_curric_profile.DATEFROM DESC, MOD(CAST(protime_cyc_dp.DAYNR AS UNSIGNED),7) ASC
";

			$stmt = $dbConn->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $r) {
				$dayOfWeek = $r["DAG"];
				if ( $dayOfWeek == '7' ) {
					$dayOfWeek = '0';
				}

				$this->low[] = array('datefrom' => $r["DATEFROM"], 'daynr' => $dayOfWeek, 'norm' => $r["HOEVEEL"]);
			}
		}
	}

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

	public function getLengthOfWorkdayInHours( $date,  $weekday ) {
		return $this->getLengthOfWorkdayInMinutes( $date,  $weekday )/60.0;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\nProtime #: " . $this->oEmployee->getProtimeId() . "\n";
	}
}
