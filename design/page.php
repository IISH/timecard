<!doctype html>

<html>
<head>
	<title>{title}</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<meta name="Robots" content="noindex,nofollow">
	<script language="JavaScript" src="javascript/shared.js"></script>
	<link rel="stylesheet" href="jquery/jquery-ui.css" />
	<script src="jquery/jquery-1.8.2.js"></script>
	<script src="jquery/jquery-ui.js"></script>
	<style type="text/css" media="all">@import url("design/timecard.css.php?c={color}");</style>
	<script language="Javascript">
	$(function() {
		$( "#tabs" ).tabs();
	});
	</script>
</head>
<body>

<div class="main">

	<div class="header">
		<div class="welcome"><span><span class="name">{welcome}</span><span class="logout">{logout}</span></span></div>
		<div class="logo"><img src="images/logo-iisg/{color}.png"></div>
		<div class="title"><span class="title">timecard</span><br><span class="subtitle">Bringing Hours Together</span></div>
	</div>

	{menu}

	<div>

		<table border="0" width="100%" cellspacing="0 cellpadding=0">
			<tr>
				<td valign="top">

					<div class="content content {extracontentclass}">
						{content}
					</div>

				</td>
				<td valign="top">

					<div class="sidebar {extrasidebarclass}">
						<div class="shortcuts {extrashortcutsclass}">{shortcuts}</div>
						<div class="recentlyused {extrarecentlyusedclass}">{recentlyused}</div>
					</div>

				</td>
			</tr>
		</table>

	</div>

	<div id="footer" class="footer">{url} - 15 January 2015</div>
</div>

<script language="Javascript">
$(document).ready(function () {
	$('#tabs').tabs({ selected: {opentab} }); // 0, 1, 2, ...
});
</script>

</body>
</html>