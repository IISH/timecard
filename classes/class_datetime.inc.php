<?php 
// modified: 2012-12-27

class class_datetime {

	// TODOEXPLAIN
	function class_datetime() {
	}

	// TODOEXPLAIN
	// todo protectie
	function getQueryDate() {
		$d = $_GET["d"];
		if ( $d == '' ) {
			$d = date("Ymd");
		}

		return $d;
	}

	// TODOEXPLAIN
	function ConvertTimeInMinutesToTimeInHoursAndMinutes($time) {
		if ( $time == '' ) {
			$time = 0;
		}

		$h = floor($time/60);
		$time = $h . ":" . substr("0" . ( $time - ( $h * 60 ) ), -2);

		return $time;
	}

	// TODOEXPLAIN
	function check_date($date) {

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

	// TODOEXPLAIN
	function get_date($protect, $field = 'd') {
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

	// TODOEXPLAIN
	function formatDatePresentOrNot($date) {
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

	// TODOEXPLAIN
	function is_legacy($oDate, $max = 1) {
		$isLegacy = false;

		if ( $max < 0 ) {
			$max = 0;
		}

		if ( $oDate->get("Y") < ( date("Y") - $max ) ) {
			$isLegacy = true;
		}

		return $isLegacy;
	}

	// TODOEXPLAIN
	function is_future( $oDate ) {
		$isFuture = false;

		if ( $oDate->get("Ymd") > date("Ymd") ) {
			$isFuture = true;
		}

		return $isFuture;
	}

	// TODOEXPLAIN
	function formatDateAsString($datum) {
		$retval = $datum["y"];
		$retval .= substr('0' . $datum["m"], -2);
		$retval .= substr('0' . $datum["d"], -2);

		return $retval;
	}

	// TODOEXPLAIN
	function formatDate($date) {
		$retval = trim($date);

		if ( $retval != '' && $retval != '0' ) {
			// 
			if ( strlen($retval) == 8 ) {
				$year = substr($retval, 0, 4);
				$month = substr($retval, 4, 2);
				$day = substr($retval, 6, 2);
				$dag = mktime(0, 0, 0, $month, $day, $year);
				$retval = date("d", $dag) . '-' . date("m", $dag) . '-' . date("Y", $dag);
			}
		} else {
			$retval = '?';
		}

		return $retval;
	}
}
?>