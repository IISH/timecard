<?php
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/class_tcdatetime.inc.php";
require_once dirname(__FILE__) . "/_misc_functions.inc.php";

class class_refresh_employee_hours_for_planning {
	private $oEmployee;
	private $year;
	private $databases;
	private $totalHoursPerWeek;
	private $totalHoursPerWeekText;
	private $isNew;

	private $oLow;
	private $oNationalHolidayBrugdag;

	private $workPerDag = array();
	private $nationalHolidayPerDag = array();
	private $brugdagPerDag = array();

	// TODOEXPLAIN
	function class_refresh_employee_hours_for_planning( $oEmployee, $year ) {
		global $databases;
		$this->databases = $databases;

		$this->oEmployee = $oEmployee;
		$this->year = $year;

		$this->isNew = true;

		$this->oLow = new class_length_of_workday( $oEmployee );
		$this->oNationalHolidayBrugdag = new class_national_holiday_brugdag( $year );
}

// TODOEXPLAIN
public function refresh( $force_refresh = false ) {

		for ( $i = 1; $i <= 12; $i++ ) {
			unset($this->workPerDag);
			$this->workPerDag = array();

			unset($this->nationalHolidayPerDag);
			$this->nationalHolidayPerDag = array();

			unset($this->brugdagPerDag);
			$this->brugdagPerDag = array();

			// get last refresh time
			$lastRefreshTime = $this->getLastRefreshTime($i);
			if ( $lastRefreshTime == '' ) {
				$this->isNew = true;
			} else {
				$this->isNew = false;
			}

			// if time stamp differs with time stamp template then it is too old and should be refreshed
			if ( $lastRefreshTime != date( class_settings::getSetting("timeStampRefreshLowPriority") ) ) {
				$force_refresh = true;
			}

			if ( $force_refresh ) {
//				// get current total hours per week
//				$oEmployeeHoursPerDayStarting = new class_employee_hours_per_day_starting($this->oEmployee, $this->year);
//				$this->totalHoursPerWeek = $oEmployeeHoursPerDayStarting->getCurrentTotalHoursPerWeek();

//				// get text for all hours per week until (including) the first one of a previous year
//				$text = '';
//				foreach ( $oEmployeeHoursPerDayStarting->getStartDayTotals( true ) as $element ) {
//					$text .= "Starting " . $element['date'] . ' : ' . hoursLeft_formatNumber($element['minutes']/60.0,1,true) . " hours per week\n";
//				}
//echo str_replace("\n", '<br>', $text);

				for ( $iDag = 1; $iDag <= 31; $iDag++ ) {
					$concatDatum = $this->year . '-' . substr('0'.$i,-2) . '-' . substr('0'.$iDag,-2);

					$oDatum = new TCDateTime();
					$oDatum->setFromString($concatDatum, 'Y-m-d');

					// protection against 2015-02-31
					if ( $concatDatum == $oDatum->getToString('Y-m-d') ) {
						$dayOfWeek = $oDatum->getToString('w');
						$length = $this->oLow->getLengthOfWorkdayInHours( $oDatum->getToString('Ymd'), $dayOfWeek );
					} else {
						$length = 0;
					}

					$this->workPerDag[ $iDag."" ] = $length;
				}
//echo count($this->workPerDag) . " ++++<br>";;
//echo "Month $i<br><pre>";
//print_r( $this->workPerDag );
//echo '</pre>';
				foreach ( $this->oNationalHolidayBrugdag->getAll() as $item ) {
					if ( substr( $item['date'], 0, 8) == $this->year . '-' . substr('0'.$i,-2) . '-' ) {
						$oDatum = new TCDateTime();
						$oDatum->setFromString($item['date'], 'Y-m-d');
						$dayOfWeek = $oDatum->getToString('w');
						// TODO verwijderen de check of dag 1 .. 5 ???
						if ( $dayOfWeek >=1 && $dayOfWeek <= 5 ) {
							$length = $this->oLow->getLengthOfWorkdayInHours( $oDatum->getToString('Ymd'), $dayOfWeek );
							if ( $length > 0 ) {
								$dag = $oDatum->getToString('j');

								if ( $item['vooreigenrekening'] == 0 ) {

									if ( isset( $this->nationalHolidayPerDag[ $dag ] ) ) {
										$this->nationalHolidayPerDag[ $dag ] += $length;
									} else {
										$this->nationalHolidayPerDag[ $dag ] = $length;
									}

								} else {

									if ( isset( $this->brugdagPerDag[ $dag ] ) ) {
										$this->brugdagPerDag[ $dag ] += $length;
									} else {
										$this->brugdagPerDag[ $dag ] = $length;
									}

								}


							}
						}
					}
				}

				$this->saveRecord( $i );
			}
		}
	}

	// TODOEXPLAIN
	private function saveRecord( $month ) {
		if ( $this->isNew ) {
			$this->newRecord( $month );
		} else {
			$this->updateRecord( $month );
		}
	}

