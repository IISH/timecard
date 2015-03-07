<?php 
require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_jira_url_browse extends class_field {
	// TODOEXPLAIN
	function view_field($row, $criteriumResult = 0) {
		$ret = '';

		$value = trim(parent::view_field($row, $criteriumResult));
		$jira_url_browse = class_settings::getSetting('jira_url_browse');

		$separator = '';

		$arr = explode(' ', $value);
		foreach ( $arr as $url ) {
			$ret .= $separator . "<a href=\"$jira_url_browse$url\" target=\"_blank\">$url</a>";
			$separator = ' ';
		}

		return $ret;
	}
}
