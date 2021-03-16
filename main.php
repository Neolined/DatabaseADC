<?php
session_start();
if ((!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT'])  || (empty($_SESSION['root'])))
{
	$_SESSION['err'] = 1;
	header('Location: index.php');
}
require_once 'connect.php';
$link = mysqli_connect($host, $user, $password, $database);
if (!$link) 
{
die('Ошибка при подключении к базе данных: ' . mysqli_connect_error($link));
}
$query = mysqli_query($link, "select worker from users where user = '".$_SESSION['user']."'");
$worker = mysqli_fetch_row($query);
$worker = $worker[0];
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="asset/css/main1.css"<?php echo(microtime(true).rand()); ?>>
  <title>Главная</title>
 </head>
 <body>
 <div class="header">
	<div class="dropdown">
		<button class="dropbtn" align="center">МЕНЮ</button>
		<div class="dropdown-content">
			<a href="accept.php">Приемка</a>
			<a href="exit.php"><img id="exit" src="exit.png"><p id="exitp">Выход</p></a>
		</div>
	</div>
	<img id="adc" src="adc.png" align="center">
	<div id="worker">
	<p><?php echo $worker; ?></p>
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
					echo "<tr>";
						if (!empty($_POST))
						$table_name = "history";
						else $table_name = "products";
						$result = mysqli_query($link, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$table_name."' order by ORDINAL_POSITION");
						if(!$result)
						{
						 die ('Ошибка запроса: mysqli_query'.mysqli_error($link)) . '<br>';
						}
						$col = 0;
						while ($row = mysqli_fetch_assoc($result))
						{
							echo "<td>";
							echo $row['COLUMN_NAME'];
							echo "</td>";
							++$col;
						}
					if (empty($_POST))
					echo "<td id=\"his\"> history </td>";
					echo "</tr>";
					$result = mysqli_query($link, "SELECT * FROM  `products`");
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
					if (!empty($_POST))
					{
					$uid = $view_rows + $_POST['history'];
					$result = mysqli_query($link, "SELECT * from `history` where `uid` = '".$uid."'" );
					}
					else $result = mysqli_query($link, "SELECT * FROM  `products` LIMIT $view_rows, $max_rows");//выводим таблицу
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
						if (empty($_POST))
						echo '<td><button id = "history" type = "submit" name = "history" value="'.$h++.'" form = "myform">Показать историю изделия</button></td>';
						echo "</tr>";
					}
					mysqli_free_result($result);
				?>
			</table>
			<div class= "pagination">
			<?php
				if(empty($_POST))
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
 </body>
</html>