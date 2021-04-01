<?php
session_start();

if ((!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT']) || ($_SESSION['root'] !== "otk"))
{
	header('Location: err403.php');
}
require_once 'connect.php';
mysqli_set_charset($link, 'utf8');
$error_s1 = 0;
$error_s2 = 0;
$error_s3 = 0;
$error_s4 = 0;
$error_n1 = 1;
$succ = 0;
$result = mysqli_query($link, "SELECT worker FROM users WHERE user = '".$_SESSION['user']."'");
$worker = mysqli_fetch_row($result);
$worker = $worker[0];
if ((!empty($_POST['token']) && !empty($_SESSION['lastToken'])) && ($_POST['token'] == $_SESSION['lastToken']))
{
    header('Location: otk.php');
}

if (!empty($_POST['savebtn']))
{
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `comment`, `otk_status`, `date`) values ('".$_SESSION['uid']."', '$worker', 'ОТК', '".$_POST['comment']."', '".$_POST['otkstatus']."', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	$result = "UPDATE products set `otk` = '".$_POST['otkstatus']."' where `uid` = '".$_SESSION['uid']."'";
	if (!(mysqli_query($link, $result)))
	die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	else $succ = 1;
	$_SESSION['lastToken'] = $_POST['token'];
	
}  
		
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="asset/css/main1.css"<?php echo(microtime(true).rand()); ?>>
  <script src="jquery.js"></script>
  <title>Интерфейс приемщика</title>
 </head>
 <body>
 <div class="header">
	<div class="dropdown">
	<button class="dropbtn" align="center"><img id = "menu" src = "menu.png"></button>
		<div class="dropdown-content">
		<a href="main.php">Главная</a>
		<a href="accept.php">Приемка</a>
		<a href="exit.php">Выход<img id="exit" src="exit.png"></a>
		</div>
	</div>
	<img id="adc" src="adc.png">
	<div id="worker">
	<p><img id="exit" src="worker.png"><?php echo $worker; ?></p>
	</div>
</div>
 <div id="forma">
		<form action="otk.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">ОТК</p>
			<div class="serial_lot">
			<div id = "inputLabel"><label>Серийный номер</label><input type="text" name="serial" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial']) && empty($_POST['savebtn']) ) echo $_POST['serial']; ?>" required/> </div>
			<input type="submit" id="nextbtn" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{
						$result = mysqli_query($link, "select `uid`, `type`, `name`, `otk` from products where serial = '".$_POST['serial']."'");
						$row = mysqli_fetch_array($result);
						if (!empty($row))
						{
							$_SESSION['uid'] = $row['uid'];
							//Рисую таблицу с информацией о типе, имени, ОТК
							echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
							echo '<caption> Данные изделия</caption>';
							echo '<tr><td>Тип</td><td>Наименование</td><td>Статус</td></tr>';
							echo "<tr>";
							echo '<td> '.$row['type'].'</td>';
							echo '<td> '.$row['name'].'</td>';
							echo '<td> '.$row['otk'].'</td>';
							echo "</tr>";
							echo '</table>';
							$result = mysqli_query($link, "select `uid`, `worker`, `date`, `otk_status`, `comment` from history where (uid = '".$row['uid']."') and (`type_write` = 'ОТК')");
							$num = mysqli_num_rows($result);
							if (!empty($num))
							{
								//Рисую таблицу с историей ОТК
								echo '<table class="tableOtk" align="center" style = "margin: 0;">';
								echo '<caption> История ОТК</caption>';
								echo '<tr><td>UID</td><td>Сотрудник</td><td>date</td><td>Статус</td><td class = "comment">Комментарий</td></tr>';
								while ($num > 0)
								{
									$row = mysqli_fetch_array($result);
									echo "<tr>";
									echo '<td> '.$row['uid'].'</td>';
									echo '<td> '.$row['worker'].'</td>';
									echo '<td> '.$row['date'].'</td>';
									echo '<td> '.$row['otk_status'].'</td>';
									echo '<td> '.$row['comment'].'</td>';
									echo "</tr>";
									$num--;
								}
								echo '</table>';
							}
							echo '<select class="select" name="otkstatus">';
							echo '<option disabled>Выберите статус ОТК</option>';
							echo '<option value="Проверка прошла успешно">Проверка прошла успешно</option>';
							echo '<option value="Изделие не прошло проверку">Изделие не прошло проверку</option>';
							echo '</select>';
							echo '<label style = "margin-top: 1em" >Комментарий</label><textarea class="comment" type="text" name="comment" onfocus="this.value=\'\'"></textarea>';
							echo '<input type="submit" id="savedata" name = "savebtn" value="Сохранить данные"/>';
							echo '<input type="hidden" name="token" value="';
							echo (rand(10000,99999));
							echo '" />';
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
	</script>
	<script>
				$('.select').each(function() {
			const _this = $(this),
				selectOption = _this.find('option'),
				selectOptionLength = selectOption.length,
				selectedOption = selectOption.filter(':selected'),
				duration = 450; // длительность анимации 

			_this.hide();
			_this.wrap('<div class="select"></div>');
			$('<div>', {
				class: 'new-select',
				text: _this.children('option:disabled').text()
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