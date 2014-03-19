<?php 
// modified: 2009-02-19

require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_time_double_field extends class_field {
    private $m_possible_hour_values;
    private $m_possible_minute_values;

	// TODOEXPLAIN
	function class_field_time_double_field($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_possible_hour_values = array("0", "1", "2", "3", "4", "5", "6", "7", "8");
		$this->m_possible_minute_values = array("00", "15", "30", "45");

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only time specific parameters

					case "possible_hour_values":
						$this->m_possible_hour_values = $fieldsettings["possible_hour_values"];
						break;

					case "possible_minute_values":
						$this->m_possible_minute_values = $fieldsettings["possible_minute_values"];
						break;

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
		$veldwaarde_hours = "0";
		$veldwaarde_minutes = "0";

		// welke waarde moeten we gebruiken, uit de db? of uit de form?
		// indien niet goed bewaard gebruik dan de form waarde
		if ( $required_typecheck_result == 0 ) {
			$veldwaarde_hours = $_POST["FORM_" . $this->get_fieldname() . "_HOURS"];
			$veldwaarde_minutes = $_POST["FORM_" . $this->get_fieldname() . "_MINUTES"];
		} else {
			$veldwaarde = $row[$this->get_fieldname()];

			$onNewValue = $this->get_onNew($m_form["primarykey"]);

			if ( $onNewValue != "" ) {
				$veldwaarde = $onNewValue;
			}

			if ( $veldwaarde == '' ) {
				$veldwaarde = 0;
			}
			$veldwaarde_hours = floor($veldwaarde/60);
			$veldwaarde_minutes = $veldwaarde - ( $veldwaarde_hours * 60 );
		}

		$veldwaarde_minutes = substr('0' . $veldwaarde_minutes, -2);

		$inputfield = '';

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		// uren
		$inputfield .= "<select name=\"FORM_::FIELDNAME::_HOURS\">\r\n";
		$found = 0;

		$possible_hour_values = $this->m_possible_hour_values;
		if ( !in_array( $veldwaarde_hours, $possible_hour_values ) ) {
			$possible_hour_values[] = $veldwaarde_hours;
		}
		asort($possible_hour_values);

		foreach ( $possible_hour_values as $urenArrayItem ) {
			$inputfield .= "<option value=\"" . $urenArrayItem . "\"";
			if ( $urenArrayItem == $veldwaarde_hours ) {
				$inputfield .= " SELECTED ";
				$found = 1;
			}
			$inputfield .= ">" . $urenArrayItem . "</option>\r\n";
		}
		$inputfield .= "</select>\r\n";

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		// separator
		$inputfield .= " <b>:</b> \r\n";

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		// tijd
		$inputfield .= "<select name=\"FORM_::FIELDNAME::_MINUTES\">\r\n";
		$found = 0;

		$possible_minute_values = $this->m_possible_minute_values;
		if ( !in_array( $veldwaarde_minutes, $possible_minute_values ) ) {
			$possible_minute_values[] = $veldwaarde_minutes;
		}
		asort($possible_minute_values);

		foreach ( $possible_minute_values as $minutenArrayItem ) {
			$inputfield .= "<option value=\"" . $minutenArrayItem . "\"";
			if ( $minutenArrayItem == $veldwaarde_minutes ) {
				$inputfield .= " SELECTED ";
				$found = 1;
			}
			$inputfield .= ">" . $minutenArrayItem . "</option>\r\n";
		}
		$inputfield .= "</select>\r\n";

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);

		$inputfield = str_replace("::VALUE_HOURS::", $veldwaarde_hours, $inputfield);
		$inputfield = str_replace("::VALUE_MINUTES::", $veldwaarde_minutes, $inputfield);

		return $inputfield;
	}

	// TODOEXPLAIN
	function is_field_value_correct($veldwaarde = "") {
		$retval = 1; // default = okay

		if ( $this->is_field_required() == 1 ) {
			if ( $veldwaarde == 0 ) {
				$retval = 0;
			}
		}

		return $retval;
	}

	// TODOEXPLAIN
	function get_form_value($field = '') {
		if ( $field == '' ) {
			$hours = $_POST["FORM_" . $this->get_fieldname() . "_HOURS"];
			$minutes = $_POST["FORM_" . $this->get_fieldname() . "_MINUTES"];
		} else {
			$hours = $_POST["FORM_" . $field . "_HOURS"];
			$minutes = $_POST["FORM_" . $field . "_MINUTES"];
		}

		if ( $hours == '' ) {
			$hours = 0;
		}
		if ( $minutes == '' ) {
			$minutes = 0;
		}

		$retval = ( $hours * 60 ) + $minutes;

		return $retval;
	}

	// TODOEXPLAIN
	function push_field_into_query_array($query_fields) {
		$value = $this->get_form_value();
		array_push($query_fields, array($this->get_fieldname() => $value));

		return $query_fields;
	}
}
?>