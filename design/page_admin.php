<!doctype html>

<html>
<head>
	<title>{title}</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
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

<div class="main{cssextension}">

	<div class="header">
		<div class="welcome">
			<span>
				<span class="name">{name}</span>
				<span class="holiday">{holiday}</span>
				<span class="checkinout">{checkinout}</span>
				<span class="logout">{logout}</span>
			</span>
		</div>
		<div class="logo"><img src="images/logo-iisg/{color}.png"></div>
		<div class="title"><span class="title">timecard</span><br><span class="subtitle">Bringing Hours Together</span></div>
	</div>

	{menu}

	<div>
			<table border="0" width="100%" cellspacing="0 cellpadding=0">
				<tr>
					<td valign="top" width="170px" class="content_admin">

						<div class="leftmenu">
						{leftmenu}
						</div>

					</td>
					<td valign="top" class="content_admin">

						<div class="content content_admin {extracontentclass}">
						{content}
						</div>

					</td>
					<td valign="top">

						<div class="sidebar_admin {extrasidebarclass}">
							<div class="shortcuts shortcuts_admin {extrashortcutsclass}">{shortcuts}</div>
							<div class="shortcuts shortcuts_admin {extrashortcutsclass}">{departmentshortcuts}</div>
							<div class="recentlyused recentlyused_admin {extrarecentlyusedclass}">{recentlyused}</div>
						</div>

					</td>
				</tr>
			</table>

	</div>

	<div id="footer" class="footer footerwidth{cssextension}">{url}</div>
</div>

<script language="Javascript">
$(document).ready(function () {
	$('#tabs').tabs({ selected: {opentab} }); // 0, 1, 2, ...
});
</script>

</body>
</html>
