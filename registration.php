<?php
session_start();
if ((isset($_SESSION['user'])) && ($_SESSION['ua'] == $_SERVER['HTTP_USER_AGENT'])  && (($_SESSION['root'] == "view") || ($_SESSION['root'] == "accept")))
header('Location: main.php');
$err = 0;
$suc = 0;
if ((!empty($_POST["login"])) && (!empty($_POST["password"])) && (!empty($_POST["password1"])))
{
	if (preg_match('/[А-Я][а-я]+\s+[А-Я]\.\s+[А-Я]\.$/u', $_POST['worker']))
	{
		if (preg_match('/^[a-z0-9-_]{3,30}$/',$_POST["login"]))
		{
			if(preg_match('/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{8,32}/',$_POST["password"]))
			{
				if ($_POST['password'] == $_POST['password1'])
				{
					require_once 'connect.php';
					if ($link)
					{
						$log = mysqli_real_escape_string($link, $_POST["login"]);
						$hash = password_hash(mysqli_real_escape_string($link, $_POST["password"]), PASSWORD_DEFAULT);
						$result = mysqli_query($link, "SELECT user FROM users WHERE user = '".$log."'");
						if (mysqli_num_rows($result) == 0)
						{
							$query = "INSERT INTO users (`user`, `password`, `worker`, `root`) VALUE ('$log', '$hash', '".$_POST['worker']."', 'view')";
							if (mysqli_query($link, $query))
							{
							$suc = 1;
							$_SESSION['user'] = $log;
							$_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
							$result = mysqli_query($link, "select `root` from users where `user` = '".$log."'");
							$tm = mysqli_fetch_row($result);
							$_SESSION['root'] = "view";
							}
							else 
							{
							$err = 6;
							}
						}
						else
						$err = 5;
					}
				}
				else
				$err = 3;
			}
			else
			$err = 2;
		}
		else
		$err = 1;
	}
	else
	$err = 7;
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="asset/css/main1.css"<?php echo(microtime(true).rand()); ?>>
  <title>Регистрация</title>
 </head>
 <body>
 <div class="header">
			<img id="adc1" src="adc.png">
</div>
		<div id="forma">
		<form action="registration.php" method="post" id="inp" align="center" class="form1">
			<label>Создайте логин:</label><input type="text" name="login" placeholder="ivanov" value="<?php if (!empty($_POST['login'])) echo $_POST['login']; ?>" required/>
			<label>Создайте пароль:</label><input type="password" name="password" placeholder="Qwerty123" value="<?php if (!empty($_POST['password'])) echo $_POST['password']; ?>" required/>
			<label>Повторите пароль:</label><input type="password" name="password1" placeholder="Qwerty123" value="<?php if (!empty($_POST['password1'])) echo $_POST['password1']; ?>" required/>
			<label>Введите фамилию и инициалы</label><input type="text" name="worker" placeholder="Иванов И. И." value="<?php if (!empty($_POST['worker'])) echo $_POST['worker']; ?>" required/>
			<input type="submit" id="regdata" value="Зарегестрироваться" />
			<?php
			if ($err == 1)
			echo "<p class=\"msg\">Ваш логин должен быть от 3-х до 30-и символов.<br>Допустимые символы только: a-z, 0-9, -, _</p>";
			else if ($err == 2)
			echo "<p class=\"msg\">Ваш пароль должен быть от 8-х до 32-и символов.<br>Содержать минимум: одну цифру, одну латинсукю <br> букву в нижнем регистре, одну в верхнем регистре</p>";
			else if ($err == 3)
			echo "<p class=\"msg\">Введенные пароли не совпадают</p>";
			else if ($err == 5)
			echo "<p class=\"msg\">Пользователь с таким логином уже существует</p>";
			else if ($err == 6)
			echo "<p class=\"msg\">Ошибка регистрации</p>";
			else if ($err == 7)
			echo "<p class=\"msg\">Не корректно введены фамилия и инициалы</p>";
			else if ($suc == 1)
			{
			echo "<p class=\"msg1\"> Регистрация прошла успешно</p>";
			echo "<a href=\"main.php\"><input type=\"button\" id=\"savedata\" value=\"Перейти на главную\" /></a>";
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