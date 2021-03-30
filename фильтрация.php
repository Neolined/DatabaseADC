<?php
require_once 'connect.php';
function selectDB($link, $option_text, $option, $table_name)
{	
	echo '<div class = "filters"><label class = "filterName">'.$option_text.'</label>';
	$result = mysqli_query($link, "select distinct $option from $table_name where $option != ''");
	$num = mysqli_num_rows($result);
	while ($num > 0)
	{
		$row = mysqli_fetch_array($result);
		echo '<label class = "filterInput"><input class = "filter" type = "checkbox" form = "myform" name="filter['.$option.'][]" value ="'.$row[$option].'">'.$row[$option].'</label>';
		$num--;
	}
	mysqli_free_result($result);
	echo '</div>';
}

function sortSelect($order, $name_disabled, $sorttag1, $sorttag2)
		{
		echo '<div class="multiselect"><div class="selectBox" onclick="showCheckboxesSort(\'order_by'.$order.'\')"><select><option>'.$name_disabled.'</option> </select> <div class="overSelect"></div></div><div id="order_by'.$order.'" class="optionClassOrder" style="display:none;"><label class="selectLabel"><input name="order_by" class = "sort" onchange="checkAddress(this)" type="checkbox" value ="1">'.$sorttag1.'</label><label class="selectLabel"><input name="order_by" class = "sort" onchange="checkAddress(this)" type="checkbox" value ="2">'.$sorttag2.'</label></div></div>';
		}
$a = "pull";
$h = "doll";
$b = array("full", "fuck");
$c = $a . " " . $b[0];
echo $c;

function requestDB($index)
{
	$str = " select * from products where ";
	echo "1";
	$j = 0;
	if (empty($_POST['filter']['date1'][0]))
	unset($_POST['filter']['date1']);
	if (empty($_POST['filter']['date2'][0]))
	unset($_POST['filter']['date2']);
	while (!empty($index[$j]))
	{
	
		if (!empty($_POST['filter'][$index[$j]]))
		{
			echo "0";
			$i = 0;
			echo "2";
			$str = $str . "(";
			while(!empty($_POST['filter'][$index[$j]][$i]))
			{
				echo "3";
				if ($index[$j] == "comment")
				$str = $str. "`" .$index[$j]. "` != '" .$_POST['filter'][$index[$j]][$i]. "'";
				else if ($index[$j] == "date1")
				$str = $str . "`date` >= '" .$_POST['filter'][$index[$j]][$i]. "'";
				else if ($index[$j] == "date2")
				$str = $str . "`date` <= '" .$_POST['filter'][$index[$j]][$i]. "'";
				else
				$str = $str. "`" .$index[$j]. "` = '" .$_POST['filter'][$index[$j]][$i]. "'";
				$i++;
				if (!empty($_POST['filter'][$index[$j]][$i]))
				$str = $str. " or ";
			}
			$str = $str . ")";
			$end = array_keys($_POST['filter']);
			if (end($end) != $index[$j])
			$str = $str . " and ";


		}
		$j++;
	}
	
	
	return($str);
}

//функция для вывода $_POST пункт 2
?>
<!DOCTYPE HTML>
<html>
<meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css">
<body>
<form action = "фильтрация.php" method = "post" id="myform"></form>
	<div class = "filterButton">
			<div id = "filtersButtonAct">
			<button id="showFilter" onclick = "show('showFilter', 'hideFilter', 'filterContent')">Показать фильтры</button>
			<button id="hideFilter" onclick = "hide('showFilter', 'hideFilter', 'filterContent')" style="display:none;">Скрыть</button>
			<input id="hideFilter" type = "submit" form = "myform" value = "Отправить данные">
			<button id="hideFilter" onclick = "clearFilter()">Очистить</button></div>
			<input value="Кнопка" type="button" onclick="location.href='clearmain.php'">
			<div id="filterContent" style="display:none;">
				<?php
				selectDB($link, "Тип", "type", "products");	
				selectDB($link, "Название", "name", "list_of_products");
				selectDB($link, "Местоположение", "location", "products");
				selectDB($link, "Владелец", "owner", "products");
				selectDB($link, "ОТК", "otk", "products");
				?>
			<div class = "filters"><label class = "filterName">Комментарий</label><label class="filterInput"><input  name = "comment[]" type="checkbox" form = "myform" value =" ">Наличие комментария</label></div>
				<div class = "filters"><label class = "filterName">Дата</label><label class="filterInput">от  <input id = "date" name = "filter[date1][]" type ="date" min="2015-01-01" max="2100-12-31" form = "myform"></label><label class="filterInput">по  </input><input id = "date" name = "filter[date2][]" type = "date" min="2016-01-01" max="2099-12-31" form = "myform"></input></label></div>
				 
	</div>

<?php
$str = requestDB(array("type","name", "location", "owner", "otk", "comment", "date1", "date2"));
echo $str;
echo '<br>';
echo " ";
echo '<br>';
print_r ($_POST);
echo '<br>';
echo $_POST['filter']['type'][0];
if (empty($_POST['date1'][0]))
unset($_POST['date1']);
echo " ";
echo '<br>';
print_r ($_POST);

?>



<script src = "script.js"></script>
</body>
</html>