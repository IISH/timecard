<?php 
require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once "class_mysql.inc.php";

class class_project_totals {
	private $databases;

	private $projectId;
	private $year;
	private $arr = array();
	private $ids = array();

	function __construct($projectId, $year) {
		global $databases;
		$this->databases = $databases;

		$this->projectId = $projectId;
		$this->year = $year;

		$this->initValues();
	}

	private function initValues() {
		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$postfix = getTablePostfix( $this->year );

		$query = "
SELECT LEFT(DateWorked, 7) AS YearMonth, WorkCode AS ProjectId, Employees.ID AS TimecardId, ProtimePersNr, NAME, FIRSTNAME, SUM(TimeInMinutes) AS TOTMIN
FROM Workhours$postfix
	INNER JOIN Employees ON Workhours$postfix.Employee = Employees.ID
	INNER JOIN protime_curric ON Employees.ProtimePersNr = protime_curric.PERSNR
WHERE WorkCode = {$this->projectId}
AND Workhours$postfix.isdeleted = 0
AND DateWorked LIKE '{$this->year}%'
GROUP BY LEFT(DateWorked, 7), WorkCode, Employees.ID, ProtimePersNr, NAME, FIRSTNAME
ORDER BY FIRSTNAME, NAME, LEFT(DateWorked, 7)
";

		$res = mysql_query($query, $oConn->getConnection());
		while ($r = mysql_fetch_assoc($res)) {
			//
			$item = new class_project_totals_item();
			$item->setYearMonth( $r['YearMonth'] );
			$item->setProjectId( $r['ProjectId'] );
			$item->setTimeInMinutes( $r['TOTMIN'] );
			$item->setTimecardId( $r['TimecardId'] );
			$item->setProtimePersNr( $r['ProtimePersNr'] );

			//
			if ( !in_array( $r['TimecardId'] , $this->ids ) ) {
				$this->ids[] = $r['TimecardId'];
			}

			//
			$this->arr[] = $item;

		}
		mysql_free_result($res);

	}

	public function getIds() {
		return $this->ids;
	}

	function getHours($timecardId, $year, $month) {
		$totHours = 0;

		foreach ( $this->arr as $item ) {
			if ( $timecardId == $item->getTimecardId() && $year == $item->getYear() && $month == $item->getMonth() ) {
				$totHours += $item->getTimeInHours();
			}
		}

		return $totHours;
	}
}

class class_project_totals_item {
	private $year;
	private $month;
	private $yearMonth;
	private $projectId;
	private $timeInMinutes;
	private $timecardId;
	private $protimePersNr;

	function __construct() {
	}

	//
	public function setYearMonth( $value ) {
		$this->yearMonth = $value;
		$this->year = (int)substr($value, 0, 4);
		$this->month = (int)substr($value, -2);
	}
	public function getYearMonth() {
		return $this->yearMonth;
	}
	public function getYear() {
		return $this->year;
	}
	public function getMonth() {
		return $this->month;
	}

	//
	public function setProjectId( $value ) {
		$this->projectId = $value;
	}
	public function getProjectId() {
		return $this->projectId;
	}

	//
	public function setTimeInMinutes( $value ) {
		$this->timeInMinutes = $value;
	}
	public function getTimeInMinutes() {
		return $this->timeInMinutes;
	}
	public function getTimeInHours() {
		return $this->timeInMinutes/60;
	}

	//
	public function setTimecardId( $value ) {
		$this->timecardId = $value;
	}
	public function getTimecardId() {
		return $this->timecardId;
	}

	//
	public function setProtimePersNr( $value ) {
		$this->protimePersNr = $value;
	}
	public function getProtimePersNr() {
		return $this->protimePersNr;
	}
}
