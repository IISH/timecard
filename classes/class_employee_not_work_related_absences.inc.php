<?php

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_employee_not_work_related_absences {
	private $oEmployee;
	private $databases;
	private $year;
	private $absences = array();

	function __construct( $oEmployee, $year ) {
		global $databases;

		$this->oEmployee = $oEmployee;
		$this->year = $year;
		$this->databases = $databases;

		$this->initValues();
	}

	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "
SELECT SUBSTRING(BOOKDATE,1,6) AS YYYYMM, SHORT_1 AS ABSENCE_DESCRIPTION, SUM(ABSENCE_VALUE) AS TOTAL_IN_MINUTES
FROM protime_absence
	INNER JOIN protime_p_absence ON protime_absence.ABSENCE = protime_p_absence.ABSENCE
WHERE PERSNR = {$this->oEmployee->getProtimeId()}
	AND BOOKDATE LIKE '{$this->year}%'
	AND ABSENCE_VALUE > 0
	AND protime_p_absence.ABSENCE NOT IN ( 13, 18 )
GROUP BY SUBSTRING(BOOKDATE,1,6), SHORT_1
ORDER BY SUBSTRING(BOOKDATE,1,6), SHORT_1
";

		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {
			$yyyymm = $row['YYYYMM'];
			$description = $row['ABSENCE_DESCRIPTION'];
			$total_in_minutes = $row['TOTAL_IN_MINUTES'];
			$this->absences[] = array( 'yyyymm' => $yyyymm, 'description' => $description, 'total_in_minutes' => $total_in_minutes );
		}
		mysql_free_result($result);

//		echo "<pre>";
//		print_r( $this->absences );
//		echo "</pre>";
	}

	public function getTotalInMinutesForSpecifiedMonth( $yyyymm ) {
		$total = 0;

		foreach ( $this->absences as $item ) {
			if ( $yyyymm == $item['yyyymm'] ) {
				$total += $item['total_in_minutes'];
			}
		}

		return $total;
	}

	public function getSummarizationForSpecifiedMonth( $yyyymm ) {
		$summarization = '';

		foreach ( $this->absences as $item ) {
			if ( $yyyymm == $item['yyyymm'] ) {
				$summarization .= $item['description'] . ' : ' . hoursLeft_formatNumber($item['total_in_minutes']/60.0) .  " hours\n";
			}
		}

		return $summarization;
	}

	public function getTotalInHoursForSpecifiedMonth( $yyyymm ) {
		return $this->getTotalInMinutesForSpecifiedMonth( $yyyymm )/60.0;
	}
}