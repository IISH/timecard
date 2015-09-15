<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasReportsAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('exports.misc'));
$oPage->setTitle('Timecard | Miscellaneous');
$oPage->setContent(createExportOracleContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createExportOracleContent() {

	$ret = '<h2>Miscellaneous</h2><br>';

	$ret .= '<a href="misc01.php">Misc 01</a> (Booked on Department)<br>';

	return $ret;
}
