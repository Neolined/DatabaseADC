<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "mismatch", false);
mysqli_set_charset($link, 'utf8');
$succ = 0;

if (!empty($_POST['savebtn']))
{
	$result = "INSERT into history (`uid`, `worker`, `type_write`, `comment`, `date`) values ((select uid from products where `serial` = '".mysqli_real_escape_string($link, $_POST['serial'])."'), '".mysqli_real_escape_string($link,$_SESSION['worker'])."', 'mismatch', '".mysqli_real_escape_string($link, $_POST['comment'])."', NOW())";
	if (!(mysqli_query($link, $result)))
	die ('Error recording in table history:'  .mysqli_error($link));
	$result = "UPDATE products set `mismatch` = 'yes' where `serial` = '".mysqli_real_escape_string($link, $_POST['serial'])."'";
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
  <title>Несоответствия</title>
 </head>
 <body>
 <?php createHeader($link);?>
 <div id="forma">
		<form action="mismatch.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Несоответствия</p>
			<div class="serial_lot">
			<div class = "inputLabel"><label>Серийный номер</label><input type="text" name="serial"  maxlength="10" <?php if (!empty($_POST['savebtn'])) echo 'onclick = "hideotk()"';?> oninput="hideotk()" value = "<?php if (!empty($_POST['serial']) && empty($_POST['savebtn']) ) echo htmlspecialchars($_POST['serial']); ?>" required/> </div>
			<input type="submit" id="nextbtn" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{
						$result = mysqli_query($link, "select name, type from products where serial = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
						$row = mysqli_fetch_row($result);
						if (!empty($row))
						{
							echo '<p id = "infoBoard">'.htmlspecialchars($row[0]).' '.htmlspecialchars($row[1]).'</p>';
							$result = mysqli_query($link, "select `date`, `type_write`, `status`, `comment` from `history` where (`type_write` = 'otk' or `type_write` = 'mismatch' or `type_write` = 'repair') and uid = (select `uid` from products where serial = '".mysqli_real_escape_string($link, $_POST['serial'])."') order by date desc");
							if (mysqli_num_rows($result) > 0)
							{
							$row = mysqli_fetch_row($result);
							$defineRu = array("otk" => "ОТК", "mismatch" => "Несоответсвия", "repair" => "Ремонт", "ok" => "Успешно", "fail" => "Не успешно");
							echo '<table class="tableMismatch">';
							echo '<tr><td>Дата</td><td>Тип записи</td><td>Статус</td><td>Комментарий</td></tr>';
							while ($row = mysqli_fetch_row($result))
							{
								echo '<tr><td>'.htmlspecialchars($row[0]).'</td><td>';
							if (isset($defineRu[$row[1]]))
								echo htmlspecialchars($defineRu[$row[1]]);
							else 
								echo htmlspecialchars($row[1]);
							echo '</td><td>';
							if (isset($defineRu[$row[2]]))
								echo htmlspecialchars($defineRu[$row[2]]);
							else 
								echo htmlspecialchars($row[2]);
							echo '</td><td>';
								echo htmlspecialchars($row[3]).'</td></tr>';
							}
							echo '</table>';
							}
							echo '<label style = "margin-top: 1em" >Комментарий</label><textarea class="comment" type="text" name="comment"  maxlength="1000"></textarea>';
							echo '<input type="submit" id="savedata" name = "savebtn" value="Сохранить данные"/>';
						}
						else echo "<p class=\"msg\">Данного изделия не существует в базе</p>";
						
					}
				}
				if ($succ == 1)
					echo "<p class=\"msg1\">Несоответствие создано</p>";
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