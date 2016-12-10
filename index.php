<?php
require_once("config.php");
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
if(isset($_COOKIE['doJazdy_c1']))
{
?>
<html>
	<head>
		<title><?php echo SYSTEM_NAME.' v'.SYSTEM_VERSION;?></title>
		<link rel="stylesheet" type="text/css" href="dojazdy.css?=<?php echo filemtime("dojazdy.css"); ?>" />
		<script type="text/javascript" src="js/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="js/dojazdy.js?=<?php echo filemtime("js/dojazdy.js"); ?>"></script>
	</head>
	<body onload="start()">
		<div id="main" class="main">
			<div id="menu_g" class="menu_g"><img src="opona.jpg" class="opona"><p class="tytul"><?php echo SYSTEM_NAME;?><span style="font-size: 10px;"> v<?php echo SYSTEM_VERSION;?></span></p><p style="text-align:right;font-size: 10px;margin:10px;">code by (-)pvg</p></div>
			<div id="menu_l" class="menu_l">
			<p><input class="menu_l_p" size="10" onclick="menu(1)" value="Przejazdy" type="button"></p>
			<p><input class="menu_l_p" size="10" onclick="menu(2)" value="Rozliczenie" type="button"></p>
			<p><input class="menu_l_p" size="10" onclick="menu(3)" value="Dodaj" type="button"></p>
			<p><input class="menu_l_p" size="10" onclick="menu(5)" value="Wyloguj" type="button"></p>
			</div>
			<div id="menu_r" class="menu_r"></div>
			<div id="obszar_r" class="obszar_r"></div>
		</div>
	</body>
</html>
<?php
}else
{
?>
<html>
	<head>
		<title><?php echo SYSTEM_NAME.' v'.SYSTEM_VERSION;?></title>
		<link rel="stylesheet" type="text/css" href="dojazdy.css?=<?php echo filemtime("dojazdy.css"); ?>" />
		<script type="text/javascript" src="js/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="js/dojazdy.js?=<?php echo filemtime("js/dojazdy.js"); ?>"></script>
		<script type="text/javascript" src="js/password_prompt.js?=<?php echo filemtime("js/password_prompt.js"); ?>"></script>
	</head>
	<body onload="start()">
		<div id="main" class="main">
			<div id="menu_g" class="menu_g"><img src="opona.jpg" class="opona"><p class="tytul"><?php echo SYSTEM_NAME;?><span style="font-size: 10px;"> v<?php echo SYSTEM_VERSION;?></span></p><p style="text-align:right;font-size: 10px;margin:10px;">code by (-)pvg</p></div>
			<div id="menu_l" class="menu_l">
			<p><input class="menu_l_p" size="10" onclick="menu(1)" value="Przejazdy" type="button"></p>
			<p><input class="menu_l_p" size="10" onclick="menu(2)" value="Rozliczenie" type="button"></p>
			<p><input class="menu_l_p" size="10" onclick="menu(3)" value="Dodaj" type="button"></p>
			<p><input class="menu_l_p" size="10" onclick="menu(4)" value="Zaloguj" type="button"></p>
			</div>
			<div id="menu_r" class="menu_r"></div>
			<div id="obszar_r" class="obszar_r"></div>
		</div>
	</body>
</html>
<?php
}
?>