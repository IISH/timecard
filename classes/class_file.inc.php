<?php 
// modified: 2012-11-25

class class_file {

	// TODOEXPLAIN
	function class_file() {
	}

	// TODOEXPLAIN
	function getFileSource($bestandsnaam) {
		$return_value = implode("\n", file($bestandsnaam));

		return $return_value;
	}
}
?>