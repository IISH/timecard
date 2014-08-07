<?php 
// modified: 2014-03-19

class class_prevnext {
	private $date;

	// TODOEXPLAIN
	function class_prevnext( $date ) {
		global $settings;

		$this->date = $date;
		$this->settings = $settings;
	}

	// TODOEXPLAIN
	function vorigeVolgendeMaand($date, $richting, $urlDescription = '') {
		$original_date = $date;

		if ( $richting == '-' ) {
			$date["m"] -= 1;
			if ( $date["m"] < 1 ) {
				$date["m"] = 12;
				$date["y"] -= 1;
			}
			$label = '&laquo; ' . $urlDescription;
		} elseif ( $richting == '+' ) {
			$date["m"] += 1;
			if ( $date["m"] > 12 ) {
				$date["m"] = 1;
				$date["y"] += 1;
			}
			$label = $urlDescription . ' &raquo;';
		} else {
			$date["y"] = date("Y");
			$date["m"] = date("m");
			$date["d"] = date("d");
//			$label = date("F") . ' ' . $date["y"];
			$label = 'current month';
		}

		// 
		$date = class_datetime::check_date($date);

		$d = mktime(0, 0, 0, $date["m"], $date["d"], $date["y"]);

		$date["y"] = date("Y", $d);
		$date["m"] = date("m", $d);
		$date["d"] = date("d", $d);
		$date["Ym"] = date("Ym", $d);
		$date["Ymd"] = date("Ymd", $d);

		$only_lable = 0;
		if ( $richting == '' ) {
			if ( date("Ym") == $original_date["y"] . $original_date["m"] ) {
				$only_lable = 1;
			}
		}

		if ( $only_lable == 1 ) {
			$retval = $label;
		} else {
			$alt = 'go to ' . date("F Y", mktime(0, 0, 0, $date["m"], $date["d"], $date["y"]));
			$retval = '<a href="' . GetModifyReturnQueryString('?', 'd', $date["Ymd"]) . '" alt="' . $alt . '" title="' . $alt . '">' . $label . '</a>';
		}

		return $retval;
	}

	// TODOEXPLAIN
	function vorigeVolgendeDag($date, $richting, $urlDescription = '') {
		$original_date = $date;

		if ( $richting == '-' ) {
			$d = mktime(0, 0, 0, $date["m"], $date["d"]-1, $date["y"]);
			$label = '&laquo; ' . $urlDescription;
		} else if ( $richting == '+' ) {
			$d = mktime(0, 0, 0, $date["m"], $date["d"]+1, $date["y"]);
			$label = $urlDescription . ' &raquo;';
		} else {
			$d = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
			$label = 'today';
		}

		$date["y"] = date("Y", $d);
		$date["m"] = date("m", $d);
		$date["d"] = date("d", $d);
		$date["Ym"] = date("Ym", $d);
		$date["Ymd"] = date("Ymd", $d);

		$only_lable = 0;
		if ( $richting == '' ) {
			if ( date("Ymd") == $original_date["y"] . $original_date["m"] . $original_date["d"] ) {
				$only_lable = 1;
			}
		}

		if ( $only_lable == 1 ) {
			$retval = $label;
		} else {
			$alt = 'go to ' . date("D F j, Y", mktime(0, 0, 0, $date["m"], $date["d"], $date["y"]));
			$retval = '<a href="' . GetModifyReturnQueryString('?', 'd', $date["Ymd"]) . '" alt="' . $alt . '" title="' . $alt . '">' . $label . '</a>';
		}

		return $retval;
	}

	// TODOEXPLAIN
	function calculatePrevNextMonth($date) {
		$separator = '&nbsp; ';

		$prev = $this->vorigeVolgendeMaand($date, "-", "prev");
		$current = $this->vorigeVolgendeMaand($date, "");
		$next = $this->vorigeVolgendeMaand($date, "+", "next");

		$retval = 'go to' . $separator . $current . ' or' . $separator . $prev . $separator . $next;

		return $retval;
	}

