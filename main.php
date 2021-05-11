<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
//checkRoot($link, NULL);
clearSESpage();
if (empty($_POST['filter']['date1'][0]))
unset($_POST['filter']['date1']);
if (empty($_POST['filter']['date2'][0]))
unset($_POST['filter']['date2']);
if (empty($_SESSION['main']))
{
	unset ($_SESSION['filter']);
	unset ($_SESSION['order']);
	$_SESSION['main'] = 1;
}
$columnName = array ( "UID", "type", "name", "perfomance", "serial", "enter", "date", "owner", "software", "location", "otk", "testing", "repair", "mismatch", "comment");
$replace = array ("yes" => "Да", "no" => "Нет", "ok" => "Успешно", "fail" => "Не успешно", "stock" => "Склад", "shipped" => "Отправлено", 
"notest" => "Не тестировалось", "nocheck" => "Не проверялось", "record" => "Запись", "otk" => "ОТК", "testing" => "Тестирование", "mismatch" => "Несоответствия",
"shipment" => "Отгрузка", "repair" => "В ремонте", "worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус",
"comment" => "Комментарий", "UID" => "№ ", "type" => "Тип", "name" => "Наименование", "perfomance" => "Исполнение", "serial" => "Серийный номер",
"enter" => "Вхождение", "owner" => "Владелец", "software" => "Программное обеспечение", "location" => "Местоположение", "protocol" => "Протокол");
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <title>Главная</title>
 </head>
 <body>
 <div class="header">
	<?php createMenu() ?>
	<img id="adc" src="images/adc.png" align="center">
	<div id="worker">
	<p><img id="exit" src="images/worker.png"><?php echo $_SESSION['worker']; ?></p>
	</div>
	</div>

	<div id="forma">
		<form action = "main.php" method = "post" id="myform"></form>
		<table class="table" align="center">
				<?php
					if (empty($_POST['history']))
					echo "<caption>База изделий АДС</caption>";
					else
					echo "<caption>История изделия</caption>";
				?>
		</table>
			<?php
				if (empty($_POST['history']))
				{
				echo '<div class = "filterButton">';
				echo '<div id = "filtersButtonAct">';
				echo '<button id="showFilter" onclick = "show(\'showFilter\', \'hideFilter\', \'filterContent\')">Показать фильтры</button>';
				echo '<button id="hideFilter" onclick = "hide(\'showFilter\', \'hideFilter\', \'filterContent\')" style="display:none;">Скрыть</button>';
				echo '<input id="hideFilter" type = "submit" form = "myform" value = "Применить фильтры"></input>';
				echo '<input id="hideFilter" type="button" onclick="location.href=\'clearmain.php\'" value = "Сбросить фильтры">';
				echo '<button id="hideFilter" onclick = "clearFilter()">Очистить</button></div>';
				echo '<div id="filterContent" style="display:none">';
				selectDB($link, "Тип", "type", "products");	
				selectDB($link, "Название", "name", "list_of_products");
				selectDB($link, "Местоположение", "location", "products");
				selectDB($link, "Владелец", "owner", "products");
				selectDB($link, "ОТК", "otk", "products");
				selectDB($link, "Тестирование", "testing", "products");
				selectDB($link, "В ремонте", "repair", "products");
				echo '<div class = "filters"><label class = "filterName">Комментарий</label><label class="filterInput"><input class = "filter"  name = "filter[comment][]" type="checkbox" form = "myform" value =" ">Наличие комментария</label></div>';
				echo '<div class = "filters"><label class = "filterName">Дата</label><label class="filterInput">от  <input id = "date" name = "filter[date1][]" type ="date" min="2015-01-01" max="2100-12-31" form = "myform"></label><label class="filterInput">по  </input><input id = "date" name = "filter[date2][]" type = "date" min="2016-01-01" max="2099-12-31" form = "myform"></input></label></div>';
				echo '</div>';
				echo '</div>';
				}
				else
				{
					$result = mysqli_query($link, "select `type`, `name`, `serial` from products where `uid` = '".$_POST['history']."'");
					$row = mysqli_fetch_row($result);
					echo '<div id = "infoHist"><p>Тип: '.$row[0].'</p>';
					echo '<p>Наименование: '.$row[1].'</p>';
					echo '<p>Серийный номер: '.$row[2].'</p></div>';
				}
				?>
			<table class="table" align="center">
				<?php
					if (!empty($_POST['history']))
					{
						$result = mysqli_query($link, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'history' order by ORDINAL_POSITION");
						if(!$result)
							die ('Ошибка запроса: mysqli_query'.mysqli_error($link)) . '<br>';
						echo '<tr>';
						$i = 0;
						unset ($columnName);
						while ($row = mysqli_fetch_assoc($result))
						{
							echo "<td>";
							if (!empty($replace[$row['COLUMN_NAME']]))
								echo $replace[$row['COLUMN_NAME']];
							else
								echo $row['COLUMN_NAME'];
							$columnName[$i] = $row['COLUMN_NAME'];
							echo "</td>";
							$i++;
						}
					}
					else
					{
						for ($i = 0; !empty($columnName[$i]); $i++)
						sortSelect($columnName[$i], $replace[$columnName[$i]], "Прямая сортировка", "Обратная сортировка");
					}
					if (empty($_POST['history']))
						echo "<td id=\"his\"> История </td>";
					echo "</tr>";
					//формирование Запроса чере SESSION
					if (empty($_SESSION['order']) && empty($_POST['order']))
						$_SESSION['order'] = '';
					else if (!empty($_POST['order']))
						$_SESSION['order'] = $_POST['order'];
					if (empty($_SESSION['filter']) && empty($_POST['filter']))
						$_SESSION['filter'] = '';
					else if (!empty($_POST['filter']))
					{
						$_SESSION['filter'] = requestDB(array("type","name", "location", "owner", "otk", "testing", "repair", "mismatch", "comment", "date1", "date2"));
						if (empty($_POST['order']))
						$_SESSION['order'] = '';
					}
					$result = mysqli_query($link, "SELECT * FROM  `products` ".$_SESSION['filter']." ".$_SESSION['order']."");
					if(!$result)
						die ('Ошибка запроса: mysqli_query'.mysqli_error($link)) . '<br>';
					$all_rows=mysqli_num_rows($result);
					$max_rows = 20;
					$pages = ((floor($all_rows/$max_rows)) + 1);
					if($all_rows%$max_rows == 0)
						$pages = floor($all_rows/$max_rows);
					if ((empty($_GET['page'])) || ($_GET['page'] == 1))
						$view_rows = 0;
					else 
						$view_rows = ($_GET['page'] - 1) * $max_rows;
					if (!empty($_POST['history']))
						$result = mysqli_query($link, "SELECT * from `history` where `uid` = '".$_POST['history']."'  order by date desc" );
					else
						$result = mysqli_query($link, "SELECT * FROM  `products` ".$_SESSION['filter']." ".$_SESSION['order']." LIMIT $view_rows, $max_rows");//выводим таблицу
					if(!$result)
						die ('Ошибка запроса: mysqli_query'.mysqli_error($link)) . '<br>';
					paintRow($result, $columnName, $replace, empty($_POST['history']));
					mysqli_free_result($result);
				?>
			</table>
			<div class= "pagination">
			<?php
				if(empty($_POST['history']))
				{
					for ($j = 1; $j <= $pages; $j++)
					{
					echo ' <a ';
					if (($_SERVER['REQUEST_URI'] == $_SERVER['SCRIPT_NAME'].'?page='.$j) || (empty($_GET['page']) && ($j == 1)))
						echo "class=\"active\"";
					echo 'href='.$_SERVER['SCRIPT_NAME'].'?page='.$j.'>'.$j.'</a> ';
					}
				}
				else 
				echo '<a class="active" href='.$_SERVER['HTTP_REFERER'].'>Назад</a> ';
			?>
			</div>
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	<script src = "js/script.js"></script>
 </body>
</html>