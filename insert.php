<?php
session_start();
require_once 'connect.php';
if ((!empty($_POST['insn'])) && (!empty($_POST['inst'])))
{
$query = "INSERT INTO list_of_products (`name`, `type`) VALUE ('".$_POST['insn']."', '".$_POST['inst']."')";
if (mysqli_query($link, $query))
    echo "Успешно.";
else 
    die ('Не успешно:'  .  mysqli_error($link));
}
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="asset/css/main1.css"<?php echo(microtime(true).rand()); ?>>
  <title>Интерфейс приемщика</title>
 </head>
 <body>
 <div class="header">
			<img id="adc1" src="adc.png">
</div>
		<div id="forma">
		<form action="insert.php" method="post" id="inp" align="center" class="form1">
			<form action="registration.php" method="post">
			<label>Ты вносишь в таблицу "Название"</label><input type="text" name="insn" onfocus="this.value=''" value="<?php if (!empty($_POST['insn'])) echo $_POST['insn']; ?>" />
			<label>Ты вносишь в таблицу "Тип"</label><input type="text" name="inst" onfocus="this.value=''" value="<?php if (!empty($_POST['inst'])) echo $_POST['inst']; ?>" />
			<input type="submit" value="Сохранить данные" id="savedata" />
		</form>
		</div>
	<div class="footer" role="contentinfo">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	</div>
 </body>
</html>