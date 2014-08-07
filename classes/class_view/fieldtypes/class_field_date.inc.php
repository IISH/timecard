<?php 
// modified: 2012-11-07

require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_date extends class_field {
	private $m_format;

	// TODOEXPLAIN
	function class_field_date($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_format = '';

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only string specific parameters

					case "format":
						$this->m_format = $fieldsettings["format"];
						break;

				}
			}
		}

	}

	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {
		$retval = parent::view_field($row, $criteriumResult);

		if ( $retval != '' ) {
			// verwijder tijd uit datum
			$retval = trim(str_replace('12:00:00:000AM', '', $retval));
			$retval = trim(str_replace('12:00AM', '', $retval));

			if ( $this->m_format != '' ) {
				$retval = date($this->m_format, strtotime($retval));
			}
		}

		if ( is_array($criteriumResult) ) {
			$href2otherpage = $criteriumResult["href"];
			$url_onclick = $criteriumResult["onclick"];
		} else {
			$href2otherpage = $this->get_href();
			$url_onclick = $this->get_onclick();
		}

		if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {

			if ( $href2otherpage <> "" ) {
				$retval = $this->get_if_no_value($retval);

				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($href2otherpage, $row);
				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($href2otherpage);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($url_onclick, $row);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($url_onclick);

				if ( $url_onclick <> "" ) {
					$url_onclick = " onClick=\"" . $url_onclick . "\"";
				}

				$target = $this->get_target();
				if ( $target <> "" ) {
					$target = " target=\"" . $target . "\" ";
				}

				$alttitle = $this->get_alttitle();
				if ( $alttitle != '' ) {
					$alttitle = " title=\"" . $alttitle . "\" ";
				}

				$retval = "<A HREF=\"" . $href2otherpage . "\" " . $url_onclick . " " . $target . " " . $alttitle . ">" . $retval . "</a>";

			}

			// no break - keep together
			if ( $this->get_nobr() === true ) {
				$retval = "<nobr>" . $retval . "</nobr>";
			}

			if ( is_array($criteriumResult ) ) {
				$fieldname = $criteriumResult["fieldname"];
				$fieldname_pointer = $criteriumResult["fieldname_pointer"];
			} else {
				$fieldname = $this->get_fieldname();
				$fieldname_pointer = $this->get_fieldname_pointer();
			}

			if ( $fieldname_pointer <> "" ) {
				$fieldname_pointer = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($fieldname_pointer, $row);
				$fieldname_pointer = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($fieldname_pointer);
			}

		} else {
			if ( $href2otherpage <> "" ) {
				$retval = $this->get_if_no_value($retval);
			}

			// no break - keep together
			if ( $this->get_nobr() === true ) {
				$retval = "<nobr>" . $retval . "</nobr>";
			}
		}

		return $retval;
	}

}
