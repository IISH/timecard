<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

if ( !$oWebuser->hasAdminAuthorisation() ) {
	echo "You are not authorized to access this page.<br>";
	die('Go to <a href="index.php">time card home</a>');
}

$date = class_datetime::get_date($protect);

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('misc.hoursleft'));
$oPage->setTitle('Timecard | Hours left');
$oPage->setContent(createHoursLeftContent() . createHoursLeftRemarks());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createHoursLeftContent() {
	//
	$s = getAndProtectSearch();

	$ret = "
<script type=\"text/javascript\">
<!--
var xmlhttpSearch=false;
var xmlhttpAddRemove=false;

if (!xmlhttpSearch && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttpSearch = new XMLHttpRequest();
	} catch (e) {
		xmlhttpSearch=false;
	}
}
if (!xmlhttpSearch && window.createRequest) {
	try {
		xmlhttpSearch = window.createRequest();
	} catch (e) {
		xmlhttpSearch=false;
	}
}

if (!xmlhttpAddRemove && typeof XMLHttpRequest!='undefined') {
	try {
		xmlhttpAddRemove = new XMLHttpRequest();
	} catch (e) {
		xmlhttpAddRemove=false;
	}
}
if (!xmlhttpAddRemove && window.createRequest) {
	try {
		xmlhttpAddRemove = window.createRequest();
	} catch (e) {
		xmlhttpAddRemove=false;
	}
}

// TODOEXPLAIN
function setDate(iYear, iMonth) {
	document.getElementById('fldMonth').value = iMonth;
	document.getElementById('fldYear').value = iYear;

	//
	tcRefreshSearch();
}

// TODOEXPLAIN
function tcRefreshSearch() {
	var strZoek = document.getElementById('fldZoek').value;
	xmlhttpSearch.open(\"GET\", \"admin_hoursleft_list.php?s=\" + escape(document.getElementById('fldZoek').value) + \"&y=\" + escape(document.getElementById('fldYear').value) + \"&m=\" + escape(document.getElementById('fldMonth').value), true);
	xmlhttpSearch.onreadystatechange=function() {
		if (xmlhttpSearch.readyState==4) {
			document.getElementById('tcContentSearch').innerHTML = xmlhttpSearch.responseText;
		}
	}
	xmlhttpSearch.send(null);
}

// TODOEXPLAIN
function tcRefreshSearchStart() {
	tcRefreshSearch();
}

// TODOEXPLAIN
function addRemove(pid, dowhat) {
	document.getElementById('divAddRemove'+pid).innerHTML = '';
	xmlhttpAddRemove.open(\"GET\", \"addremove_favourite.php?id=\" + pid + \"&dowhat=\" + dowhat + \"&fav=hoursleft\", true);
	xmlhttpAddRemove.onreadystatechange=function() {
		if (xmlhttpAddRemove.readyState==4) {
			document.getElementById('divAddRemove'+pid).innerHTML = xmlhttpAddRemove.responseText;
		}
	}
	xmlhttpAddRemove.send(null);
}

// TODOEXPLAIN
function setSearchField(fldValue) {
	document.getElementById('fldZoek').value = fldValue;
	tcRefreshSearch();
	return false;
}
// -->
</script>
<form name=\"frmTc\" method=\"GET\" onsubmit=\"return false;\">
<TABLE width=\"100%\">
<TR>
	<TD>

Quick search: <input type=\"\" name=\"fldZoek\" id=\"fldZoek\" maxlength=\"20\" onkeyup=\"tcRefreshSearch();\" value=\"" . $s . "\">
 &nbsp; <a href=\"#\" onclick=\"javascript:setSearchField('');\">Clear</a> &nbsp; <font size=-2><em>(min. 3 characters)</em></font>
	</TD>
	<TD align=\"right\">
	</TD>
</TR>
<TR>
	<TD>Start month: 
";
	$curyear = date("Y");
	$curmonth = date("m");

	$ret .= "<input type=\"hidden\" name=\"fldYear\" id=\"fldYear\" value=\"" . $curyear . "\">";
	$ret .= "<input type=\"hidden\" name=\"fldMonth\" id=\"fldMonth\" value=\"" . $curmonth . "\">";

	$separator = ' &nbsp; &nbsp;';
	for ( $i = $curmonth; $i <= 12; $i++ ) {
		$ret .= $separator . "<a href=\"#\" onclick=\"javascript:setDate(" . $curyear . ", " . $i . ");\">" . $curyear . '-' . str_pad( $i, 2, '0', STR_PAD_LEFT) . "</a>";
	}
	$ret .= $separator . "<a href=\"#\" onclick=\"javascript:setDate(" . ($curyear+1) . ", 1);\">" . ($curyear+1) . "-01</a>";

$ret .= "
	</TD>
</TR>
</table>
</form>
<br>
<div id=\"tcContentSearch\">...</div>
<script type=\"text/javascript\">
<!--
tcRefreshSearchStart();
// -->
</script>
";

	return $ret;
}

// TODOEXPLAIN
function createHoursLeftRemarks() {
	// REMARKS
	$ret = "<br>Remarks
<ol>
	<li>List of not disabled employees or employees with hours entered in current year</li>
	<li>If there a no vacation hours shown, please edit user and set 'Protime link'</li>
	<li>If there is no hour calculation, please go to <a href=\"admin_hoursperweek.php?backurl=" . urlencode(get_current_url()) . "\">Hours per week</a> and enter how many hours the user works per week.</li>
	<li>Every year the <a href=\"nationalholidays.php?backurl=" . urlencode(get_current_url()) . "\">holidays</a> must be entered.</li>
	<li>" . (int)(class_settings::getSetting("percentage_rule")*100.0) . "% rule = " . class_settings::getSetting("percentage_rule") . "</li>
</ol>
";

	return $ret;
}
