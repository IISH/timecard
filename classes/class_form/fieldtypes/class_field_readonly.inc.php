<?php 
// modified: 2012-11-07

require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_readonly extends class_field {

	// TODOEXPLAIN
	function class_field_readonly($fieldsettings) {
		parent::class_field($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// 

				}
			}
		}
	}

	// TODOEXPLAIN
	function form_field($row, $m_form, $required_typecheck_result = 0 ) {
		$veldwaarde = $row[$this->get_fieldname()];

		$onNewValue = $this->get_onNew($m_form["primarykey"]);
		if ( $onNewValue != "" ) {
			$veldwaarde = $onNewValue;
		}

		// strip slashes
		$veldwaarde = stripslashes($veldwaarde);
		$veldwaarde = str_replace("\"", "&quot;", $veldwaarde);

		$inputfield = "::VALUE::";

		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

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
		return $query_fields;
	}
}
