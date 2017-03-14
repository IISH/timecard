<?php 
class class_datetime {

	// todoprotectie
	public static function getQueryDate() {
		$d = ( isset($_GET["d"]) ? $_GET["d"] : '' );
		if ( $d == '' ) {
			$d = date("Ymd");
		}

		return $d;
	}

	public static function ConvertTimeInMinutesToTimeInHoursAndMinutes($time) {
		if ( $time == '' ) {
			$time = 0;
		}

		$isNegative = ( $time < 0 );

		if ( $isNegative ) {
			$time *= -1;
		}

		$h = floor($time/60);
		$time = $h . ":" . substr("0" . ( $time - ( $h * 60 ) ), -2);

		if ( $isNegative ) {
			$time = '-' . $time;
		}

		return $time;
	}

	public static function check_date($date) {

		// snelle controle of maand/dag niet te hoog/laag zijn
		if ( $date["m"] < 1 ) {
			$date["m"] = 1;
		} else if ( $date["m"] > 12 ) {
			$date["m"] = 12;
		}
		if ( $date["d"] < 1 ) {
			$date["d"] = 1;
		} else if ( $date["d"] > 31 ) {
			$date["d"] = 31;
		}

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		// controleer of datum wel klopt
		$oD = new class_date($date["y"], $date["m"], 1);
		$max_day = $oD->getNumberOfDaysInMonth();
		if ( $max_day < $date["d"] ) {
			$date["d"] = $max_day;
		}

		return $date;
	}

	public static function get_date($protect, $field = 'd') {
		if ( $field == '' ) {
			$field = 'd';
		}

		//
		if ( !isset( $_GET[$field] ) ) {
			$_GET[$field] = date("Ymd");
		}

		// get d value via regexp, is dus altijd 6 lang (of leeg)
		$d = $protect->request('get', $field, "/^20[0-1][0-9][0-1][0-9][0-3][0-9]$/");
		if ( $d != '' ) {
			$year = substr($d, 0, 4);
			$month = substr($d, 4, 2);
			$day = substr($d, 6, 2);

			// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

			// snelle controle of maand/dag niet te hoog/laag zijn
			if ( $month < 1 ) {
				$month = 1;
			} else if ( $month > 12 ) {
				$month = 12;
			}
			if ( $day < 1 ) {
				$day = 1;
			} else if ( $day > 31 ) {
				$day = 31;
			}

			// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

			// controleer of datum wel klopt
			$oD = new class_date($year, $month);
			$max_day = $oD->getNumberOfDaysInMonth();
			if ( $max_day < $day ) {
				$day = $max_day;
			}

			// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

			$new_d = $year . substr("0" . $month, -2) . substr("0" . $day, -2);
			if ( $d != $new_d ) {
				Header("Location: " . $_SERVER["SCRIPT_NAME"] . "?d=" . $new_d);
				die('go to <a href="' . $_SERVER["SCRIPT_NAME"] . '?d=' . $new_d . '">' . $new_d . '</a>');
			}
		} else {
			$year = date("Y");
			$month = date("m");
			$day = date("d");
		}

		$date["d"] = $day;
		$date["m"] = $month;
		$date["y"] = $year;

		$date["Ym"] = $year . substr("0".$date["m"],-2);
		$date["Ymd"] = $year . substr("0".$date["m"],-2) . substr("0".$date["d"],-2);

		return $date;
	}

	public static function formatDatePresentOrNot($date) {
		$retval = trim($date);

		if ( $retval != '' ) {
			if ( $retval == date("Ymd") ) {
				$retval = 'Today';
			} else {
				// 
				if ( strlen($retval) == 8 ) {
					$year = substr($retval, 0, 4);
					$month = substr($retval, 4, 2);
					$day = substr($retval, 6, 2);

					$dag = mktime(0, 0, 0, $month, $day, $year);

					$retval = date("D", $dag) . " " . date("d", $dag) . " " . date("M", $dag);
				}
			}
		}

		return $retval;
	}

	public static function is_future( $oDate ) {
		$isFuture = false;

		if ( $oDate->get("Ymd") > date("Ymd") ) {
			$isFuture = true;
		}

		return $isFuture;
	}

	public static function formatDateAsString($datum) {
		$retval = $datum["y"];
		$retval .= str_pad( $datum["m"], 2, '0', STR_PAD_LEFT);
		$retval .= str_pad( $datum["d"], 2, '0', STR_PAD_LEFT);

		return $retval;
	}
}
