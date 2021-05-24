<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "repair");
clearSESSION1('repairSES', array("serial", "uid", "repair", "diagnostic"));
clearSESpage();
mysqli_set_charset($link, 'utf8');
$succ = 0;
$access = 0;
if (!empty($_POST['acceptbtn']))
{
	$result = "UPDATE products set `repair` = '".$_SESSION['user']."', `location` = 'repair' where `uid` = '".$_SESSION['uid']."'";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `comment`, `date`) values ('".$_SESSION['uid']."', '".$_SESSION['worker']."', 'repair', 'Изделие принято в ремонт', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	$acceptBtn = 1;
}
if (!empty($_POST['diagBtn']))
{
	$_SESSION['diagnostic'] = $_POST['diComment'];
	$_POST['diComment'] = mysqli_real_escape_string($link, $_POST["diComment"]);
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `comment`, `date`) values ('".$_SESSION['uid']."', '".$_SESSION['worker']."', 'mismatch', '".$_POST['diComment']."', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	$result = "UPDATE products set `mismatch` = 'yes' where `uid` = '".$_SESSION['uid']."'";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
}
if (!empty($_POST['repairBtn']))
{
	$_SESSION['repair'] = $_POST['reComment'];
	$_POST['reComment'] = mysqli_real_escape_string($link, $_POST["reComment"]);
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `comment`, `date`) values ('".$_SESSION['uid']."', '".$_SESSION['worker']."', 'repair', '".$_POST['reComment']."', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
}
if (!empty($_POST['endRepair']))
{
	if (!empty($_POST['status']))
	{
		$result = "UPDATE products set `repair` = 'no', `location` = 'stock' where `uid` = '".$_SESSION['uid']."'";
		if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
		if ($_POST['status'] == 'ok')
		{
			$result = "UPDATE products set `mismatch` = 'no' where `uid` = '".$_SESSION['uid']."'";
			if (!(mysqli_query($link, $result)))
			die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
		}
	}
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `status`, `date`) values ('".$_SESSION['uid']."', '".$_SESSION['worker']."', 'repair', '".$_POST['status']."', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	unset ($_SESSION['uid']);
	unset ($_SESSION['serial']);
	unset ($_SESSION['diagnostic']);
	unset ($_SESSION['repair']);
}
if (!empty($_POST['nextbtn']))
	{
		unset ($_SESSION['uid']);
		unset ($_SESSION['serial']);
		unset ($_SESSION['diagnostic']);
		unset ($_SESSION['repair']);
	}
