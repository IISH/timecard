<?php 
// modified: 2014-03-17

class class_settings {

	// TODOEXPLAIN
	function getSettings( $handle ) {
		$arr = array();

		$result = mysql_query("SELECT * FROM settings ", $handle);
		if ( mysql_num_rows($result) > 0 ) {

			while ($row = mysql_fetch_assoc($result)) {
				$arr[ $row["property"] ] = $row["value"];
			}
			mysql_free_result($result);

		}

		return $arr;
	}
}
