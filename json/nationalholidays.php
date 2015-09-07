<?php
require_once "../classes/start.inc.php";
require_once "../classes/class_feestdagen.inc.php";

$holidays = class_feestdagen::getNationalHolidays();

$arr = array();
foreach ( $holidays as $holiday ) {
	$arr[] = array(
			'id' => $holiday->getId()
			, 'date' => $holiday->getDate()
			, 'description' => $holiday->getDescription()
			, 'vooreigenrekening' => $holiday->getVooreigenrekening()
			, 'isdeleted' => $holiday->getIsdeleted()
		);
}

// return value, set content type json
header('Content-type: application/json');
echo json_encode($arr);