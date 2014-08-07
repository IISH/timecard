<?php 
// modified: 2012-11-07

require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_string extends class_field {
	// TODOEXPLAIN
	function class_field_string($fieldsettings) {
		parent::class_field($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only string specific parameters

				}
			}
		}

	}

	// TODOEXPLAIN
	function form_field($row, $m_form, $required_typecheck_result = 0 ) {
		// welke waarde moeten we gebruiken, uit de db? of uit de form?
		// indien niet goed bewaard gebruik dan de form waarde
		if ( $required_typecheck_result == 0 ) {
			$veldwaarde = $this->get_form_value();
		} else {
			$veldwaarde = $row[$this->get_fieldname()]; // RETOURNEERT EEN SPATIE BIJ LEGE VELD ???

			$onNewValue = $this->get_onNew($m_form["primarykey"]);
			if ( $onNewValue != "" ) {
				$veldwaarde = $onNewValue;
			}
		}

		// strip slashes
		$veldwaarde = stripslashes($veldwaarde);
		$veldwaarde = str_replace("\"", "&quot;", $veldwaarde);

		// extra
		$veldwaarde = trim($veldwaarde);

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" type=\"text\" value=\"::VALUE::\" size=\"::SIZE::\" ::STYLE:: ::CLASS::>";

		$inputfield = str_replace("::SIZE::", $this->m_size, $inputfield);

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);

		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

		if ( $this->get_style() != '' ) {
			$inputfield = str_replace("::STYLE::", "STYLE=\"" . $this->get_style() . "\"", $inputfield);
		} else {
			$inputfield = str_replace("::STYLE::", '', $inputfield);
		}

		return $inputfield;
	}

	// TODOEXPLAIN
	function form_row($row, $tmp_data, $m_form, $required_typecheck_result = 0) {
		// place input field in row template
		$field = $this->form_field($row, $m_form, $required_typecheck_result);
		$tmp_data = str_replace("::FIELD::", $field, $tmp_data);

		// place fieldname in row template
		$tmp_data = str_replace("::LABEL::", $this->get_fieldlabel(), $tmp_data);

		// place if necessary required sign in row template
		$tmp_data = str_replace("::REQUIRED::", $this->get_required_sign(), $tmp_data);

		$tmp_data = str_replace("::REFRESH::", '', $tmp_data);
		$tmp_data = str_replace("::ADDNEW::", '', $tmp_data);

		return $tmp_data;
	}
}
