<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, NULL);
if (!empty($_POST['postFromOrders']))
{
	$result = mysqli_query($link, "select replace (`composition`,' ','') from `orders` where `uid` = '".mysqli_real_escape_string($link, $_POST['postFromOrders'])."'");
	$row = mysqli_fetch_row($result);
	if ($row[0]!= '')
	$_POST['filter']['serial'][0] = $row[0];
}
if (empty($_POST['filter']['serial'][0]))
	unset($_POST['filter']['serial']);
if (empty($_POST['filter']['date1'][0]))
	unset($_POST['filter']['date1']);
if (empty($_POST['filter']['date2'][0]))
	unset($_POST['filter']['date2']);
if (!empty($_POST['applyFilter']) && (empty($_POST['filter'])) && (empty($_POST['lot']) && (empty($_POST['order']))))
header('Location: clearmain.php');
$columnName = array ( "UID", "type", "name", "perfomance", "serial", "date", "owner", "location", "otk", "testing", "repair", "mismatch");
$replace = array ("worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус",
"comment" => "Комментарий", "protocol" => "Протокол", "location" => "Местоположение");
//формирование Запроса чере SESSION
if (empty($_POST['orderHide']) && empty($_POST['order']))
$_POST['orderHide'] = 'order by `UID` desc';
else if (!empty($_POST['order']))
$_POST['orderHide'] = mysqli_real_escape_string($link, $_POST['order']);
if (!isset($_POST['filter']) && isset($_POST['filterHide']))
	$_POST['filter'] = $_POST['filterHide'];
if (empty($_POST['filter']) || (empty($_POST['filter']) && !empty ($_POST['lot'])))
{
$request = '';
unset($_POST['filterHide']);
}
else if (!empty($_POST['filter']))
	$request = requestDB(array("serial", "type","name", "location", "owner", "otk", "testing", "repair", "mismatch", "date1", "date2"), $link);
