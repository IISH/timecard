<?php
die('deprecated. contact gcu');

require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

$query_update = "UPDATE Employees SET HoursDoubleField=HoursDoubleField*(-1) WHERE ID=" . $oWebuser->getTimecardId();
$stmt = $dbConn->prepare($query_update);
$stmt->execute();

require_once "classes/_db_disconnect.inc.php";

$backurl = "aboutme.php";
Header("location: " . $backurl);
die('go <a href="' . $backurl . '">back</a>');
