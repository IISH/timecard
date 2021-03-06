<?php 
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_time_free_input_field extends class_field {
	private $m_addquotes;

	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		$this->m_addquotes = 0;

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only integer specific parameters

				}
			}
		}
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

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" type=\"text\" value=\"::VALUE::\" size=\"::SIZE::\" ::STYLE:: ::CLASS:: ::PLACEHOLDER:: autocomplete=\"off\">";

		if ( $veldwaarde == '' ) {
			$inputfield = str_replace("::VALUE::", '', $inputfield);

		} else {
			$inputfield = str_replace("::VALUE::", class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($veldwaarde), $inputfield);
		}

		//
		$inputfield = $this->setInputFieldAttributes($inputfield);
		$inputfield = $this->cleanUpLabels($inputfield);

		return $inputfield;
	}

	function push_field_into_query_array($query_fields) {
		$value = $this->get_form_value();

		if ( $value == '' ) {
			$value = "NULL";
		}

		array_push($query_fields, array($this->get_fieldname() => $value));

		return $query_fields;
	}

	function get_form_value($field = '' ) {
		if ( $field == '' ) {
			$retval = $_POST["FORM_" . $this->get_fieldname()];
		} else {
			$retval = $_POST["FORM_" . $field];
		}

		// replace everything except integers to semicolon
		$retval = preg_replace('/[^0-9:]/', ':', $retval);
		// TODO
		$retval = str_replace('::::', ':', $retval);
		$retval = str_replace(':::', ':', $retval);
		$retval = str_replace('::', ':', $retval);

		$arr = explode(':', $retval);
		switch ( count($arr) ) {
			case 0:
				$retval = 0;
				break;
			case 1:
				$arr[0] = substr("00" . $arr[0],-2);
				$retval = $arr[0] * 60;
				break;
			default:
				$arr[0] = substr("00" . $arr[0],-2);
				$arr[1] = substr("00" . $arr[1],-2);
				$retval = ( $arr[0] * 60 ) + $arr[1];
		}

		return $retval;
	}

	function is_field_value_correct($veldwaarde = "") {
		$retval = 1; // default = okay

		if ( $this->is_field_required() == 1 ) {
			if ( $veldwaarde == '' ) {
				$retval = '0:00';
			}
		}

		return $retval;
	}
}