	// TODOEXPLAIN
	function calculatePrevNextQuarter($date) {
		$separator = '&nbsp; ';

		$prev = $this->vorigeVolgendeQuarter($date, "-", "prev");
		$current = $this->vorigeVolgendeQuarter($date, "");
		$next = $this->vorigeVolgendeQuarter($date, "+", "next");

		$retval = 'go to' . $separator . $current . ' or' . $separator . $prev . $separator . $next;

		return $retval;
	}

	// TODOEXPLAIN
	function calculatePrevNextYear($date) {
		$separator = '&nbsp; ';

		$prev = vorigeVolgendeJaar($date, "-", "prev");
		$current = vorigeVolgendeJaar($date, "");
		$next = vorigeVolgendeJaar($date, "+", "next");

		$retval = 'go to' . $separator . $current . ' or' . $separator . $prev . $separator . $next;

		return $retval;
	}

	// TODOEXPLAIN
	function calculatePrevNextDay($date) {
		$separator = '&nbsp; ';

		$prev = $this->vorigeVolgendeDag($date, "-", "prev");
		$current = $this->vorigeVolgendeDag($date, "");
		$next = $this->vorigeVolgendeDag($date, "+", "next");

		$scriptname = $_SERVER["SCRIPT_NAME"];
		$querystring = $_SERVER["QUERY_STRING"];

		$calendarDiv = "
<script language=\"javascript\">
<!--

// TODOEXPLAIN
function toggleCalendar() {
	var ele = document.getElementById(\"toggleTextCalendar\");
	var text = document.getElementById(\"displayTextCalendar\");
	if(ele.style.display == \"block\") {
		ele.style.display = \"none\";
		text.innerHTML = \"<img src=images/expand2-down.gif>\";
	} else {
		ele.style.display = \"block\";
		text.innerHTML = \"<img src=images/expand2-up.gif>\";
	}
}

var xmlhttpCalendar=false;

if (!xmlhttpCalendar && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttpCalendar = new XMLHttpRequest();
	} catch (e) {
		xmlhttpCalendar=false;
	}
}
if (!xmlhttpCalendar && window.createRequest) {
	try {
		xmlhttpCalendar = window.createRequest();
	} catch (e) {
		xmlhttpCalendar=false;
	}
}

// TODOEXPLAIN
function tcRefreshCalendar( sDate, sOriginalDate ) {
	xmlhttpCalendar.open(\"GET\", \"get_calendar.php?d=\" + sDate + \"&pd=\" + sOriginalDate + \"&s=" . urlencode($scriptname) . "&q=" . urlencode($querystring) . "\", true);
	xmlhttpCalendar.onreadystatechange=function() {
		if (xmlhttpCalendar.readyState==4) {
			document.getElementById('toggleTextCalendar').innerHTML = xmlhttpCalendar.responseText;
		}
	}
	xmlhttpCalendar.send(null);
}
// -->
</script>

<a href=\"javascript:toggleCalendar();\" id=\"displayTextCalendar\" class=\"nolink\" title=\"Show/hide calendar\"><img src=\"images/expand2-down.gif\" border=0></a>
<div id=\"toggleTextCalendar\" class=\"calendarPositioning\"></div>

<script type=\"text/javascript\">
<!--
var sDate = '" . $date["Ymd"] . "';
tcRefreshCalendar(sDate, sDate);
// -->
</script>

</div>
";

		$retval = 'go to' . $separator . $current . $calendarDiv . ' or' . $separator . $prev . $separator . $next;

		return $retval;
	}

	// TODOEXPLAIN
	function getMonthRibbon( $format = "F Y" ) {
		$fields["label"] = date($format, mktime(0, 0, 0, $this->date["m"], $this->date["d"], $this->date["y"]));
		$fields["buttons"] = $this->calculatePrevNextMonth($this->date);

		$design = new class_contentdesign("div_prevnextribbon");
		return fillTemplate($design->getContent(), $fields);
	}

	// TODOEXPLAIN
	function getDayRibbon( $format = "l F j, Y" ) {
		$fields["label"] = date($format, mktime(0, 0, 0, $this->date["m"], $this->date["d"], $this->date["y"]));
		$fields["buttons"] = $this->calculatePrevNextDay($this->date);

		$design = new class_contentdesign("div_prevnextribbon");
		return fillTemplate($design->getContent(), $fields);
	}

	// TODOEXPLAIN
	function getExtraMonthCriterium() {
		$quarter = achterhaalQuarter($this->date);
		$extra_month_criterium = '';

		switch ( $quarter ) {
			case 1:
				$extra_month_criterium = " AND Month(DateWorked) IN (1,2,3) ";
				break;
			case 2:
				$extra_month_criterium = " AND Month(DateWorked) IN (4,5,6) ";
				break;
			case 3:
				$extra_month_criterium = " AND Month(DateWorked) IN (7,8,9) ";
				break;
			case 4:
				$extra_month_criterium = " AND Month(DateWorked) IN (10,11,12) ";
				break;
		}

		return $extra_month_criterium;
	}

	// TODOEXPLAIN
	function getQuarterRibbon( $pretext = '' ) {
		$quarter = achterhaalQuarter($this->date);
		$quarter_label = achterhaalQuarterLabel($quarter, 'F');

		$fields["label"] = $pretext . $quarter_label . date("Y", mktime(0, 0, 0, $this->date["m"], $this->date["d"], $this->date["y"]));
		$fields["buttons"] = $this->calculatePrevNextQuarter($this->date);

		$design = new class_contentdesign("div_prevnextribbon");
		return fillTemplate($design->getContent(), $fields);
	}

	// TODOEXPLAIN
	function getYearRibbon() {
		$fields["label"] = date("Y", mktime(0, 0, 0, $this->date["m"], $this->date["d"], $this->date["y"]));
		$fields["buttons"] = $this->calculatePrevNextYear($this->date);

		$design = new class_contentdesign("div_prevnextribbon");
		return fillTemplate($design->getContent(), $fields);
	}

	// TODOEXPLAIN
	function vorigeVolgendeQuarter($date, $richting, $urlDescription = '') {
		$original_date = $date;
		$original_quarter = achterhaalQuarter($date);

		if ( $richting == '-' ) {
			$date["m"] -= 3;
			if ( $date["m"] < 1 ) {
				$date["m"] += 12;
				$date["y"] -= 1;
			}
			$label = '&laquo; ' . $urlDescription;
		} else if ( $richting == '+' ) {
			$date["m"] += 3;
			if ( $date["m"] > 12 ) {
				$date["m"] -= 12;
				$date["y"] += 1;
			}
			$label = $urlDescription . ' &raquo;';
		} else {
			$date["y"] = date("Y");
			$date["m"] = date("m");
			$date["d"] = date("d");

//			$quarter_label = achterhaalQuarterLabel( achterhaalQuarter($date) , 'F');
//			$label = $quarter_label . ' ' . date("Y");
			$label = 'current quarter';
		}

		// 
		$date = class_datetime::check_date($date);

		$d = mktime(0, 0, 0, $date["m"], $date["d"], $date["y"]);

		$date["y"] = date("Y", $d);
		$date["m"] = date("m", $d);
		$date["d"] = date("d", $d);
		$date["Ym"] = date("Ym", $d);
		$date["Ymd"] = date("Ymd", $d);

		$quarter = achterhaalQuarter($date);
		$quarter_label = achterhaalQuarterLabel($quarter, 'F');

		$only_lable = 0;
		if ( $richting == '' ) {
			if ( date("Y") . $quarter == $original_date["y"] . $original_quarter ) {
				$only_lable = 1;
			}
		}

		if ( $only_lable == 1 ) {
			$retval = $label;
		} else {
			$alt = 'go to ' . $quarter_label . date("Y", mktime(0, 0, 0, $date["m"], $date["d"], $date["y"]));
			$retval = '<a href="' . GetModifyReturnQueryString('?', 'd', $date["Ymd"]) . '" alt="' . $alt . '" title="' . $alt . '">' . $label . '</a>';
		}

		return $retval;
	}

	// TODOEXPLAIN
	public function __toString() {
		return "Class: " . get_class($this) . "\ndate: " . $this->date . "\n";
	}
}
