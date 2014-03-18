<?php 
// version: 2013-06-02

require_once("./classes/class_date.inc.php");

class class_dateasstring extends class_date {
	private $date;

	// TODOEXPLAIN
	function __construct( $date ) {
		$year = 1;
		$month = 1;
		$day = 1;
		$hour = 0;
		$minutes = 0;
		$seconds = 0;

		if ( strlen($date . "") > 4 ) {
			$year = substr($date, 0, 4)+0;
		}

		if ( strlen($date . "") >= 6 ) {
			$month = substr($date, 4, 2)+0;
		}

		if ( strlen($date . "") >= 8 ) {
			$day = substr($date, 6, 2)+0;
		}

		parent::__construct($year, $month, $day, $hour, $minutes, $seconds);
	}
}
?>