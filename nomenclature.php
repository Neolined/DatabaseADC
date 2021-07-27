<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, NULL);
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <script src="js/jquery.js"></script>
  <title>Номенклатура</title>
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
		<table class="table1" align="center" style = "width: 30em;">
		<form action = "nomenclature.php" method = "post" id = "myform">
			<?php
			if (!isset($_POST['hiddenOrder']))
				$_POST['hiddenOrder'] = 'order by `type` asc';
			echo '<input type = "hidden" name = "hiddenOrder" id = "hiddenOrder"  value = "';
				echo htmlspecialchars($_POST['hiddenOrder']);
			echo '"></input>';
			$result = mysqli_query($link, 'select distinct `type` from list_of_products '.$_POST['hiddenOrder'].'');
			$idCapt = '';
			echo '<caption onclick = "tranPost(\'hiddenOrder\', \'order by `type` ';
			if (!empty($_POST['hiddenOrder']) && $_POST['hiddenOrder'] == 'order by `type` asc')
				{
				echo 'desc';
				$idCapt = 'id = \'activeColumnSortAsc\'';
				}
			else if (!empty($_POST['hiddenOrder']) && $_POST['hiddenOrder'] == 'order by `type` desc')
				{
				echo 'asc';
				$idCapt = 'id = \'activeColumnSortDesc\'';
				}
			echo '\', \'myform\')" '.$idCapt.'>Номенклатура</caption>';
			$columnName = mysqli_fetch_all ($result);
			$i = 0;
			for ($i = 0; !empty($columnName[$i][0]); $i++)
			{
				echo '<input type = "hidden" name = "'.$columnName[$i][0].'" id = "'.$columnName[$i][0].'"';
				if (!empty($_POST[$columnName[$i][0]]))
				{
					echo '<input type = "hidden" name = "'.$columnName[$i][0].'" id = "'.$columnName[$i][0].'" value = "'.htmlspecialchars($_POST[$columnName[$i][0]]).'" >';//для того, чтобы сохранять выбранную сортировку в массиве пост после обновления страницы
				}
				else
					$_POST[$columnName[$i][0]] = "order by `name` asc";//сортировка по-умолчанию
				echo '<tr>';
				echo '<td onclick = "tranPost(\''.$columnName[$i][0].'\', \'order by `name` ';
				if (!empty($_POST[$columnName[$i][0]]) && $_POST[$columnName[$i][0]] == 'order by `name` asc')
				{
					echo 'desc';
					$idMainRow = 'id = \'activeColumnSortAsc\'';
				}
				if (!empty($_POST[$columnName[$i][0]]) && $_POST[$columnName[$i][0]] == 'order by `name` desc')
					{
					echo 'asc';
					$idMainRow = 'id = \'activeColumnSortDesc\'';
					}
				echo '\', \'myform\')"'.$idMainRow.'>'.$columnName[$i][0].'</td>';
				echo '</tr>';
				if (!empty($_POST[$columnName[$i][0]]))
					$query = mysqli_query($link, "select `name` from list_of_products where `type` = '".$columnName[$i][0]."' ".$_POST[$columnName[$i][0]]."");
				else
				$query = mysqli_query($link, "select `name` from list_of_products where `type` = '".$columnName[$i][0]."'");
					while ($row = mysqli_fetch_row($query))
						echo '<tr><td style = "background: beige !important;">'.$row[0].'</td></tr>';
			}
			?>
		</form>
		</table>
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	<script src = "js/script.js"></script>
 </body>
</html>