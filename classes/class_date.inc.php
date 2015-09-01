<?php 
class class_date {
	private $date;

	// TODOEXPLAIN
	function __construct( $year, $month = 1, $day = 1, $hours = 1, $minutes = 1, $seconds = 1 ) {
		$this->date = mktime((int)$hours, (int)$minutes, (int)$seconds, (int)$month, (int)$day, (int)$year);
	}

	// TODOEXPLAIN
	function get( $format = 'Ymd' ) {
		if ( $format == '' ) {
			$format = 'Ymd';
		}

		return date($format, $this->date);
	}

	// TODOEXPLAIN
	function getNumberOfDaysInMonth() {
		return $this->get('t');
	}

	// TODOEXPLAIN
	function isLeapYear() {
		return $this->get('L');
	}

	// TODOEXPLAIN
	function getFirstMonthInQuarter() {
		$firstMonthInQuarter = false;

		switch ( $this->get('n') ) {
			case 1:
			case 2:
			case 3:
				$firstMonthInQuarter = 1;
				break;

			case 4:
			case 5:
			case 6:
				$firstMonthInQuarter = 4;
				break;

			case 7:
			case 8:
			case 9:
				$firstMonthInQuarter = 7;
				break;

			case 10:
			case 11:
			case 12:
				$firstMonthInQuarter = 10;
				break;
		}

		return $firstMonthInQuarter;
	}

	// TODOEXPLAIN
	function getLastMonthInQuarter() {
		$lastMonthInQuarter = false;

		switch ( $this->get('n') ) {
			case 1:
			case 2:
			case 3:
				$lastMonthInQuarter = 3;
				break;

			case 4:
			case 5:
			case 6:
				$lastMonthInQuarter = 6;
				break;

			case 7:
			case 8:
			case 9:
				$lastMonthInQuarter = 9;
				break;

			case 10:
			case 11:
			case 12:
				$lastMonthInQuarter = 12;
				break;
		}

		return $lastMonthInQuarter;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\ndate: " . $this->date . "\n";
	}
}
