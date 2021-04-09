<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "accept");
mysqli_set_charset($link, 'utf8');

$error_s1 = 0;
$error_s2 = 0;
$error_s3 = 0;
$error_s4 = 0;
$error_n1 = 1;
$suc= 0;		

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
								
								$query = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ('$id', NOW(), '".$_SESSION['worker']."', 'Запись', '".$_POST['order_from']."', 'АДС', '".$_POST['comment']."')";
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
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <title>Интерфейс приемщика</title>
  <script src="js/jquery.js"></script>
  <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
 </head>
 <body>
 <div class="header">
 	<?php createMenu(); ?>
	<img id="adc" src="images/adc.png">
	<div id="worker">
	<p><img id="exit" src="images/worker.png"><?php echo $_SESSION['worker']; ?></p>
	</div>
</div>
 <div id="forma">
		<form action="accept.php" position= "bottom" method="post" id="cleardata" class = "form">
		<p id="priem_name" align="center">Первичный прием </p>
		<input type="submit" name = "villy" value="Очистить данные формы"/>
		</form>
		<form action="accept.php" method="post" align="left" class="form">
			<label>Тип</label><input type="text" id = "type" name="type" onfocus="this.value=''" value="<?php if (!empty($_POST['type'])) echo $_POST['type']; ?>" required/>
			<label>Название изделия</label><input <?php if ($error_n1 == 0) echo "class=\"color_err1\"";?> id = "name" type="text" name="name" onfocus="this.value=''" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>" required/>
			<label>Исполнение</label><input type="text" id = "perfomance" name="perfomance" onfocus="this.value=''" value="<?php if (!empty($_POST['perfomance'])) echo $_POST['perfomance']; ?>"required/>
			
			<div class="serial_lot">
			<div><label>Серийный номер</label><input <?php if (($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) echo "class=\"color_err\""; else echo "class=\"serial\""; ?> value="<?php if (($error_s1>0) || ($error_s4>0) || ($error_n1 == 0)) echo $_POST['serial']; if ($error_s2>0) echo "Системная ошибка"; if ($error_s3>0) echo $str; ?>" type="text" name="serial" required/> </div>
			<div><label>Количество</label><input class="lot" type="text" name="lot" onfocus="this.value=''" value="<?php if ((($error_n1 == 0) || ($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) && (!empty($_POST['lot']))) echo $_POST['lot']; else echo '1'; ?>"/></div>
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
	<script>
$(document).ready(function(){
$("#type").autocompleteArray(
	<?php
	$result = mysqli_query($link, "select distinct `type` from `list_of_products`");
	$row = mysqli_fetch_all($result);
	echo json_encode($row); 
	?>
	,
		{
			delay:10,
			minChars:1,
			matchSubset:1,
			autoFill:true,
			maxItemsToShow:10
		}
	);
});

$(document).ready(function(){
$("#name").autocompleteArray(
	<?php
	$result = mysqli_query($link, "select distinct `name` from `list_of_products`");
	$ass = mysqli_fetch_all($result);
	echo json_encode($ass); 
	?>
	,
		{
			delay:10,
			minChars:1,
			matchSubset:1,
			autoFill:true,
			maxItemsToShow:10
		}
);
});
function show_item(id, status)
{
	if (status==0)	$('#'+id).animate({ height: "hide"}, "hide");
	else $('#'+id).animate({ height: "show" }, "slow");
}
</script>
 </body>
</html>