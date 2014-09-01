<?php 
header('Content-type: text/css');
require_once "../classes/class_misc.inc.php";
require_once "../classes/class_website_protection.inc.php";

// number of tabs
$t = 10;

// menu/footer color (only 6 char/digit allowed)
$protect = new class_website_protection();
$c = $protect->request('get', 'c', '/^[0-9a-zA-Z]{6,6}$/');
if ( $c == '' ) {
	$c = '#73A0C9';
} else {
	$c = '#' . $c;
}
?>
html, body, input, textarea, select {
	font-family: Verdana;
	font-size: 95%;
}

table {
	border-spacing: 0px;
	border-collapse: separate;
}

.bold {
	font-weight: bold;
}

.boldRed {
	font-weight: bold;
	color: red;
}

.calendarPositioning {
	display: none;
	position:absolute;
	right: 295px;
}

.login, .password {
	width: 175px;
}

.error {
	color: red;
}

.calendar_header {
	text-align: center;
}

.calendar_align {
	text-align: center;
}

.calendar_weekend {
}

.calendar_weekday {
}

.selectedday {
	font-weight: bold;
}

a, a:visited, a:active, a:hover {
	color: <?php echo $c; ?>;
	text-decoration: none;
	border-bottom: 1px blue dotted;
}

a.PT, a.PT:visited, a.PT:active, a.PT:hover {
	color: black;
	text-decoration: none;
	border-bottom: 0px;
	font-style:italic;
	font-size:80%;
}

a.nolink, a.nolink:visited, a.nolink:active, a.nolink:hover {
	text-decoration: none;
	border-bottom: 0px;
}

a.add, a.add:visited, a.add:active, a.add:hover {
	font-size: 90%;
	font-style:italic;
}

input, select, textarea {
	border-width: 1px;
	border-style: solid;
	border-color: <?php echo $c; ?>;
}

.button {
	color: <?php echo $c; ?>;
	background-color: white;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	width: 75px;
	border: 1px solid <?php echo $c; ?>;
	padding-left: 15px;
	padding-right: 15px;
	padding-top: 3px;
	padding-bottom: 3px;
}

h2 {
	color: <?php echo $c; ?>;
	margin-top: 0px;
	margin-bottom: 0px;
	font-size: 15px;
}

h3 {
	color: <?php echo $c; ?>;
	margin-top: 0px;
	margin-bottom: 10px;
	font-size: 15px;
}

h4 {
	color: <?php echo $c; ?>;
	margin-top: 0px;
	margin-bottom: 0px;
	font-size: 15px;
}

hr {
	color: <?php echo $c; ?>;
	border: 1px solid;
}

.contenttitle {
	color: <?php echo $c; ?>;
	font-size: 18px;
	font-weight: bold;
}

div {
	border: 0px solid;
}

div.main {
	width: 960px;
	margin-left: auto;
	margin-right: auto;
}

div.mainiframe {
}

div.header {
	position: relative;
	margin-top: auto;
	margin-bottom: auto;
}

div.logo {
	position: relative;
	margin-left: -13px;
	margin-bottom: 7px;
	height: 94px;
	width: 122px;
}

div.title {
	position: absolute;
	margin-left: 117px;
	top: 12px;
}

div.welcome {
	float: right;
	margin-top: 20px;
}

div.logout {
	float: right;
	margin-top: 50px;
}

span.name {
	display: block;
	font-family: 'Times New Roman';
	font-size: 18px;
	font-weight: bold;
	color: <?php echo $c; ?>;
	text-align: right;
}

span.logout {
	display: block;
	font-family: 'Times New Roman';
	font-size: 14px;
	font-style: italic;
	color: <?php echo $c; ?>;
	text-align: right;
}

td.content_admin {
	padding-right: 5px;
}

