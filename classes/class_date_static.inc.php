<?php
class class_date_static {
	// TODOEXPLAIN
	public static function previousWeekMonday( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		return date("Y-m-d", mktime( 0,0,0, date("m", $d), date("d", $d)-date("N", $d)-6, date("Y", $d) ));
	}

	// TODOEXPLAIN
	public static function previousWeekSunday( $date = '' ) {
		if ( $date == '' ) {
			$date = date("Y-m-d");
		}
		$d = strtotime($date);
		return date("Y-m-d", mktime( 0,0,0, date("m", $d), date("d", $d)-date("N", $d), date("Y", $d) ));
	}
}