<?php 
// TEMPLATES

//
$template["shortcuts"]["table"] = "
<h3>Shortcuts</h3>
<table>
{records}
</table>
";

$template["shortcuts"]["records"] = "
<tr>
	<td valign=\"top\">&bull;&nbsp;</td>
	<td><a href=\"{url}\">{projectname} ({hourminutes})</a> {autosave}{description}</td>
</tr>
";

$template["settings"]["shortcuts"]["table"] = "
<table>
<tr>
	<td><b>Project</b></td>
	<td><b>Time</b></td>
	<td><b>Description</b></td>
</tr>
{records}
</table>
";

$template["settings"]["shortcuts"]["records"] = "
<tr>
	<td>{strike_start}<a href=\"{url}\">{projectname}</a>{strike_end}</td>
	<td>{strike_start}{minutes}{strike_end}</td>
	<td>{strike_start}{description}{strike_end}</td>
</tr>
";

//
$template["recentlyused"]["table"] = "
<h3>Recently used</h3>
<table>
{records}
</table>
";

$template["recentlyused"]["records"] = "
<tr>
	<td valign=\"top\">&bull;&nbsp;</td>
	<td><a href=\"{url}\">{description}</a></td>
</tr>
";

$template["prevnextribbon"] = "
<div class=\"prevNextRibbon\">
<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">
<tr>
	<td><span class=\"contenttitle\">{label}</span></td>
	<td align=\"right\"> &nbsp; {buttons}</td>
</tr>
</table>
</div>
";

