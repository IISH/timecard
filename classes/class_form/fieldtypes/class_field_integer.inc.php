<?php 
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_integer extends class_field {
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

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" type=\"text\" value=\"::VALUE::\" size=\"::SIZE::\" ::STYLE:: ::CLASS:: ::PLACEHOLDER::>";

		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

		//
		$inputfield = $this->setInputFieldAttributes($inputfield);
		$inputfield = $this->cleanUpLabels($inputfield);

		return $inputfield;
	}

	function is_field_value_correct($veldwaarde = "") {
		if ( is_numeric($veldwaarde) === false ) {
			// not an integer
			return 0;
		}

		return 1;
	}

	function push_field_into_query_array($query_fields) {
		$value = $this->get_form_value();

		if ( $value == '' ) {
			$value = "NULL";
		}

		array_push($query_fields, array($this->get_fieldname() => $value));

		return $query_fields;
	}
}
