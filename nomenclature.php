<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
checkRoot($link, NULL);
clearSESpage();
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <title>Номенклатура</title>
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
		<table class="table" align="center" style = "width: 30em;">
		<form action = "nomenclature.php" method = "post" id="myform">
			<?php
			if (!empty($_POST['order']))
				$_SESSION['orderSort'] = $_POST['order'];
			else if (empty($_SESSION['orderSort']))
				$_SESSION['orderSort'] = '';
			$result = mysqli_query($link, 'select distinct `type` from list_of_products '.$_SESSION['orderSort'].'');
			echo '<caption><div class="multiselect" style="width: -webkit-fill-available;"><div class="selectBox" onclick="showCheckboxesSort(\'order_by_type\')"><select style="background: center;color: white;font-size: initial;"><option>Номенклатура</option> </select> <div class="overSelect"></div></div><div id="order_by_type" style="color: black;margin-left: 11em;" class="optionClassOrder" style="display:none;"><label class="selectLabel"><input name="order" form = "myform" class = "sort" onchange="checkAddress(this, \'sort\'); this.form.submit();" type="checkbox" value ="order by `type` asc"';
			if (empty($_SESSION['orderSort']) || $_SESSION['orderSort'] == "order by `type` asc")
				echo ' checked';
			echo '>A-Z</label><label class="selectLabel"><input name="order" form = "myform" class = "sort" onchange="checkAddress(this, \'sort\'); this.form.submit();" type="checkbox" value ="order by `type` desc"';
			if (!empty($_SESSION['orderSort']) && $_SESSION['orderSort'] == "order by `type` desc")
				echo 'checked';
			echo '>Z-A</label></div></div></caption>';
			$columnName = mysqli_fetch_all ($result);
			$i = 0;
			for ($i = 0; !empty($columnName[$i][0]); $i++)
			{
			echo '<tr><td style="max-width:-webkit-fill-available;">';
			echo '<div class="multiselect"><div class="selectBox" onclick="showCheckboxesSort(\'order_by'.$columnName[$i][0].'\')"><select><option>'.$columnName[$i][0].'</option> </select> <div class="overSelect"></div></div><div id="order_by'.$columnName[$i][0].'" class="optionClassOrder" style="display:none;"><label class="selectLabel"><input name=" '.$columnName[$i][0].'" form = "myform" class = "sort" onchange="checkAddress(this, \'sort\'); this.form.submit();" type="checkbox" value ="order by `name` asc"'; 
			if (!empty($_POST[$columnName[$i][0]]) && $_POST[$columnName[$i][0]] == "order by `name` asc")
				echo "checked";
			echo '>A-Z</label><label class="selectLabel"><input name=" '.$columnName[$i][0].'" form = "myform" class = "sort" onchange="checkAddress(this, \'sort\'); this.form.submit();" type="checkbox" value ="order by `name` desc"';
			if (!empty($_POST[$columnName[$i][0]]) && $_POST[$columnName[$i][0]] == "order by `name` desc")
			echo "checked";
			echo '>Z-A</label></div></div>';
			echo '</td></tr>';
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