<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_employee_vast_werk {
	private $oEmployee;
	private $databases;
	private $year;
	private $vastwerk = array();

	// TODOEXPLAIN
	function class_employee_vast_werk( $oEmployee, $year ) {
		global $databases;

		$this->oEmployee = $oEmployee;
		$this->year = $year;
		$this->databases = $databases;

		$this->initValues();
	}

	// TODOEXPLAIN
	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "
SELECT *
FROM VastWerk
WHERE EmployeeID = {$this->oEmployee->getTimecardId()}
	AND year = '{$this->year}'
	AND isdeleted=0
";

		$result = mysql_query($query, $oConn->getConnection());
		while ($row = mysql_fetch_assoc($result)) {
			$period = $row['period'];
			$hours = $row['hours'];
			$this->vastwerk[] = array( 'period' => $period, 'hours' => $hours );
		}
		mysql_free_result($result);
	}

	// TODOEXPLAIN
	public function getMonthTotal( $month ) {
		$total = 0;
		$month += 0;

		foreach ( $this->vastwerk as $item ) {
			if ( strtolower($item['period']) == 'y' ) {
				$total += $item['hours']/12.0;
			} elseif ( strtolower($item['period']) == 'q' . $this->getQuarterOfMonth($month) )  {
				$total += $item['hours']/3.0;
			} elseif ( strtolower($item['period']) == 'm' . $month ) {
				$total += $item['hours'];
			}
		}

		return $total;
	}

	// TODOEXPLAIN
	private function getQuarterOfMonth( $month ) {
		return (int)(($month+2)/3);
	}
}