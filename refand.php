<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "refand");
sessStart($link, "refand");
mysqli_set_charset($link, 'utf8');
$succ = 0;
$error_n1 = 1;
$error_t1 = 1;
if (!empty($_POST['savebtY']))
{
	$_SESSION['rOrder_from'] = $_POST['order_from'];
	$_SESSION['rComment'] = $_POST['comment'];
	$_SESSION['rLocation'] = $_POST['location'];
	$result = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ((select uid from products where `serial` = '".$_SESSION['serial']."'), NOW(), '".$_SESSION['worker']."', 'record', '".mysqli_real_escape_string($link, $_SESSION['rOrder_from'])."', 'АДС', '".mysqli_real_escape_string($link, $_SESSION['rComment'])."')";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	$result = "UPDATE `products` SET `location` = '".mysqli_real_escape_string($link, $_SESSION['rLocation'])."', `owner` = 'АДС' where `serial` = '".$_SESSION['serial']."'";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
		$succ = 1;
	unset ($_SESSION['serial']);
}
if (!empty($_POST['savebtN']))
{
	$_SESSION['rType'] = $_POST['type'];
	$_SESSION['rName'] = $_POST['name'];
	$_SESSION['rPerfomance'] = $_POST['perfomance'];
	$_SESSION['rLocation'] = $_POST['location'];
	$_SESSION['rOrder_from'] = $_POST['order_from'];
	$_SESSION['rComment'] = $_POST['comment'];
	$result = mysqli_query($link, "select `type` from `list_of_products` where `type` = '".mysqli_real_escape_string($link, $_SESSION['rType'])."'");
	$error_t1 = mysqli_num_rows($result);
	if ($error_t1 > 0)
	{
		$result = mysqli_query($link, "select `name` from `list_of_products` where `name` = '".mysqli_real_escape_string($link, $_SESSION['rName'])."'");
		$error_n1 = mysqli_num_rows($result);
		if ($error_n1 > 0)
		{
			$result = "INSERT INTO products (`type`, `name`, `perfomance`, `serial`, `location`, `owner`,  `date`) VALUES ('".mysqli_real_escape_string($link, $_SESSION['rType'])."', '".mysqli_real_escape_string($link, $_SESSION['rName'])."', '".mysqli_real_escape_string($link, $_SESSION['rPerfomance'])."', '".$_SESSION['serial']."', '".mysqli_real_escape_string($link, $_SESSION['rLocation'])."', 'АДС', NOW())";
			if (mysqli_query($link, $result))
				$id = (mysqli_insert_id($link));
			else 
				die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
			$result = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ($id, NOW(), '".mysqli_real_escape_string($link, $_SESSION['worker'])."', 'record', '".mysqli_real_escape_string($link, $_SESSION['rOrder_from'])."', 'АДС', '".mysqli_real_escape_string($link, $_SESSION['rComment'])."')";
			if (!(mysqli_query($link, $result)))
				die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
			$succ = 2;
			unset ($_SESSION['serial']);
		}
	}
}
if (!empty($_POST['postFromMain']))
{
	$_POST['nextbtn'] = 1;
	$_POST['serial'] = $_POST['postFromMain'];
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <script src="js/jquery.js"></script>
  <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
  <title>Возврат</title>
 </head>
 <body>
 <div class="header">
 <?php createMenu($link) ?>
	<img id="adc" src="images/adc.png">
	<div id="worker">
	<p><img id="exit" src="images/worker.png"><?php echo $_SESSION['worker']; ?></p>
	</div>
</div>
 <div id="forma">
 		<form action="refand.php" method="post" align="left" id="nextForm"></form>
		<form action="refand.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Возврат</p>
			<div class="serial_lot">
			<div id = "inputLabel"><label>Серийный номер</label><input type="text" form = "nextForm" name="serial"  maxlength="10" <?php if (!empty($_POST['savebtN'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial'])) echo $_POST['serial']; else if ($error_t1 == 0 || $error_n1 == 0) echo $_SESSION['serial']; ?>" required/> </div>
			<input type="submit" id="nextbtn" form = "nextForm" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{
						if (preg_match('/^[A-Z]\d{5}$/', $_POST['serial']) || preg_match('/^[Р]\d{6}$/u', $_POST['serial']) || preg_match('/^[А-Я]\d{4}$/u', $_POST['serial']) || preg_match('/^\d{5}$/', $_POST['serial']))
						{
							$_SESSION['serial'] = mysqli_real_escape_string($link, $_POST['serial']);
							$result = mysqli_query($link, "select type, name from products where serial = '".$_SESSION['serial']."'");
							$row = mysqli_fetch_row($result);
							
							if (!empty($row))
							{
								echo '<p id = "infoBoard">'.$row[0].' '.$row[1].'</p>';
								echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" id = "order_from" maxlength="100" '; if (!empty($_SESSION['rOrder_from'])) echo 'value = "'.htmlspecialchars($_SESSION['rOrder_from']).'"'; echo '></input></div>';
								echo '<select class="select" name="location" required>';
								echo '<option value = "">Выберите местоположение</option>';
								echo '<option value="stock"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'stock') echo 'selected'; echo '>Склад</option>';
								echo '<option value="develop"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'develop') echo 'selected'; echo '>Разработка</option>';
								echo '<option value="isolator"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'isolator') echo 'selected'; echo '>Изолятор брака</option>';
								echo '<option value="nelikvid"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'nelikvid') echo 'selected'; echo '>Неликвид</option>';
								echo '<option value="repair"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'repair') echo 'selected'; echo '>Ремонт</option>';
								echo '<option value="work"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'work') echo 'selected'; echo '>Производство</option>';
								echo '</select>';
								echo '<label style = "margin-top: 1em" >Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000">'; if (!empty($_SESSION['rComment'])) echo htmlspecialchars($_SESSION['rComment']); echo '</textarea>';
								echo '<input type="submit" id="savedata" name = "savebtY" value="Сохранить данные"/>';
							}
							else
							{
								echo '<div id = "inp"> <label>Тип</label><input id = "type" type="text" name="type" maxlength="100" required '; if (!empty($_SESSION['rType'])) echo 'value = "'.$_SESSION['rType'].'"'; echo '></input></div>';
								echo '<div id = "inp"> <label>Название изделия</label><input id = "name" type="text" name="name" maxlength="100" required '; if (!empty($_SESSION['rName'])) echo 'value = "'.$_SESSION['rName'].'"'; echo '></input></div>';
								echo '<div id = "inp"> <label>Исполнение</label><input type="text" name="perfomance" maxlength="100" '; if (!empty($_SESSION['rPerfomance'])) echo 'value = "'.htmlspecialchars($_SESSION['rPerfomance']).'"'; echo '></input></div>';
								echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" id = "order_from" maxlength="100" required '; if (!empty($_SESSION['rOrder_from'])) echo 'value = "'.htmlspecialchars($_SESSION['rOrder_from']).'"'; echo '></input></div>';
								echo '<select class="select" name="location" required>';
								echo '<option value = "">Выберите местоположение</option>';
								echo '<option value="stock"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'stock') echo 'selected'; echo '>Склад</option>';
								echo '<option value="develop"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'develop') echo 'selected'; echo '>Разработка</option>';
								echo '<option value="isolator"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'isolator') echo 'selected'; echo '>Изолятор брака</option>';
								echo '<option value="nelikvid"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'nelikvid') echo 'selected'; echo '>Неликвид</option>';
								echo '<option value="repair"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'repair') echo 'selected'; echo '>Ремонт</option>';
								echo '<option value="work"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'work') echo 'selected'; echo '>Производство</option>';
								echo '</select>';
								echo '<div id = "inp"><label>Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000">'; if (!empty($_SESSION['rComment'])) echo htmlspecialchars($_SESSION['rComment']); echo '</textarea></div>';
								echo '<input type="submit" id="savedata" name = "savebtN" value="Сохранить данные"/>';
							}
						}
						else echo "<p class=\"msg\">Некорректно введен серийный номер</p>";

					}
				}
				else if (!empty($_POST['savebtN']) && ($error_n1 == 0 || $error_t1 == 0))
				{
					echo '<div id = "inp"> <label>Тип</label><input id = "type" type="text" name="type" maxlength="100" required '; if (!empty($_SESSION['rType'])) echo 'value = "'.$_SESSION['rType'].'"'; echo '></input></div>';
					echo '<div id = "inp"> <label>Название изделия</label><input id = "name" type="text" name="name" maxlength="100" required '; if (!empty($_SESSION['rName'])) echo 'value = "'.$_SESSION['rName'].'"'; echo '></input></div>';
					echo '<div id = "inp"> <label>Исполнение</label><input type="text" name="perfomance" maxlength="100" '; if (!empty($_SESSION['rPerfomance'])) echo 'value = "'.htmlspecialchars($_SESSION['rPerfomance']).'"'; echo '></input></div>';
					echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" id = "order_from" maxlength="100" required '; if (!empty($_SESSION['rOrder_from'])) echo 'value = "'.htmlspecialchars($_SESSION['rOrder_from']).'"'; echo '></input></div>';
					echo '<select class="select" name="location" required>';
					echo '<option value = "">Выберите местоположение</option>';
					echo '<option value="stock"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'stock') echo 'selected'; echo '>Склад</option>';
					echo '<option value="develop"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'develop') echo 'selected'; echo '>Разработка</option>';
					echo '<option value="isolator"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'isolator') echo 'selected'; echo '>Изолятор брака</option>';
					echo '<option value="nelikvid"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'nelikvid') echo 'selected'; echo '>Неликвид</option>';
					echo '<option value="repair"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'repair') echo 'selected'; echo '>Ремонт</option>';
					echo '<option value="work"'; if (!empty($_SESSION['rLocation']) && $_SESSION['rLocation'] == 'work') echo 'selected'; echo '>Производство</option>';
					echo '</select>';
					echo '<div id = "inp"><label>Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000">'; if (!empty($_SESSION['rComment'])) echo htmlspecialchars($_SESSION['rComment']); echo '</textarea></div>';
					echo '<input type="submit" id="savedata" name = "savebtN" value="Сохранить данные"/>';
					if ($error_n1 == 0)
						echo "<p class=\"msg\"> Неккоректно введено название изделия</p>";
					if ($error_t1 == 0)
						echo "<p class=\"msg\"> Неккоректно введено название типа изделия</p>";
				}
				if ($succ == 1)
					echo "<p class=\"msg1\">Данные сохранены</p>";	
				else if ($succ == 2)
					echo "<p class=\"msg1\">Данные сохранены <br> Плата была создана</p>";	
				
				?>
				
			</div>
		</form>
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	<script>
		var once = 0;
		function hideotk()
		{
			if (once == 0)
			{
			document.getElementById('contentOtk').setAttribute("style", "display: none");
			once = 1;
			}
		}
		if ( window.history.replaceState )
		{
        	window.history.replaceState( null, null, window.location.href );
    	}
	</script>
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
<script>
				$('.select').each(function() {
			const _this = $(this),
				selectOption = _this.find('option'),
				selectOptionLength = selectOption.length,
				selectedOption = selectOption.filter(':selected'),
				duration = 100; // длительность анимации 

			_this.wrap('<div class="select"></div>');
			$('<div>', {
				class: 'new-select',	
				text: _this.children('option:<?php if (!empty($_SESSION['rLocation'])) echo "selected"; else echo "first";?>').text()
			}).insertAfter(_this);

			const selectHead = _this.next('.new-select');
			$('<div>', {
				class: 'new-select__list'
			}).insertAfter(selectHead);

			const selectList = selectHead.next('.new-select__list');
			for (let i = 1; i < selectOptionLength; i++) {
				$('<div>', {
					class: 'new-select__item',
					html: $('<span>', {
						text: selectOption.eq(i).text()
					})
				})
				.attr('data-value', selectOption.eq(i).val())
				.appendTo(selectList);
			}

			const selectItem = selectList.find('.new-select__item');
			selectList.slideUp(0);
			selectHead.on('click', function() {
				if ( !$(this).hasClass('on') ) {
					$(this).addClass('on');
					selectList.slideDown(duration);

					selectItem.on('click', function() {
						let chooseItem = $(this).data('value');

						$('select').val(chooseItem).attr('selected', 'selected');
						selectHead.text( $(this).find('span').text() );

						selectList.slideUp(duration);
						selectHead.removeClass('on');
					});

				} else {
					$(this).removeClass('on');
					selectList.slideUp(duration);
				}
			});
		});
		
	</script>
 </body>
</html>