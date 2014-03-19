<?php 
// modified: 2012-11-26

// connection to the timecard database
$dbhandleTimecard = mysql_connect($connection_settings["timecard_server"], $connection_settings["timecard_user"], $connection_settings["timecard_password"]) or die("Couldn't connect to MySql Server on " . $connection_settings["timecard_server"]);

// select a database to work with
$selectedTimecard = mysql_select_db($connection_settings["timecard_database"], $dbhandleTimecard) or die("Couldn't open database " . $connection_settings["timecard_database"]);
?>