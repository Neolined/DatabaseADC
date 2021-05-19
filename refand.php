<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "refand");
clearSESSION1('refand', array("serial"));
clearSESpage();
mysqli_set_charset($link, 'utf8');
$succ = 0;
if (!empty($_POST['savebtY']))
{
	$result = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ((select uid from products where `serial` = '".$_SESSION['serial']."'), NOW(), '".$_SESSION['worker']."', 'record', '".$_POST['order_from']."', 'АДС', '".$_POST['comment']."')";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	$result = "UPDATE `products` SET `location` = '".$_POST['location']."', `owner` = 'АДС' where `serial` = '".$_SESSION['serial']."'";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
		$succ = 1;
	unset ($_SESSION['serial']);
}
if (!empty($_POST['savebtN']))
{
	$result = "INSERT INTO products (`type`, `name`, `perfomance`, `serial`, `location`, `owner`,  `date`) VALUES ('".$_POST['type']."', '".$_POST['name']."', '".$_POST['perfomance']."', '".$_SESSION['serial']."', '".$_POST['location']."', 'АДС', NOW())";
	if (mysqli_query($link, $result))
		$id = (mysqli_insert_id($link));
	else 
		die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
	$result = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ($id, NOW(), '".$_SESSION['worker']."', 'record', '".$_POST['order_from']."', 'АДС', '".$_POST['comment']."')";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
		$succ = 2;
		unset ($_SESSION['serial']);
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
 <?php createMenu() ?>
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
			<div id = "inputLabel"><label>Серийный номер</label><input type="text" form = "nextForm" name="serial"  maxlength="10" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial']) && empty($_POST['savebtn']) ) echo $_POST['serial']; ?>" required/> </div>
			<input type="submit" id="nextbtn" form = "nextForm" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{
						if (preg_match('/^[A-Z]\d{5}$/', $_POST['serial']) || preg_match('/^[А-Я]\d{4}$/u', $_POST['serial']) || preg_match('/^\d{5}$/', $_POST['serial']))
						{
							$_SESSION['serial'] = $_POST['serial'];
							$result = mysqli_query($link, "select * from products where serial = '".$_SESSION['serial']."'");
							$row = mysqli_num_rows($result);
							if ($row != 0)
							{
								echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" maxlength="100"></input></div>';
								echo '<select class="select" name="location" required>';
								echo '<option value = "">Выберите местоположение</option>';
								echo '<option value="stock">Склад</option>';
								echo '<option value="develop">Разработка</option>';
								echo '<option value="isolator">Изолятор брака</option>';
								echo '<option value="nelikvid">Неликвид</option>';
								echo '</select>';
								echo '<label style = "margin-top: 1em" >Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000"></textarea>';
								echo '<input type="submit" id="savedata" name = "savebtY" value="Сохранить данные"/>';
							}
							else
							{
								echo '<div id = "inp"> <label>Тип</label><input id = "type" type="text" name="type" maxlength="100" required></input></div>';
								echo '<div id = "inp"> <label>Название изделия</label><input id = "name" type="text" name="name" maxlength="100" required></input></div>';
								echo '<div id = "inp"> <label>Исполнение</label><input type="text" name="perfomance" maxlength="100" required></input></div>';
								echo '<div id = "inp"> <label>От кого</label><input type="text" name="order_from" maxlength="100" required></input></div>';
								echo '<select class="select" name="location" required>';
								echo '<option value = "">Выберите местоположение</option>';
								echo '<option value="stock">Склад</option>';
								echo '<option value="develop">Разработка</option>';
								echo '<option value="isolator">Изолятор брака</option>';
								echo '<option value="nelikvid">Неликвид</option>';
								echo '</select>';
								echo '<div id = "inp"><label>Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000"></textarea></div>';
								echo '<input type="submit" id="savedata" name = "savebtN" value="Сохранить данные"/>';
							}
						}
						else echo "<p class=\"msg\">Некорректно введен серийный номер</p>";

					}
				}
				if ($succ == 1)
				{
					echo "<p class=\"msg1\">Данные сохранены</p>";	
				}
				else if ($succ == 2)
				{
					echo "<p class=\"msg1\">Данные сохранены <br> Плата была создана</p>";	
				}
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
				text: _this.children('option:first').text()
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