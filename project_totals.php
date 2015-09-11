<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();

$tab = $protect->request('get', 'tab');
if (  $tab == 'exports' ) {
	$oPage->setTab($menuList->findTabNumber('exports.projectemployeetotaals'));
} else {
	$oPage->setTab($menuList->findTabNumber('projects.project_hour_totals'));
}

$oPage->setTitle('Timecard | Project hours - Totals');
$oPage->setContent(createProjectContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

function createProjectContent() {
	global $protect, $oWebuser;

	// get design
	$design = new class_contentdesign("page_project_employee_totals");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	$projectId = $protect->request_positive_number_or_empty('get', 'ID');
	$oProject = new class_project( $projectId );

	$year = date("Y");

	$templateUrl = "<a href=\"#\" onClick=\"javascript:changeYear('{direction}');return false;\" title=\"{alt}\">{label}</a>";
	$downloadButtonTemplate = "<a href=\"#\" onClick=\"javascript:downloadHours();return false;\" title=\"{alt}\">{label}</a>";

	// PREVIOS MONTH
	$dataPrev['label'] = "&laquo; previous";
	$dataPrev['alt'] = 'go to previous year';
	$dataPrev['direction'] = '-';
	$prev = fillTemplate($templateUrl, $dataPrev);

	// NEXT MONTH
	$dataNext['label'] = "next &raquo;";
	$dataNext['alt'] = 'go to next year';
	$dataNext['direction'] = '+';
	$next = fillTemplate($templateUrl, $dataNext);

	// CURRENT DATE
	$dataToday['label'] = '*';
	$dataToday['direction'] = '0';
	$dataToday['alt'] = 'go to current year';
	$today = fillTemplate($templateUrl, $dataToday);

	// CURRENT DATE
	$download['label'] = 'Download';
	$download['alt'] = 'Download excel file';
	$downloadButton = '';
	if ( $oWebuser->hasAdminAuthorisation() || $oWebuser->hasDepartmentAuthorisation() || $oWebuser->hasFaAuthorisation() || $oWebuser->isProjectLeader() ) {
		$downloadButton = fillTemplate($downloadButtonTemplate, $download);
	}

	$ret .= "
<script language=\"JavaScript\">
<!--
var xmlhttpSearch=false;

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

function downloadHours() {
	var url = \"project_totals_data.php?o=xlsx&y=\" + escape(document.getElementById('spanYear').innerHTML) + \"&ID=" . $oProject->getId() . "\";
	window.open(url);
}

function refreshProjectOutput() {
	xmlhttpSearch.open(\"GET\", \"project_totals_data.php?y=\" + escape(document.getElementById('spanYear').innerHTML) + \"&ID=" . $oProject->getId() . "\", true);
	xmlhttpSearch.onreadystatechange=function() {
		if (xmlhttpSearch.readyState==4) {
			document.getElementById('spanData').innerHTML = xmlhttpSearch.responseText;
		}
	}
	xmlhttpSearch.send(null);
}

function changeYear( direction ) {
	// get old value
	var spanYear = document.getElementById('spanYear');
	var iYear = spanYear.innerHTML;
	iYear = parseInt(iYear, 10);

	// calculate new value
	if ( direction == '-' ) {
		iYear--;
	} else if ( direction == '+' ) {
		iYear++;
	} else {
		// set current year
		iYear = " . date("Y") . ";
	}

	// set new value
	spanYear.innerHTML = iYear;

	//
	refreshProjectOutput();
}
// -->
</script>

<form name=\"frmForm\" id=\"frmForm\">
<table width=100%>
<tr>
	<td>Year: " . $prev . " &nbsp; <span name=\"spanYear\" id=\"spanYear\">" . $year . "</span> " . $today . " &nbsp; " . $next . " &nbsp; &nbsp; " . $downloadButton . "</td>
	<td align=right><a href=\"" . getBackUrl() . "\">Go back</a></td>
</tr>
</table>
<br>

<span name=\"spanData\" id=\"spanData\">...</span><br>
</form>

<script language=\"JavaScript\">
refreshProjectOutput();
</script>
";

	// add footer
	$ret .= $design->getFooter();

	return $ret;
}