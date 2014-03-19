<?php 
// modified: 2013-01-02

class class_misc {

	function class_misc() {
	}

	// TODOEXPLAIN
	function multiplyTag($tag, $code, $start, $end) {
		$ret = '';
		$separator = '';

		for ( $i = $start ; $i <= $end; $i++ ) {
			$ret .= $separator . str_replace($code, $i, $tag);
			$separator = ', ';
		}

		return $ret;
	}

	// TODOEXPLAIN
	function PlaceURLParametersInQuery($query, $change_back_url) {
		$return_value = $query;

		// vervang in de url, de FLD: door waardes
		$pattern = '\[FLD\:[a-zA-Z0-9_]*\]';
		ereg($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) { 

			if ( "[FLD:" . $this->m_form["primarykey"] . "]" == $matches[0] ) {
				$return_value = str_replace($matches[0], $this->m_doc_id, $return_value);
			} else {
				$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", '', str_replace("[FLD:", '', $matches[0]))]), $return_value);
			}

			$matches = null;
			ereg($pattern, $return_value, $matches);
		}

		if ( $change_back_url <> "no" ) {
			// 
			$backurl = $_SERVER["QUERY_STRING"];
			if ( $backurl <> "" ) {
				$backurl = "?" . $backurl;
			}
			$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);
		} else {
			$backurl = getBackUrl();
//			$backurl = $_GET["parentbackurl"];
//			if ( $backurl == '' ) {
//				$backurl = $_GET["backurl"];
//			}

			$backurl = urlencode($backurl);
		}
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;;
	}

	// TODOEXPLAIN
	function ReplaceSpecialFieldsWithDatabaseValues($url, $row) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '\[FLD\:[a-zA-Z0-9_]*\]';
		ereg($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) { 
			$return_value = str_replace($matches[0], addslashes($row[str_replace("]", '', str_replace("[FLD:", '', $matches[0]))]), $return_value);
			$matches = null;
			ereg($pattern, $return_value, $matches);
		}

		$backurl = $_SERVER["QUERY_STRING"];
		if ( $backurl <> "" ) {
			$backurl = "?" . $backurl;
		}
		$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;
	}

	// TODOEXPLAIN
	function ReplaceSpecialFieldsWithQuerystringValues($url) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '\[QUERYSTRING\:[a-zA-Z0-9_]*\]';
		ereg($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) { 
			$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", '', str_replace("[QUERYSTRING:", '', $matches[0]))]), $return_value);
			$matches = null;
			ereg($pattern, $return_value, $matches);
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
}
?>