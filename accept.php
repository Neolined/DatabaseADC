<?php
session_start();
if ((!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT']) || ($_SESSION['root'] !== "accept"))
{
	header('Location: main.php');
}
require_once 'connect.php';
mysqli_set_charset($link, 'utf8');

$error_s1 = 0;
$error_s2 = 0;
$error_s3 = 0;
$error_s4 = 0;
$error_n1 = 1;
$suc= 0;
$query = mysqli_query($link, "select worker from users where user = '".$_SESSION['user']."'");
$worker = mysqli_fetch_row($query);
$worker = $worker[0];		

if (!empty ($_POST['name']))
{
	$name = mysqli_query($link, "select `name` from `list_of_products` where `name` = '".$_POST['name']."'");
	$error_n1 = mysqli_num_rows($name);
	if ($error_n1 > 0)
	{
		if ((!empty($_POST['lot'])) && (!empty($_POST['serial'])))
		{
			if (preg_match('/^[A-Z]\d{5}$/', $_POST['serial']))
				{
					if ($_POST['lot']>=1)
					{	
						$str = $_POST['serial'];
						$lot = (int) $_POST['lot'];
						while($lot != 0)
						{
								if ((mb_substr($_POST['serial'], 0, 1)) != (mb_substr($str, 0, 1)))
								{
									$error_s2 = 1;//если первая буква внаименовании изделия изменилась 
									break;
								}
								$serial = mysqli_query($link, "select `serial` from `products` where `serial` = '".$str."'");
								$error_s3 = mysqli_num_rows($serial);
								if ($error_s3 > 0)
								{
									break;//если во время цикла увидел, что такой серийный номер есть в бд
								}
								$str++;
								$lot--;
						}
						if (($error_s2 != 1) && ($error_s3 == 0)) //передача данных в бд дополнить!
						{
							$str = $_POST['serial'];
							$lot = (int) $_POST['lot'];
							$suc = 1;
							while ($lot > 0)
							{
								$query = "INSERT INTO products (`type`, `name`, `perfomance`, `serial`, `date`) VALUES ('".$_POST['type']."', '".$_POST['name']."', '".$_POST['perfomance']."', '".$str."', NOW())";
								if (mysqli_query($link, $query))
									$id = (mysqli_insert_id($link));
								else 
									die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
								
								$query = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ('$id', NOW(), '$worker', 'Запись', '".$_POST['order_from']."', 'АДС', '".$_POST['comment']."')";
								if (!(mysqli_query($link, $query)))
									die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
								$str++;
								$lot--;

							}
						}
						
					}
				}
		
			else 
			$error_s4 = 1;
		}
	}
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="asset/css/main1.css"<?php echo(microtime(true).rand()); ?>>
  <title>Интерфейс приемщика</title>
 </head>
 <body>
 <div class="header">
	<div class="dropdown">
		<button class="dropbtn" align="center">МЕНЮ</button>
		<div class="dropdown-content">
			<a href="main.php">Главная</a>
			<a href="exit.php"><img id="exit" src="exit.png"><p id="exitp">Выход</p></a>
		</div>
	</div>
	<img id="adc" src="adc.png">
	<div id="worker">
	<p><?php echo $worker; ?></p>
	</div>
</div>
 <div id="forma">
		<form action="accept.php" position= "bottom" method="post" id="cleardata" class = "form">
		<p id="priem_name" align="center">Первичный прием </p>
		<input type="submit" name = "villy" value="Очистить данные формы"/>
		</form>
		<form action="accept.php" method="post" align="left" class="form">
			<label>Тип изделия</label>
				<select size="1" name="type" required>
				<?php
				if (empty($_POST['type']))
				echo "<option selected disabled hidden style='display: none' value=''>Выберите тип</option>";
				else
				{
				echo "<option selected style='display: none'>";
				echo $_POST['type'];
				echo "</option>";
				}
				?>
				<?php
				$result = mysqli_query($link, "select distinct `type` from `list_of_products`");
				$num = mysqli_num_rows($result);
				while ($num > 0)
				{
				$row = mysqli_fetch_array($result);
				echo '<option>'.$row['type'].'</option>';
				$num--;
				}
				?>
				</select>
			<label>Название изделия</label><input <?php if ($error_n1 == 0) echo "class=\"color_err1\"";?> type="text" name="name" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>" required/>
			<label>Исполнение</label><input type="text" name="perfomance" onfocus="this.value=''" value="<?php if (!empty($_POST['perfomance'])) echo $_POST['perfomance']; ?>"/>
			
			<div class="serial_lot">
			<div><label>Серийный номер</label><input <?php if (($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) echo "class=\"color_err\""; else echo "class=\"serial\""; ?> value="<?php if (($error_s1>0) || ($error_s4>0) || ($error_n1 == 0)) echo $_POST['serial']; if ($error_s2>0) echo "Системная ошибка"; if ($error_s3>0) echo $str; ?>" type="text" name="serial" required/> </div>
			<div class="lol"><label>Количество</label><input class="lot" type="text" name="lot" onfocus="this.value=''" value="<?php if ((($error_n1 == 0) || ($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) && (!empty($_POST['lot']))) echo $_POST['lot']; else echo '1'; ?>"/></div>
			</div>
			<label>От кого</label><input type="text" name="order_from" onfocus="this.value=''" value="<?php if (!empty($_POST['order_from'])) echo $_POST['order_from']; ?>" required/>
			<label>Комментарий</label><textarea class="comment" type="text" name="comment" onfocus="this.value=''"> <?php if (!empty($_POST['comment'])) echo $_POST['comment']; ?></textarea>
			<input type="submit" id="savedata" value="Сохранить данные" />
			<?php
			if (($error_s1>0) || ($error_s3>0))
			echo "<p class=\"msg\"> Серийный номер уже зарегистрирован </p>";
			if ($error_s4>0)
			echo "<p class=\"msg\"> Неккоректно введен серийный номер</p>";
			if ($error_n1 == 0)
			echo "<p class=\"msg\"> Неккоректно введено название изделия</p>";
			if ($suc == 1) 
			echo "<p class=\"msg1\"> Данные успешно занесены!</p>";
		?>
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
 </body>
</html>