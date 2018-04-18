<?php 
require_once("./classes/class_misc.inc.php");

class class_field {
	protected $oClassMisc;
	private $m_fieldname;
	private $m_fieldname_pointer;
	private $m_fieldlabel;
	private $m_fieldlabel_alttitle;
	private $m_href;
	private $m_nobr;
	private $m_onclick;
	private $m_view_max_length;
	private $m_view_max_length_extension;
	private $m_if_no_value;
	private $m_target;
	public $m_viewfilter;
	private $m_table_cell_width;
	private $m_show_different_value = '';
	private $m_no_href_if = '';
	private $m_alttitle = '';
	private $m_class = '';
	private $m_style = '';
	private $m_noheader;
	private $m_protectSpecialChars = 0;

	function __construct($fieldsettings) {
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
		$this->m_if_no_value = '';
		$this->m_target = '';
		$this->m_viewfilter = '';
		$this->m_table_cell_width = '';
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
					case "if_no_value":
						$this->m_if_no_value = $fieldsettings["if_no_value"];
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
					case "class":
						$this->m_class = $fieldsettings["class"];
						break;
					case "style":
						$this->m_style = $fieldsettings["style"];
						break;
					case "noheader":
						$this->m_noheader = $fieldsettings["noheader"];
						break;
					case "protectSpecialChars":
						$this->m_protectSpecialChars = $fieldsettings["protectSpecialChars"];
						break;
				}
			}
		}
	}

	function get_if_no_value($retval) {
		$retval = trim($retval);
		if ( strlen($retval) == 0 ) {
			$retval = trim($this->m_if_no_value);
			if ( strlen($retval) == 0 ) {
				$retval = "..no value..";
			}
		}
		return $retval;
	}

	function get_fieldname() {
		return $this->m_fieldname;
	}

	function get_target() {
		return $this->m_target;
	}

	function get_alttitle() {
		return $this->m_alttitle;
	}

	function get_nobr() {
		return $this->m_nobr;
	}

	function get_fieldname_pointer() {
		return $this->m_fieldname_pointer;
	}

	function get_fieldlabel() {
		return $this->m_fieldlabel;
	}

	function get_fieldlabel_alttitle() {
		return $this->m_fieldlabel_alttitle;
	}

	function get_table_cell_width() {
		return $this->m_table_cell_width;
	}

	function get_href() {
		return $this->m_href;
	}

	function get_onclick() {
		return $this->m_onclick;
	}

	function get_noheader() {
		return $this->m_noheader;
	}

	function get_no_href_if() {
		return $this->m_no_href_if;
	}

	function get_viewfilter() {
		return $this->m_viewfilter;
	}

	function get_value($row) {
		$retval = stripslashes($row[$this->get_fieldname()]);
		return $retval;
	}

	function get_protectSpecialChars() {
		return $this->m_protectSpecialChars;
	}

	function view_field($row) {

		$retval = stripslashes($row[$this->get_fieldname()]);

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

				if ( !isset( $_GET["vf_" . $this->m_fieldname ] ) ) {
					$_GET["vf_" . $this->m_fieldname ] = '';
				}
				$tmp_searchstring = strtolower(trim($_GET["vf_" . $this->m_fieldname ]));
				if ( $tmp_searchstring != '' ) {
					$all_search_found_in_max_length_value = 1;
					$tmp_searchstring_array = explode(' ', $tmp_searchstring);

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
			if ( $this->m_if_no_value != '' ) {
				$retval = $this->m_if_no_value;
			}
		}

		return $retval;
	}
}
