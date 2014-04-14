<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('pp.login'));
$oPage->setTitle('Timecard | Login');
$oPage->setContent(createLoginPage());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createLoginPage() {
	global $protect, $settings, $settings_from_database;

	$fldLogin = '';
	$error = '';

	if ( $protect->request_positive_number_or_empty('post', 'issubmitted') == '1' ) {
		// get values
		$fldLogin = $protect->request('post', 'fldLogin');
		$fldPassword = $protect->request('post', 'fldPassword');
		$burl = trim($protect->request('get', 'burl'));

		// quick protect
		$fldLogin = str_replace(array(';', ':', '!', '<', '>', '(', ')', '%'), ' ', $fldLogin);

		// remove domainnames
		$fldLogin = str_replace(array('@iisg.nl', '@iisg.net', 'iisgnet\\'), ' ', $fldLogin);

		// trim
		$fldLogin = trim($fldLogin);
		$fldPassword = trim($fldPassword);

		// use the left part until the space
		$fldLogin = $protect->get_left_part($fldLogin, ' ');

		// check if both field are entered
		if ( $fldLogin != '' && $fldPassword != '' ) {

			$result_login_check = class_authentication::authenticate($fldLogin, $fldPassword);

			if ( $result_login_check == 1 ) {
				// check if person can be found in database, get id
				// if not add new user
				$persinfo = getAddEmployeeToTimecard($fldLogin);

				// save id
				$_SESSION["timecard"]["id"] = $persinfo["id"];

				$oWebuser = new class_employee($persinfo["id"], $settings);

				// update wanneer gebruiker voor het laatst is ingelogd op timecard
				updateLastUserLogin($oWebuser->getTimecardId());

				//
				syncProtimeAndTimecardEmployeeData( $oWebuser );

				// redirect to prev page
				if ( $burl == '' ) {
					$burl = 'index.php';
				}

				Header("Location: " . $burl);
				die("Go to <a href=\"" . $burl . "\">next</a>");
			} else {
				// show error
				$error .= "User/Password combination incorrect.";
			}
		} else {
			// show error
			$error .= "Both field are required.<br>";
		}
	}

	$ret = "
<h2>Please log in...</h2>
";

	if ( $error != '' ) {
		$ret .= "<span class=\"error\">" . $error . "</span><br>";
	}

	$ret .= "
<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
<form name=\"frmA\" method=\"POST\">
<input type=\"hidden\" name=\"issubmitted\" value=\"1\">
<tr>
	<td>Login name:</td>
	<td><input type=\"text\" name=\"fldLogin\" class=\"login\" maxlength=\"50\" value=\"" . $fldLogin . "\"> <i>(SA / 2X account, firstname.lastname)</i></td>
</tr>
<tr>
	<td>Password:&nbsp;</td>
	<td><input type=\"password\" name=\"fldPassword\" class=\"password\" maxlength=\"50\" value=\"\"></td>
</tr>
<tr>
	<td></td>
</tr>
<tr>
	<td align=\"right\"><input class=\"button_login\" type=\"reset\" name=\"btnReset\" value=\"Clear\">&nbsp;</td>
	<td>&nbsp;<input class=\"button_login\" type=\"submit\" name=\"btnSubmit\" value=\"Submit\"></td>
</tr>
</form>
</table>

<br>
" . $settings_from_database["text_functional_maintainer"] . "
<script language=\"javascript\">
<!--
document.frmA.fldLogin.focus();
// -->
</script>
";

	return $ret;
}
?>