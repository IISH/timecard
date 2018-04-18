<?php 
class class_file {
	function getFileSource($bestandsnaam) {
		//$return_value = implode("\n", file($bestandsnaam));
		$return_value = implode("", file($bestandsnaam));

		return $return_value;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
