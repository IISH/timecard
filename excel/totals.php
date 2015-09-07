<?php

$username = ( isset($_GET["u"]) ? $_GET["u"] : '' );
$projectId = ( isset($_GET["p"]) ? $_GET["p"] : '' );
$year = ( isset($_GET["y"]) ? $_GET["y"] : '' );
$month = ( isset($_GET["m"]) ? $_GET["m"] : '' );

$username = trim($username);
$projectId = trim($projectId);
$year = trim($year);
$month = trim($month);

echo $username . '-' . $projectId . '-' . $year . '-' . $month . '-' . rand(1000,9999);
