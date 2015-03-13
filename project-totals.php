<?php 
require_once "classes/start.inc.php";

$oWebuser->checkLoggedIn();

// create webpage
$oPage = new class_page('design/page.php', $settings);
$oPage->removeSidebar();
$oPage->setTab($menuList->findTabNumber('projects.project_totals'));
$oPage->setTitle('Timecard | Project Hours - Month overview - Totals');
$oPage->setContent(createProjectContent());

// show page
echo $oPage->getPage();

require_once "classes/_db_disconnect.inc.php";

// TODOEXPLAIN
function createProjectContent() {
	global $protect;

	// get design
	$design = new class_contentdesign("page_projects_year_totals");

	// add header
	$ret = $design->getHeader();

	// add content
	$ret .= $design->getContent();

	$projectId = $protect->request_positive_number_or_empty('get', 'ID');
	$oProject = new class_project( $projectId );

//	$year = $protect->request_positive_number_or_empty('get', 'y');
//	if ( $year == '' ) {
	$year = date("Y");
//	}

	$templateUrl = "<a href=\"#\" onClick=\"javascript:changeYear('{direction}');\" xxxhref=\"?ID={ID}&y={y}\" title=\"{alt}\">{label}</a>";

	// PREVIOS MONTH
	$dataPrev['label'] = "&laquo;";
	$dataPrev['ID'] = $oProject->getId();
	$dataPrev['y'] = $year-1;
	$dataPrev['alt'] = 'go to previous year';
	$dataPrev['direction'] = '-';
	$prev = fillTemplate($templateUrl, $dataPrev);

	// NEXT MONTH
	$dataNext['label'] = "&raquo;";
	$dataNext['ID'] = $oProject->getId();
	$dataNext['y'] = $year+1;
	$dataNext['alt'] = 'go to next year';
	$dataNext['direction'] = '+';
	$next = fillTemplate($templateUrl, $dataNext);

	// CURRENT DATE
	$dataToday['label'] = '*';
	$dataToday['ID'] = $oProject->getId();
	$dataToday['y'] = date("Y");
	$dataToday['direction'] = '0';
	$dataToday['alt'] = 'go to current year';
	$today = fillTemplate($templateUrl, $dataToday);

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

// TODOEXPLAIN
function refreshProjectOutput() {
	xmlhttpSearch.open(\"GET\", \"project-totals-data.php?y=\" + escape(document.getElementById('spanYear').innerHTML) + \"&ID=" . $oProject->getId() . "\", true);
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

	// calculate new value
	if ( direction == '-' ) {
		iYear--;
	} else if ( direction == '+' ) {
		iYear++;
	} else {
		iYear = " . date("Y") . ";
	}

	// set new value
	spanYear.textContent = iYear;
	refreshProjectOutput();
}
// -->
</script>
Year: <span id=\"spanYear\">" . $year . "</span> " . $prev . " " . $today . " " . $next . "<br><br>

<span id=\"spanData\">...</span><br>

<script language=\"JavaScript\">
refreshProjectOutput();
</script>
";

// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
//	$ret .= "Project: " . $oProject->getDescription() . "<br>\n";
//	$ret .= "Project number: " . $oProject->getProjectnumber() . "<br>\n";
//	$projectLeaderName = '-';
//	if ( $oProject->getProjectleader() != null ) {
//		$projectLeaderName = $oProject->getProjectleader()->getFirstLastname();
//	}
//	$ret .= "Project leader: " . $projectLeaderName . "<br>\n";

	//
	$body = "<br><table>\n";
	$total = 0.0;

	// get list of project workhours for specified period
	$workhours = class_workhours_static::getWorkhoursPerEmployeeGroupedMonth( $oProject->getId(), $year );

	// name / hours
	foreach ($workhours as $p) {
		$body .= "<tr>\n\t<td>" . $p["employee"]->getFirstname() . ' ' . verplaatsTussenvoegselNaarBegin($p["employee"]->getLastname()) . " </td>";
		$body .= "<td align=right>" . number_format(class_misc::convertMinutesToHours($p["timeinminutes"]),2, ',', '.') . "</td>\n\t<td> hour(s)</td>\n</tr>";

		//
		$total += $p["timeinminutes"];
	}

	$body .= "<tr>\n\t<td><b>Total:</b></td>\n\t<td align=right><b>" . number_format(class_misc::convertMinutesToHours($total),2, ',', '.') . "</b></td>\n\t<td><b> hour(s)</b></td>\n</tr>";
	$body .= "</table>\n";

	$ret .= $body;
// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +


	// add footer
	$ret .= $design->getFooter();

	return $ret;
}