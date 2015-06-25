<?php 
require_once("./classes/class_file.inc.php");
require_once("./classes/class_misc.inc.php");

class class_view {
	protected $oDb;
	protected $oClassFile;
	protected $oClassMisc;
	private $settings;

	private $m_view;
	private $m_array_of_fields = Array();

	private $m_order_by;

	// TODOEXPLAIN
	function class_view($settings, $oDb) {
		$this->settings = $settings;

		$this->oDb = $oDb;
		$this->oClassFile = new class_file();
		$this->oClassMisc = new class_misc();
	}

	// TODOEXPLAIN
	function calculate_order_by() {
		$order_by = $_GET["order_by"];
		if ( $order_by == '' ) {
			$order_by = $this->m_view["order_by"];
		}

		$order_by = str_replace("%20", " ", $order_by);

		$this->m_order_by = $order_by;

		return 1;
	}

	// TODOEXPLAIN
	function add_filters_to_query($query) {
		$filter = '';

		if ( isset($this->m_view["filter"]) && is_array($this->m_view["filter"]) ) {

			$filtervalue = ( isset($_GET["filter"]) ? $_GET["filter"] : '' );
			if ( $filtervalue <> "" ) {

				if ( $this->m_view["filter"]["command"] == 'L_LIKE' ) {
					$filtervalue = "'" . $filtervalue . "%'";
				} else if ( $this->m_view["filter"]["command"] == 'R_LIKE' ) {
					$filtervalue = "'%" . $filtervalue . "'";
				} else if ( $this->m_view["filter"]["command"] == 'LR_LIKE' ) {
					$filtervalue = "'%" . $filtervalue . "%'";
				} else if ( $this->m_view["filter"]["command"] == 'RL_LIKE' ) {
					$filtervalue = "'%" . $filtervalue . "%'";
				} else {
					$filtervalue = "'" . $filtervalue . "'";
				}

				if ( $this->m_view["filter"]["concat"] <> "" ) {
					$filter .= " " . $this->m_view["filter"]["concat"] . " ";
				} else {
					$filter .= " WHERE ";
				}

				$separator = '';

				$filter .= " ( ";

				$fields = explode(';', $this->m_view["filter"]["field"]);

				foreach ( $fields as $filterfield ) {
					$filter .= $separator;

					// als we zoeken naar -NULL-
					if ( $filtervalue == "'-NULL-'" ) {
						// dan zoeken we naar een lege veld
						$filter .= " ( " . $filterfield . " = '' OR " . $filterfield . " IS NULL )" ;
					} else {

						// special query?
//						if ( $this->m_view["filter"]["command_special_criterium"] != '' ) {
//							// yes, special query
//							$filter .= $this->m_view["filter"]["command_special_criterium"];
//						} else {
							// no, normal query

							// zoeken we niet naar -NULL- (lege veld) dan moeten we de opgegeven criteria gebruiken
							$filter .= $filterfield;
							if ( $this->m_view["filter"]["command"] == 'R_LIKE' ) {
								$filter .= " LIKE ";
							} else if ( $this->m_view["filter"]["command"] == 'L_LIKE' ) {
								$filter .= " LIKE ";
							} else if ( $this->m_view["filter"]["command"] == 'LR_LIKE' ) {
								$filter .= " LIKE ";
							} else if ( $this->m_view["filter"]["command"] == 'RL_LIKE' ) {
								$filter .= " LIKE ";
							} else if ( $this->m_view["filter"]["command"] <> "" ) {
								$filter .= " " . $this->m_view["filter"]["command"] . " ";
							} else {
								$filter .= " LIKE ";
							}
							$filter .= $filtervalue;
//						}

					}

					$separator = " OR ";
				}

				$filter .= ") ";

				$query .= $filter;
			}

		}

		return $query;
	}

	// TODOEXPLAIN
	function add_viewfilters_to_query($query) {
		$filter = '';

		foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {

			if ( $one_field_in_array_of_fields->m_viewfilter ) {

				foreach ( $one_field_in_array_of_fields->m_viewfilter["filter"] as $field => $value) {

					$fieldname = ( isset($value["fieldname"]) ? $value["fieldname"] : '' );
					$type = ( isset($value["type"]) ? $value["type"] : '' );
					$fieldname_pointer = ( isset($value["fieldname_pointer"]) ? $value["fieldname_pointer"] : '' );
					$different_query_fieldname = ( isset($value["different_query_fieldname"]) ? $value["different_query_fieldname"] : '' );
					$extra_left_criterium = ( isset($value["extra_left_criterium"]) ? $value["extra_left_criterium"] : '' );
					$extra_right_criterium = ( isset($value["extra_right_criterium"]) ? $value["extra_right_criterium"] : '' );

					$tmp_filter = $this->CreateSpecialCriterium($fieldname, $type, $fieldname_pointer, $different_query_fieldname, $extra_left_criterium, $extra_right_criterium);

					if ( $tmp_filter <> "" ) {
						$filter .= " AND " . $tmp_filter . " ";
					}
				}
			}
		}

		$query .= $filter;

		return $query;
	}

