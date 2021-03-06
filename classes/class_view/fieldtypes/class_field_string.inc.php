<?php 
require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_string extends class_field {
	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only string specific parameters

				}
			}
		}
	}

	function view_field($row) {
		$retval = parent::view_field($row);

		$href2otherpage = $this->get_href();
		$url_onclick = $this->get_onclick();

		if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {

			if ( $href2otherpage <> "" ) {
				$retval = $this->get_if_no_value($retval);

				$no_href = 0;
				$noHrefIf = $this->get_no_href_if();

				if ( is_array($noHrefIf)
					&& isset($noHrefIf["field"]) && isset($noHrefIf["value"]) && isset($noHrefIf["operator"])
					&& isset($row[$noHrefIf["field"]] )
				) {
					//
					switch ( $noHrefIf["operator"] ) {
						case "<>":
							if ( $row[$noHrefIf["field"]] <> $noHrefIf["value"] ) {
								$no_href = 1;
							}
							break;
						case "==":
							if ( $row[$noHrefIf["field"]] == $noHrefIf["value"] ) {
								$no_href = 1;
							}
							break;
						case "=":
							if ( $row[$noHrefIf["field"]] == $noHrefIf["value"] ) {
								$no_href = 1;
							}
							break;
						case ">":
							if ( $row[$noHrefIf["field"]] > $noHrefIf["value"] ) {
								$no_href = 1;
							}
							break;
						case ">=":
							if ( $row[$noHrefIf["field"]] >= $noHrefIf["value"] ) {
								$no_href = 1;
							}
							break;
						case "<":
							if ( $row[$noHrefIf["field"]] < $noHrefIf["value"] ) {
								$no_href = 1;
							}
							break;
						case "<=":
							if ( $row[$noHrefIf["field"]] <= $noHrefIf["value"] ) {
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

				$target = $this->get_target();
				if ( $target <> "" ) {
					$target = "target=\"" . $target . "\"";
				}

				$alttitle = $this->get_alttitle();
				if ( $alttitle != '' ) {
					$alttitle = " title=\"" . $alttitle . "\" ";
				}

				if ( $no_href == 0 ) {
					$retval = "<A HREF=\"" . $href2otherpage . "\" " . $url_onclick . " " . $target . $alttitle . ">" . $retval . "</a>";
				}

			}

			// no break - keep together
			if ( $this->get_nobr() === true ) {
				$retval = "<nobr>" . $retval . "</nobr>";
			}

			$fieldname = $this->get_fieldname();
			$fieldname_pointer = $this->get_fieldname_pointer();

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
