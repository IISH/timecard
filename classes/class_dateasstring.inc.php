<?php 
// modified: 2014-06-04

require_once dirname(__FILE__) . "/class_date.inc.php";

class class_dateasstring extends class_date {
	private $date;

	// TODOEXPLAIN
	function __construct( $date ) {
		$this->date = $date;

		$year = 1;
		$month = 1;
		$day = 1;
		$hour = 0;
		$minutes = 0;
		$seconds = 0;

		if ( strlen($date . "") > 4 ) {
			$year = (int)(substr($date, 0, 4));
		}

		if ( strlen($date . "") >= 6 ) {
			$month = (int)(substr($date, 4, 2));
		}

		if ( strlen($date . "") >= 8 ) {
			$day = (int)(substr($date, 6, 2));
		}

		parent::__construct($year, $month, $day, $hour, $minutes, $seconds);
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\ndate: " . $this->date . "\n";
	}
}
