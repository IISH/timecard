<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

//
require_once __DIR__ . "/../vendor/autoload.php";

$settings = array();

require_once dirname(__FILE__) . "/../sites/default/settings.php";
require_once dirname(__FILE__) . "/adserver.inc.php";
require_once dirname(__FILE__) . "/class_authentication.inc.php";
require_once dirname(__FILE__) . "/class_calendar.inc.php";
require_once dirname(__FILE__) . "/class_contentdesign.inc.php";
require_once dirname(__FILE__) . "/class_dailyaddition.inc.php";
require_once dirname(__FILE__) . "/class_date.inc.php";
require_once dirname(__FILE__) . "/class_date_static.inc.php";
require_once dirname(__FILE__) . "/class_datetime.inc.php";
require_once dirname(__FILE__) . "/class_employee.inc.php";
require_once dirname(__FILE__) . "/class_employee_hours_for_planning.inc.php";
require_once dirname(__FILE__) . "/class_employee_hours_per_week.inc.php";
require_once dirname(__FILE__) . "/class_employee_not_work_related_absences.inc.php";
require_once dirname(__FILE__) . "/class_employee_vast_werk.inc.php";
require_once dirname(__FILE__) . "/class_feestdag.inc.php";
require_once dirname(__FILE__) . "/class_feestdagen.inc.php";
require_once dirname(__FILE__) . "/class_length_of_workday.inc.php";
require_once dirname(__FILE__) . "/class_misc01.inc.php";
require_once dirname(__FILE__) . "/class_misc02.inc.php";
require_once dirname(__FILE__) . "/class_national_holiday_brugdag.inc.php";
require_once dirname(__FILE__) . "/class_page.inc.php";
require_once dirname(__FILE__) . "/class_prevnext.inc.php";
require_once dirname(__FILE__) . "/class_protime_user.inc.php";
require_once dirname(__FILE__) . "/class_protime_worklocation.inc.php";
require_once dirname(__FILE__) . "/class_recentlyused.inc.php";
require_once dirname(__FILE__) . "/class_refresh_employee_hours_for_planning.inc.php";
require_once dirname(__FILE__) . "/class_refresh_employee_hours_per_week.inc.php";
require_once dirname(__FILE__) . "/class_shortcut.inc.php";
require_once dirname(__FILE__) . "/class_shortcuts.inc.php";
require_once dirname(__FILE__) . "/class_tcdatetime.inc.php";
require_once dirname(__FILE__) . "/class_website_protection.inc.php";
require_once dirname(__FILE__) . "/class_project.inc.php";
require_once dirname(__FILE__) . "/class_project_static.inc.php";
require_once dirname(__FILE__) . "/class_project_totals.inc.php";
require_once dirname(__FILE__) . "/class_workhours.inc.php";
require_once dirname(__FILE__) . "/class_workhours_static.inc.php";
require_once dirname(__FILE__) . "/class_settings.inc.php";
require_once dirname(__FILE__) . "/mail.inc.php";
require_once dirname(__FILE__) . "/misc.inc.php";
require_once dirname(__FILE__) . "/pdo.inc.php";
require_once dirname(__FILE__) . "/syncinfo.inc.php";

//
require_once dirname(__FILE__) . "/_misc_functions.inc.php";

//
$protect = new class_website_protection();

// connect to databases
$db = new class_pdo( $databases['default'] );
$dbConn = $db->getConnection();

// TODO remove before submit
//$_SESSION["timecard"]["id"] = 1;
error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);
//error_reporting(E_ALL & ~E_NOTICE);

//
$oWebuser = new class_employee( ( isset($_SESSION["timecard"]["id"]) ? $_SESSION["timecard"]["id"] : 0 ), $settings);

//
require_once dirname(__FILE__) . "/class_menu.inc.php";

// make menu sublist depending on authentication
$menuList = $menu->getMenuSubset();

header('Content-Type: text/html; charset=utf-8');
