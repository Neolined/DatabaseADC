<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "refand");
mysqli_set_charset($link, 'utf8');
$succ = 0;
$error_n1 = 1;
$error_t1 = 1;
if (!empty($_POST['savebtY']))
{
	$result = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ((select uid from products where `serial` = '".$_POST['serialHide']."'), NOW(), '".mysqli_real_escape_string($link,$_SESSION['worker'])."', 'record', '".mysqli_real_escape_string($link, $_POST['order_from'])."', 'АДС', '".mysqli_real_escape_string($link, $_POST['comment'])."')";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	$result = "UPDATE `products` SET `location` = '".mysqli_real_escape_string($link, $_POST['location'])."', `owner` = 'АДС' where `serial` = '".$_POST['serialHide']."'";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
		$succ = 1;
}
if (!empty($_POST['savebtN']))
{
	$result = mysqli_query($link, "select `type` from `list_of_products` where `type` = '".mysqli_real_escape_string($link, $_POST['type'])."'");
	$error_t1 = mysqli_num_rows($result);
	if ($error_t1 > 0)
	{
		$result = mysqli_query($link, "select `name` from `list_of_products` where `name` = '".mysqli_real_escape_string($link, $_POST['name'])."'");
		$error_n1 = mysqli_num_rows($result);
		if ($error_n1 > 0)
		{
			$result = "INSERT INTO products (`type`, `name`, `perfomance`, `serial`, `location`, `owner`,  `date`) VALUES ('".mysqli_real_escape_string($link, $_POST['type'])."', '".mysqli_real_escape_string($link, $_POST['name'])."', '".mysqli_real_escape_string($link, $_POST['perfomance'])."', '".$_POST['serialHide']."', '".mysqli_real_escape_string($link, $_POST['location'])."', 'АДС', NOW())";
			if (mysqli_query($link, $result))
				$id = (mysqli_insert_id($link));
			else 
				die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
			$result = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ($id, NOW(), '".mysqli_real_escape_string($link, $_SESSION['worker'])."', 'record', '".mysqli_real_escape_string($link, $_POST['order_from'])."', 'АДС', '".mysqli_real_escape_string($link, $_POST['comment'])."')";
			if (!(mysqli_query($link, $result)))
				die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
			$succ = 2;
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
 <?php createHeader($link);?>
 <div id="forma">
 		<form action="refand.php" method="post" align="left" id="nextForm"></form>
		<form action="refand.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Возврат</p>
			<div class="serial_lot">
			<div id = "inputLabel"><label>Серийный номер</label><input type="text" form = "nextForm" name="serial"  maxlength="10" <?php if (!empty($_POST['savebtN'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial'])) echo $_POST['serial']; else if ($error_t1 == 0 || $error_n1 == 0) echo $_POST['serial']; ?>" required/> </div>
			<input type="submit" id="nextbtn" form = "nextForm" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				echo '<input type = "hidden" name =  "serialHide" value = "';//реализация сохранения данных в переменную пост
				if (!empty($_POST['serial']))
					echo htmlspecialchars($_POST['serial']);
				echo '">';
				$arrInput = array("order_from", "type", "name", "perfomance", "comment", "location");
				$i = 0;
				while (isset($arrInput[$i]))
				{
					echo '<input type = "hidden" name =  "'.$arrInput[$i].'Hide" form = "nextForm" value = "';
					if (!empty($_POST[$arrInput[$i]]))
						echo htmlspecialchars($_POST[$arrInput[$i]]);
					else if (!empty ($_POST[$arrInput[$i].'Hide']))
						echo htmlspecialchars($_POST[$arrInput[$i].'Hide']);
					echo '">';
					$i++;
				}
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{
						if (preg_match('/^[A-Z]\d{5}$/', $_POST['serial']) || preg_match('/^[Р]\d{6}$/u', $_POST['serial']) || preg_match('/^[А-Я]\d{4}$/u', $_POST['serial']) || preg_match('/^\d{5}$/', $_POST['serial']))
						{
							echo '<div id = "inp"><input type="reset" id = "clearForm" value="Очистить данные формы"/></div>';
							$result = mysqli_query($link, "select type, name from products where serial = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
							$row = mysqli_fetch_row($result);
							if (!empty($row))
							{
								echo '<p id = "infoBoard">'.$row[0].' '.$row[1].'</p>';
								echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" id = "order_from" maxlength="100" '; if (!empty($_POST['order_fromHide'])) echo 'value = "'.htmlspecialchars($_POST['order_fromHide']).'"'; echo '></input></div>';
								echo '<select class="select" name="location" required>';
								echo '<option value = "">Выберите местоположение</option>';
								echo '<option value="stock"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'stock') echo 'selected'; echo '>Склад</option>';
								echo '<option value="develop"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'develop') echo 'selected'; echo '>Разработка</option>';
								echo '<option value="isolator"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'isolator') echo 'selected'; echo '>Изолятор брака</option>';
								echo '<option value="nelikvid"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'nelikvid') echo 'selected'; echo '>Неликвид</option>';
								echo '<option value="repair"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'repair') echo 'selected'; echo '>Ремонт</option>';
								echo '<option value="work"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'work') echo 'selected'; echo '>Производство</option>';
								echo '</select>';
								echo '<div id = "inp"><label>Комментарий</label><textarea class="comment" id = "comment" type="text" name="comment" maxlength="1000">'; if (!empty($_POST['commentHide'])) echo htmlspecialchars($_POST['commentHide']); echo '</textarea></div>';
								echo '<input type="submit" id="savedata" name = "savebtY" value="Сохранить данные"/>';
							}
							else
							{
								
								echo '<div id = "inp"> <label>Тип</label><input id = "type" type="text" name="type" maxlength="100" required '; if (!empty($_POST['typeHide'])) echo 'value = "'.$_POST['typeHide'].'"'; echo '></input></div>';
								echo '<div id = "inp"> <label>Название изделия</label><input id = "name" type="text" name="name" maxlength="100" required '; if (!empty($_POST['nameHide'])) echo 'value = "'.$_POST['nameHide'].'"'; echo '></input></div>';
								echo '<div id = "inp"> <label>Исполнение</label><input type="text" name="perfomance" id = "perfomance" maxlength="100" '; if (!empty($_POST['perfomanceHide'])) echo 'value = "'.htmlspecialchars($_POST['perfomanceHide']).'"'; echo '></input></div>';
								echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" id = "order_from" maxlength="100" required '; if (!empty($_POST['order_fromHide'])) echo 'value = "'.htmlspecialchars($_POST['order_fromHide']).'"'; echo '></input></div>';
								echo '<select class="select" name="location" id = "location" required>';
								echo '<option value = "">Выберите местоположение</option>';
								echo '<option value="stock"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'stock') echo 'selected'; echo '>Склад</option>';
								echo '<option value="develop"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'develop') echo 'selected'; echo '>Разработка</option>';
								echo '<option value="isolator"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'isolator') echo 'selected'; echo '>Изолятор брака</option>';
								echo '<option value="nelikvid"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'nelikvid') echo 'selected'; echo '>Неликвид</option>';
								echo '<option value="repair"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'repair') echo 'selected'; echo '>Ремонт</option>';
								echo '<option value="work"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'work') echo 'selected'; echo '>Производство</option>';
								echo '</select>';
								echo '<div id = "inp"><label>Комментарий</label><textarea class="comment" id = "comment" type="text" name="comment" maxlength="1000">'; if (!empty($_POST['commentHide'])) echo htmlspecialchars($_POST['commentHide']); echo '</textarea></div>';
								echo '<input type="submit" id="savedata" name = "savebtN" value="Сохранить данные"/>';
							}
						}
						else echo "<p class=\"msg\">Некорректно введен серийный номер</p>";

					}
				}
				else if (!empty($_POST['savebtN']) && ($error_n1 == 0 || $error_t1 == 0))
				{
					echo '<div id = "inp"> <label>Тип</label><input id = "type" type="text" name="type" maxlength="100" required '; if (!empty($_POST['typeHide'])) echo 'value = "'.$_POST['typeHide'].'"'; echo '></input></div>';
					echo '<div id = "inp"> <label>Название изделия</label><input id = "name" type="text" name="name" maxlength="100" required '; if (!empty($_POST['nameHide'])) echo 'value = "'.$_POST['nameHide'].'"'; echo '></input></div>';
					echo '<div id = "inp"> <label>Исполнение</label><input type="text" name="perfomance" id = "perfomance" maxlength="100" '; if (!empty($_POST['perfomanceHide'])) echo 'value = "'.htmlspecialchars($_POST['perfomanceHide']).'"'; echo '></input></div>';
					echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" id = "order_from" maxlength="100" required '; if (!empty($_POST['order_fromHide'])) echo 'value = "'.htmlspecialchars($_POST['order_fromHide']).'"'; echo '></input></div>';
					echo '<select class="select" name="location" id = "location" required>';
					echo '<option value = "">Выберите местоположение</option>';
					echo '<option value="stock"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'stock') echo 'selected'; echo '>Склад</option>';
					echo '<option value="develop"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'develop') echo 'selected'; echo '>Разработка</option>';
					echo '<option value="isolator"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'isolator') echo 'selected'; echo '>Изолятор брака</option>';
					echo '<option value="nelikvid"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'nelikvid') echo 'selected'; echo '>Неликвид</option>';
					echo '<option value="repair"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'repair') echo 'selected'; echo '>Ремонт</option>';
					echo '<option value="work"'; if (!empty($_POST['locationHide']) && $_POST['locationHide'] == 'work') echo 'selected'; echo '>Производство</option>';
					echo '</select>';
					echo '<div id = "inp"><label>Комментарий</label><textarea class="comment" id = "comment" type="text" name="comment" maxlength="1000">'; if (!empty($_POST['commentHide'])) echo htmlspecialchars($_POST['commentHide']); echo '</textarea></div>';
					echo '<input type="submit" id="savedata" name = "savebtN" value="Сохранить данные"/>';
					if ($error_n1 == 0)
						echo "<p class=\"msg\"> Неккоректно введено название изделия</p>";
					if ($error_t1 == 0)
						echo "<p class=\"msg\"> Неккоректно введено название типа изделия</p>";
				}
				if ($succ == 1)
					echo "<p class=\"msg1\">Данные сохранены</p>";	
				else if ($succ == 2)
					echo "<p class=\"msg1\">Данные сохранены. Плата была создана</p>";
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
				text: _this.children('option:<?php if (!empty($_POST['locationHide'])) echo "selected"; else echo "first";?>').text()
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
		$(document).ready(function(){//сброс данных на странице через jquery по кнопке
		$('#clearForm').click(function(){
			$('.select').prop('selectedIndex',0);
			$(".new-select").text(function(){
			return($(".select option:first").text());
			})
		})
	});
	</script>
	<script src = "js/script.js"></script>
 </body>
</html>