if (!empty($_POST['serial']))
	$_SESSION['serial'] = mysqli_real_escape_string($link, $_POST["serial"]);
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <script src="js/jquery.js"></script>
  <title>Ремонт</title>
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
 		<form action="repair.php" method="post" align="left" id="diagForm"></form>
		<form action="repair.php" method="post" align="left" id="repairForm"></form>
		<form action="repair.php" method="post" align="left" id="endRepairForm"></form>
		<form action="repair.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Ремонт</p>
			<div class="serial_lot">
			<div id = "inputLabel"><label>Серийный номер</label><input type="text" name="serial" maxlength="10" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_SESSION['serial']) && empty($_POST['savebtn']) ) echo $_SESSION['serial']; elseif (empty($_SESSION['serial']) && empty($_POST['savebtn']) && !empty($_POST['serial'])) echo $_POST['serial']; ?>" required/> </div>
			<input type="submit" id="nextbtn" name = "nextbtn"  value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']) || !empty($_POST['acceptbtn']) || !empty($_POST['diagBtn']) || !empty($_POST['repairBtn']))
				{
					$result = mysqli_query($link, "select `uid`, `type`, `name`, `repair` from products where serial = '".$_SESSION['serial']."'");
					$row = mysqli_fetch_array($result);
					if (!empty($row))
					{
						$_SESSION['uid'] = $row['uid'];
						//Рисую таблицу с информацией о типе, имени, ОТК
						echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
						echo '<caption> Данные изделия</caption>';
						echo '<tr><td>Тип</td><td>Наименование</td><td>Ответственный за ремонт</td></tr>';
						echo "<tr>";
						echo '<td>'.$row['type'].'</td>';
						echo '<td>'.$row['name'].'</td>';
						echo '<td>';
						if ($row['repair'] != 'no')
						{
							$result = mysqli_query($link, "select `worker` from users where user = '".$row['repair']."'");
							$usr = mysqli_fetch_row($result);
							echo $usr[0];
						}
						else 
						echo 'Отсутствует';
						echo '</td>';
						echo "</tr>";
						echo '</table>';
						$access = $row['repair'];
						$result = mysqli_query($link, "select `uid`, `worker`, `type_write`, `date`, `status`, `comment` from history where (uid = '".$row['uid']."') and (`type_write` = 'otk' or `type_write` = 'testing' or `type_write` = 'mismatch' or `type_write` = 'record' or `type_write` = 'repair') order by `date` asc ");
						$num = mysqli_num_rows($result);
						if (!empty($num))
						{
							//Рисую таблицу с историей
							echo '<table class="tableOtk" align="center" style = "margin: 0;">';
							echo '<caption> История ОТК</caption>';
							echo '<tr><td>UID</td><td>Сотрудник</td><td>Тип записи</td><td>Дата</td><td>Статус</td><td class = "comment">Комментарий</td></tr>';
							while ($row = mysqli_fetch_array($result))
							{
								echo "<tr>";
								echo '<td> '.$row['uid'].'</td>';
								echo '<td> '.$row['worker'].'</td>';
								echo '<td> '.$row['type_write'].'</td>';
								echo '<td> '.$row['date'].'</td>';
								echo '<td> '.$row['status'].'</td>';
								echo '<td> '.$row['comment'].'</td>';
								echo "</tr>";
								$num--;
							}
							echo '</table>';
						}
						if ($access == 'no')
						echo '<input type="submit" class = "buttons" id="acceptbtn" name = "acceptbtn" value="Принять в ремонт"/>';
						else if ($access != $_SESSION['user'])
						echo "<p class=\"msg\">Изделие уже в ремонте</p>";

						if ((!empty($_POST['acceptbtn']) || !empty($_POST['diagBtn']) || !empty($_POST['repairBtn'])) || $access == $_SESSION['user'])
						{
							
							echo '<div id = "downContentrepair">';
							echo '<label style = "margin-top: 1em" >Дагностика</label><textarea class="comment" type="text" name="diComment" maxlength="1000" form = "diagForm" required>'; if (!empty($_SESSION['diagnostic'])) echo htmlspecialchars($_SESSION['diagnostic']); echo '</textarea>';
							echo '<input type="submit" class = "buttons" id="savedata" name = "diagBtn" form = "diagForm" value="Записать диагностику"/>';
							if (!empty($_SESSION['diagnostic']))
							echo "<p class=\"msg2\">Данные сохранены</p>";	
							echo '</div>';
							echo '<div id = "downContentrepair">';
							echo '<label style = "margin-top: 1em" >Ремонт</label><textarea class="comment" type="text" name="reComment" maxlength="1000" form = "repairForm" required>'; if (!empty($_SESSION['repair'])) echo htmlspecialchars($_SESSION['repair']); echo '</textarea>';
							echo '<input type="submit" class = "buttons" id="savedata" name = "repairBtn" form = "repairForm" value="Записать ремонт"/>';
							if (!empty($_SESSION['repair']))
							echo "<p class=\"msg2\">Данные сохранены</p>";
							echo '</div>';
							echo '<div id = "downContentrepair">';
							echo '<select class="select" name="status" form = "endRepairForm" required>';
							echo '<option value = "">Выберите статус ремонта</option>';
							echo '<option value="ok">Отремонтировано</option>';
							echo '<option value="fail">Не отремонтировано</option>';
							echo '</select>';
							echo '<input type="submit" class = "buttons" id="acceptbtn" name = "endRepair" form = "endRepairForm" value="Закончить ремонт"/>';
							echo '</div>';
						}
					}
					else echo "<p class=\"msg\">Данного изделия не существует в базе</p>";
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
 </body>
</html>