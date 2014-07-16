<?php
// modified: 2012-11-07

require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_bit extends class_field {
	private $m_addquotes;

	// TODOEXPLAIN
	function class_field_bit($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_addquotes = 0;

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only bit specific parameters

				}
			}
		}
	}

	// TODOEXPLAIN
	function form_field($row, $m_form, $required_typecheck_result = 0) {
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

		$veldwaarde = $veldwaarde . '';

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" type=\"checkbox\" ::CHECKED:: ::STYLE:: ::CLASS::>";

		if ( $veldwaarde == "1" || $veldwaarde == "on" || $veldwaarde == "checked" ) {
			$inputfield = str_replace("::CHECKED::", "CHECKED", $inputfield);
		} else {
			$inputfield = str_replace("::CHECKED::", '', $inputfield);
		}

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);

		return $inputfield;
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
	function push_field_into_query_array($query_fields) {

		$veldwaarde = $this->get_form_value();

		// als integer 0 of 1, bewaar dan gewoon de integer
		if ( $veldwaarde <> "1" && $veldwaarde <> "0" ) {
			// anders, converteer on naar 1, en rest naar 0
			if ( strtolower($veldwaarde) == "on" || strtolower($veldwaarde) == "checked" ) {
				$veldwaarde = "1";
			} else {
				$veldwaarde = "0";
			}
		}
		array_push($query_fields, array($this->get_fieldname() => $veldwaarde));

		return $query_fields;
	}

}
?>