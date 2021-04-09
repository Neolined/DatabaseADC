<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
//checkRoot($link, NULL);
if (empty($_POST['filter']['date1'][0]))
unset($_POST['filter']['date1']);
if (empty($_POST['filter']['date2'][0]))
unset($_POST['filter']['date2']);
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
		<form action = "<?php echo $_SERVER['REQUEST_URI'];?>" method = "post" id="myform"></form>
		<table class="table" align="center">
				<?php
					if (empty($_POST))
					echo "<caption>База изделий АДС</caption>";
					else
					echo "<caption>История изделия</caption>";
				?>
		</table>
	<div class = "filterButton">
			<div id = "filtersButtonAct">
			<button id="showFilter" onclick = "show('showFilter', 'hideFilter', 'filterContent')">Показать фильтры</button>
			<button id="hideFilter" onclick = "hide('showFilter', 'hideFilter', 'filterContent')" style="display:none;">Скрыть</button>
			<input id="hideFilter" type = "submit" form = "myform" value = "Применить фильтры"></input>
			<input id="hideFilter" type="button" onclick="location.href='clearmain.php'" value = "Сбросить фильтры">
			<button id="hideFilter" onclick = "clearFilter()">Очистить</button></div>
			<div id="filterContent" style="display:none;">
			<?php
				selectDB($link, "Тип", "type", "products");	
				selectDB($link, "Название", "name", "list_of_products");
				selectDB($link, "Местоположение", "location", "products");
				selectDB($link, "Владелец", "owner", "products");
				selectDB($link, "ОТК", "otk", "products");
				?>
			<div class = "filters"><label class = "filterName">Комментарий</label><label class="filterInput"><input class = "filter"  name = "comment[]" type="checkbox" form = "myform" value =" ">Наличие комментария</label></div>
				<div class = "filters"><label class = "filterName">Дата</label><label class="filterInput">от  <input id = "date" name = "filter[date1][]" type ="date" min="2015-01-01" max="2100-12-31" form = "myform"></label><label class="filterInput">по  </input><input id = "date" name = "filter[date2][]" type = "date" min="2016-01-01" max="2099-12-31" form = "myform"></input></label></div>
			</div>
	</div>
	<table class="table" align="center">
<?php
						if (!empty($_POST['history']))
						$table_name = "history";
						else $table_name = "products";
						$result = mysqli_query($link, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$table_name."' order by ORDINAL_POSITION");
						if(!$result)
						{
						 die ('Ошибка запроса: mysqli_query'.mysqli_error($link)) . '<br>';
						}
						echo '<tr>';
						if ($table_name == "history")
						{
							$col = 0;
							while ($row = mysqli_fetch_assoc($result))
							{
								echo "<td>";
								echo $row['COLUMN_NAME'];
								echo "</td>";
								++$col;
							}
						}
						else
						{
							$col = mysqli_num_rows($result);
							sortSelect("uid", "UID", "По возрастанию", "По убыванию");
							sortSelect("type", "Тип", "А-Я", "Я-А");
							sortSelect("name", "Имя", "A-Z", "Z-A");
							sortSelect("perfomance", "Исполнение", "A-Z", "Z-A");
							sortSelect("serial", "Сирийный номер", "Прямой порядок", "Обратный порядок");
							sortSelect("enter", "Вхождение", "Прямой порядок", "Обратный порядок");
							sortSelect("date", "Дата", "Сначала", "С конца");
							sortSelect("location", "Местонахождение", "А-Я", "Я-А");
							sortSelect("owner", "Владелец", "А-Я", "Я-А");
							sortSelect("software", "ПО", "Прямой порядок", "Обратный порядок");
							sortSelect("otk", "ОТК", "А-Я", "Я-А");
							sortSelect("comment", "Комментарий", "А-Я", "Я-А");
						}
					if (empty($_POST['history']))
					echo "<td id=\"his\"> history </td>";
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
					$_SESSION['filter'] = requestDB(array("type","name", "location", "owner", "otk", "comment", "date1", "date2"));
					if (empty($_POST['order']))
					$_SESSION['order'] = '';
					}
					$result = mysqli_query($link, "SELECT * FROM  `products` ".$_SESSION['filter']." ".$_SESSION['order']."");

					if(!$result)
						{
						die ('Ошибка запроса: mysqli_query'.mysqli_error($link)) . '<br>';
						}
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
					{
					$uid = $view_rows + $_POST['history'];
					$result = mysqli_query($link, "SELECT * from `history` where `uid` = '".$uid."'  order by date desc" );
					}
					else 
					$result = mysqli_query($link, "SELECT * FROM  `products` ".$_SESSION['filter']." ".$_SESSION['order']." LIMIT $view_rows, $max_rows");//выводим таблицу
					if(!$result)
					{
					die ('Ошибка запроса: mysqli_query'.mysqli_error($link)) . '<br>';
					}
					$h=1;
					while ($row = mysqli_fetch_array($result))
					{
						echo "<tr>";
						$c = -1;
						$i = 0;
						while ($i<$col)
						{
						echo '<td> '.$row[++$c].'</td>';
						++$i;
						}
						if (empty($_POST['history']))
						echo '<td id = "tdAlign"><button id = "history" type = "submit" name = "history" value="'.$h++.'" form = "myform">Показать историю изделия</button></td>';
						echo "</tr>";
					}
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
				{
				echo '<a class="active" href='.$_SERVER['SCRIPT_NAME'].'>Назад</a> ';
				}
			?>
			</div>
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	<script src = "js/script.js"></script>
 </body>
</html>