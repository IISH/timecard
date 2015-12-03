<?php
require_once "../classes/start.inc.php";
require_once "../classes/class_department.inc.php";
require_once "../classes/class_department_static.inc.php";

ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

// check cron key
$cron_key = '';
if ( isset($_GET["cron_key"]) ) {
	$cron_key = $_GET["cron_key"];
} elseif ( isset($_POST["cron_key"]) ) {
	$cron_key = $_POST["cron_key"];
}
if ( trim( $cron_key ) != Settings::get('cron_key') ) {
	die('Error: Incorrect cron key...');
}

// show time
echo "Start time: " . date("Y-m-d H:i:s") . "<br>\n<hr>\n";

$startdate =  class_date_static::previousWeekMonday();
$enddate = class_date_static::previousWeekSunday();

// get all enabled departments with a department leader
$departments = class_department_static::getEnabledDepartmentsWithAHead();

$fieldseparator = "\t";

// loop found departments
foreach ( $departments as $oDepartment ) {
	$total = 0;

	// department info
	$mail_subject = "Timecard Department Hours: " . $oDepartment->getName() . " (" . $startdate . " - " . $enddate . ")";
	$mail_body = "Department:" . $fieldseparator . $oDepartment->getName() . " \n";

	// get department head
	$oHead = $oDepartment->getHead();
	$mail_body .= "Head:" . $fieldseparator . $oHead->getFirstname . ' ' . verplaatsTussenvoegselNaarBegin( $oHead->getLastname() ) . " \n\n";

	// start / end date
	$mail_body .= "From:" . $fieldseparator . $startdate . " \n";
	$mail_body .= "Until (incl.):" . $fieldseparator . $enddate . " \n\n";

	//
	$employees = $oDepartment->getEmployeesAndHours($startdate, $enddate);
	foreach ( $employees as $oEmployee ) {
		$mail_body .= $oEmployee["employee"]->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin( $oEmployee["employee"]->getLastname() ) . ":" . $fieldseparator;
		$mail_body .= number_format(class_misc::convertMinutesToHours( $oEmployee["timeinminutes"] ),2, ',', '.') . " hour(s) \n";
		$total += $oEmployee["timeinminutes"];
	}

	$mail_body .= "Total:" . $fieldseparator;
	$mail_body .=  number_format(class_misc::convertMinutesToHours($total),2, ',', '.') . " hour(s) \n";

	//
	$mail_body .= "\nEmail sent on:" . $fieldseparator . date("Y-m-d H:i:s") . " \n\n";

	// show message on screen
	echo "Mail subject: " . str_replace("\t", " &nbsp; &nbsp; ", str_replace("\n", "<br>\n", $mail_subject)) . "<br><br>\n";
	echo "Mail body: " . str_replace("\t", " &nbsp; &nbsp; ", str_replace("\n", "<br>\n", $mail_body)); echo "<br>";

	// set headers
	$mail_headers = 'From: "' . Settings::get('email_sender_name') . '" <' . Settings::get('email_sender_email') . '>' . "\r\n" .
		'Reply-To: "' . Settings::get('email_sender_name') . '" <' . Settings::get('email_sender_email') . '>';

	// check if weekly report e-mail is enabled
	if ( !$oDepartment->getEnableweeklyreportmail() ) {
		echo "SKIPPED: Weekly report e-mail is disabled for this department.";
	} else {
		// get email department head
		$headEmail = $oHead->getEmail();
		if ( $headEmail != '' ) {
			// send email to department head
			//mail($headEmail, $mail_subject, $mail_body, $mail_headers);
			//mail("gcu@iisg.nl", $mail_subject, $mail_body, $mail_headers);
			echo "DISABLED: Would have been sent to $headEmail";
		} else {
			echo "SKIPPED: Weekly report e-mail is disabled for this department.";
		}
	}

	echo "<hr>\n";
}

// show time
echo "End time: " . date("Y-m-d H:i:s") . "<br>\n";
