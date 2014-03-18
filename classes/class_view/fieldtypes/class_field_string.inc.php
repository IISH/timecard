<?php 
// version: 2012-11-07

require_once("./classes/class_view/fieldtypes/class_field.inc.php");

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
	function view_field($row, $criteriumResult = 0) {
		$retval = parent::view_field($row, $criteriumResult);

		if ( is_array($criteriumResult) ) {
			$href2otherpage = $criteriumResult["href"];
			$url_onclick = $criteriumResult["onclick"];
		} else {
			$href2otherpage = $this->get_href();
			$url_onclick = $this->get_onclick();
		}

		if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {

			if ( $href2otherpage <> "" ) {
				$retval = $this->get_if_no_value_value($retval);

				$no_href = 0;
				if ( is_array($this->m_no_href_if) ) {
					//
					switch ( $this->m_no_href_if["operator"] ) {
						case "<>":
							if ( $row[$this->m_no_href_if["field"]] <> $row[$this->m_no_href_if["value"]] ) {
								$no_href = 1;
							}
							break;
						case "==":
							if ( $row[$this->m_no_href_if["field"]] == $row[$this->m_no_href_if["value"]] ) {
								$no_href = 1;
							}
							break;
						case "=":
							if ( $row[$this->m_no_href_if["field"]] == $row[$this->m_no_href_if["value"]] ) {
								$no_href = 1;
							}
							break;
						case ">":
							if ( $row[$this->m_no_href_if["field"]] > $row[$this->m_no_href_if["value"]] ) {
								$no_href = 1;
							}
							break;
						case ">=":
							if ( $row[$this->m_no_href_if["field"]] >= $row[$this->m_no_href_if["value"]] ) {
								$no_href = 1;
							}
							break;
						case "<":
							if ( $row[$this->m_no_href_if["field"]] < $row[$this->m_no_href_if["value"]] ) {
								$no_href = 1;
							}
							break;
						case "<=":
							if ( $row[$this->m_no_href_if["field"]] <= $row[$this->m_no_href_if["value"]] ) {
								$no_href = 1;
							}
							break;
					}
				}

				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($href2otherpage, $row);
				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($href2otherpage);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($url_onclick, $row);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($url_onclick);

				if ( $url_onclick <> "" ) {
					$url_onclick = " onClick=\"" . $url_onclick . "\"";
				}

				$target = $this->m_target;
				if ( $target <> "" ) {
					$target = "target=\"" . $target . "\"";
				}

				$alttitle = $this->m_alttitle;
				if ( $alttitle != '' ) {
					$alttitle = " alt=\"" . $alttitle . "\" title=\"" . $alttitle . "\" ";
				}

				if ( $no_href == 0 ) {
					$retval = "<A HREF=\"" . $href2otherpage . "\" " . $url_onclick . " " . $target . $alttitle . ">" . $retval . "</a>";
				}

			}

			// no break - keep together
			if ( $this->m_nobr === true ) {
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
				$retval = $this->get_if_no_value_value($retval);
			}

			// no break - keep together
			if ( $this->m_nobr === true ) {
				$retval = "<nobr>" . $retval . "</nobr>";
			}
		}

		return $retval;
	}

}
?>