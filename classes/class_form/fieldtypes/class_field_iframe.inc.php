<?php 
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_iframe extends class_field {
	private $m_src;

	function class_field_iframe($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_src = '';

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only integer specific parameters

					case "src":
						$this->m_src = $fieldsettings["src"];
						break;

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
		$tmp_data = str_replace("::REQUIRED::", '', $tmp_data);

		return $tmp_data;
	}

	function form_field($row, $m_form, $required_typecheck_result = 0 ) {
		$inputfield = "<a name=\"anchor_::NAME::\"></a><iframe id=\"::NAME::\" name=\"::NAME::\" src=\"::SRC::\" width=\"::WIDTH::\" height=\"::HEIGHT::\" ::STYLE::></iframe>";

		$inputfield = str_replace("::SRC::", $this->m_src, $inputfield);

		//
		$inputfield = $this->setInputFieldAttributes($inputfield);
		$inputfield = $this->cleanUpLabels($inputfield);

		return $inputfield;
	}

	/**
	 */
	function push_field_into_query_array($query_fields) {
		return $query_fields;
	}
}