	// TODOEXPLAIN
	function CreateSpecialCriterium($field, $type, $fieldname_pointer, $different_query_fieldname, $extra_left_criterium="", $extra_right_criterium="") {
		$retval = '';

		if ( $fieldname_pointer <> "" ) {
			if ( !isset($_GET["vf_" . $field . "__" . $fieldname_pointer . "__"]) ) {
				$_GET["vf_" . $field . "__" . $fieldname_pointer . "__"] = '';
			}

			$value = trim($_GET["vf_" . $field . "__" . $fieldname_pointer . "__"]);
		} else {
			if ( !isset($_GET["vf_" . $field]) ) {
				$_GET["vf_" . $field] = '';
			}

			$value = trim($_GET["vf_" . $field]);
		}

		$separatorAND = '';
		$separatorOR = '';

		if ( $value <> "" ) {

			if ( $different_query_fieldname <> '' ) {
				$fields = explode(";", $different_query_fieldname);
			} else {
				$fields = explode(";", $field);
			}
			$values = explode(" ", $value);

			foreach ( $values as $values_field => $values_value) {
				$separatorOR = '';
				$retval .= $separatorAND . " ( ";

				foreach ( $fields as $fields_field => $fields_value) {
					if ( $values_value == "-NULL-" ) {
						// indien men zoekt op -NULL- moet gecontroleerd worden of veld leeg is (leeg of null)
						// is eigenlijk gemaakt voor een select/list opdracht General/Special of leeg
						$retval .= $separatorOR . " ( " . $fields_value . " = '' OR " . $fields_value . " IS NULL ) ";
					} else {
						$retval .= $separatorOR . $fields_value . " LIKE '";

						$retval .= $extra_left_criterium;

						if ( $type == "select" ) {
							$retval .= $values_value;
						} else { // string
							$retval .= "%" . $values_value . "%";
						}

						$retval .= $extra_right_criterium;

						$retval .= "' ";
					}
					$separatorOR = " OR ";
				}

				$retval .= " ) ";
				$separatorAND = " AND ";
			}
		}

		return $retval;
	}

