<?php
session_start(); ///////////////

$settings = array();

require_once dirname(__FILE__) . "/../sites/default/settings.php";
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
//require_once dirname(__FILE__) . "/class_hoursperweek.inc.php";
require_once dirname(__FILE__) . "/class_length_of_workday.inc.php";
require_once dirname(__FILE__) . "/class_mysql.inc.php";
require_once dirname(__FILE__) . "/class_national_holiday_brugdag.inc.php";
require_once dirname(__FILE__) . "/class_page.inc.php";
require_once dirname(__FILE__) . "/class_prevnext.inc.php";
require_once dirname(__FILE__) . "/class_protime_user.inc.php";
require_once dirname(__FILE__) . "/class_protime_worklocation.inc.php";
require_once dirname(__FILE__) . "/class_recentlyused.inc.php";
require_once dirname(__FILE__) . "/class_refresh_employee_hours_for_planning.inc.php";
require_once dirname(__FILE__) . "/class_refresh_employee_hours_per_week.inc.php";
require_once dirname(__FILE__) . "/class_shortcuts.inc.php";
require_once dirname(__FILE__) . "/class_syncprotimemysql.inc.php";
require_once dirname(__FILE__) . "/class_tcdatetime.inc.php";
require_once dirname(__FILE__) . "/class_website_protection.inc.php";
require_once dirname(__FILE__) . "/class_project.inc.php";
require_once dirname(__FILE__) . "/class_project_static.inc.php";
require_once dirname(__FILE__) . "/class_project_totals.inc.php";
require_once dirname(__FILE__) . "/class_workhours.inc.php";
require_once dirname(__FILE__) . "/class_workhours_static.inc.php";
require_once dirname(__FILE__) . "/class_settings.inc.php";

//
require_once dirname(__FILE__) . "/_misc_functions.inc.php";

//
$protect = new class_website_protection();

// TODO remove before submit
//$_SESSION["timecard"]["id"] = 1;
//error_reporting(E_ERROR | E_PARSE);
//error_reporting(E_ALL);

//
$oWebuser = new class_employee($_SESSION["timecard"]["id"], $settings);

//
require_once dirname(__FILE__) . "/class_menu.inc.php";

// make menu sublist depending on authentication
$menuList = $menu->getMenuSubset();

// always connect to timecard database
$oConn = new class_mysql($databases['default']);
$oConn->connect();
