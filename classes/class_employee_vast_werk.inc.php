<?php
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "pdo.inc.php";

class class_employee_vast_werk {
	private $oEmployee;
	private $year;
	private $vastwerk = array();

	function __construct( $oEmployee, $year ) {
		$this->oEmployee = $oEmployee;
		$this->year = $year;
		$this->initValues();
	}

	private function initValues() {
		global $dbConn;

		$query = "
SELECT *
FROM VastWerk
WHERE EmployeeID = {$this->oEmployee->getTimecardId()}
	AND year = '{$this->year}'
	AND isdeleted=0
";

		$stmt = $dbConn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$period = $row['period'];
			$hours = $row['hours'];
			$this->vastwerk[] = array( 'period' => $period, 'hours' => $hours );
		}
	}

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

	private function getQuarterOfMonth( $month ) {
		return (int)(($month+2)/3);
	}
}