	// TODOEXPLAIN
	function generate_view_header() {
		$return_value = '';
		$total_header = '';
		$tmp_header = '';

		$row_template = "<tr>::TR::</tr>";
		$header_template = "
<TH ::TABLE_CELL_WIDTH:: align=\"left\" valign=\"top\"><a title=\"::ALTTITLE::\" class=\"nolink\">::TH::</a>::FILTER::&nbsp;</TH>
";

		foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {

			if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {

				$asc_url = '';
				$desc_url = '';

				$tmp_header = $header_template;

				// plaats de tabel cell breedtes (width) in de tabel
				$tmp_table_cell_width = $one_field_in_array_of_fields->get_table_cell_width();
				if ( $tmp_table_cell_width != '' ) {
					$tmp_header = str_replace("::TABLE_CELL_WIDTH::", "width=\"" . $tmp_table_cell_width . "\"", $tmp_header);
				} else {
					$tmp_header = str_replace("::TABLE_CELL_WIDTH::", '', $tmp_header);
				}

				// plaats label en buttons in header

				if ( $one_field_in_array_of_fields->get_noheader() ) {
					$tmp_header = str_replace("::TH::", '&nbsp;', $tmp_header);
				} else {
					$tmp_header = str_replace("::TH::", $one_field_in_array_of_fields->get_fieldlabel(), $tmp_header);
				}
				$tmp_header = str_replace("::ALTTITLE::", $one_field_in_array_of_fields->get_fieldlabel_alttitle(), $tmp_header);

				if ( is_array( $one_field_in_array_of_fields->m_viewfilter ) ) {
					$filter = $one_field_in_array_of_fields->m_viewfilter["labelfilterseparator"];

					foreach ( $one_field_in_array_of_fields->m_viewfilter["filter"] as $filterfield => $filtervalue ) {
						$filter .= $this->CreateViewFilterInputField($filtervalue);
					}

					$tmp_header = str_replace("::FILTER::", $filter, $tmp_header);
				} else {
					$tmp_header = str_replace("::FILTER::", '', $tmp_header);
				}

				$total_header .= $tmp_header;
			}
		}

		$return_value = str_replace("::TR::", $total_header, $row_template);

		if ( $this->m_view["viewfilter"] === true ) {

			$viewfilter = "
<script LANGUAGE=JavaScript>
<!--
document.onkeydown = onKeyDown;
document.onkeyup = onKeyUp;

var anyChanges = 0;

// TODOEXPLAIN
function onKeyUp(e) {
	var code = (window.event) ? event.keyCode : e.keyCode;

	if (code != 13 && code != 37 && code != 38	&& code != 39 && code != 40) {
		anyChanges = 1;
	}
}

// TODOEXPLAIN
function onSelectChange() {
	anyChanges = 1;
}

// TODOEXPLAIN
function onKeyDown(e) {
	var code = (window.event) ? event.keyCode : e.keyCode;
	if (code == 13) {
		if (anyChanges == 1) {
			document.filterform.submit();
		}
	}
}

// TODOEXPLAIN
function onchange_change_filter_doc_submit(obj) {
	document.forms['filterform'].elements['filter'].value=obj.value;
	anyChanges = 1;
	return true;
}
// -->
</script>

<form name=\"filterform\" type=\"get\">
<input type=\"hidden\" name=\"filter\" value=\"::FILTER::\">

::HIDDENFIELDS::

::HEADER::

</form>
";
			// plaats filter waarde in form viewfilter
			// filter veld bestaat altijd in viewfilter pagina
			// andere hidden velden worden dynamisch toegevoegd
			$viewfilter = str_replace("::FILTER::", $_GET["filter"], $viewfilter);

			$hiddenfields = '';

			$querystring_argument_item = '';
			$tmp_separator = '';
			if ( is_array($_SERVER["argv"]) ) {
				foreach ( $_SERVER["argv"] as $tmp_value ) {
					$querystring_argument_item .= $tmp_separator . $tmp_value;
					$tmp_separator = "+";
				}
			}

			$querystring_argument_item = str_replace("&amp;", "__amp;", $querystring_argument_item);

			$querystring_argument_array = explode("&", $querystring_argument_item);

			foreach ( $querystring_argument_array as $querystring_argument_field => $querystring_argument_value ) {

				$querystring_argument_value2 = explode("=", $querystring_argument_value, 2);

				$value1 = $querystring_argument_value2[1];
				$value1 = str_replace("__amp;", "&amp;", $value1);
				$value1 = urldecode($value1);
				$value1 = str_replace("\"", "&quot;", $value1);


				if ( substr($querystring_argument_value2[0], 0, 3) != "vf_" ) {
					if ( $querystring_argument_value2[0] != "filter" ) {
						// toon (verborgen) alleen querystring velden die niet onderdeel zijn van de view
						// en ook niet filter is (filter wordt altijd in viewfilter geplaatst)
						$hiddenfields .= "<input type=\"hidden\" name=\"" . $querystring_argument_value2[0] . "\" value=\"" . $value1 . "\">\n";
					}
				}
			}

			if ( isset($this->m_view["extra_hidden_viewfilter_fields"]) ) {
				$hiddenfields .= $this->m_view["extra_hidden_viewfilter_fields"];
			}

			//
			$viewfilter = str_replace("::HIDDENFIELDS::", $hiddenfields, $viewfilter);

			//
			$return_value = str_replace("::HEADER::", $return_value, $viewfilter);
		}

		return $return_value;
	}

