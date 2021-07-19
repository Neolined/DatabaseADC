<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "otk");
mysqli_set_charset($link, 'utf8');
$succ = 0;
if (!empty($_POST['savebtn']))
{
	if (!empty($_POST['order']))
	{
		$_POST['order'] = mysqli_real_escape_string($link, $_POST['order']);
		if (preg_match('[^\d{7}$]', $_POST['order']))
		{
			$result = mysqli_query($link, "select uid from orders where UID = '".$_POST['order']."'");
			$row = mysqli_num_rows($result);
			if ($row == 0)
			{
				$result = "INSERT INTO orders (uid, composition) VALUES ('".$_POST['order']."','".$_POST['serialHide'].",') ON DUPLICATE KEY UPDATE composition=CONCAT(composition,' ".$_POST['serialHide'].",')";
				if (!(mysqli_query($link, $result)))
					die ('Ошибка записи в ТБ заказы:'  .mysqli_error($link));
				$msgOrder = 1;
				$msgCmpsn1 = 1;
			}
			else
			{
				$result = mysqli_query($link, "select composition from orders where UID = '".$_POST['order']."'");
				$row = mysqli_fetch_row($result);
				if (!(strpos($row[0], $_POST['serialHide']) !==false))
				{
					$result = "update orders set composition = concat (composition, ' ".$_POST['serialHide'].",') where uid = '".$_POST['order']."'";
					if (!(mysqli_query($link, $result)))
						die ('Ошибка записи в ТБ заказы:'  .mysqli_error($link));
					$msgCmpsn1 = 1;
				}
				else $msgCmpsn = 1;
			}
		}
		else $msgOrder2 = 1;
	}
	if (empty($msgOrder2))
	{
		$_POST['status'] = mysqli_real_escape_string($link, $_POST['status']);
		$_POST['comment'] = mysqli_real_escape_string($link, $_POST['comment']);
		$result = "INSERT into history (`uid`, `worker`, `type_write`, `comment`, `status`, `number_order`, `date`) values ('".$_POST['uidHide']."', '".$_SESSION['worker']."', 'otk', '".$_POST['comment']."', '".$_POST['status']."', '".$_POST['order']."', NOW())";
		if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в историю:'  .mysqli_error($link));
		if ($_POST['status'] == 'fail')
			$result = "UPDATE products set `otk` = '".$_POST['status']."', `mismatch` = 'yes' where `uid` = '".$_POST['uidHide']."'";
		else
			if ($_POST['status'] == 'ok')
				$result = "UPDATE products set `otk` = '".$_POST['status']."', `mismatch` = 'no' where `uid` = '".$_POST['uidHide']."'";
			else			
				$result = "UPDATE products set `otk` = '".$_POST['status']."' where `uid` = '".$_POST['uidHide']."'";
		if (!(mysqli_query($link, $result)))
			die ('Ошибка записи в продукты:'  .mysqli_error($link));
		else $succ = 1;
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
  <title>ОТК</title>
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
 		<form action="otk.php" method="post" align="left" id="nextForm"></form>
		<form action="otk.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">ОТК</p>
			<div class="serial_lot">
			<div id = "inputLabel"><label>Серийный номер</label><input type="text" name="serial"  maxlength="10" form = "nextForm" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial'])) echo $_POST['serial']; elseif (!empty($_POST['serialHide'])) echo $_POST['serialHide']; ?>" required/> </div>
			<input type="submit" id="nextbtn" name = "nextbtn" form = "nextForm" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']) || !empty($_POST['savebtn']))
				{
					if (!empty($_POST['serial']) || !empty($_POST['serialHide']))
					{
						if (!empty($_POST['serial']))
						  $result = mysqli_query($link, "select `uid`, `type`, `name`, `otk` from products where serial = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
						if (!empty($_POST['serialHide']))
						$result = mysqli_query($link, "select `uid`, `type`, `name`, `otk` from products where serial = '".mysqli_real_escape_string($link, $_POST['serialHide'])."'");
						$row = mysqli_fetch_array($result);
						if (!empty($row))
						{
							echo '<div id = "inp"> <label>UID заказа</label><input type="text" id ="order" name="order"></input></div>';
							if (!empty($msgOrder))
							echo "<p class=\"msg1\">Новый заказ создан</p>";
							if (!empty($msgOrder2))
							echo "<p class=\"msg\">Некорректно введен номер заказа</p>";
							if (!empty($msgCmpsn1))
							echo "<p class=\"msg1\">Изделие добавлено в состав заказа</p>";
							if (!empty($msgCmpsn))
							echo "<p class=\"msg\">Данное изделие уже есть составе заказа</p>";
              echo '<input type="hidden" name="uidHide" value = "'.$row['uid'].'">';
							//Рисую таблицу с информацией о типе, имени, ОТК
							echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
							echo '<caption> Данные изделия</caption>';
							echo '<tr><td>Тип</td><td>Наименование</td><td>Статус</td></tr>';
							if (!empty($_POST['serial']))
						  $result = mysqli_query($link, "select `uid`, `type`, `name`, `otk` from products where serial = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
              if (!empty($_POST['serialHide']))
              $result = mysqli_query($link, "select `uid`, `type`, `name`, `otk` from products where serial = '".mysqli_real_escape_string($link, $_POST['serialHide'])."'");
							$columnName = array("type", "name", "otk");
							paintRow($result, $columnName, false, false);
							echo '</table>';
							$result = mysqli_query($link, "select `uid`, `worker`, `date`, `status`, `comment` from history where (uid = '".$row['uid']."') and (`type_write` = 'otk')");
							$num = mysqli_num_rows($result);
							if (!empty($num))
							{
								//Рисую таблицу с историей ОТК
								echo '<table class="tableOtk" align="center" style = "margin: 0;">';
								echo '<caption> История ОТК</caption>';
								echo '<tr><td>UID</td><td>Сотрудник</td><td>Дата</td><td>Статус</td><td class = "comment">Комментарий</td></tr>';
								$columnName = array("uid", "worker", "date", "status", "comment");
								paintRow($result, $columnName, false, false);
								
								echo '</table>';
							}
							echo '<select class="select" name="status" required>';
							echo '<option value = "">Выберите статус ОТК</option>';
							echo '<option value="ok">Проверка прошла успешно</option>';
							echo '<option value="fail">Изделие не прошло проверку</option>';
							echo '</select>';
							echo '<label style = "margin-top: 1em" >Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000"></textarea>';
							echo '<input type="submit" id="savedata" name = "savebtn" value="Сохранить данные"/>';
							echo '<input type="hidden" name="serialHide" value ="';
              if (!empty($_POST['serial'])) echo htmlspecialchars($_POST['serial']);
              else echo htmlspecialchars($_POST['serialHide']);
              echo '">';
						}
						else echo "<p class=\"msg\">Данного изделия не существует в базе</p>";
						
					}
				}
				if ($succ == 1)
				{
					echo "<p class=\"msg1\">Данные сохранены</p>";	
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
	<script>
	$(document).ready(function(){
	$("#order").autocompleteArray(
	<?php
	$result = mysqli_query($link, "select `uid` from `orders` where `uid` != ''");
	$order = mysqli_fetch_all($result);
	echo json_encode($order); 
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
	</script>
 </body>
</html>