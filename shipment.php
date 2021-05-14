<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "shipment");
clearSESSION1('shipment', array("year", "number", "str", "orderArr"));
clearSESpage();
mysqli_set_charset($link, 'utf8');
if (!empty($_POST['savebtn']))
{
	$result = "UPDATE orders set `recipient` = '".$_POST['recipient']."', `shipped` = 'yes', `comment` = '".$_POST['comment']."' where `uid` = '".$_SESSION['year']."".$_SESSION['number']."'";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в таблицу "Заказы":'  .mysqli_error($link));
	$result = "UPDATE products set `location` = 'shipped', `owner` = '".$_POST['recipient']."'".$_SESSION['str']."";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в таблицу "Продукты":'  .mysqli_error($link));
	$result = "insert into history (`uid`, `worker`, `type_write`, `whom_order`, `order_from`, `comment`, `date`) values";
	$i = 0;
	while (!empty($_SESSION['orderArr'][$i]))
	{
		$result = $result . " ((select uid from products where `serial` = '".$_SESSION['orderArr'][$i]."'), '".$_SESSION['worker']."', 'shipping', '".$_POST['recipient']."', 'АДС', '".$_POST['comment']."', NOW())";
		$i++;
		if (!empty($_SESSION['orderArr'][$i]))
		$result = $result . ",";
	}
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в таблицу "История":'  .mysqli_error($link));
	else $succ = 1;
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <script src="js/jquery.js"></script>
  <title>Интерфейс испытателя</title>
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
 		<form action="shipment.php" method="post" align="left" id="nextForm"></form>
		<form action="shipment.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Отгрузка</p>
			<div class="serial_lot">
			<div id = "inputLabel"><label>Год</label><input type="text" name="year" maxlength="4" form = "nextForm" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['year'])) echo $_POST['year']; elseif (!empty($_SESSION['year'])) echo $_SESSION['year']; else echo date ( 'Y' ) ; ?>" required/> </div>
			<div id = "inputLabel"><label>Номер заказа</label><input type="text" name="order" maxlength="10" form = "nextForm" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['order'])) echo $_POST['order']; elseif (!empty($_SESSION['number'])) echo $_SESSION['number']; ?>" required/> </div>
			<input type="submit" id="nextbtn" name = "nextbtn" value="Далее"  form = "nextForm"/>
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']) || !empty($_POST['savebtn']))
				{
					if ((!empty($_SESSION['year']) && !empty($_SESSION['number'])) || (!empty($_POST['year']) && !empty($_POST['order'])))
					{
						if (!empty($_POST['order']) && !empty($_POST['year']))
						{
							$_SESSION['year'] = $_POST['year'];
							$_SESSION['number'] = $_POST['order'];
						}
						$result = mysqli_query($link, "select (uid) from orders where `uid` = '".$_SESSION['year']."".$_SESSION['number']."'");
						$row = mysqli_num_rows($result);
						if ($row != 0)
						{
							$result = mysqli_query($link, "select shipped, replace (composition,',','')  from orders where `uid` = '".$_SESSION['year']."".$_SESSION['number']."'");
							$row = mysqli_fetch_row($result);
							if ($row[0] == 'yes')
								$msgShip = 1;
							if (!empty($row[1]))
							{
							$_SESSION['orderArr'] = str_split($row[1], 6);
							$i = 0;
							$str = " where";
							while (!empty($_SESSION['orderArr'][$i]))
							{
								$str = $str . " `serial` = '".$_SESSION['orderArr'][$i]."'";
								$i++;
								if (!empty($_SESSION['orderArr'][$i]))
								$str = $str . " or ";
							}
							$_SESSION['str'] = $str;
							$result = mysqli_query($link, "select type, name, count(type) as duplicates from products $str group by type, name");
							echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
							echo '<caption> Данные о заказе</caption>';
							echo '<tr><td>Тип</td><td>Наименование</td><td>Кол-во</td></tr>';
							while ($row = mysqli_fetch_row($result))
							{
							echo "<tr>";
							echo '<td> '.$row[0].'</td>';
							echo '<td> '.$row[1].'</td>';
							echo '<td> '.$row[2]. ' шт.</td>';
							echo "</tr>";
							}
							echo '</table>';
							if (!empty($succ))
							echo '<p class="msg1">Заказ отгружен</p>';
							else if (!empty($msgShip))
							echo '<p class="msg">Заказ уже отгружен</p>';
							echo '<div id = "inputLabel"<label>Получатель</label><input type="text" name="recipient" maxlength="100" required></input></div>';
							echo '<label>Примечание</label><textarea class="comment" type="text" name="comment" maxlength="1000"></textarea>';
							echo '<input type="submit" id="savedata" name = "savebtn" value="Отгружено"/>';
							}
							else echo '<p class="msg">В состав заказа не входит ни одно изделие</p>';

						}
						else echo '<p class="msg">Данного заказа не существует в базе</p>';
						
					}
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