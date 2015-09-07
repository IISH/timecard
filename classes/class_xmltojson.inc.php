<?php 
// modified version of: http://lostechies.com/seanbiefeld/2011/10/21/simple-xml-to-json-with-php/

class XmlToJson {

	public function Parse ($text) {

		$text = str_replace(array("\n", "\r", "\t"), '', $text);
		$text = trim(str_replace('"', "'", $text));

		$simpleXml = simplexml_load_string($text);

		return json_encode($simpleXml);
	}
}
