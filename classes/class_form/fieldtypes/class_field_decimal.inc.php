<?php 
// modified: 2012-11-07

require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_decimal extends class_field {
    private $m_addquotes;

	// TODOEXPLAIN
	function class_field_decimal($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_addquotes = 0;

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only integer specific parameters

				}
			}
		}
	}

	// TODOEXPLAIN
	function form_row($row, $tmp_data, $m_form, $required_typecheck_result = 0) {
		// place input field in row template
		$tmp_data = str_replace("::FIELD::", $this->form_field($row, $m_form, $required_typecheck_result), $tmp_data);

		// place fieldname in row template
		$tmp_data = str_replace("::LABEL::", $this->get_fieldlabel(), $tmp_data);

		// place if necessary required sign in row template
		$tmp_data = str_replace("::REQUIRED::", $this->get_required_sign(), $tmp_data);

		$tmp_data = str_replace("::REFRESH::", '', $tmp_data);
		$tmp_data = str_replace("::ADDNEW::", '', $tmp_data);

		return $tmp_data;
	}

	// TODOEXPLAIN
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

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" type=\"text\" value=\"::VALUE::\" size=\"::SIZE::\" ::STYLE:: ::CLASS::>";

		$inputfield = str_replace("::SIZE::", $this->m_size, $inputfield);

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);
		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

		$style = $this->get_style();
		if ( $style != '' ) {
			$inputfield = str_replace("::STYLE::", ' style="' . $style . '" ', $inputfield);
		}

		return $inputfield;
	}

	// TODOEXPLAIN
	function is_field_value_correct($veldwaarde = "") {
		$retval = 1; // default = okay

		if ( is_numeric($veldwaarde) === false ) {
			// not an integer
			$retval = 0;
		}

		return $retval;
	}

	// TODOEXPLAIN
	function push_field_into_query_array($query_fields) {
		$value = $this->get_form_value();

		if ( $value == '' ) {
			$value = "NULL";
		}

		array_push($query_fields, array($this->get_fieldname() => $value));

		return $query_fields;
	}
}
?>