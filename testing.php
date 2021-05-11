<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
//checkRoot($link, "testing");
clearSESSION1('testing', array("uid"));
clearSESpage();
mysqli_set_charset($link, 'utf8');
$succ = 0;
if (!empty($_POST['savebtn']))
{
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `status`, `comment`, `protocol`, `date`) values ('".$_SESSION['uid']."', '".$_SESSION['worker']."', 'testing', '".$_POST['status']."', '".$_POST['comment']."', '".$_POST['protocol']."', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Error recording in table history:'  .mysqli_error($link));
	$result = "UPDATE products set `testing` = '".$_POST['status']."' where `uid` = '".$_SESSION['uid']."'";
	if (!(mysqli_query($link, $result)))
	die ('Error recording in table products:'  .mysqli_error($link));
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
		<form action="testing.php" method="post" align="left" id="nextForm"></form>
		<form action="testing.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Тестирование</p>
			<div class="serial_lot">
			<div id = "inputLabel"><label>Серийный номер</label><input type="text" form = "nextForm" name="serial" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial']) && empty($_POST['savebtn']) ) echo $_POST['serial']; ?>" required/> </div>
			<input type="submit" id="nextbtn" form = "nextForm" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{
						$result = mysqli_query($link, "select * from products where serial = '".$_POST['serial']."'");
						$row = mysqli_fetch_row($result);
						if (!empty($row))
						{
							$result = mysqli_query($link, "select * from products where serial = '".$_POST['serial']."'");
							//Рисую таблицу с информацией о типе, имени, ОТК
							echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
							echo '<caption> Данные изделия</caption>';
							$mass = array('UID', 'Тип', 'Имя', 'Исполнение', 'Серийный номер', 'Вхождение', 'Дата', 'Владелец', 'ПО', 'Местоположение', 'Тестирование', 'ОТК', 'Комментарий');
							$columnName = array ( "UID", "type", "name", "perfomance", "serial", "enter", "date", "owner", "software", "location", "testing", "otk", "comment");
							$replace = array ("ok" => "Прошло успешно", "fail" => "Не успешно",
							"notest" => "Не проверялось", "record" => "Запись", "otk" => "ОТК", "mismatch" => "Несоответствия", "testing" => "Испытания",
							"shipment" => "Отгрузка", "repair" => "Ремонт", "worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
							"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус",
							"comment" => "Комментарий", "UID" => "№ ", "type" => "Тип", "name" => "Наименование", "perfomance" => "Исполнение", "serial" => "Серийный номер",
							"enter" => "Вхождение", "owner" => "Владелец", "software" => "Программное обеспечение", "location" => "Местоположение", "repair_possition" => "Позиция в ремонте", "repair_element" => "Ремонтируемый элемент", "protocol" => "Протокол");
							echo '<tr>';
							for ($i = 0; (!empty($mass[$i])); $i++)
							{
							echo '<td>'.$mass[$i].'</td>';
							}
							echo '</tr>';
							echo "<tr>";
							paintRow($result ,$columnName, $replace, false);
							echo "</tr>";
							echo '</table>';
							$_SESSION['uid'] = $row[0];
							echo '<div id = "downContentTesting">';
							echo '<select class="select" name="status" required>';
							echo '<option value = "">Выберите статус тестирования</option>';
							echo '<option value="ok">Тестирование прошло успешно</option>';
							echo '<option value="fail">Выявлены ошибки</option>';
							echo '</select>';
							echo '<label>Протокол</label><input type = text name="protocol"></input>';
							echo '<label style = "margin-top: 1em" >Комментарий</label><textarea class="comment" type="text" name="comment"></textarea>';
							echo '<input type="submit" id="savedata" name="savebtn" value="Сохранить данные"/>';
							echo '</div>';
							
						}
						else echo "<p class=\"msg\">Данного изделия не существует в базе</p>";
						
					}
				}
				if ($succ == 1)
				{
					echo "<p class=\"msg1\">Данные успешно занесены в БД</p>";	
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