<?php 
// modified: 2012-11-26

// connection to the timecard database
$dbhandleTimecard = mysql_connect($settings["timecard_server"], $settings["timecard_user"], $settings["timecard_password"]) or die("Couldn't connect to MySql Server on " . $settings["timecard_server"]);

// select a database to work with
$selectedTimecard = mysql_select_db($settings["timecard_database"], $dbhandleTimecard) or die("Couldn't open database " . $settings["timecard_database"]);
?>