	// TODOEXPLAIN
	function CreateViewFilterInputField($field) {
		$retval = '';

		$label = ( isset($field["fieldlabel"]) ? $field["fieldlabel"] : '' );
		$name = ( isset($field["fieldname"]) ? $field["fieldname"] : '' );

		if ( isset($field["fieldname_pointer"]) && $field["fieldname_pointer"] <> "" ) {
			$name .= "__" . $field["fieldname_pointer"] . "__";
		}

		if ( $field["type"] == '' || $field["type"] == "string" ) {
			$size = $field["size"];
			if ( $size == '' ) {
				$size = "20";
			}

			if ( !isset($_GET["vf_" . $name]) ) {
				$_GET["vf_" . $name] = '';
			}
			$value = $_GET["vf_" . $name];
			$value = str_replace("\\\"", "&quot;", $value);
			$value = str_replace("\'", "'", $value);

			$retval .= "::LABEL:: <input type=\"text\" name=\"vf_::NAME::\" value=\"::VALUE::\" size=\"::SIZE::\">\n";
			$retval = str_replace("::VALUE::", $value, $retval);
			$retval = str_replace("::SIZE::", $size, $retval);
		} elseif ( $field["type"] == "select" ) {
			$retval .= "::LABEL:: <SELECT name=\"vf_::NAME::\" onchange=\"onSelectChange();\">\n::OPTIONS::\n</SELECT>\n";

			$value = $_GET["vf_" . $name];
			$value = str_replace("\\\"", "&quot;", $value);
			$value = str_replace("\'", "'", $value);

			$options = '';
			foreach ( $field["choices"] as $choice_label => $choice_value) {

				$tmpOption = "<OPTION value=\"" . $choice_value . "\" ::SELECTED::>" . $choice_label . "</OPTION>\n";

				// 'SELECT' de gekozen optie
				if ( $choice_value == $value ) {
					$tmpOption = str_replace("::SELECTED::", "SELECTED", $tmpOption);
				} else {
					$tmpOption = str_replace("::SELECTED::", '', $tmpOption);
				}

				$options .= $tmpOption;
			}

			$retval = str_replace("::OPTIONS::", $options, $retval);
		}

		$retval = str_replace("::LABEL::", $label, $retval);
		$retval = str_replace("::NAME::", $name, $retval);

		return trim($retval);
	}

