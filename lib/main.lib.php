<?php
function connect()//connect to DB
{
    $host = 'localhost';
    $database = 'adcproducts';
    $user = 'root';
    $password = 'qwerty123';
    $link = mysqli_connect($host, $user, $password, $database);
    if (!$link) 
    {
        die('Ошибка при подключении к базе данных: ' . mysqli_connect_error($link));
    }
    return($link);
}
function createHeader($link){
	echo '<div class="header">';
	createMenu($link);
	echo '<a href="main.php" style = "position: absolute;"><img  id="adc" src="images/adc.png" align="center"></a>';
	echo '<div id="worker"><p><img id="exit" src="images/worker.png">'.htmlspecialchars($_SESSION['worker']).'</p></div>';
	echo '</div>';
}
function createMenu($link)
	{
		echo '<div class="dropdown">';
		echo '<button class="dropbtn" align="center"><img id = "menu" src = "images/menu.png"></button>';
		echo '<div class="dropdown-content">';
		if ((!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT']) || (empty($_SESSION['hash'])))
			echo '<a href="index.php">Авторизоваться</a>';
		else
		{
			$result = mysqli_query($link, "select `root` from `users` where `user` = '".$_SESSION['user']."'");
			$rootdb = mysqli_fetch_row($result);
			if (!strpos($_SERVER['SCRIPT_NAME'], "main.php"))
				echo '<a href="main.php">Главная</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "accept.php") && (strpos($rootdb[0], "accept") !==false))
				echo '<a href="accept.php">Приемка</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "otk.php") && (strpos($rootdb[0], "otk") !==false))
				echo '<a href="otk.php">ОТК</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "testing.php") && (strpos($rootdb[0], "testing") !==false))
			echo '<a href="testing.php">Тестирование</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "mismatch.php") && (strpos($rootdb[0], "mismatch") !==false))
				echo '<a href="mismatch.php">Несоответствия</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "repair.php") && (strpos($rootdb[0], "repair") !==false))
				echo '<a href="repair.php">Ремонт</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "shipment.php") && (strpos($rootdb[0], "shipment") !==false))
				echo '<a href="shipment.php">Отгрузка</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "refand.php") && (strpos($rootdb[0], "refand") !==false))
				echo '<a href="refand.php">Возврат</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "orders.php"))
				echo '<a href="orders.php">Заказы</a>';
			if (!strpos($_SERVER['SCRIPT_NAME'], "nomenclature.php"))
				echo '<a href="nomenclature.php">Номенклатура</a>';
			echo '<a href="exit.php">Выход<img id="exit" src="images/exit.png"></a>';
		}
		echo '</div>';
		echo '</div>';
	}
function createFooter(){
	echo '<div class="footer"><p>Для служебного пользования сотрудниками АДС</p></div>';
}
function selectDB($link, $option_text, $option, $table_name) //create filters for table
{	
	echo '<div class = "filters"><label class = "filterName">'.$option_text.'</label>';
	$result = mysqli_query($link, "select distinct $option from $table_name where $option != '' order by $option asc");
	$num = mysqli_num_rows($result);
	echo '<div class = "overflowClass">';
		while ($num > 0)
		{
			$row = mysqli_fetch_array($result);
			echo '<label class = "filterInput"><input class = "filter" type = "checkbox" form = "myform" name="filter['.$option.'][]" value ="'.$row[$option].'"';
			$j = 0;
			while (!empty($_POST['filterHide'][$option][$j]))
			{
				if ($row[$option] == $_POST['filterHide'][$option][$j])
				echo 'checked';
				$j++;
			}
			echo '>';
			if ($row[$option] == 'ok')
			echo 'Успешно';
			else if ($row[$option] == 'fail')
			echo 'Не успешно';
			else if ($row[$option] == 'yes')
			echo 'Да';
			else if ($row[$option] == 'no')
			echo 'Нет';
			else if ($row[$option] == 'notest')
			echo 'Не тестировалось';
			else if ($row[$option] == 'nocheck')
			echo 'Не проверялось';
			else if ($row[$option] == 'stock')
			echo 'Склад';
			else if ($row[$option] == 'shipped')
			echo 'Отправлено';
			else if ($row[$option] == 'nelikvid')
			echo 'Неликвид';
			else if ($row[$option] == 'isolator')
			echo 'Изолятор брака';
			else if ($row[$option] == 'develop')
			echo 'Разработка';
			else if ($row[$option] == 'repair')
			echo 'Ремонт';
			else if ($row[$option] == 'work')
			echo 'Производство';
			else if ($option == 'repair')
				{
					$query = mysqli_query($link, "select worker from users where user = '".mysqli_real_escape_string($link, $row[$option])."'");
					$rowWorker = mysqli_fetch_row($query);
					echo htmlspecialchars($rowWorker[0]);
				}
			else echo $row[$option];
			echo '</label>';
			$num--;
		}
	mysqli_free_result($result);
	echo '</div>';
	echo '</div>';
}
function sortSelect($columnName) //create sort for table
		{
			$replace = array ("yes" => "Да", "no" => "Нет", "ok" => "Успешно", "fail" => "Не успешно", "stock" => "Склад", "shipped" => "Отправлено", 
"notest" => "Не тестировалось", "nocheck" => "Не проверялось", "record" => "Запись", "otk" => "ОТК", "testing" => "Тест", "mismatch" => "Несоотв.",
"shipment" => "Отгрузка", "repair" => "В ремонте", "worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус",
"comment" => "Комментарий", "UID" => "№ ", "type" => "Тип", "name" => "Имя", "serial" => "S/N",
"owner" => "Владелец", "location" => "Местопол.", "protocol" => "Протокол", "develop" => "Разработка", "isolator" => "Изолятор брака", "nelikvid" => "Неликвид", "work" => "Производство");
			for ($i = 0; !empty($columnName[$i]); $i++)
			{
					$id = '';
					echo '<td onclick = "tranPost(\'orderHide\', \'order by `'.$columnName[$i].'`';
					if (isset($_POST['orderHide']) && $_POST['orderHide'] == 'order by `'.$columnName[$i].'` asc')
						{
							echo ' desc';
							$id = ' id = \'activeColumnSortAsc\' ';
						}
					else if (isset($_POST['orderHide']) && $_POST['orderHide'] == 'order by `'.$columnName[$i].'` desc')
					{
						echo ' asc';
						$id = ' id = \'activeColumnSortDesc\'';
					}
					else
					{
						$id = ' class = "pounterCurs" ';
						echo ' asc';
					}
					echo '\', \'myform\' )"'.$id.'>'.$replace[$columnName[$i]].'</td>';
			}
		}
function requestDB($index, $link) //create request for DB from main
{
	$str = "where ";
	$j = 0;
	while (!empty($index[$j]))
	{
	
		if (!empty($_POST['filter'][$index[$j]]))
		{
			$i = 0;
			if ($index[$j] == "serial" && strpos($_POST['filter'][$index[$j]][$i], ',') !== false)
				$_POST['filter'][$index[$j]] = explode(',', $_POST['filter'][$index[$j]][$i]);
			$str = $str . "(";
			while(!empty($_POST['filter'][$index[$j]][$i]))
			{
				if ($index[$j] == "serial")
					$str = $str. "`" .$index[$j]. "` LIKE '%" .mysqli_real_escape_string($link, $_POST['filter'][$index[$j]][$i]). "%'";
				else if ($index[$j] == "comment")
				$str = $str. "`" .$index[$j]. "` != '" .mysqli_real_escape_string($link, $_POST['filter'][$index[$j]][$i]). "'";
				else if ($index[$j] == "date1")
				$str = $str . "`date` >= '" .mysqli_real_escape_string($link, $_POST['filter'][$index[$j]][$i]). "'";
				else if ($index[$j] == "date2")
				$str = $str . "`date` <= '" .mysqli_real_escape_string($link, $_POST['filter'][$index[$j]][$i]). "'";
				else
				$str = $str. "`" .$index[$j]. "` = '" .mysqli_real_escape_string($link, $_POST['filter'][$index[$j]][$i]). "'";
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
function checkRoot($link, $root) //check root from database
{
    if (empty($_SESSION))
    error403($link);
    $result = mysqli_query($link, "select `password` from `users` where `user` = '".mysqli_real_escape_string($link, $_SESSION['user'])."'");
    $hash = mysqli_fetch_row($result);
    if (($hash[0] != $_SESSION['hash']) || (!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT']))
    error403($link);
    $result = mysqli_query($link, "select `root` from `users` where `user` = '".mysqli_real_escape_string($link, $_SESSION['user'])."'");
    $rootdb = mysqli_fetch_row($result);
    if (strpos($_SERVER['SCRIPT_NAME'], "main.php") || strpos($_SERVER['SCRIPT_NAME'], "orders.php") || strpos($_SERVER['SCRIPT_NAME'], "nomenclature.php"))
    {
        if ($rootdb[0] == "")
        error403($link);
    }
    else if (!(strpos($rootdb[0], $root)!==false))
        error403($link);
}
function error403($link){
	echo '<!DOCTYPE html>
		<html>
		<head>
		<meta charset=utf-8">
		<link rel="stylesheet" href="css/main.css">
		<title>Главная</title>
		</head>
		<body>
		<div class="header">';
		createMenu($link);
	if (!empty($_SESSION['worker']))
	{
		echo '<div id="worker"><p><img id="exit" src="images/worker.png">';
		echo $_SESSION['worker'];
		echo '</p></div>';
	}
	echo '</div>
			<div id="errForma">
			<img id = "errAccess" src="images/403.jpg" align="center">
			</div>
			<div class="footer">
					<p>Для служебного пользования сотрудниками АДС</p>
			</div>
		</body>
		</html>';
	die;
}
function paintRow($link, $result, $array, $posthist, $pageName)
{	
	$replace = array ("yes" => "Да", "no" => "Нет", "ok" => "Успешно", "fail" => "Не успешно", "stock" => "Склад", "shipped" => "Отправлено", 
"notest" => "Не тестировалось", "nocheck" => "Не проверялось", "record" => "Запись", "otk" => "ОТК", "testing" => "Тестирование", "mismatch" => "Несоответствия",
"shipment" => "Отгрузка", "shipping" => "Отгрузка", "repair" => "Ремонт", "worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус", "location" => "Местоположение",
"comment" => "Комментарий", "UID" => "№ ", "type" => "Тип", "name" => "Наименование", "serial" => "Серийный номер",
"owner" => "Владелец", "location" => "Местоположение", "protocol" => "Протокол", "develop" => "Разработка", "isolator" => "Изолятор брака", "nelikvid" => "Неликвид", "work" => "Производство");
	if(mysqli_num_rows($result) != 0)
	{
		while ($row = mysqli_fetch_assoc($result))
		{
			$i = 0;
			echo "<tr>";
			while (!empty($array[$i]))
			{
				echo '<td';
				if ($pageName == "otk" && $array[$i] == 'history')
					echo ' id = "historyOtk" onclick = "tranPost(\'history\', \''.$row['uid'].'\', \''.htmlspecialchars($array[$i]).'Post\')">Показать</td>';
				else if (!empty($replace[$row[$array[$i]]]))
				{
					if ($posthist == true && $pageName == "main" && ($array[$i] == 'location' || $array[$i] == 'otk' || $array[$i] == 'testing' || $array[$i] == 'mismatch'))
						echo '><a href="#" id="form_submit" onclick = "tranPost(\''.htmlspecialchars($array[$i]).'Inp\', \''.htmlspecialchars($row["serial"]).'\', \''.htmlspecialchars($array[$i]).'Post\')">'.$replace[$row[$array[$i]]].'</a>';
					else if ($pageName == "otk" && $array[$i] == 'mismatch')
						echo '><a href="#" id="form_submit" onclick = "tranPost(\''.htmlspecialchars($array[$i]).'Inp\', \''.htmlspecialchars($row["serial"]).'\', \''.htmlspecialchars($array[$i]).'Post\')">'.$replace[$row[$array[$i]]].'</a>';
					else
						echo '>'.htmlspecialchars($replace[$row[$array[$i]]]);
				}
				else 
				{
					if ($posthist == true && $pageName == "main" && ($array[$i] == 'location' || $array[$i] == 'otk' || $array[$i] == 'testing' || $array[$i] == 'mismatch'))
					echo '><a href="#" id="form_submit" onclick = "tranPost("'.htmlspecialchars($array[$i]).'", '.htmlspecialchars($row["serial"]).')">'.htmlspecialchars($row[$array[$i]]).'</a>';
					else if ($posthist == true && $pageName == "main" && $array[$i] == 'repair')
					{
						echo '><a href="#" id="form_submit" onclick = "tranPost(\''.htmlspecialchars($array[$i]).'Inp\', \''.htmlspecialchars($row["serial"]).'\', \''.htmlspecialchars($array[$i]).'Post\')">';
						if (empty($row[$array[$i]]))
							echo 'Нет';
						else
						{
							echo '<div title = "'.htmlspecialchars($row[$array[$i]]).'">';
							$query = mysqli_query($link, "select worker from users where user = '".mysqli_real_escape_string($link, $row[$array[$i]])."'");
							$queryRow = mysqli_fetch_row($query);
							echo htmlspecialchars($queryRow[0]);
							echo '</div>';
						}
						echo '</a>';
						
					}
					else if ($array[$i] == 'serial' && $posthist == true)
						echo ' id = "history" onclick = "tranPost(\'history\', \''.$row['UID'].'\', \'myform\')">'.htmlspecialchars($row[$array[$i]]).'</td>';
					else 
						echo '>'.htmlspecialchars($row[$array[$i]]);
				}
				echo '</td>';
				$i++;
			}
			echo "</tr>";
		}
	}
	else echo "<tr><td id = 'empty'>Пусто</td></tr>";
}

function paintRowOrder($result, $array, $replace, $posthist)
{
	while ($row = mysqli_fetch_assoc($result))
	{
		$i = 0;
		echo "<tr>";
		while (!empty($array[$i]))
		{
			echo '<td>';
			if ($array[$i] == 'composition' && (mb_substr($row[$array[$i]], -1) == ','))
			echo mb_substr($row[$array[$i]], 0, -1);
			else if (!empty($replace[$row[$array[$i]]]))
			echo $replace[$row[$array[$i]]];
			else if ($array[$i] == 'UID')
				echo '<a href="#" id="form_submit" onclick = "rer(this.innerText)">'.$row[$array[$i]].'</a>';
			else echo $row[$array[$i]];
			echo '</td>';
			$i++;
		}
	}
}
function crHiddenInpPostFilters($postFilters)
{
	$indexFilters = array_keys($postFilters);
	$i = 0;
	while (!empty($indexFilters[$i]))
	{
		$x = 0;
		while (isset($postFilters[$indexFilters[$i]][$x]))
		{
			echo '<input type = "hidden" name = "filterHide['.htmlspecialchars($indexFilters[$i]).']['.htmlspecialchars($x).']" form = "myform" value = "'.htmlspecialchars($postFilters[$indexFilters[$i]][$x]).'">';
			$x++;
		}
		$i++;
	}
}
?>