<?php 
require_once "classes/start.inc.php";

$_SESSION["timecard"]["id"] = 0;
Header("Location: login.php");

die();
?>