	// generate_view
	function generate_view() {
		if ( !isset( $_POST["form_fld_pressed_button"] ) ) {
			$_POST["form_fld_pressed_button"] = '';
		}

//		$extra_left_criterium = ( isset($value["extra_left_criterium"]) ? $value["extra_left_criterium"] : '' );

		$return_value = '';

		// connect to server
		$this->oDb->connect();

		// preload templates
		$preloaded_templates = array();
		// default template for view
		array_push($preloaded_templates, array('default' => "<TD class=\"recorditem\">::TD::&nbsp;</td>\n"));

		// place querystring parameters in query
		$this->m_view["query"] = $this->oClassMisc->PlaceURLParametersInQuery($this->m_view["query"]);

		// add filters to query
		$this->m_view["query"] = $this->add_filters_to_query($this->m_view["query"]);

		// add viewfilters to query
		$this->m_view["query"] = $this->add_viewfilters_to_query($this->m_view["query"]);

		if ( $_POST["form_fld_pressed_button"] == '-delete-' || $_POST["form_fld_pressed_button"] == '-delete-now-' ) {
			$checked_records = '';
			$tmp_separator = '';

			if ( $_POST["form_fld_pressed_button"] == '-delete-now-') {
				$record_list_array = explode(';', $_POST["list_of_records"]);
			} else {
				die('error: 541289');
			}

			if ( is_array($record_list_array) ) {
				$checked_records .= " AND (";
				foreach ( $record_list_array as $record_id ) {
					$checked_records .= $tmp_separator . $this->m_view["anchor_field"] . '=' . $record_id;
					$tmp_separator = " OR ";
				}
				$checked_records .= ") ";

				$this->m_view["query"] .= $checked_records;

			}
		}

		// calculate order by
		$this->calculate_order_by();
		if ( $this->m_order_by <> "" ) {
			$this->m_view["query"] .= " ORDER BY " . $this->m_order_by;
		}

		// execute query
		$res = mysql_query($this->m_view["query"], $this->oDb->getConnection()) or die( "error 8712378" . "<br>" . mysql_error());

		// get submit buttons (add new / go back)
		// show buttons at top
		$return_value .= $this->get_view_buttons();

		if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {
			// get filter buttons
			$filter_buttons = $this->get_filter_buttons();
		}

		// als -delete-now-
		if ( $_POST["form_fld_pressed_button"] == '-delete-now-' ) {
			// delete dan de records en toon eind-bericht
			$tmp_query_delete = $this->m_view["delete_query"];

			if ( trim($_POST["list_of_records"]) != '' ) {
				$array_of_records = explode(';', $_POST["list_of_records"]);
				foreach ( $array_of_records as $record_id ) {
					$query_delete = $tmp_query_delete . $record_id;

					$res_delete = mysql_query($query_delete, $this->oDb->getConnection()) or die( "error 52129398" . "<br>" . mysql_error());
				}
			}

			$return_value .= 'Record(s) deleted...';
			$return_value .= "<br>\n&nbsp;<br>\n&nbsp;<br>\n";

		}

		if($res){
			// show buttons at top
			$return_value .= $filter_buttons;

			if ( $_POST["form_fld_pressed_button"] == '-delete-now-' ) {

				// go back button
				$current_url = $_SERVER["SCRIPT_NAME"];
				if ( $_SERVER["QUERY_STRING"] != '' ) {
					$current_url .= "?" . $_SERVER["QUERY_STRING"];
				}
				$tmp_button = "<input type=\"button\" onclick=\"window.location.href='" . $current_url . "';return false;\" value=\"::LABEL::\">\n";
				$tmp_button = str_replace("::LABEL::", 'Go back', $tmp_button);
				$return_value .= $tmp_button;

				$return_value .= "<br>\n";

			} else if ( $_POST["form_fld_pressed_button"] == '-delete-' ) {

				$return_value .= "<CENTER>\n";
				$separator = '';

				// go back button
				$current_url = $_SERVER["SCRIPT_NAME"];
				if ( $_SERVER["QUERY_STRING"] != '' ) {
					$current_url .= "?" . $_SERVER["QUERY_STRING"];
				}
				$tmp_button = "<input type=\"button\" onclick=\"window.location.href='" . $current_url . "';return false;\" value=\"::LABEL::\">\n";
				$tmp_button = str_replace("::LABEL::", 'Cancel / Go back', $tmp_button);
				$return_value .= $separator . $tmp_button;

				$separator = "&nbsp; &nbsp; &nbsp;";

				$return_value .= "</CENTER><br>\n";

			}

			// moet overzicht wel getoond worden
			// niet tonen als delete bevestigings bericht (- delete - now -)
			// en ook niet tonen als men niks heeft geselecteerd bij - delete -
			$show_view_table = true;
			if ( $_POST["form_fld_pressed_button"] == '-delete-now-' ) {
				$show_view_table = false;
			} else {
				if ( $_POST["form_fld_pressed_button"] == '-delete-' ) {
					$show_view_table = false;
				}
			}

			// show calculate_total
			if ( isset($this->m_view["calculate_total"]) && is_array($this->m_view["calculate_total"]) ) {
				$calculate_total[$this->m_view["calculate_total"]["field"]] = 0;
			}

			if ( $show_view_table === true ) {

				$return_value .= "<table";

				// extra tabel parameters
				if ( $this->m_view["table_parameters"] != '' ) {
					$return_value .= " " . $this->m_view["table_parameters"] . " ";
				}

				// sluit tabel
				$return_value .= ">";

				$row_template = "<tr>::TR::</tr>";
				$total_row = '';

				// add header row
				if ( !isset($this->m_view["show_view_header"]) || $this->m_view["show_view_header"] == true ) {
					$return_value .= $this->generate_view_header();
				}

				// doorloop gevonden recordset
				while($row = mysql_fetch_assoc($res)){
					$total_data = '';
					$anchor = '';

					if ( $this->m_view["anchor_field"] <> "" ) {
						$anchor = "<A NAME=\"::ANCHOR::\"></A>";
						$anchor = str_replace("::ANCHOR::", $row[$this->m_view["anchor_field"]], $anchor);
					}

					foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {

						if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {

							$tmp_data = $this->Get_PreloadedTemplateDesign($preloaded_templates, "default");

							// get veld waarde
							$veldwaarde = $one_field_in_array_of_fields->view_field($row);
							$dbwaarde = $one_field_in_array_of_fields->get_value($row);
							// add calculate_total
							if ( isset($this->m_view["calculate_total"]) && is_array($this->m_view["calculate_total"]) ) {
								if ( strtolower( $one_field_in_array_of_fields->get_fieldname() ) == strtolower($this->m_view["calculate_total"]["field"]) ) {
									$calculate_total[$this->m_view["calculate_total"]["field"]] += $dbwaarde;
								}
							}

							// plaats veldwaarde in tabel cell
							$tmp_data = str_replace("::TD::", $veldwaarde, $tmp_data);
							$total_data .= $tmp_data;
						}
					}
					// plaats alle cellen in row template
					$total_row .= str_replace("::TR::", $anchor . $total_data, $row_template);
				}
				// voeg alle rijen toe aan tabel
				$return_value .= $total_row;

				// show calculate_total
				if ( isset($this->m_view["calculate_total"]) && is_array($this->m_view["calculate_total"]) ) {
					$return_value .= "<tr><td colspan=\"" . $this->m_view["calculate_total"]["nrofcols"] . "\"><hr></td></tr>";
					$return_value .= "<tr><td colspan=\"" . ($this->m_view["calculate_total"]["totalcol"]-1) . "\"><b>Total:</b></td>";

					$t = $calculate_total[$this->m_view["calculate_total"]["field"]];
					if ( $this->m_view["calculate_total"]["type"] == 'integer' ) {
						$t = $t;
					} else if ( $this->m_view["calculate_total"]["type"] == 'integer_thousand_separator' ) {
						$t = number_format ($t, 0, ',', '.');
					} else {
						$t = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($t);
					}
					$return_value .= "<td><b>" . $t . "</b>&nbsp;&nbsp;</td>";

					$return_value .= "</tr>";
				}

				// end table
				$return_value .= "</table>";
			}

		}

		// free result set
		mysql_free_result($res);

		// return result
		return $return_value;
	}

