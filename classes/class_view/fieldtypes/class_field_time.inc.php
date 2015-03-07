<?php 
require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_time extends class_field {

	// TODOEXPLAIN
	function class_field_time($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_if_zero_hide = 0;

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only time specific parameters

					case "if_zero_hide":
						$this->m_if_zero_hide = $fieldsettings["if_zero_hide"];
						break;

				}
			}
		}
	}

	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {
		$retval = parent::view_field($row, $criteriumResult);

		if ( $this->m_if_zero_hide == 1 && $retval == 0 ) {
			$retval = '';
		} else {
			$retval = class_datetime::ConvertTimeInMinutesToTimeInHoursAndMinutes($retval);

			if ( is_array($criteriumResult) ) {
				$href2otherpage = $criteriumResult["href"];
				$url_onclick = $criteriumResult["onclick"];
			} else {
				$href2otherpage = $this->get_href();
				$url_onclick = $this->get_onclick();
			}

			if ( $href2otherpage <> "" ) {
				$retval = $this->get_if_no_value($retval);

				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($href2otherpage, $row);
				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($href2otherpage);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($url_onclick, $row);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($url_onclick);

				if ( $url_onclick <> "" ) {
					$url_onclick = " onClick=\"" . $url_onclick . "\"";
				}

				$retval = "<A HREF=\"" . $href2otherpage . "\" " . $url_onclick . ">" . $retval . "</a>";
			}
		}

		return $retval;
	}
}
