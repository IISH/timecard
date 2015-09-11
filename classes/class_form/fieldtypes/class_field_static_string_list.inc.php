<?php 
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_static_string_list extends class_field {
	private $m_choices;

	function class_field_static_string_list($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_choices = '';

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// 

					case "choices":
						$this->m_choices = $fieldsettings["choices"];
						break;

				}
			}
		}
	}

	function get_choices() {
		$retval = $this->m_choices;
		return $retval;
	}

	function form_field($row, $m_form, $required_typecheck_result = 0 ) {
		// welke waarde moeten we gebruiken, uit de db? of uit de form?
		// indien niet goed bewaard gebruik dan de form waarde
		if ( $required_typecheck_result == 0 ) {
			$veldwaarde = $this->get_form_value();
		} else {
			$veldwaarde = $row[$this->get_fieldname()];

			$onNewValue = $this->get_onNew($m_form["primarykey"]);
			if ( $onNewValue != "" ) {
				$veldwaarde = $onNewValue;
			}
		}

		// strip slashes
		$veldwaarde = stripslashes($veldwaarde);
		$veldwaarde = str_replace("\"", "&quot;", $veldwaarde);

		$inputfield = "<select name=\"FORM_::FIELDNAME::\">\n";
		if ( is_array($this->get_choices()) ) {
			foreach ( $this->get_choices() as $selectchoice ) {

				// zijn de veldwaardes een array van id en omschrijving
				if ( is_array($selectchoice) ) {
					// ja
					// veld 0: id
					// vled 1: omschrijving
					$inputfield .= "\t<option value=\"" . trim($selectchoice[0]) . "\"";
					if ( $selectchoice[0] == $veldwaarde ) {
						$inputfield .= " SELECTED";
					}
					$inputfield .= ">" . trim($selectchoice[1]) . "</option>\n";

				} else {
					// nee
					// id / omschrijving gelijk
					$inputfield .= "\t<option value=\"" . trim($selectchoice) . "\"";
					if ( $selectchoice == $veldwaarde ) {
						$inputfield .= " SELECTED";
					}
					$inputfield .= ">" . trim($selectchoice) . "</option>\n";
				}
			}
		}
		$inputfield .= "</select>\n";

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);
		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

		return $inputfield;
	}

	function form_row($row, $tmp_data, $m_form, $required_typecheck_result = 0) {
		// place input field in row template
		$tmp_data = str_replace("::FIELD::", $this->form_field($row, $m_form, $required_typecheck_result), $tmp_data);

		// place fieldname in row template
		$tmp_data = str_replace("::LABEL::", $this->get_fieldlabel(), $tmp_data);

		// place if necessary required sign in row template
		$tmp_data = str_replace("::REQUIRED::", $this->get_required_sign(), $tmp_data);

		return $tmp_data;
	}

}
