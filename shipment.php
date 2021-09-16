<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "shipment", false);
mysqli_set_charset($link, 'utf8');
if (!empty($_POST['savebtn']))
{
	$result = "UPDATE orders set `recipient` = '".mysqli_real_escape_string($link, $_POST['recipient'])."', `shipped` = 'yes', `comment` = '".mysqli_real_escape_string($link, $_POST['comment'])."' where `uid` = '".mysqli_real_escape_string($link, $_POST['yearHide'])."".mysqli_real_escape_string($link, $_POST['orderHide'])."'";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в таблицу "Заказы":'  .mysqli_error($link));
	$result = "UPDATE products set `location` = 'shipped', `owner` = '".mysqli_real_escape_string($link, $_POST['recipient'])."'".mysqli_real_escape_string($link, $_POST['str'])."";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в таблицу "Продукты":'  .mysqli_error($link));
	$result = "insert into history (`uid`, `worker`, `type_write`, `whom_order`, `order_from`, `comment`, `date`) values";
	$i = 0;
	while (!empty($_POST['orderArrHide'][$i]))
	{
		$result = $result . " ((select uid from products where `serial` = '".$_POST['orderArrHide'][$i]."'), '".mysqli_real_escape_string($link, $_SESSION['worker'])."', 'shipping', '".mysqli_real_escape_string($link, $_POST['recipient'])."', 'АДС', '".mysqli_real_escape_string($link, $_POST['comment'])."', NOW())";
		$i++;
		if (!empty($_POST['orderArrHide'][$i]))
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
  <title>Отгрузка</title>
 </head>
 <body>
 <?php createHeader($link);?>
 <div id="forma">
 		<form action="shipment.php" method="post" align="left" id="nextForm"></form>
		<form action="shipment.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Отгрузка</p>
			<div class="serial_lot">
			<div class = "inputLabel"><label>Год</label><input type="text" name="year" maxlength="4" form = "nextForm" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['year'])) echo $_POST['year']; elseif (!empty($_POST['yearHide'])) echo $_POST['yearHide']; else echo date ( 'Y' ) ; ?>" required/> </div>
			<div class = "inputLabel"><label>Номер заказа</label><input type="text" name="order" maxlength="10" form = "nextForm" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['order'])) echo $_POST['order']; elseif (!empty($_POST['orderHide'])) echo $_POST['orderHide']; ?>" required/> </div>
			<input type="submit" id="nextbtn" name = "nextbtn" value="Далее"  form = "nextForm"/>
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']) || !empty($_POST['savebtn']))
				{
					if ((!empty($_POST['yearHide']) && !empty($_POST['orderHide'])) || (!empty($_POST['year']) && !empty($_POST['order'])))
					{
						if (!empty($_POST['order']) && !empty($_POST['year']))
						{
							echo '<input type = "hidden" name = "orderHide" value = "'.htmlspecialchars($_POST['order']).'">';
							echo '<input type = "hidden" name = "yearHide" value = "'.htmlspecialchars($_POST['year']).'">';
						}
						else if (!empty($_POST['yearHide']) && !empty($_POST['orderHide']))
						{
							$_POST['year'] = $_POST['yearHide'];
							$_POST['order'] = $_POST['orderHide'];
						}
						$result = mysqli_query($link, "select (uid) from orders where `uid` = '".mysqli_real_escape_string($link, $_POST['year'])."".mysqli_real_escape_string($link, $_POST['order'])."'");
						$row = mysqli_num_rows($result);
						if ($row != 0)
						{
							$result = mysqli_query($link, "select shipped, replace (composition,' ','')  from orders where `uid` = '".mysqli_real_escape_string($link, $_POST['year'])."".mysqli_real_escape_string($link, $_POST['order'])."'");
							$row = mysqli_fetch_row($result);
							if ($row[0] == 'yes')
								$msgShip = 1;
							if (!empty($row[1]))
							{
								$orderArr = explode(',', $row[1]);
								$i = 0;
								while($orderArr[$i])
								{
									echo '<input type = "hidden" name = "orderArrHide['.$i.']" value = "'.htmlspecialchars($orderArr[$i]).'">';
									$i++;
								}
								$i = 0;
								$str = " where";
								while (!empty($orderArr[$i]))
								{
									$str = $str . " `serial` = '".$orderArr[$i]."'";
									$i++;
									if (!empty($orderArr[$i]))
									$str = $str . " or ";
								}
								echo '<input type = "hidden" name = "str" value = "'.htmlspecialchars($str).'">';
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
								echo '<div class = "inputLabel"<label>Получатель</label><input type="text" name="recipient" maxlength="100" required></input></div>';
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
	<?php createFooter();?>
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