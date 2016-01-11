<?php

function preprint( $object ) {
	echo '<pre>';
	print_r( $object );
	echo '</pre>';
}


class class_misc {

	public static function convertArrayToHtmlTable( $arr, $class = 'misc' ) {
		$ret = '';

		$counter = 0;
		foreach ( $arr as $row ) {
			$counter++;
			$r = '';

			if ( $counter == 1 ) {
				$start = '<th class="misc">';
				$end = '</th>';
			} else {
				$start = '<td class="misc">';
				$end = '</td>';
			}

			foreach ( $row as $item ) {
				$r .= $start . $item . $end;
			}

			$r = '<tr class=\"misc\">' . $r . '</tr>';

			$ret .= $r;
		}

		if ( $ret != '' ) {
			$ret = "<table class=\"misc\">\n" . $ret . "</table>\n";
		}

		return $ret;
	}

	public static function convertMinutesToHours($value, $zero_value = '0') {
		if ( $value == 0 || $value == '' ) {
			$retval = $zero_value = '0';
		} else {
			$retval = $value*1.0;
			$retval /= 60;
		}

		return $retval;
	}

	function multiplyTag($tag, $code, $start, $end) {
		$ret = '';
		$separator = '';

		for ( $i = $start ; $i <= $end; $i++ ) {
			$ret .= $separator . str_replace($code, $i, $tag);
			$separator = ', ';
		}

		return $ret;
	}

	function PlaceURLParametersInQuery($query) {
		$return_value = $query;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[FLD\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) {
			if ( isset($this->m_form["primarykey"]) && "[FLD:" . $this->m_form["primarykey"] . "]" == $matches[0] ) {
				$return_value = str_replace($matches[0], $this->m_doc_id, $return_value);
			} else {
				$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", '', str_replace("[FLD:", '', $matches[0]))]), $return_value);
			}

			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		$return_value = str_replace("[BACKURL]", urlencode(getBackUrl()), $return_value);

		return $return_value;
	}

	function ReplaceSpecialFieldsWithDatabaseValues($url, $row) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[FLD\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) {
			$return_value = str_replace($matches[0], addslashes($row[str_replace("]", '', str_replace("[FLD:", '', $matches[0]))]), $return_value);
			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		$backurl = $_SERVER["QUERY_STRING"];
		if ( $backurl <> "" ) {
			$backurl = "?" . $backurl;
		}
		$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;
	}

	function ReplaceSpecialFieldsWithQuerystringValues($url) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[QUERYSTRING\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) {
			$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", '', str_replace("[QUERYSTRING:", '', $matches[0]))]), $return_value);
			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		// calculate 'go back' url
		$backurl = $_SERVER["QUERY_STRING"];
		if ( $backurl <> "" ) {
			$backurl = "?" . $backurl;
		}
		$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);

		// if there is a backurl then place the new blackurl into the string
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
