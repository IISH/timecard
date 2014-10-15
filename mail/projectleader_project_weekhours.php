<?php
require_once "../classes/start.inc.php";
require_once "../classes/class_project.inc.php";

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
}
if ( trim( $cron_key ) != class_settings::getSetting('cron_key') ) {
	die('Error: Incorrect cron key...');
}

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n<hr>\n";

$startdate =  class_date_static::previousWeekMonday();
$enddate = class_date_static::previousWeekSunday();

// get all enabled projects with a project leader
$projects = class_project_static::getEnabledProjectsWithAProjectleader();

$fieldseparator = "\t";

// loop found project
foreach ( $projects as $oProject ) {
	$total = 0;

	// project info
	$mail_subject = "Timecard Project Hours: " . $oProject->getDescription() . " (" . $startdate . " - " . $enddate . ")";
	$mail_body = "Project:" . $fieldseparator . $oProject->getDescription() . " \n";
	$mail_body .= "Project number:" . $fieldseparator . $oProject->getProjectnumber() . " \n";

	// get projectleader
	$oProjectleader = $oProject->getProjectleader();
	$mail_body .= "Project leader:" . $fieldseparator . $oProjectleader->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin($oProjectleader->getLastname()) . " \n\n";

	// start / end date
	$mail_body .= "From:" . $fieldseparator . $startdate . " \n";
	$mail_body .= "Until (incl.):" . $fieldseparator . $enddate . " \n\n";

	// get list of project workhours for specified period
	$workhours = class_workhours_static::getWorkhoursPerEmployeeGrouped($oProject->getId(), $startdate, $enddate);

	// name / hours
	foreach ($workhours as $p) {
		$mail_body .= $p["employee"]->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin($p["employee"]->getLastname()) . ":" . $fieldseparator;
		$mail_body .= number_format(class_misc::convertMinutesToHours($p["timeinminutes"]),2) . " hour(s) \n";
		$total += $p["timeinminutes"];
	}
	$mail_body .= "Total:" . $fieldseparator;
	$mail_body .=  number_format(class_misc::convertMinutesToHours($total),2) . " hour(s) \n";

	//
	$mail_body .= "\nEmail sent on:" . $fieldseparator . date("Y-m-d H:i:s") . " \n\n";

	// show message on screen
	echo "Mail subject: " . str_replace("\t", " &nbsp; &nbsp; ", str_replace("\n", "<br>\n", $mail_subject)) . "<br><br>\n";
	echo "Mail body: " . str_replace("\t", " &nbsp; &nbsp; ", str_replace("\n", "<br>\n", $mail_body)); echo "<br>";

	// check if weekly report e-mail is enabled
	if ( !$oProject->getEnableweeklyreportmail() ) {
		echo "SKIPPED: Weekly report e-mail is disabled for this project.";
	} else {
		// get email projectleader
		$projectleaderEmail = $oProjectleader->getEmail();
		if ( $projectleaderEmail != '' ) {
			// send email to projectleader
			//mail($projectleaderEmail, $mail_subject, $mail_body);
			//mail("gcu@iisg.nl", $mail_subject, $mail_body);
			echo "DISABLED: Would have been sent to $projectleaderEmail";
		} else {
			echo "SKIPPED: Weekly report e-mail is disabled for this project.";
		}
	}

	echo "<hr>\n";
}

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";