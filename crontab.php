<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !( $oWebuser->hasAdminAuthorisation() ) ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('administrator.crontab'));
$oPage->setTitle('Timecard | Crontab');
$oPage->setContent(createCrontabContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createCrontabContent() {
	global $protect, $settings, $oWebuser;

	// get design
	$design = new class_contentdesign("page_crontab");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret = $design->getContent();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
