<?php 
require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_jira_url_browse extends class_field {
	function view_field($row) {
		$ret = '';

		$value = trim(parent::view_field($row));
		$jira_url_browse = Settings::get('jira_url_browse');

		$separator = '';

		$arr = explode(' ', $value);
		foreach ( $arr as $url ) {
			$ret .= $separator . "<a href=\"$jira_url_browse$url\" target=\"_blank\">$url</a>";
			$separator = ' ';
		}

		return $ret;
	}
}
