<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "testing", false);
mysqli_set_charset($link, 'utf8');
$succ = 0;
if (!empty($_POST['savebtn']))
{	
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `status`, `comment`, `protocol`, `date`) values ('".mysqli_real_escape_string($link, $_POST['uid'])."', '".mysqli_real_escape_string($link, $_SESSION['worker'])."', 'testing', '".mysqli_real_escape_string($link, $_POST['status'])."', '".mysqli_real_escape_string($link, $_POST['comment'])."', '".mysqli_real_escape_string($link, $_POST['protocol'])."', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Error recording in table history:'  .mysqli_error($link));	
	$result = "UPDATE products set `testing` = '".mysqli_real_escape_string($link, $_POST['status'])."' where `uid` = '".mysqli_real_escape_string($link, $_POST['uid'])."'";
	if (!(mysqli_query($link, $result)))
	die ('Error recording in table products:'  .mysqli_error($link));
	else $succ = 1;
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
 <?php HtmlHead();?>	 
  <title>Интерфейс испытателя</title>
 </head>
 <body>
 <?php createHeader($link);?>
 <div id="forma">
		<form action="testing.php" method="post" align="left" id="nextForm"></form>
		<form action="testing.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Тестирование</p>
			<div class="serial_lot">
			<div class = "inputLabel"><label>Серийный номер</label><input type="text" form = "nextForm" name="serial" maxlength="10" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial']) && empty($_POST['savebtn']) ) echo $_POST['serial']; ?>" required/> </div>
			<input type="submit" id="nextbtn" form = "nextForm" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{						
						$result = mysqli_query($link, "select * from products where serial = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
						$row = mysqli_fetch_assoc($result);						
						mysqli_data_seek($result,0); // Reset pointer to the beginning of the result set 						
						if (!empty($row))
						{																					
							//Products table
							echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
							echo '<caption> Данные изделия</caption>';
							$mass = array('Дата', 'Тип', 'Имя', '№','Владелец', 'Местоположение', 'Тестирование', 'ОТК', 'Комментарий');
							$columnName = array ( "date", "type", "name", "serial","owner", "location", "testing", "otk", "comment");
							echo '<tr>';
							for ($i = 0; (!empty($mass[$i])); $i++)
							{
							echo '<td><i>'.$mass[$i].'</i></td>';
							}
							echo '</tr>';
							echo "<tr>";
							paintRow($link, $result, $columnName, false, "testing");
							echo "</tr>";
							echo '</table>';
							
							//History table 
							$result = mysqli_query($link, "select * from history where UID = '".$row['UID']."' AND type_write IN ('testing','otk','repair','mismatch')");
							if  (mysqli_num_rows($result)>0){
							  echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
							  echo '<caption> История</caption>';
							  $mass = array('Дата', 'Кто', 'Тип', 'Статус', 'Комментарий','Протокол');
							  $columnName = array ("date","worker","type_write", "status", "comment", "protocol");
							  echo '<tr>';
							  for ($i = 0; (!empty($mass[$i])); $i++){
							    echo '<td><i>'.$mass[$i].'</i></td>';
							  }
							  echo '</tr>';
							  echo "<tr>";
							  paintRow($link, $result ,$columnName, false, "testing");
							  echo "</tr>";
							  echo '</table>';
							}
							echo '<input type = "hidden" name = "uid" value = "'.htmlspecialchars($row['UID']).'">';
							echo '<div id = "downContentTesting">';
							echo '<select class="select" name="status" required>';
							echo '<option value = "">Выберите статус тестирования</option>';
							echo '<option value="ok">Тестирование прошло успешно</option>';
							echo '<option value="fail">Выявлены ошибки</option>';
							echo '</select>';
							echo '<label>Протокол</label><input type = text name="protocol" maxlength="100"';
							echo 'value = "'.$row['serial'].'"';
							echo ' />';							
							echo '<label style = "margin-top: 1em" >Комментарий</label><textarea class="comment" type="text" name="comment" maxlength="1000"></textarea>';
							echo '<input type="submit" id="savedata" name="savebtn" value="Сохранить данные"/>';
							echo '</div>';
							
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