<?php 
//
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

//
$id = substr(trim($protect->request_positive_number_or_empty('get', "id")), 0, 4);
$dowhat = substr(trim($_GET["dowhat"]), 0, 1);
if ( !in_array($dowhat, array('a', 'r') ) ) {
	die('Error 541742526');
}

$fav = substr(trim($protect->request_only_characters_or_numbers_or_empty('get', "fav")), 0, 10);
if ( !in_array($fav, array('', 'vakantie', 'present', 'hoursleft', 'birthdays') ) ) {
	die('Error 541774522');
}

//
$query = '';
if ( $dowhat == 'a' ) {
	// add to database
	$query = 'INSERT INTO EmployeeFavourites(TimecardID, ProtimeID, type) VALUES(' . $oWebuser->getTimecardId() . ', ' . $id . ', \'' . $fav . '\') ';
	// show remove button in window
	$alttitle = "Click to remove the person from your favourites";
	$div = '<a href="#" onClick="addRemove(' . $id . ', \'r\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink favourites_on">&#9733;</a>';
} elseif ( $dowhat == 'r' ) {
	// remove from database
	$query = 'DELETE FROM EmployeeFavourites WHERE TimecardID=' . $oWebuser->getTimecardId() . " AND ProtimeID=" . $id . ' AND type=\'' . $fav . '\' ';
	// show add button in window
	$alttitle = "Click to add the person from your favourites";
	$div = '<a href="#" onClick="addRemove(' . $id . ', \'a\');" alt="' . $alttitle . '" title="' . $alttitle . '" class="nolink favourites_off">&#9733;</a>';
}

if ( $query != '' ) {
	$stmt = $dbConn->prepare($query);
	$stmt->execute();

	echo $div;
} else {
	echo 'Error';
}

require_once "classes/_db_disconnect.inc.php";