	// TODOEXPLAIN
	private function newRecord($month) {
		$month2 = substr('0'.$month,-2);

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$curtime = date( class_settings::getSetting("timeStampRefreshLowPriority") );
//		$totalHoursPerWeekText = addslashes($this->totalHoursPerWeekText);

		$query = "
INSERT INTO `Employee_Cache_Planning` (
	EmployeeID
	, yearmonth
	, last_refresh
) VALUES (
	{$this->oEmployee->getTimecardId()}
	, '{$this->year}-{$month2}'
	, '{$curtime}'
)";

		$result = mysql_query($query, $oConn->getConnection());

		// TODO temp disabled
		//$this->updateRecordNationalHoliday( $month );
		//$this->updateRecordBrugdag( $month );
	}

	// TODOEXPLAIN
	private function updateRecord($month) {
		$month2 = substr('0'.$month,-2);

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$curtime = date( class_settings::getSetting("timeStampRefreshLowPriority") );
//		$totalHoursPerWeekText = addslashes($this->totalHoursPerWeekText);

		$total = 0;

		$query = "
UPDATE `Employee_Cache_Planning`
SET
";

		$separator = '';
		for ( $i = 1; $i <= 31; $i ++ ) {
			$aantalUur = 0;

			if ( isset( $this->workPerDag[$i] ) ) {
				$aantalUur = $this->workPerDag[$i];
			}

			$total += $aantalUur;

//			if ( $aantalUur > 0 ) {
//				$aantalNationalHolidays++;
//			}

			$query .= $separator . "`work$i` = " . ($aantalUur*1.0);
			$separator = ', ';
		}

		$query .= "
	, `total_work` = '{$total}'
	, `last_refresh` = '{$curtime}'
WHERE `EmployeeID` = {$this->oEmployee->getTimecardId()} AND `yearmonth` = '{$this->year}-{$month2}'";

//echo "<br><br>" . $query . " ++<br>";

		$result = mysql_query($query, $oConn->getConnection());

		// TODO temp disabled
		//$this->updateRecordNationalHoliday( $month );
		//$this->updateRecordBrugdag( $month );
	}

	// TODOEXPLAIN
	private function updateRecordNationalHoliday( $month ) {
		$month2 = substr('0'.$month,-2);
		$aantalNationalHolidays = 0;
		$total = 0;

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "
UPDATE `Employee_Cache_Planning`
SET
";

		$separator = '';
		for ( $i = 1; $i <= 31; $i ++ ) {
			$aantalUur = 0;

			if ( isset( $this->nationalHolidayPerDag[$i] ) ) {
				$aantalUur = $this->nationalHolidayPerDag[$i];
			}

			$total += $aantalUur;

			if ( $aantalUur > 0 ) {
				$aantalNationalHolidays++;
			}

			$query .= $separator . "`nationalholiday$i` = " . ($aantalUur*1.0);
			$separator = ', ';
		}

		$query .= "
	, `total_nationalholiday` = '{$total}'
	, `number_of_nationalholidays` = {$aantalNationalHolidays}
WHERE `EmployeeID` = {$this->oEmployee->getTimecardId()} AND `yearmonth` = '{$this->year}-{$month2}' ";

		$result = mysql_query($query, $oConn->getConnection());
	}

	// TODOEXPLAIN
	private function updateRecordBrugdag( $month ) {
		$month2 = substr('0'.$month,-2);
		$aantalBrugdagen = 0;
		$total = 0;

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "
UPDATE `Employee_Cache_Planning`
SET
";

		$separator = '';
		for ( $i = 1; $i <= 31; $i ++ ) {
			$aantalUur = 0;

			if ( isset( $this->brugdagPerDag[$i] ) ) {
				$aantalUur = $this->brugdagPerDag[$i];
			}

			$total += $aantalUur;

			if ( $aantalUur > 0 ) {
				$aantalBrugdagen++;
			}

			$query .= $separator . "`brugdag$i` = " . ($aantalUur*1.0);
			$separator = ', ';
		}

		$query .= "
	, `total_brugdag` = '{$total}'
	, `number_of_brugdagen` = {$aantalBrugdagen}
WHERE `EmployeeID` = {$this->oEmployee->getTimecardId()} AND `yearmonth` = '{$this->year}-{$month2}' ";

		$result = mysql_query($query, $oConn->getConnection());
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\Employee Timecard #: " . $this->oEmployee->getTimecardId() . "\n";
	}

	// TODOEXPLAIN
	public function getLastRefreshTime( $month ) {
		$lastRefreshTime = '';

		$oConn = new class_mysql($this->databases['default']);
		$oConn->connect();

		$query = "SELECT * FROM Employee_Cache_Planning WHERE EmployeeID=" . $this->oEmployee->getTimecardId() . " AND yearmonth='" . $this->year . '-' . substr('0'.$month,-2) . "' ";

		$result = mysql_query($query, $oConn->getConnection());
		if ($row = mysql_fetch_assoc($result)) {
			$lastRefreshTime = $row['last_refresh'];
		}
		mysql_free_result($result);

		return $lastRefreshTime;
	}
}
