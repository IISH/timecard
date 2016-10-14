<?php 
require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_integer extends class_field {

	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only integer specific parameters

				}
			}
		}
	}

	function view_field($row) {
		$retval = parent::view_field($row);

		$href2otherpage = $this->get_href();
		$url_onclick = $this->get_onclick();

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

		return $retval;
	}

}
