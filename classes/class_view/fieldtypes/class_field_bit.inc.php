<?php 
require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_bit extends class_field {
	private $m_show_different_values;
	private $m_different_true_value;
	private $m_different_false_value;

	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		$this->m_show_different_values = false;
		$this->m_different_true_value = '';
		$this->m_different_false_value = '';

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only bit specific parameters

					case "show_different_values":
						$this->m_show_different_values = $fieldsettings["show_different_values"];
						break;
					case "different_true_value":
						$this->m_different_true_value = $fieldsettings["different_true_value"];
						break;
					case "different_false_value":
						$this->m_different_false_value = $fieldsettings["different_false_value"];
						break;

				}
			}
		}
	}

	function view_field($row) {
		$retval = parent::view_field($row);

		if ( $this->get_show_different_values() == true || $this->get_show_different_values() == 1 ) {
			if ( $retval == "1" || $retval == true || $retval == "on" ) {
				$retval = $this->get_different_true_value();
			} else {
				$retval = $this->get_different_false_value();
			}
		}

		return $retval;
	}

	function get_show_different_values() {
		return $this->m_show_different_values;
	}

	function get_different_true_value() {
		return $this->m_different_true_value;
	}

	function get_different_false_value() {
		return $this->m_different_false_value;
	}

}
