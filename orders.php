<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, NULL);
$columnName = array ( "UID", "composition", "recipient", "shipped", "comment");
$columnNameRu = array ( "№ Заказа", "Состав", "Получатель", "Отправлено", "Комментарий");
$replace = array ("no" => "Нет", "yes" => "Да");
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <title>Заказы</title>
 </head>
 <body>
 <div class="header">
	<?php createMenu($link) ?>
	<img id="adc" src="images/adc.png" align="center">
	<div id="worker">
	<p><img id="exit" src="images/worker.png"><?php echo $_SESSION['worker']; ?></p>
	</div>
	</div>
	<div id="forma">
		<table class="table" align="center" style = "width:unset">
			<caption>Таблица заказов</caption>
			<?php
				echo '<tr>';
				for ($i = 0; (!empty($columnNameRu[$i])); $i++)
				{
				echo '<td>'.$columnNameRu[$i].'</td>';
				}
				echo '</tr>';
				echo "<tr>";
				$result = mysqli_query($link, "select * from orders");
				paintRowOrder($result, $columnName, $replace, false, false);
				echo "</tr>";
				echo "<tr style = 'height: 0.5em;' ></tr>";
			?>	
		</table>
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
 </body>
</html>
<script>
	function rer(a) {
	document.getElementById('seb').value = a;
	document.getElementById('form2').submit();
    };
</script>