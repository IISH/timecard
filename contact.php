<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.contact'));
$oPage->setTitle('Timecard | Contact');
$oPage->setContent( createContactContent() );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createContactContent() {
	// get design
	$design = new class_contentdesign("page_contact");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
