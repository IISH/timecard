<?php
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_remark extends class_field {

	/**
	 */
	function __construct($settings) {
		parent::__construct($settings);

		if ( is_array( $settings ) ) {
			foreach ( $settings as $field => $value ) {
				switch ($field) {
					// only dummy specific parameters

				}
			}
		}
	}


	/**
	 */
	function form_field($row, $m_form, $required_typecheck_result = 0 ) {
		return $this->get_onNew($m_form["primarykey"]);
	}

	/**
	 */
	function form_row($row, $tmp_data, $m_form, $required_typecheck_result = 0) {
		// place fieldname in row template

		$tmp_data = str_replace("::LABEL::", $this->get_fieldlabel(), $tmp_data);

		$field = $this->form_field($row, $m_form, $required_typecheck_result);
		$tmp_data = str_replace("::FIELD::", $field, $tmp_data);

		$tmp_data = str_replace("::REQUIRED::", "", $tmp_data);

		return $tmp_data;
	}

	/**
	 */
	function push_field_into_query_array($query_fields) {
		return $query_fields;
	}
}
