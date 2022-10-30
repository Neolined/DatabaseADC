<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "accept", false);
mysqli_set_charset($link, 'utf8');
$error_s1 = 0;
$error_s2 = 0;
$error_s3 = 0;
$error_s4 = 0;
$error_n1 = 1;
$error_t1 = 1;
$suc= 0;
$decSer = 1;
if (!empty ($_POST['sumb']))
{
	$_POST['type'] = mysqli_real_escape_string($link, $_POST['type']);
	$name = mysqli_query($link, "select `type` from `list_of_products` where `type` = '".$_POST['type']."'");
	$error_t1 = mysqli_num_rows($name);
	if ($error_t1 > 0)
	{
		$_POST['name'] = mysqli_real_escape_string($link, $_POST['name']);
		$name = mysqli_query($link, "select `name` from `list_of_products` where `name` = '".$_POST['name']."'");
		$error_n1 = mysqli_num_rows($name);
		if ($error_n1 > 0)
		{
			if ((!empty($_POST['lot'])) && (!empty($_POST['serial'])))
			{
				if (preg_match('/^[A-Z]\d{5}$/', $_POST['serial']) || preg_match('/^[Р]\d{6}$/u', $_POST['serial']) || preg_match('/^[А-Я]\d{4}$/u', $_POST['serial']) || preg_match('/^\d{5}$/', $_POST['serial']))
					{
						if (preg_match('/^\d{5}$/', $_POST['serial']))
						$decSer = 1;
						if ($_POST['lot']>=1)
						{	
							$str = mysqli_real_escape_string($link, $_POST['serial']);
							$lot = (int) $_POST['lot'];
							while($lot != 0)
							{
								if ($decSer == 0 && (mb_substr($_POST['serial'], 0, 1)) != (mb_substr($str, 0, 1)))
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
								$str = mysqli_real_escape_string($link, $_POST['serial']);
								$lot = (int) $_POST['lot'];
								$suc = 1;
								while ($lot > 0)
								{
									$query = "INSERT INTO products (`type`, `name`, `serial`, `location`, `owner`, `otk`, `date`) VALUES ('".$_POST['type']."', '".$_POST['name']."', '".$str."', 'stock', 'АДС', 'nocheck', NOW())";
									if (mysqli_query($link, $query))
										$id = (mysqli_insert_id($link));
									else 
										die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
									
									$query = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`, `location`) VALUES ('$id', NOW(), '".mysqli_real_escape_string($link, $_SESSION['worker'])."', 'record', '".mysqli_real_escape_string($link, $_POST['order_from'])."', 'АДС', '".mysqli_real_escape_string($link, $_POST['comment'])."','stock')";
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
}
?>
<!DOCTYPE html>
<html>
 <head>
<?php HtmlHead();?>
  <title>Интерфейс приемщика</title>
 </head>
 <body>
<?php createHeader($link);?>
 <div id="forma">
		<form action="accept.php" method="post" align="left" class="form">
			<p id="priem_name" align="center">Первичный прием </p>
			<input type="reset" id = "clearFormAccept" position= "bottom" value="Очистить данные формы"/>
			<label>Тип</label><input <?php if ($error_t1 == 0) echo "class=\"color_err1\"";?> type="text" id = "type" name="type" maxlength="100" value="<?php if (!empty($_POST['type'])) echo htmlspecialchars($_POST['type']); ?>" required/>
			<label>Название изделия</label><input <?php if ($error_n1 == 0) echo "class=\"color_err1\"";?> id = "name" type="text" name="name" maxlength="100" value="<?php if (!empty($_POST['name'])) echo htmlspecialchars($_POST['name']); ?>" required/>
			<div class="serial_lot">
			<div><label>Серийный номер</label><input <?php if (($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) echo "class=\"color_err\""; else echo "class=\"serial\""; ?> value="<?php if (($error_s1>0) || ($error_s4>0) || ($error_n1 == 0)) echo $_POST['serial']; if ($error_s2>0) echo "Системная ошибка"; if ($error_s3>0) echo $str; ?>" type="text" name="serial" maxlength="100" required/> </div>
			<div><label>Количество</label><input class="lot" type="text" name="lot" maxlength="3" value="<?php if ((($error_n1 == 0) || ($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) && (!empty($_POST['lot']))) echo $_POST['lot']; else echo '1'; ?>"/></div>
			</div>
			<label>От кого</label><input type="text" id = "order_from" name="order_from" maxlength="100" value= "<?php if (!empty($_POST['order_from'])) echo htmlspecialchars($_POST['order_from']); ?>" required/>
			<label>Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000"><?php if (!empty($_POST['comment'])) echo htmlspecialchars($_POST['comment']); ?></textarea>
			<input type="submit" id="savedata" name = "sumb" value="Сохранить данные" />
			<?php
			if (($error_s1>0) || ($error_s3>0))
			echo "<p class=\"msg\"> Серийный номер уже зарегистрирован </p>";
			if ($error_s4>0)
			echo "<p class=\"msg\"> Неккоректно введен серийный номер</p>";
			if ($error_n1 == 0)
			echo "<p class=\"msg\"> Неккоректно введено название изделия</p>";
			if ($error_t1 == 0)
			echo "<p class=\"msg\"> Неккоректно введено название типа изделия</p>";
			if ($suc == 1)
			echo "<p class=\"msg1\"> Данные сохранены</p>";
		?>
	</div>
	<?php createFooter();?>
	<script>
$(document).ready(function(){
$("#type").autocompleteArray(
	<?php
	$result = mysqli_query($link, "select distinct `type` from `list_of_products` where `type` != ''");
	$type = mysqli_fetch_all($result);
	echo json_encode($type); 
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
$("#order_from").autocompleteArray(
	<?php
	$result = mysqli_query($link, "select distinct `order_from` from `history` where `order_from` != ''");
	$order_from = mysqli_fetch_all($result);
	echo json_encode($order_from); 
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
	$result = mysqli_query($link, "select distinct `name` from `list_of_products` where `name` != ''");
	$name = mysqli_fetch_all($result);
	echo json_encode($name); 
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