if (!empty($_POST['applyFilter']))
{
if (!empty($_POST['lot']))
	$_POST['lotHide'] = $_POST['lot'];
else 
	unset ($_POST['lotHide']);
}
else 
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <script src="js/jquery.js"></script>
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
		<form action = "main.php" method = "post" id="myform"><input type="hidden" id = "history" name = "history" form = "myform"/></form>
		<form method="post" id="locationPost" action = "refand.php"><input type="hidden" id = "locationInp" name = "postFromMain"/></form>
		<form method="post" id="otkPost" action = "otk.php"><input type="hidden" id = "otkInp" name = "postFromMain"/></form>
		<form method="post" id="testingPost" action = "testing.php"><input type="hidden" id = "testingInp" name = "postFromMain"/></form>
		<form method="post" id="repairPost" action = "repair.php"><input type="hidden" id = "repairInp" name = "postFromMain"/></form>
		<form method="post" id="mismatchPost" action = "mismatch.php"><input type="hidden" id = "mismatchInp" name = "postFromMain"/></form>
		<table class="table" align="center">
				<?php
				if (empty($_POST['history']))
					echo "<caption>База изделий АДС</caption>";
				else
					echo "<caption>История изделия</caption>";
				if (!empty($_POST['maxrows']))
					{
						echo '<input type = "hidden" name = "maxrowsHide" form = "myform" value = "'.htmlspecialchars($_POST['maxrows']).'" >';
						$_POST['maxrowsHide'] = $_POST['maxrows'];
					}
				else if (!empty($_POST['maxrowsHide']))
					echo '<input type = "hidden" name = "maxrowsHide" form = "myform" value = "'.htmlspecialchars($_POST['maxrowsHide']).'" >';
				else if (empty($_POST['maxrowsHide']))
					$_POST['maxrowsHide'] = 30;
				if (isset($_POST['filter']))
					$_POST['filterHide'] = $_POST['filter'];
				if (isset($_POST['filter']))
						crHiddenInpPostFilters($_POST['filter']);
				if (!empty($_POST['lot']))
					echo '<input type = "hidden" name = "lotHide" value = "'.htmlspecialchars($_POST['lot']).'">';
				if (isset($_POST['page']))
					echo '<input type = "hidden" name = "pageHide" form = "myform" value = "'.htmlspecialchars($_POST['page']).'">';
				if (isset($_POST['orderHide']))
					echo '<input type = "hidden" name = "orderHide" id = "orderHide" form = "myform" value = "'.htmlspecialchars($_POST['orderHide']).'">';
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
				echo '<button id="hideFilter" onclick = "clearFilter()">Очистить</button>';
				if (empty($_POST['lotHide']))
				{
				$result = mysqli_query($link, "SELECT count(*) FROM  `products` ".$request." ".$_POST['orderHide']."");
					if(!$result)
					die ('Ошибка запроса в Продукты: mysqli_query'.mysqli_error($link));
				$all_rows=mysqli_fetch_row($result);
				$all_rows= $all_rows[0];
				echo '<h4 id = "allRows">Всего записей: '.$all_rows.'</h4>';
				}
				echo '</div>';
				echo '<div id="filterContent" style="display:none">';
				echo '<div class = "filters"><label class = "filterName">Серийный номер</label><label class="filterInput"><input type = "text" name = "filter[serial][]"  form = "myform" value ="'; if (!empty($_POST['filterHide']['serial'][0])) echo $_POST['filterHide']['serial'][0]; echo '"></label></div>';
				echo '<div class = "filters"><label class = "filterName">Отображение</label><label class="filterInput"><input  class = "viewSort" onchange="checkAddress(this, \'viewSort\')" name = "lot" type="checkbox" form = "myform" value ="1"'; if (!empty($_POST['lotHide']) && $_POST['lotHide'] == 1) echo 'checked'; echo '>Количество</label>';
				echo '<label class="filterInput"><input class = "viewSort" onchange="checkAddress(this, \'viewSort\')"  name = "lot" type="checkbox" form = "myform" value ="2"'; if (!empty($_POST['lotHide']) && $_POST['lotHide'] == 2 ) echo 'checked'; echo '>Склад</label></div>';
				selectDB($link, "Тип", "type", "products");	
				selectDB($link, "Название", "name", "products");
				selectDB($link, "Местоположение", "location", "products");
				selectDB($link, "Владелец", "owner", "products");
				selectDB($link, "ОТК", "otk", "products");
				selectDB($link, "Тестирование", "testing", "products");
				selectDB($link, "В ремонте", "repair", "products");
				echo '<div class = "filters"><label class = "filterName">Дата</label><label class="filterInput">от  <input class = "date" name = "filter[date1][]" type ="date" min="2015-01-01" max="'; echo date("Y-m-d"); echo '" value = "'; if (!empty($_POST['filterHide']['date1'][0])) echo $_POST['filterHide']['date1'][0];
				echo '" form = "myform"></label><label class="filterInput">по  </input><input class = "date" name = "filter[date2][]" type = "date" min="2016-01-01" max="'; echo date("Y-m-d"); echo '" form = "myform" value = "'; if (!empty($_POST['filterHide']['date2'][0])) echo $_POST['filterHide']['date2'][0]; echo '"></input></label></div>';
				echo '</div>';
				echo '</div>';
				}
				else
				{
					$result = mysqli_query($link, "select `type`, `name`, `serial` from products where `uid` = '".mysqli_real_escape_string($link, $_POST['history'])."'");
					if(!$result)
						die ('Ошибка запроса в Историю: mysqli_query'.mysqli_error($link));
					$row = mysqli_fetch_row($result);
					echo '<div id = "infoHist"><p> '.$row[0].'</p>';
					echo '<p> '.$row[1].'</p>';
					echo '<p> '.$row[2].'</p></div>';
				}
				?>
			<table class="table" align="center">
				<?php
					if (empty($_POST['lotHide']))
					{
						if (!empty($_POST['history']))
						{
							$result = mysqli_query($link, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'history' order by ORDINAL_POSITION");
							if(!$result)
								die ('Ошибка запроса: mysqli_query'.mysqli_error($link));
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
							echo "</tr>";
							$result = mysqli_query($link, "SELECT * from `history` where `uid` = '".mysqli_real_escape_string($link, $_POST['history'])."'  order by date desc" );
							if(!$result)
								die ('Ошибка запроса в Историю: mysqli_query'.mysqli_error($link));
						}
						else
						{
							sortSelect($columnName);
						$pages = ((floor($all_rows/$_POST['maxrowsHide'])) + 1);
						if($all_rows%$_POST['maxrowsHide'] == 0)
							$pages = floor($all_rows/$_POST['maxrowsHide']);
						if ((empty($_POST['page'])) || ($_POST['page'] == 1))
							$view_rows = 0;
						else 
							$view_rows = ($_POST['page'] - 1) * $_POST['maxrowsHide'];
						$result = mysqli_query($link, "SELECT * FROM  `products` ".$request." ".$_POST['orderHide']." LIMIT $view_rows, ".mysqli_real_escape_string($link, $_POST['maxrowsHide'])."");//выводим таблицу
						if(!$result)
							die ('Ошибка запроса в Продукты: mysqli_query'.mysqli_error($link));
						}
						paintRow($result, $columnName, empty($_POST['history']), "main");
						mysqli_free_result($result);
					}
					else
					{
						if (($_POST['lotHide']) == 1)
						{
							$result = mysqli_query($link, "select type, name, count(type) as duplicates from products ".$request." group by type, name");
							echo '<thead><tr><td class = "pounterCurs">Тип</td class = "pounterCurs"><td class = "pounterCurs">Наименование</td><td style="padding-right: 35em;" class = "pounterCurs">Кол-во</td></tr></thead><tbody>';
							while ($row = mysqli_fetch_row($result))
							{
							echo "<tr>";
							echo '<td> '.$row[0].'</td>';
							echo '<td> '.$row[1].'</td>';
							echo '<td> '.$row[2]. ' шт.</td>';
							echo "</tr>";
							}
							echo '</tbody>';
						}
						if (($_POST['lotHide']) == 2)
						{
						$locatMass = array("stock", "develop", "nelikvid", "isolator", "work", "repair");
						$result = mysqli_query($link, "select type, name, count(type) as duplicates from products ".$request." group by type, name");
						echo '<thead><tr><td class = "pounterCurs">Тип</td><td class = "pounterCurs">Наименование</td><td class = "pounterCurs">Склад</td><td class = "pounterCurs">Разработка</td><td class = "pounterCurs">Неликвид</td><td class = "pounterCurs">Изолятор</td><td class = "pounterCurs">Производство</td><td class = "pounterCurs">В ремонте</td></tr></thead><tbody>';
						$viewStockSer = '';
						if (!empty($_POST['filterHide']['serial']))
						{
							$i = 0;
							while (!empty($_POST['filterHide']['serial'][$i]))
							{
								if ($i == 0)
								$viewStockSer = "and (";
								else $viewStockSer = $viewStockSer . " or ";
								$viewStockSer = $viewStockSer . "`serial` LIKE '%".$_POST['filterHide']['serial'][$i]."%'";
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
						echo '</tbody>';
						}
					}
				?>
			</table>
			<div class= "allPagination">
			<?php
				if(empty($_POST['lotHide']))
				if(empty($_POST['history']))
				{
					echo '<div class= "pagination">';
					$activepage = 0;
					if (empty($_POST['page']))
						$_POST['page'] = 1;
					else $activepage = $_POST['page'];
					for ($j = 1; $j <= $pages; $j++)
					{
						if(($j==1)||($j==$pages)||(abs($activepage-$j) < 6))
						{
							echo '<input type = "submit" name = "page" form = "myform" class = "pagBtn" value = "'.htmlspecialchars($j).'"';
						if ($_POST['page'] == $j)
							echo ' id = "pagBtnActive"';
						echo '">';
						}
						if(($j!=1)&&($j!=$pages)&&(abs($activepage-$j) == 6)) echo ' <a> ... </a> ';
					}
					echo '</div>';
					echo '<div id = "maxrows">';
					$arrMaxrows = array("20", "30", "50", "100");
					for ($i=0; isset($arrMaxrows[$i]); $i++)
					{
						echo '<button type = "submit" form = "myform" class = "maxrowsBtn" name = "maxrows"';
						if (isset($_POST['maxrowsHide']) && $_POST['maxrowsHide'] == $arrMaxrows[$i])
							echo 'id = "activeMaxRows"';
						echo 'value = "'.htmlspecialchars($arrMaxrows[$i]).'">'.htmlspecialchars($arrMaxrows[$i]).'</button>|';
					}
					echo '</div>';
				}
				else
				{
					echo '<button type = "submit" id = "returnBtn" form = "myform" name = "page" value = "';
					if (isset($_POST['pageHide']))
						echo htmlspecialchars($_POST['pageHide']);
					echo '">Назад</button>';
				}
			?>
			</div>
			
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	<script src = "js/script.js"></script>
	<script>document.addEventListener('DOMContentLoaded', () => {

const getSort = ({ target }) => {
	const order = (target.dataset.order = -(target.dataset.order || -1));
	const index = [...target.parentNode.cells].indexOf(target);
	const collator = new Intl.Collator(['en', 'ru'], { numeric: true });
	const comparator = (index, order) => (a, b) => order * collator.compare(
		a.children[index].innerHTML,
		b.children[index].innerHTML
	);
	
	for(const tBody of target.closest('table').tBodies)
		tBody.append(...[...tBody.rows].sort(comparator(index, order)));

	for(const cell of target.parentNode.cells)
		cell.classList.toggle('sorted', cell === target);
};

document.querySelectorAll('.table thead').forEach(tableTH => tableTH.addEventListener('click', () => getSort(event)));

});
</script>
 </body>
</html>