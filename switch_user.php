<?php
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">timecard home</a>');
}

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('administrator.change_user'));
$oPage->setTitle('Timecard | Switch user');
$oPage->setContent(createChangeUserContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createChangeUserContent() {
	global $protect, $settings;

	$fldUserName = '';
	$error = '';

	if ( $protect->request_positive_number_or_empty('post', 'issubmitted') == '1' ) {
		// get values
		$fldUserName = $protect->request('post', 'fldUserName');

		// quick protect
		$fldUserName = str_replace(array(';', ':', '!', '<', '>', '(', ')', '%'), ' ', $fldUserName);

		// remove domainnames
		$fldUserName = str_replace(array('@iisg.nl', '@iisg.net', 'iisgnet\\'), ' ', $fldUserName);

		// trim
		$fldUserName = trim($fldUserName);

		// use the left part until the space
		$fldUserName = $protect->get_left_part($fldUserName, ' ');

		// check if both field are entered
		if ( $fldUserName != '' ) {

			// check if person can be found in database, get id
			$persinfo = getEmployeeIdByLoginName($fldUserName);

			if ( $persinfo["id"] != "" && $persinfo["id"] != "0" ) {
				// save id
				$_SESSION["timecard"]["id"] = $persinfo["id"];

				// redirect to ...
				$burl = 'day.php';
				Header("Location: " . $burl);
				die("Go to <a href=\"" . $burl . "\">next</a>");
			} else {
				// show error
				$error .= "User unknown.";
			}
		}
	}

	// get design
	$design = new class_contentdesign("page_change_user");

	// add header
	$ret = $design->getHeader();

	if ( $error != '' ) {
		$ret .= "<span class=\"error\">" . $error . "</span><br>";
	}


	$allEmployees = getAllEmployeesLoginnameAndFullname();

	$options = "\t\t<option value=\"\"></option>\n";
	foreach ( $allEmployees as $med ) {
		$label = trim($med['FIRSTNAME'] . ' ' . verplaatsTussenvoegselNaarBegin($med['NAME']));
		if ( $label == '' ) {
			$label = $med['LongCodeKnaw'];
		}
		$value = $med['LongCodeKnaw'];

		$options .= "\t\t<option value=\"$value\">$label</option>\n";
	}

	$ret .= "
<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
<form name=\"frmA\" method=\"POST\">
<input type=\"hidden\" name=\"issubmitted\" value=\"1\">
<tr>
	<td>User name:</td>
	<td>
		<select name=\"fldUserName\" class=\"login\">
$options	
		</select>
	</td>
</tr>
<tr>
	<td></td>
</tr>
<tr>
	<td align=\"right\">&nbsp;</td>
	<td>&nbsp;<input class=\"button\" type=\"submit\" name=\"btnSubmit\" value=\"Submit\"></td>
</tr>
</form>
</table>

<br>
<script language=\"javascript\">
<!--
document.frmA.fldUserName.focus();
// -->
</script>
";

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}
