<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new class_page('design/page.php', $connection_settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.contact'));
$oPage->setTitle('Timecard | Contact');
$oPage->setContent( $settings_from_database["page_contact"] );

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";
?>