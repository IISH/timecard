<?php 
// version: 2009-02-19

require_once("./classes/class_misc.inc.php");

class class_field {
	var $oClassMisc;
	var $m_fieldname;
	var $m_fieldname_pointer;
	var $m_fieldlabel;
	var $m_fieldlabel_alttitle;
	var $m_href;
	var $m_nobr;
	var $m_onclick;
	var $m_view_max_length;
	var $m_view_max_length_extension;
	var $m_if_no_value_value;
	var $m_target;
	var $m_viewfilter;
	var $m_table_cell_width;
	var $m_show_different_value = '';
	var $m_no_href_if = '';
	var $m_alttitle = '';
	var $m_class = '';
	var $m_style = '';
	var $m_noheader;

	// TODOEXPLAIN
	function class_field($fieldsettings) {
		$this->oClassMisc = new class_misc();
		$this->m_fieldname = '';
		$this->m_fieldname_pointer = '';
		$this->m_fieldlabel = '';
		$this->m_fieldlabel_alttitle = '';
		$this->m_href = '';
		$this->m_nobr = '';
		$this->m_onclick = '';
		$this->m_view_max_length = 0;
		$this->m_view_max_length_extension = '..';
		$this->m_if_no_value_value = '';
		$this->m_target = '';
		$this->m_viewfilter = '';
		$this->m_table_cell_width = '';
//		$this->m_template = '';
		$this->m_show_different_value = '';
		$this->m_no_href_if = '';
		$this->m_alttitle = '';
		$this->m_class = '';
		$this->m_style = '';
		$this->m_noheader = false;

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					case "fieldname":
						$this->m_fieldname = $fieldsettings["fieldname"];
						break;
					case "fieldname_pointer":
						$this->m_fieldname_pointer = $fieldsettings["fieldname_pointer"];
						break;
					case "fieldlabel":
						$this->m_fieldlabel = $fieldsettings["fieldlabel"];
						break;
					case "fieldlabel_alttitle":
						$this->m_fieldlabel_alttitle = $fieldsettings["fieldlabel_alttitle"];
						break;
					case "table_cell_width":
						$this->m_table_cell_width = $fieldsettings["table_cell_width"];
						break;
					case "href":
						$this->m_href = $fieldsettings["href"];
						break;
					case "nobr":
						$this->m_nobr = $fieldsettings["nobr"];
						break;
					case "onclick":
						$this->m_onclick = $fieldsettings["onclick"];
						break;
					case "view_max_length":
						$this->m_view_max_length = $fieldsettings["view_max_length"];
						break;
					case "view_max_length_extension":
						$this->m_view_max_length_extension = $fieldsettings["view_max_length_extension"];
						break;
					case "if_no_value_value":
						$this->m_if_no_value_value = $fieldsettings["if_no_value_value"];
						break;
					case "target":
						$this->m_target = $fieldsettings["target"];
						break;
					case "viewfilter":
						$this->m_viewfilter = $fieldsettings["viewfilter"];
						break;
					case "show_different_value":
						$this->m_show_different_value = $fieldsettings["show_different_value"];
						break;
					case "no_href_if":
						$this->m_no_href_if = $fieldsettings["no_href_if"];
						break;
					case "href_alttitle":
						$this->m_alttitle = $fieldsettings["href_alttitle"];
						break;
					case "class":
						$this->m_class = $fieldsettings["class"];
						break;
					case "style":
						$this->m_style = $fieldsettings["style"];
						break;
					case "noheader":
						$this->m_noheader = $fieldsettings["noheader"];
						break;
				}
			}
		}
	}

	// TODOEXPLAIN
	function get_if_no_value_value($retval) {
		$retval = trim($retval);
		if ( strlen($retval) == 0 ) {
			$retval = trim($this->m_if_no_value_value);
			if ( strlen($retval) == 0 ) {
				$retval = "..no value..";
			}
		}
		return $retval;
	}

	// TODOEXPLAIN
	function get_fieldname() {
		return $this->m_fieldname;
	}

	// TODOEXPLAIN
	function get_fieldname_pointer() {
		return $this->m_fieldname_pointer;
	}

	// TODOEXPLAIN
	function get_fieldlabel() {
		return $this->m_fieldlabel;
	}

	// TODOEXPLAIN
	function get_fieldlabel_alttitle() {
		return $this->m_fieldlabel_alttitle;
	}

	// TODOEXPLAIN
	function get_table_cell_width() {
		return $this->m_table_cell_width;
	}

	// TODOEXPLAIN
	function get_href() {
		return $this->m_href;
	}

	// TODOEXPLAIN
	function get_nobr() {
		return $this->m_nobr;
	}

	// TODOEXPLAIN
	function get_onclick() {
		return $this->m_onclick;
	}

	// TODOEXPLAIN
	function get_noheader() {
		return $this->m_noheader;
	}

	// TODOEXPLAIN
	function get_value($row, $criteriumResult = 0) {

		if ( is_array($criteriumResult) ) {
			// 
			if ( $criteriumResult["fieldname"] == "-novalue-" ) {
				$retval = '';
			} elseif ( $criteriumResult["fieldname"] <> "" ) {
				$retval = stripslashes($row[$criteriumResult["fieldname"]]);
			} else {
				$retval = stripslashes($row[$this->get_fieldname()]);
			}
		} else {
			$retval = stripslashes($row[$this->get_fieldname()]);
		}

		return $retval;
	}

	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {

		if ( is_array($criteriumResult) ) {
			// 
			if ( $criteriumResult["fieldname"] == "-novalue-" ) {
				$retval = '';
			} elseif ( $criteriumResult["fieldname"] <> "" ) {
				$retval = stripslashes($row[$criteriumResult["fieldname"]]);
			} else {
				$retval = stripslashes($row[$this->get_fieldname()]);
			}
		} else {
			$retval = stripslashes($row[$this->get_fieldname()]);
		}

		// toon andere waarde
		if ( is_array($this->m_show_different_value) ) {
			if ( $retval == $this->m_show_different_value["value"] ) {
				if ( isset($this->m_show_different_value["showvalue"]) ) {
					$retval = $this->m_show_different_value["showvalue"];
				}
			} else {
				if ( isset($this->m_show_different_value["showelsevalue"]) ) {
					$retval = $this->m_show_different_value["showelsevalue"];
				}
			}

		}

		// remember the value (before we do some calculations on it)
		$long_value = $retval;

		if ( $this->m_view_max_length != 0 ) {
			if ( strlen($retval) > $this->m_view_max_length ) {

				$tmp_retval = $retval;

				// omdat we gaan knippen, vervang dan paar speciale html characters door gewone characters
				$tmp_retval = str_replace("&ndash;", "-", $tmp_retval);
				$tmp_retval = str_replace("&mdash;", "-", $tmp_retval);

				// neem de eerste x karakters
				$tmp_retval = substr($tmp_retval, 0, $this->m_view_max_length);

				// moeten er nog extra puntjes achter de string geplaatst worden
				if ( $this->m_view_max_length_extension !== false ) {
					$tmp_retval .= $this->m_view_max_length_extension;
				}

				$tmp_searchstring = strtolower(trim($_GET["vf_" . $this->m_fieldname ]));
				if ( $tmp_searchstring != '' ) {
					$all_search_found_in_max_length_value = 1;
					$tmp_searchstring_array = split(" ", $tmp_searchstring);

					foreach ( $tmp_searchstring_array as $array_value) {

						$pos = strpos(strtolower($tmp_retval), $array_value);
						if ( $pos === false ) {
							$all_search_found_in_max_length_value = 0;
						}
					}

					if ( $all_search_found_in_max_length_value != 1 ) {
						$tmp_retval = $retval;
					}

				}

				$retval = $tmp_retval;

			} else {
				// controleer of string langer is dan de maximale opgegeven lengte
				if ( strlen($retval) > $this->m_view_max_length ) {
					// ja, neem dan alleen maximaal x karakters

					// omdat we gaan knippen, vervang dan paar speciale html characters door gewone characters
					$retval = str_replace("&ndash;", "-", $retval);
					$retval = str_replace("&mdash;", "-", $retval);

					// neem de eerste x karakters
					$retval = substr($retval, 0, $this->m_view_max_length);

					// moeten er nog extra puntjes achter de string geplaatst worden
					if ( $this->m_view_max_length_extension !== false ) {
						$retval .= $this->m_view_max_length_extension;
					}
				}
			}
		}

		// als veld geen waarde heeft, toon dan de -empty- waarde
		if ( $retval == '' ) {
			if ( $this->m_if_no_value_value != '' ) {
				$retval = $this->m_if_no_value_value;
			}
		}

		return $retval;
	}
}
?>