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

function createContactContent() {
	global $oWebuser;

	if ( $oWebuser->isLoggedIn() ) {
		$pageTemplate = "page_contact_when_logged_in";
	} else {
		$pageTemplate = "page_contact_when_not_logged_in";
	}

	// get design
	$design = new class_contentdesign($pageTemplate);

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	// get extra
	$extra = new class_contentdesign("page_contact_extra");
	$ret .= $extra->getHeader();
	$ret .= $extra->getContent();
	$ret .= $extra->getFooter();

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
