<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="asset/css/main1.css"<?php echo(microtime(true).rand()); ?>>
  <title>Главная</title>
 </head>
 <body>
 <div class="header">
 <div class="header">
	<div class="dropdown">
		<button class="dropbtn" align="center"><img id = "menu" src = "menu.png"></button>
		<div class="dropdown-content">
		<?php
			session_start();
			if ((!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT'])  || (($_SESSION['root'] !== "") && ($_SESSION['root'] !== "accept") && ($_SESSION['root'] !== "otk")))
			{
				echo '<a href="index.php">Авторизация</a>';
			}
			else if ((isset($_SESSION['user'])) || ($_SESSION['ua'] == $_SERVER['HTTP_USER_AGENT'])  || ($_SESSION['root'] == ""))
			{
				echo '<a href="main.php">Главная</a>';
				echo '<a href="otk.php">ОТК</a>';
				echo '<a href="exit.php">Выход<img id="exit" src="exit.png"></a>';
			}
		?>
		</div>
	</div>
</div>
	<div id="errForma">
	<img id = "errAccess" src="403.jpg" align="center">
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	<script src = "script.js"></script>
 </body>
</html>