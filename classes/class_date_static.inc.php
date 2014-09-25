<?php
// modified: 2014-09-19

class class_date_static {
	public static function previousWeekMonday( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		return date("Y-m-d", mktime( 0,0,0, date("m", $d), date("d", $d)-date("N", $d)-6, date("Y", $d) ));
	}

	public static function previousWeekSunday( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		return date("Y-m-d", mktime( 0,0,0, date("m", $d), date("d", $d)-date("N", $d), date("Y", $d) ));
	}

	public static function previousMonthFirstDate( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		return date("Y-m-d", mktime( 0,0,0, date("m", $d)-1, 1, date("Y", $d) ));
	}

	public static function previousMonthLastDate( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		$d2 = mktime( 0,0,0, date("m", $d)-1, 1, date("Y", $d) );
		return date("Y-m-d", mktime( 0,0,0, date("m", $d2), date("t", $d2), date("Y", $d2) ));
	}

	public static function nextMonthFirstDate( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		return date("Y-m-d", mktime( 0,0,0, date("m", $d)+1, 1, date("Y", $d) ));
	}

	public static function nextMonthLastDate( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		$d2 = mktime( 0,0,0, date("m", $d)+1, 1, date("Y", $d) );
		return date("Y-m-d", mktime( 0,0,0, date("m", $d2), date("t", $d2), date("Y", $d2) ));
	}
}