div.content {
	top:0;
	width: 690px;
	border: 1px solid #AAAAAA;
	margin-top: 5px;
	margin-bottom: 5px;
	padding-top: 5px;
	padding-bottom: 15px;
	padding-left: 5px;
	padding-right: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

div.content_admin {
	width: 520px;
}

div.leftmenu {
	width: 170px;
	border: 1px solid #AAAAAA;
	margin-top: 5px;
	margin-bottom: 5px;
	padding-top: 5px;
	padding-bottom: 10px;
	padding-left: 5px;
	padding-right: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

div.contentiframe {
	border: 0px solid #AAAAAA;
	margin-top: 0px;
	margin-bottom: 0px;
	padding-top: 0px;
	padding-bottom: 2px;
	padding-left: 2px;
	padding-right: 2px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

div.contentfullwidth {
	width: 945px;
}

div.contentfullwidth_admin {
	width: 760px;
}

div.sidebar {
	width:250px;
	padding-top: 5px;
}

div.sidebar_admin {
	width:230px;
	padding-top: 5px;
}

div.shortcuts {
	background-color: white;
	float: right;
	border: 1px solid #AAAAAA;
	width:240px;
	margin-bottom: 5px;
	padding-top: 5px;
	padding-bottom: 5px;
	padding-left: 5px;
	padding-right: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

div.shortcuts_admin {
	width:220px;
}

div.recentlyused {
	background-color: white;
	float: right;
	border: 1px solid #AAAAAA;
	width: 240px;
	padding-top: 5px;
	padding-bottom: 5px;
	padding-left: 5px;
	padding-right: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
}

div.recentlyused_admin {
	width: 220px;
}

div.footer {
	color: white;
	background-color: <?php echo $c; ?>;
	text-align: right;
	border: 1px solid #AAAAAA;
	width: 960px;
}

#footer {
	position: relative;
	bottom: 0;
	margin-top: 10px;
}

div.hidden {
	display:none;
}

span.title {
	font-family: 'Times New Roman';
	font-size: 32px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

span.subtitle {
	font-family: 'Times New Roman';
	font-size: 16px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

#tabs {
	padding: 0px;
	background: none;
	border-width: 0px;
}
#tabs .ui-tabs-nav {
	padding-left: 0px;
	background: transparent;
	border-width: 0px 0px 1px 0px;
	-moz-border-radius: 0px;
	-webkit-border-radius: 0px;
	border-radius: 0px;
}
#tabs .ui-tabs-panel {
	border-width: 0px 1px 1px 1px;
}

<?php echo class_misc::multiplyTag('#tabs-{x}', '{x}', 0, $t); ?> {
	font-size:8pt;
	color: <?php echo $c; ?>;
	font-weight: bold;
}

.ui-tabs .ui-tabs-nav li a {
	font-size:8pt;
	color: <?php echo $c; ?>;
	font-weight: bold;
	text-decoration: none;
	border-bottom: 0px;
}

<?php echo class_misc::multiplyTag('#tabs-{x} ul', '{x}', 0, $t); ?> {
	list-style-type: none;
	padding: 0px;
	margin: 0px;
}

<?php echo class_misc::multiplyTag('#tabs-{x} ul li', '{x}', 0, $t); ?> {
	display: inline;
	list-style-type: none;
	padding-right: 20px;
}

<?php echo class_misc::multiplyTag('#tabs-{x} ul li a', '{x}', 0, $t); ?> {
	font-size:9pt;
	color: <?php echo $c; ?>;
	font-weight: bold;
	text-decoration: none;
	border-bottom: 0px;
}

.comment {
	line-height: 95%;
	font-size: 85%;
	font-style:italic;
}

div.checkedInOut {
	margin-top: 20px;
	margin-bottom: 10px;
}

div.goBackTo {
	margin-top: 10px;
	margin-bottom: 10px;
}

div.prevNextRibbon {
	margin-top: 0px;
	margin-bottom: 0px;
}

ul {
	margin-top: 0px; 
	margin-bottom: 20px; 
}

.presentornot_absence {
	font-size: 80%;
	text-align: center;
}

.youcannot {
	font-style: italic;
	color: red;
}

.recorditem {
	padding-right: 15px;
}
