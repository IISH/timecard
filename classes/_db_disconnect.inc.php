<?php 
// close ALL connections
if ( $dbhandleProtime ) {
	@mssql_close($dbhandleProtime);
}
if ( $dbhandleTimecard ) {
	@mysql_close($dbhandleTimecard);
}
?>