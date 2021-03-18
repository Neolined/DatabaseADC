<?php
session_start();
if ((isset($_SESSION['user'])) && ($_SESSION['ua'] == $_SERVER['HTTP_USER_AGENT'])  && (($_SESSION['root'] == "view") || ($_SESSION['root'] == "accept")))
header('Location: main.php');
require_once 'connect.php';
$err=0;
if ((!empty($_POST['login'])) && (!empty($_POST['password'])))
{
	if (preg_match('/^[a-z0-9-_]{2,20}$/',$_POST["login"]))
	{
		$log = mysqli_real_escape_string($link, $_POST["login"]);
		$pass = mysqli_real_escape_string($link, $_POST["password"]);
		$result = mysqli_query($link, "select `password` from `users` where `user` = '".$log."'");
		$tm = mysqli_fetch_row($result);
		if (!empty($tm))
		{
			mysqli_free_result($result);
			if (password_verify($pass, $tm[0]))
			{
				echo $err;
				$_SESSION['user'] = $log;
				$_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
				$result = mysqli_query($link, "select `root` from users where `user` = '".$log."'");
				$tm = mysqli_fetch_row($result);
				$_SESSION['root'] = $tm[0];
				mysqli_close($link);
				header('Location: main.php');
			}
			else
			$err = 1;
		}
		else 
		$err=1;
	}
	else 
	$err=1;
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="asset/css/main1.css"<?php echo(microtime(true).rand()); ?>>
  <title>Авторизация</title>
 </head>
 <body>
 <div class="header">
			<img id="adc1" src="adc.png">
</div>
		<div id="forma">
		<form action="index.php" method="post" id="inp" align="center" class="form1">
			<p id="priem_name" align="center">Авторизация</p>
			<label>Введите логин:</label><input type="text" name="login" placeholder="ivanov" required/>
			<label>Введите пароль:</label><input type="password" name="password" placeholder="Qwerty123" required/>
			<input type="submit" value="Войти" id="savedata"/>
			<a href="registration.php"><input type="button" id="regdata" value="Зарегистрироваться"/></a>
			<?php
			if (($err == 1))
			echo "<p class=\"msg\"> Неправильно введен логин или пароль </p>";
			else if (!empty($_SESSION['err']))
			{
			if ($_SESSION['err'] == 1)
			echo "<p class=\"msg\">В доступе отказано. Требуется авторизация</p>";
			session_unset();
			}
			?>
		</form>
		</div>
	<div class="footer" role="contentinfo">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	</div>
 </body>
</html>