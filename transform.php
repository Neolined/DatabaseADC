<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, "transform", false);
mysqli_set_charset($link, 'utf8');
$succ = 0;
$error_n1 = 1;
$error_t1 = 1;
if (!empty($_POST['savebtY']))
{
	$result = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `comment`) VALUES ((select uid from products where `serial` = '".mysqli_real_escape_string($link, $_POST['serialHide'])."'), NOW(), '".mysqli_real_escape_string($link,$_SESSION['worker'])."', 'transform', '".mysqli_real_escape_string($link, $_POST['comment'])."')";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
	$result = "UPDATE `products` SET `type` = '".mysqli_real_escape_string($link, $_POST['type'])."', `name` = '".mysqli_real_escape_string($link, $_POST['name'])."' where `serial` = '".mysqli_real_escape_string($link, $_POST['serialHide'])."'";
	if (!(mysqli_query($link, $result)))
		die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
		$succ = 1;
}
?>
<!DOCTYPE html>
<html>
 <head>
 <?php HtmlHead();?>	 
  <title>Трансформация</title>
 </head>
 <body>
 <div class="header">
 <?php createHeader($link);?>
 <div id="forma">
 		<form action="transform.php" method="post" align="left" id="nextForm"></form>
		<form action="transform.php" method="post" align="left" class="form1">
			<p id="priem_name" align="center">Трансформация</p>
			<div class="serial_lot">
			<div class = "inputLabel"><label>Серийный номер</label><input type="text" form = "nextForm" name="serial"  maxlength="10" oninput="hideotk()" value = "<?php if (!empty($_POST['serial'])) echo $_POST['serial']; else if ($error_t1 == 0 || $error_n1 == 0) echo htmlspecialchars($_POST['serial']); ?>" required/> </div>
			<input type="submit" id="nextbtn" form = "nextForm" name = "nextbtn" value="Далее" />
			</div>
			<div id = "contentOtk">
				<?php
				echo '<input type = "hidden" name =  "serialHide" value = "';//реализация сохранения данных в переменную пост
				if (!empty($_POST['serial']))
					echo htmlspecialchars($_POST['serial']);
				echo '">';
				$arrInput = array("order_from", "type", "name", "comment", "location");
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


				//---------------------------------------
				if (!empty($_POST['nextbtn']))
				{
					if (!empty($_POST['serial']))
					{
						if (preg_match('/^[A-Z]\d{5}$/', $_POST['serial']) || preg_match('/^[Р]\d{6}$/u', $_POST['serial']) || preg_match('/^[А-Я]\d{4}$/u', $_POST['serial']) || preg_match('/^\d{5}$/', $_POST['serial']))
						{
							$result = mysqli_query($link, "select type, name from products where serial = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
							$row = mysqli_fetch_row($result);
							if (!empty($row))
							{
							echo '<div id = "inp"><input type="reset" id = "clearForm" value="Очистить данные формы"/></div>';
							echo '<table class="tableOtk" align="center" style = "margin: 1em 0;">';
							echo '<caption> Данные изделия</caption>';
							echo '<tr><td>Тип</td><td>Наименование</td></tr>';
							echo "<tr>";
							echo '<td>'.$row[0].'</td>';
							echo '<td>'.$row[1].'</td>';
							echo '</tr>';
							echo '</table>';
							echo '<div id = "inp"> <label>Новое наименование</label><input id = "name" type="text" name="name" maxlength="100" required></input></div>';	
							echo '<div id = "inp"> <label>Новый тип</label><input id = "type" type="text" name="type" maxlength="100" required readonly></input></div>';
							echo '<div id = "inp"><label>Комментарий</label><textarea class="comment" id = "comment" type="text" name="comment" maxlength="1000"></textarea></div>';
							echo '<input type="submit" id="savedata" name = "savebtY" value="Сохранить данные"/>';
							}
						}
						else echo "<p class=\"msg\">Некорректно введен серийный номер</p>";
					}
				}
				if ($succ == 1)
					echo "<p class=\"msg1\">Данные сохранены</p>";	
				else if ($succ == 2)
					echo "<p class=\"msg1\">Ошибка</p>";
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
		//$("#nextbtn").on(click, function(){
			//$("#name").val()= '';
			//$("#comment").val() = '';
			//$("#type").val() = '';
		//}))
	</script>
	<script>
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

	$("#name").on('change', function (index, value){//При изменении данных в строке "Новое наименование", подставляется значение из БД с новым типом
		<?php
			$result = mysqli_query($link, "select distinct * from `list_of_products`");
			$mass_list_of_products = mysqli_fetch_all($result);
		?>
		$.each(<?php echo json_encode($mass_list_of_products); ?> , function (index, value) {
		if (value[1] == $("#name").val())
    		$("#type").val(value[0]);
		});
	})

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