<?php
session_start();
if ((isset($_SESSION['user'])) && ($_SESSION['ua'] == $_SERVER['HTTP_USER_AGENT'])  && (!empty($_SESSION['hash'])))
header('Location: main.php');
require_once 'lib/main.lib.php';
$link = connect();
$err=0;
if ((!empty($_POST['login'])) && (!empty($_POST['password'])))
{
	if (preg_match('/^[A-Za-z0-9-_!@#$%^&*()?<>+=*]{3,30}$/',$_POST["login"]))
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
				$_SESSION['hash'] = $tm[0];
				$result = mysqli_query($link, "select worker from users where user = '".$log."'");
				$worker = mysqli_fetch_row($result);
				$_SESSION['worker'] = $worker[0];
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
 <?php HtmlHead();?>
  <title>Авторизация</title>
 </head>
 <body>
 <div class="header">
			<img id="adc1" src="images/adc.png">
</div>
		<div id="forma">
		<form action="index.php" method="post" id="inp" align="center" class="form1">
			<p id="priem_name" align="center">Авторизация</p>
			<label>Введите логин:</label><input type="text" name="login"  minlength="3" maxlength="30" placeholder="ivanov" required/>
			<label>Введите пароль:</label><input type="password" name="password"  minlength="8" maxlength="20" placeholder="Qwerty123" required/>
			<input type="submit" value="Войти" id="savedata"/>
			<a href="registration.php"><input type="button" id="regdata" value="Зарегистрироваться"/></a>
			<?php
			if (($err == 1))
			echo "<p class=\"msg\"> Неправильно введен логин или пароль </p>";
			?>
		</form>
		</div>
	<div class="footer" role="contentinfo">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	</div>
 </body>
</html>