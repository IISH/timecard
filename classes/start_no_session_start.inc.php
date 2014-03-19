<?php 
ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

session_start(); /////////////// MOET EIGENLIJK WEG

$connection_settings = array();

require_once "classes/settings.inc.php";
require_once "classes/class_authentication.inc.php";
require_once "classes/class_calendar.inc.php";
require_once "classes/class_date.inc.php";
require_once "classes/class_dateasstring.inc.php";
require_once "classes/class_datetime.inc.php";
require_once "classes/class_employee.inc.php";
require_once "classes/class_employees.inc.php";
require_once "classes/class_feestdag.inc.php";
require_once "classes/class_hoursperweek.inc.php";
require_once "classes/class_page.inc.php";
require_once "classes/class_prevnext.inc.php";
require_once "classes/class_recentlyused.inc.php";
require_once "classes/class_shortcuts.inc.php";
require_once "classes/class_website_protection.inc.php";
require_once "classes/class_protime_user.inc.php";
require_once "classes/class_settings.inc.php";

//
require_once "classes/_misc_functions.inc.php";

//
require_once "classes/_db_connect_timecard.inc.php";
require_once "classes/_db_connect_protime.inc.php";

//
$protect = new class_website_protection();

//
$oWebuser = new class_employee($_SESSION["timecard"]["id"], $connection_settings);

//
require_once "classes/class_menu.inc.php";

// make sublist depending on authentication
$menuList = $menu->getMenuSubset();

// settings from database
$settings_from_database = class_settings::getSettings( $dbhandleTimecard );
?>