<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasFaAuthorisation() || $oWebuser->hasReportsAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('exports.misc'));
$oPage->setTitle('Timecard | Miscellaneous');
$oPage->setContent(createContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createContent() {
	global $protect;

	$ret = '<h2>Misc 02 (User Rights)</h2><br>';

	$ret .= class_misc::convertArrayToHtmlTable(class_misc02::getMisc02());

	return $ret;
}
