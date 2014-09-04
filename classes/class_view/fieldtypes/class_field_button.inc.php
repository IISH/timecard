<?php 
// modified: 2012-11-07

require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_button extends class_field {
	private $m_buttonlabel;

	// TODOEXPLAIN
	function class_field_button($fieldsettings) {
		parent::class_field($fieldsettings);

		$this->m_buttonlabel = '';

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only integer specific parameters

					case "buttonlabel":
						$this->m_buttonlabel = $fieldsettings["buttonlabel"];
						break;

				}
			}
		}
	}

	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {
		$retval = $this->m_buttonlabel;

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

		return $retval;
	}

}