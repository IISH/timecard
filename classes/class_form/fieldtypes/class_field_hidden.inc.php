<?php 
// modified: 2012-11-07

require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_hidden extends class_field {

	// TODOEXPLAIN
	function class_field_hidden($fieldsettings) {
		parent::class_field($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only hidden specific parameters

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
			$veldwaarde = $row[$this->get_fieldname()];

			$onNewValue = $this->get_onNew($m_form["primarykey"]);
			if ( $onNewValue != "" ) {
				$veldwaarde = $onNewValue;
			}
		}

		// strip slashes
		$veldwaarde = stripslashes($veldwaarde);
		$veldwaarde = str_replace("\"", "&quot;", $veldwaarde);

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" type=\"hidden\" value=\"::VALUE::\" ::STYLE:: ::CLASS::>";

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);
		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

		return $inputfield;
	}

	// TODOEXPLAIN
	function form_row($row, $tmp_data, $m_form, $required_typecheck_result = 0) {
		$tmp_data = $this->form_field($row, $m_form, $required_typecheck_result);

		return $tmp_data;
	}

}
?>