	// set_view
	function set_view($aView) {
		$this->m_view = $aView;

		return 1;
	}

	// add_field
	function add_field($aField) {
		array_push($this->m_array_of_fields, $aField);
		return 1;
	}

	// TODOEXPLAIN
	function Get_PreloadedTemplateDesign($preloaded_templates, $template) {
		$retval = '';

		foreach($preloaded_templates as $itemvolgnr => $itemarray) {
			foreach($itemarray as $criterium => $templatedesign) {
				if ( $criterium == $template ) {
					$retval = $templatedesign;
				}
			}
		}

		return $retval;
	}

	// TODOEXPLAIN
	function get_view_buttons() {
		// show add new button?
		$add_new_url = $this->m_view["add_new_url"];

		if ( $add_new_url == '' ) {
			return '';
		}

		// place submit buttons
		$submitbuttons = "<p style=\"line-height:20px\"><a href=\"::ADDNEWURL::\" class=\"button\">Add new</a></p>";

		$add_new_url = str_replace("\n", '', $add_new_url);
		$add_new_url = str_replace("\t", '', $add_new_url);
		$add_new_url = str_replace("\r", '', $add_new_url);

		$add_new_url = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($add_new_url);

		// create add new button
		$submitbuttons = str_replace("::ADDNEWURL::", $add_new_url, $submitbuttons);

		return $submitbuttons;
	}

	// TODOEXPLAIN
	function get_filter_buttons() {
		$submitbuttons = '';

		$separator = '';

		if ( is_array($this->m_view["filter"]) ) {

			// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

			if ( is_array($this->m_view["filter"]["choices"]) ) {

				// label
				$filterlabel = $this->m_view["filter"]["label"];
				if ( $filterlabel <> "" ) {
					$submitbuttons .= str_replace("::FILTERLABEL::", $filterlabel, "<span class=\"filter_label\">::FILTERLABEL:::&nbsp;&nbsp;</span>");
				}

				foreach ( $this->m_view["filter"]["choices"] as $field => $value ) {
					$url = "?" . urldecode($_SERVER["QUERY_STRING"]);
					$url = str_replace("&filter=" . urldecode($_GET["filter"]), '', $url);
					$url = str_replace("?filter=" . urldecode($_GET["filter"]), '', $url);

					if ( $url == "?" ) {
						$url = '';
					}

					// als men op de keuze: '-reset-' klikt dan worden alle filters en search criteria ongedaan gemaakt (de pagina wordt opnieuw geopend zonder parameters)
					if ( $value == '-reset-' ) {
						$url = '';
					} else {
						if ( $url <> "" ) {
							$url .= "&filter=" . urlencode($value);
						} else {
							$url = "?filter=" . urlencode($value);
						}
					}

					if ( $url[0] == "&" ) {
						$url[0] = "?";
					}

					$url = $_SERVER["SCRIPT_NAME"] . $url;

					// is filter selected ?
					if ( $value <> urldecode($_GET["filter"]) ) {
						// no, this filter is not selected
						$submitbuttons .= $separator . "<a href=\"" . $url . "\">" . $field . "</a>";
					} else {
						// yes, this filter is selected
						$submitbuttons .= $separator . "<b>" . $field . "</b>";
					}

					$separator = "&nbsp;&nbsp;&nbsp;";
				}
			}
		}

		if ( $submitbuttons <> "" ) {
			$submitbuttons = "<table width=\"100%\">\n<tr><td align=\"center\">" . $submitbuttons . "</td></tr></table>\n";
		}

		return $submitbuttons;
	}

}
