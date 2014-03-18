<?php 
// modified version of: http://lostechies.com/seanbiefeld/2011/10/21/simple-xml-to-json-with-php/

class XmlToJson {

	public function Parse ($tekst) {

		$tekst = str_replace(array("\n", "\r", "\t"), '', $tekst);
		$tekst = trim(str_replace('"', "'", $tekst));
		$simpleXml = simplexml_load_string($tekst);
		$json = json_encode($simpleXml);

		return $json;
	}

}
