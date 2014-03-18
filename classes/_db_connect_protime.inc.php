<?php 
// version: 2012-11-26

// connection to the database
$dbhandleProtime = mssql_connect($connection_settings["protime_server"], $connection_settings["protime_user"], $connection_settings["protime_password"]) or die("Couldn't connect to SQL Server on: " . $connection_settings["protime_server"]);

// select a database to work with
$selectedProtime = mssql_select_db($connection_settings["protime_database"], $dbhandleProtime) or die("Couldn't open database " . $connection_settings["protime_database"]);
?>