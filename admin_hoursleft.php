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
$oPage->setTab($menuList->findTabNumber('pl.hoursleft'));
$oPage->setTitle('Timecard | Hours for planning');
$oPage->setContent(createHoursLeftContent() . createHoursLeftRemarks());
$oPage->setCssExtension('_full');

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createHoursLeftContent( $curyear = '' ) {
	//
	$s = getAndProtectSearch();

	if ( $curyear == '' ) {
		$curyear = date("Y");
	}

	$ret = "<h2>Hours for planning " . $curyear . "</h2>
<br>
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
	document.getElementById('fldYear').value = iYear;

	//
	tcRefreshSearch();
}

// TODOEXPLAIN
function tcRefreshSearch() {
	var strZoek = document.getElementById('fldZoek').value;
	xmlhttpSearch.open(\"GET\", \"admin_hoursleft_list.php?s=\" + escape(document.getElementById('fldZoek').value) + \"&y=\" + escape(document.getElementById('fldYear').value), true);
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

function toggleMonths() {
	// hide headers
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+2; i++ ) {
			$('#tblHours th:nth-child(' + i + ')').toggle();
		}
		start = start+4;
	}

	// hide months
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+8; i++ ) {
			$('#tblHours td:nth-child(' + i + ')').toggle();
		}
		start = start+12;
	}
}

function hideMonths() {
	// hide headers
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+2; i++ ) {
			$('#tblHours th:nth-child(' + i + ')').hide();
		}
		start = start+4;
	}

	// hide months
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+8; i++ ) {
			$('#tblHours td:nth-child(' + i + ')').hide();
		}
		start = start+12;
	}
}

function hidePastMonths() {
	// hide headers
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+2; i++ ) {
			$('#tblHours th:nth-child(' + i + ')').hide();
		}
		start = start+4;
	}

	// hide months
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+8; i++ ) {
			$('#tblHours td:nth-child(' + i + ')').hide();
		}
		start = start+12;
	}
}

function showMonths() {
	// hide headers
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+2; i++ ) {
			$('#tblHours th:nth-child(' + i + ')').show();
		}
		start = start+4;
	}

	// hide months
	start = 5;
	for ( j = 1; j <= 4; j++ ) {
		for ( i = start; i <= start+8; i++ ) {
			$('#tblHours td:nth-child(' + i + ')').show();
		}
		start = start+12;
	}
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
";

	$ret .= "<input type=\"hidden\" name=\"fldYear\" id=\"fldYear\" value=\"" . $curyear . "\">";

	$ret .= "
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
	<li>Every year the <a href=\"nationalholidays.php?backurl=" . urlencode(get_current_url()) . "\">holidays</a> must be entered.</li>
	<li>If there a no hours shown, please edit user and set <a href=\"admin_not_linked_employees.php?backurl=" . urlencode(get_current_url()) . "\">Protime link'</a></li>
	<li>" . (int)(class_settings::getSetting("percentage_rule")*100.0) . "% rule = " . class_settings::getSetting("percentage_rule") . ", the rest of the hours is for overhead (meetings, courses, sick leaves, ...)</li>
</ol>
";

	return $ret;
}
