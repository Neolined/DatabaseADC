<?php
function selectDB($link, $option_text, $option, $table_name) //create filters for table
{	
	echo '<div class = "filters"><label class = "filterName">'.$option_text.'</label>';
	$result = mysqli_query($link, "select distinct $option from $table_name where $option != ''");
	$num = mysqli_num_rows($result);
	echo '<div class = "overflowClass">';
		while ($num > 0)
		{
			$row = mysqli_fetch_array($result);
			echo '<label class = "filterInput"><input class = "filter" type = "checkbox" form = "myform" name="filter['.$option.'][]" value ="'.$row[$option].'"';
			$j = 0;
			while (!empty($_SESSION['filter'][$option][$j]))
			{
				if ($row[$option] == $_SESSION['filter'][$option][$j])
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
			else echo $row[$option];
			echo '</label>';
			$num--;
		}
	mysqli_free_result($result);
	echo '</div>';
	echo '</div>';
}
function sortSelect($columnName, $sorttag1, $sorttag2) //create sort for table
		{
			$replace = array ("yes" => "Да", "no" => "Нет", "ok" => "Успешно", "fail" => "Не успешно", "stock" => "Склад", "shipped" => "Отправлено", 
"notest" => "Не тестировалось", "nocheck" => "Не проверялось", "record" => "Запись", "otk" => "ОТК", "testing" => "Тестирование", "mismatch" => "Несоответствия",
"shipment" => "Отгрузка", "repair" => "В ремонте", "worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус",
"comment" => "Комментарий", "UID" => "№ ", "type" => "Тип", "name" => "Наименование", "perfomance" => "Исполнение", "serial" => "Серийный номер",
"owner" => "Владелец", "location" => "Местоположение", "protocol" => "Протокол", "develop" => "Разработка", "isolator" => "Изолятор брака", "nelikvid" => "Неликвид");
			for ($i = 0; !empty($columnName[$i]); $i++)
			{
			echo '<td>';
			echo '<div class="multiselect"><div class="selectBox" onclick="showCheckboxesSort(\'order_by'.$columnName[$i].'\')"><select><option>'.$replace[$columnName[$i]].'</option> </select> <div class="overSelect"></div></div><div id="order_by'.$columnName[$i].'" class="optionClassOrder" style="display:none;"><label class="selectLabel"><input name="order" form = "myform" class = "sort" onchange="checkAddress(this, \'sort\')" type="checkbox" value ="order by '.$columnName[$i].' asc ">'.$sorttag1.'</label><label class="selectLabel"><input name="order" form = "myform" class = "sort" onchange="checkAddress(this, \'sort\')" type="checkbox" value ="order by '.$columnName[$i].' desc ">'.$sorttag2.'</label></div></div>';
			echo '</td>';
			}
		}
function requestDB($index) //create request for DB from main
{
	$str = "where ";
	$j = 0;
	while (!empty($index[$j]))
	{
	
		if (!empty($_POST['filter'][$index[$j]]))
		{
			$i = 0;
			$str = $str . "(";
			while(!empty($_POST['filter'][$index[$j]][$i]))
			{
				if ($index[$j] == "repair" && $_POST['filter'][$index[$j]][$i] == 'no')
				$str = $str. "repair = 'no'";
				else if ($index[$j] == "repair" && $_POST['filter'][$index[$j]][$i] == 'yes')
				$str = $str. "repair != 'no'";
				else if ($index[$j] == "comment")
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
function checkRoot($link, $root) //check root from database
{
    if (empty($_SESSION))
    error403($link);
    $result = mysqli_query($link, "select `password` from `users` where `user` = '".$_SESSION['user']."'");
    $hash = mysqli_fetch_row($result);
    if (($hash[0] != $_SESSION['hash']) || (!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT']))
    error403($link);
    $result = mysqli_query($link, "select `root` from `users` where `user` = '".$_SESSION['user']."'");
    $rootdb = mysqli_fetch_row($result);
    if (strpos($_SERVER['SCRIPT_NAME'], "main.php") || strpos($_SERVER['SCRIPT_NAME'], "orders.php") || strpos($_SERVER['SCRIPT_NAME'], "nomenclature.php"))
    {
        if ($rootdb[0] == "")
        error403($link);
    }
    else if (!(strpos($rootdb[0], $root)!==false))
        error403($link);
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
function paintRow($result, $array, $posthist)
{	
	$replace = array ("yes" => "Да", "no" => "Нет", "ok" => "Успешно", "fail" => "Не успешно", "stock" => "Склад", "shipped" => "Отправлено", 
"notest" => "Не тестировалось", "nocheck" => "Не проверялось", "record" => "Запись", "otk" => "ОТК", "testing" => "Тестирование", "mismatch" => "Несоответствия",
"shipment" => "Отгрузка", "repair" => "В ремонте", "worker" => "Сотрудник", "date" => "Дата", "type_write" => "Тип записи",
"order_from" => "От кого принята", "whom_order" => "Кому отправлена", "number_order" => "Номер заказа", "status" => "Статус",
"comment" => "Комментарий", "UID" => "№ ", "type" => "Тип", "name" => "Наименование", "perfomance" => "Исполнение", "serial" => "Серийный номер",
"owner" => "Владелец", "location" => "Местоположение", "protocol" => "Протокол", "develop" => "Разработка", "isolator" => "Изолятор брака", "nelikvid" => "Неликвид");
	if(mysqli_num_rows($result) != 0)
	{
	while ($row = mysqli_fetch_assoc($result))
	{
		$i = 0;
		echo "<tr>";
		while (!empty($array[$i]))
		{
			echo '<td>';
			if (!empty($replace[$row[$array[$i]]]))
			echo $replace[$row[$array[$i]]];
			else echo $row[$array[$i]];
			echo '</td>';
			$i++;
		}
		if ($posthist == true)
		echo '<td id = "tdAlign"><button id = "history" type = "submit" name = "history" value="'.$row['UID'].'" form = "myform">Показать историю изделия</button></td>';
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
			else echo $row[$array[$i]];
			echo '</td>';
			$i++;
		}
	}
}
function clearSESpage()
{
	if (!strpos($_SERVER['SCRIPT_NAME'], "main.php"))
		unset ($_SESSION['main']);
	if (!strpos($_SERVER['SCRIPT_NAME'], "otk.php"))
		unset ($_SESSION['otk']);
	if (!strpos($_SERVER['SCRIPT_NAME'], "repair.php"))
		unset ($_SESSION['repairSES']);
	if (!strpos($_SERVER['SCRIPT_NAME'], "refand.php"))
		unset ($_SESSION['refand']);
	if (!strpos($_SERVER['SCRIPT_NAME'], "shipment.php"))
		unset ($_SESSION['shipment']);
	if (!strpos($_SERVER['SCRIPT_NAME'], "testing.php"))
		unset ($_SESSION['testing']);
	if (!strpos($_SERVER['SCRIPT_NAME'], "nomenclature.php"))
		unset ($_SESSION['orderSort']);
}
function clearSESSION1($page, $index)
{
	$i = 0;
	if (empty($_SESSION[$page]))
{
	while(!empty($index[$i]))
	{
	unset($_SESSION[$index[$i]]);
	$i++;
	}
	$_SESSION[$page] = 1;
}
}
?>