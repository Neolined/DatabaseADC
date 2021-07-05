<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, NULL);
sessStart($link, "main");
if (!empty($_POST['postFromOrders']))
{
	$result = mysqli_query($link, "select replace (`composition`,' ','') from `orders` where `uid` = '".mysqli_real_escape_string($link, $_POST['postFromOrders'])."'");
	$row = mysqli_fetch_row($result);
	if ($row[0]!= '')
	$_POST['filter']['serial'][0] = $row[0];
}
	if (empty($_POST['filter']['serial'][0]))
	{
		unset($_POST['filter']['serial']);
	}
	if (empty($_POST['filter']['date1'][0]))
		unset($_POST['filter']['date1']);
	if (empty($_POST['filter']['date2'][0]))
		unset($_POST['filter']['date2']);
if (!empty($_POST['filter']))
$_SESSION['filter'] = $_POST['filter'];
if (!empty($_POST['applyFilter']) && (empty($_POST['filter'])) && (empty($_POST['lot']) && (empty($_POST['order']))))
header('Location: clearmain.php');
$columnName = array ( "UID", "type", "name", "perfomance", "serial", "date", "owner", "location", "otk", "testing", "repair", "mismatch", "comment");
$replace = array ("worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус",
"comment" => "Комментарий", "protocol" => "Протокол");
//формирование Запроса чере SESSION
if (empty($_SESSION['order']) && empty($_POST['order']))
$_SESSION['order'] = 'order by `uid` desc';
else if (!empty($_POST['order']))
$_SESSION['order'] = mysqli_real_escape_string($link, $_POST['order']);
if ((empty($_SESSION['request']) && empty($_POST['filter'])) || (empty($_POST['filter']) && !empty ($_POST['lot'])))
{
$_SESSION['request'] = '';
unset($_SESSION['filter']);
}
else if (!empty($_POST['filter']))
{
	$_SESSION['request'] = requestDB(array("serial", "type","name", "location", "owner", "otk", "testing", "repair", "mismatch", "comment", "date1", "date2"), $link);
	if (empty($_POST['order']))
		$_SESSION['order'] = 'order by `uid` desc';
}
if (!empty($_POST['applyFilter']))
{
if (!empty($_POST['lot']))
	$_SESSION['lot'] = $_POST['lot'];
else 
	unset ($_SESSION['lot']);
}
else 
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
	<?php createMenu($link) ?>
	<img id="adc" src="images/adc.png" align="center">
	<div id="worker">
	<p><img id="exit" src="images/worker.png"><?php echo $_SESSION['worker']; ?></p>
	</div>
	</div>
	<div id="forma">
		<form action = "main.php" method = "post" id="myform"></form>
		<form method="post" id="locationPost" action = "refand.php"><input type="hidden" id = "locationInp" name = "postFromMain"/></form>
		<form method="post" id="otkPost" action = "otk.php"><input type="hidden" id = "otkInp" name = "postFromMain"/></form>
		<form method="post" id="testingPost" action = "testing.php"><input type="hidden" id = "testingInp" name = "postFromMain"/></form>
		<form method="post" id="repairPost" action = "repair.php"><input type="hidden" id = "repairInp" name = "postFromMain"/></form>
		<form method="post" id="mismatchPost" action = "mismatch.php"><input type="hidden" id = "mismatchInp" name = "postFromMain"/></form>
		<form method="post" id="maxrowsPost" action = "main.php"><input type="hidden" id = "maxrowsInp" name = "maxrows"/></form>
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
				echo '<input id="hideFilter" type = "submit" form = "myform" name = "applyFilter" value = "Применить фильтры"></input>';
				echo '<input id="hideFilter" type="button" onclick="location.href=\'clearmain.php\'" value = "Сбросить фильтры">';
				echo '<button id="hideFilter" onclick = "clearFilter()">Очистить</button></div>';
				echo '<div id="filterContent" style="display:none">';
				echo '<div class = "filters"><label class = "filterName">Серийный номер</label><label class="filterInput"><input type = "text" name = "filter[serial][]"  form = "myform" value ="'; if (!empty($_SESSION['filter']['serial'][0])) echo $_SESSION['filter']['serial'][0]; echo '"></label></div>';
				echo '<div class = "filters"><label class = "filterName">Отображение</label><label class="filterInput"><input  class = "viewSort" onchange="checkAddress(this, \'viewSort\')" name = "lot" type="checkbox" form = "myform" value ="1"'; if (!empty($_SESSION['lot']) && $_SESSION['lot'] == 1) echo 'checked'; echo '>Количество</label>';
				echo '<label class="filterInput"><input class = "viewSort" onchange="checkAddress(this, \'viewSort\')"  name = "lot" type="checkbox" form = "myform" value ="2"'; if (!empty($_SESSION['lot']) && $_SESSION['lot'] == 2 ) echo 'checked'; echo '>Склад</label></div>';
				selectDB($link, "Тип", "type", "products");	
				selectDB($link, "Название", "name", "products");
				selectDB($link, "Местоположение", "location", "products");
				selectDB($link, "Владелец", "owner", "products");
				selectDB($link, "ОТК", "otk", "products");
				selectDB($link, "Тестирование", "testing", "products");
				selectDB($link, "В ремонте", "repair", "products");
				echo '<div class = "filters"><label class = "filterName">Комментарий</label><label class="filterInput"><input  class = "sort" onchange="checkAddress(this, \'sort\')" name = "filter[comment][]" type="checkbox" form = "myform" value =" "'; if (!empty($_SESSION['filter']['comment'][0])) echo 'checked'; echo '>Наличие комментария</label></div>';
				echo '<div class = "filters"><label class = "filterName">Дата</label><label class="filterInput">от  <input id = "date" name = "filter[date1][]" type ="date" min="2015-01-01" max="'; echo date("Y-m-d"); echo '" value = "'; if (!empty($_SESSION['filter']['date1'][0])) echo $_SESSION['filter']['date1'][0];
				echo '" form = "myform"></label><label class="filterInput">по  </input><input id = "date" name = "filter[date2][]" type = "date" min="2016-01-01" max="'; echo date("Y-m-d"); echo '" form = "myform" value = "'; if (!empty($_SESSION['filter']['date2'][0])) echo $_SESSION['filter']['date2'][0]; echo '"></input></label></div>';
				echo '</div>';
				echo '</div>';
				}
				else
				{
					$result = mysqli_query($link, "select `type`, `name`, `serial` from products where `uid` = '".mysqli_real_escape_string($link, $_POST['history'])."'");
					if(!$result)
						die ('Ошибка запроса в Историю: mysqli_query'.mysqli_error($link)) . '<br>';
					$row = mysqli_fetch_row($result);
					echo '<div id = "infoHist"><p> '.$row[0].'</p>';
					echo '<p> '.$row[1].'</p>';
					echo '<p> '.$row[2].'</p></div>';
				}
				?>
			<table class="table" align="center">
				<?php
					if (empty($_SESSION['lot']))
					{
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
							
							sortSelect($columnName, "Прямая сортировка", "Обратная сортировка");
						}
						echo "</tr>";
						$result = mysqli_query($link, "SELECT * FROM  `products` ".$_SESSION['request']." ".$_SESSION['order']."");
						if(!$result)
							die ('Ошибка запроса в Продукты: mysqli_query'.mysqli_error($link)) . '<br>';
						$all_rows=mysqli_num_rows($result);
						if (!empty($_POST['maxrows']))
							$_SESSION['maxrows'] = $_POST['maxrows'];
						else if (empty($_SESSION['maxrows']))
							$_SESSION['maxrows'] = 30;
						$pages = ((floor($all_rows/$_SESSION['maxrows'])) + 1);
						if($all_rows%$_SESSION['maxrows'] == 0)
							$pages = floor($all_rows/$_SESSION['maxrows']);
						if ((empty($_GET['page'])) || ($_GET['page'] == 1))
							$view_rows = 0;
						else 
							$view_rows = ($_GET['page'] - 1) * $_SESSION['maxrows'];
						if (!empty($_POST['history']))
						{
							$result = mysqli_query($link, "SELECT * from `history` where `uid` = '".mysqli_real_escape_string($link, $_POST['history'])."'  order by date desc" );
							if(!$result)
								die ('Ошибка запроса в Историю: mysqli_query'.mysqli_error($link)) . '<br>';
						}
						else
							$result = mysqli_query($link, "SELECT * FROM  `products` ".$_SESSION['request']." ".$_SESSION['order']." LIMIT $view_rows, ".mysqli_real_escape_string($link, $_SESSION['maxrows'])."");//выводим таблицу
						if(!$result)
							die ('Ошибка запроса в Продукты: mysqli_query'.mysqli_error($link)) . '<br>';
						paintRow($result, $columnName, empty($_POST['history']), true);
						mysqli_free_result($result);
					}
					else
					{
						if (($_SESSION['lot']) == 1)
						{
						$result = mysqli_query($link, "select type, name, count(type) as duplicates from products ".$_SESSION['request']." group by type, name");
						echo '<tr><td>Тип</td><td>Наименование</td><td style="padding-right: 40em;">Кол-во</td></tr>';
						while ($row = mysqli_fetch_row($result))
						{
						echo "<tr>";
						echo '<td> '.$row[0].'</td>';
						echo '<td> '.$row[1].'</td>';
						echo '<td> '.$row[2]. ' шт.</td>';
						echo "</tr>";
						}
						}
						if (($_SESSION['lot']) == 2)
						{
						$locatMass = array("stock", "develop", "nelikvid", "isolator", "work", "repair");
						$result = mysqli_query($link, "select type, name, count(type) as duplicates from products ".$_SESSION['request']." group by type, name");
						echo '<tr><td>Тип</td><td>Наименование</td><td>Склад</td><td>Разработка</td><td>Неликвид</td><td>Изолятор</td><td>Производство</td><td>В ремонте</td></tr>';
						$viewStockSer = '';
						if (!empty($_SESSION['filter']['serial']))
						{
							$i = 0;
							while (!empty($_SESSION['filter']['serial'][$i]))
							{
								if ($i == 0)
								$viewStockSer = "and (";
								else $viewStockSer = $viewStockSer . " or ";
								$viewStockSer = $viewStockSer . "`serial` LIKE '%".$_SESSION['filter']['serial'][$i]."%'";
								$i++;
							}
							$viewStockSer = $viewStockSer . ")";
						}
						while ($row = mysqli_fetch_row($result))
						{
							echo "<tr>";
							echo '<td> '.$row[0].'</td>';
							echo '<td> '.$row[1].'</td>';
							for ($i=0; !empty($locatMass[$i]); $i++)
							{
								$query = mysqli_query($link, "select count(location) as location from products where location = '".$locatMass[$i]."' and type = '".$row[0]."' and name = '".$row[1]."'".$viewStockSer."");
								$lotOkBrds = mysqli_fetch_row($query);
								echo '<td> '.$lotOkBrds[0];
								if ($locatMass[$i] == 'stock' || $locatMass[$i] == 'develop')
								{
									$query = mysqli_query($link, "select count(location) as location from products where location = '".$locatMass[$i]."' and type = '".$row[0]."' and name = '".$row[1]."' and mismatch = 'no'".$viewStockSer."");
									$locat = mysqli_fetch_row($query);
									{
										if (!empty($locat[0]) && $locat[0] != $lotOkBrds[0])
											echo '('.$locat[0].')';
									}
								}
								echo ' шт.</td>';
							}
							echo "</tr>";
						}
						}
					}
				?>
			</table>
			<div class= "pagination">
			<?php
				if(empty($_SESSION['lot']))
				if(empty($_POST['history']))
				{
				 	$activepage = 0;
					for ($j = 1; $j <= $pages; $j++)
					{
						if (($_SERVER['REQUEST_URI'] == $_SERVER['SCRIPT_NAME'].'?page='.$j) || (empty($_GET['page']) && ($j == 1)))
							$activepage = $j;
					}

					for ($j = 1; $j <= $pages; $j++)
					{
					if(($j!=1)&&($j!=$pages)&&(abs($activepage-$j) == 6)) echo '<a>...</a> ';
					echo ' <a ';
					if ($activepage == $j)
					{
						echo "class=\"active\"";
					}
					if(($j==1)||($j==$pages)||(abs($activepage-$j) < 6)) echo 'href='.$_SERVER['SCRIPT_NAME'].'?page='.$j.'>'.$j.'</a> ';
					}
					echo '<div id = "maxrows">';
					echo '<a href="#" id="form_submit" onclick = "tranPost(\'maxrowsInp\', \'20\', \'maxrowsPost\')">20</a>|';
					echo '<a href="#" id="form_submit" onclick = "tranPost(\'maxrowsInp\', \'30\', \'maxrowsPost\')">30</a>|';
					echo '<a href="#" id="form_submit" onclick = "tranPost(\'maxrowsInp\', \'50\', \'maxrowsPost\')">50</a>|';
					echo '<a href="#" id="form_submit" onclick = "tranPost(\'maxrowsInp\', \'100\', \'maxrowsPost\')">100</a>';
					echo '</div>';
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