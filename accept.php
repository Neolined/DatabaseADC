<?php
session_start();
if ((!isset($_SESSION['user'])) || ($_SESSION['ua'] !== $_SERVER['HTTP_USER_AGENT']) || ($_SESSION['root'] !== "accept"))
{
	header('Location: main.php');
}
require_once 'connect.php';
mysqli_set_charset($link, 'utf8');

$error_s1 = 0;
$error_s2 = 0;
$error_s3 = 0;
$error_s4 = 0;
$error_n1 = 1;
$suc= 0;
$query = mysqli_query($link, "select worker from users where user = '".$_SESSION['user']."'");
$worker = mysqli_fetch_row($query);
$worker = $worker[0];		

//создание массива для подсказки названия изделия
$query = mysqli_query($link, "select distinct `name` from `list_of_products`");
while ($row = mysqli_fetch_array($query))
{
$arr[] = $row['name'];
}
if (!empty ($_POST['name']))
{
	$name = mysqli_query($link, "select `name` from `list_of_products` where `name` = '".$_POST['name']."'");
	$error_n1 = mysqli_num_rows($name);
	if ($error_n1 > 0)
	{
		if ((!empty($_POST['lot'])) && (!empty($_POST['serial'])))
		{
			if (preg_match('/^[A-Z]\d{5}$/', $_POST['serial']))
				{
					if ($_POST['lot']>=1)
					{	
						$str = $_POST['serial'];
						$lot = (int) $_POST['lot'];
						while($lot != 0)
						{
								if ((mb_substr($_POST['serial'], 0, 1)) != (mb_substr($str, 0, 1)))
								{
									$error_s2 = 1;//если первая буква внаименовании изделия изменилась 
									break;
								}
								$serial = mysqli_query($link, "select `serial` from `products` where `serial` = '".$str."'");
								$error_s3 = mysqli_num_rows($serial);
								if ($error_s3 > 0)
								{
									break;//если во время цикла увидел, что такой серийный номер есть в бд
								}
								$str++;
								$lot--;
						}
						if (($error_s2 != 1) && ($error_s3 == 0)) //передача данных в бд дополнить!
						{
							$str = $_POST['serial'];
							$lot = (int) $_POST['lot'];
							$suc = 1;
							while ($lot > 0)
							{
								$query = "INSERT INTO products (`type`, `name`, `perfomance`, `serial`, `date`) VALUES ('".$_POST['type']."', '".$_POST['name']."', '".$_POST['perfomance']."', '".$str."', NOW())";
								if (mysqli_query($link, $query))
									$id = (mysqli_insert_id($link));
								else 
									die ('Ошибка записи в ТБ продукты:'  .mysqli_error($link));
								
								$query = "INSERT INTO `history` (`UID`, `date`,  `worker`, `type_write`, `order_from`, `whom_order`, `comment`) VALUES ('$id', NOW(), '$worker', 'Запись', '".$_POST['order_from']."', 'АДС', '".$_POST['comment']."')";
								if (!(mysqli_query($link, $query)))
									die ('Ошибка записи в ТБ история:'  .mysqli_error($link));
								$str++;
								$lot--;

							}
						}
						
					}
				}
		
			else 
			$error_s4 = 1;
		}
	}
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
	<div class="dropdown">
		<button class="dropbtn" align="center">МЕНЮ</button>
		<div class="dropdown-content">
			<a href="main.php">Главная</a>
			<a href="exit.php"><img id="exit" src="exit.png"><p id="exitp">Выход</p></a>
		</div>
	</div>
	<img id="adc" src="adc.png">
	<div id="worker">
	<p><?php echo $worker; ?></p>
	</div>
</div>
 <div id="forma">
		<form action="accept.php" position= "bottom" method="post" id="cleardata" class = "form">
		<p id="priem_name" align="center">Первичный прием </p>
		<input type="submit" name = "villy" value="Очистить данные формы"/>
		</form>
		<form action="accept.php" method="post" align="left" class="form">
			<label>Тип изделия</label>
				<select size="1" name="type" required>
				<?php
				if (empty($_POST['type']))
				echo "<option selected disabled hidden style='display: none' value=''>Выберите тип</option>";
				else
				{
				echo "<option selected style='display: none'>";
				echo $_POST['type'];
				echo "</option>";
				}
				?>
				<?php
				$result = mysqli_query($link, "select distinct `type` from `list_of_products`");
				$num = mysqli_num_rows($result);
				while ($num > 0)
				{
				$row = mysqli_fetch_array($result);
				echo '<option>'.$row['type'].'</option>';
				$num--;
				}
				$mass = array("Johny", "Lolka", "Pavel");
				?>
				</select>
			<label>Название изделия</label><div class="autocomplete"><input id = "myInput" autocomplete="off"<?php if ($error_n1 == 0) echo "class=\"color_err1\"";?> type="text" name="name" value="<?php if (!empty($_POST['name'])) echo $_POST['name']; ?>" required/>
			</div>
			<label>Исполнение</label><input type="text" name="perfomance" onfocus="this.value=''" value="<?php if (!empty($_POST['perfomance'])) echo $_POST['perfomance']; ?>"/>
  
			<div class="serial_lot">
			<div><label>Серийный номер</label><input <?php if (($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) echo "class=\"color_err\""; else echo "class=\"serial\""; ?> value="<?php if (($error_s1>0) || ($error_s4>0) || ($error_n1 == 0)) echo $_POST['serial']; if ($error_s2>0) echo "Системная ошибка"; if ($error_s3>0) echo $str; ?>" type="text" name="serial" required/> </div>
			<div class="lol"><label>Количество</label><input class="lot" type="text" name="lot" onfocus="this.value=''" value="<?php if ((($error_n1 == 0) || ($error_s1 > 0) || ($error_s2 > 0) || ($error_s3 > 0)||($error_s4 > 0)) && (!empty($_POST['lot']))) echo $_POST['lot']; else echo '1'; ?>"/></div>
			</div>
			<label>От кого</label><input type="text" name="order_from" onfocus="this.value=''" value="<?php if (!empty($_POST['order_from'])) echo $_POST['order_from']; ?>" required/>
			<label>Комментарий</label><textarea class="comment" type="text" name="comment" onfocus="this.value=''"> <?php if (!empty($_POST['comment'])) echo $_POST['comment']; ?></textarea>
			<input type="submit" id="savedata" value="Сохранить данные" />
			<?php
			if (($error_s1>0) || ($error_s3>0))
			echo "<p class=\"msg\"> Серийный номер уже зарегистрирован </p>";
			if ($error_s4>0)
			echo "<p class=\"msg\"> Неккоректно введен серийный номер</p>";
			if ($error_n1 == 0)
			echo "<p class=\"msg\"> Неккоректно введено название изделия</p>";
			if ($suc == 1) 
			echo "<p class=\"msg1\"> Данные успешно занесены!</p>";
		?>
	</div>
	<div class="footer">
			<p>Для служебного пользования сотрудниками АДС</p>
	</div>
	<?php
?>
	<script src="script.js"></script>
	<script>function autocomplete(inp, arr) {
    /* функция автозаполнения принимает два аргумента,
    элемент текстового поля и массив возможных значений автозаполнения: */
    var currentFocus;
    //arr = JSON.parse(arr);
    /* выполнение функции, когда кто-то пишет в текстовом поле: */
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value;
        /* закрыть все уже открытые списки значений автозаполнения */
        closeAllLists();
        if (!val) { return false;}
        currentFocus = -1;
        /* создайте элемент DIV, который будет содержать элементы (значения): */
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        /* добавьте элемент DIV в качестве дочернего элемента контейнера автозаполнения: */
        this.parentNode.appendChild(a);
        /* для каждого элемента в массиве... */
        for (i = 0; i < arr.length; i++) {
          /* проверьте, начинается ли элемент с тех же букв, что и значение текстового поля: */
          if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
            /* создайте элемент DIV для каждого соответствующего элемента: */
            b = document.createElement("DIV");
            /* сделайте соответствующие буквы жирным шрифтом: */
            b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
            b.innerHTML += arr[i].substr(val.length);
            /* вставьте поле ввода, которое будет содержать значение текущего элемента массива: */
            b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
            /* выполнение функции, когда кто-то нажимает на значение элемента (элемент DIV): */
                b.addEventListener("click", function(e) {
                /* вставьте значение для текстового поля автозаполнения: */
                inp.value = this.getElementsByTagName("input")[0].value;
                /* закройте список значений автозаполнения,
                (или любые другие открытые списки значений автозаполнения : */
                closeAllLists();
            });
            a.appendChild(b);
          }
        }
    });
    /* выполнение функции нажимает клавишу на клавиатуре: */
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
          /* Если нажата клавиша со стрелкой вниз,
          увеличение текущей переменной фокуса: */
          currentFocus++;
          /* и сделать текущий элемент более видимым: */
          addActive(x);
        } else if (e.keyCode == 38) { //вверх
          /* Если нажата клавиша со стрелкой вверх,
          уменьшите текущую переменную фокуса: */
          currentFocus--;
          /* и сделать текущий элемент более видимым: */
          addActive(x);
        } else if (e.keyCode == 13) {
          /* Если нажата клавиша ENTER, предотвратите отправку формы, */
          e.preventDefault();
          if (currentFocus > -1) {
            /* и имитировать щелчок по элементу "active": */
            if (x) x[currentFocus].click();
          }
        }
    });
    function addActive(x) {
      /* функция для классификации элемента как "active": */
      if (!x) return false;
      /* начните с удаления "активного" класса для всех элементов: */
      removeActive(x);
      if (currentFocus >= x.length) currentFocus = 0;
      if (currentFocus < 0) currentFocus = (x.length - 1);
      /*добавить класса "autocomplete-active": */
      x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
      /* функция для удаления "активного" класса из всех элементов автозаполнения: */
      for (var i = 0; i < x.length; i++) {
        x[i].classList.remove("autocomplete-active");
      }
    }
    function closeAllLists(elmnt) {
      /* закройте все списки автозаполнения в документе,
      кроме того, который был передан в качестве аргумента: */
      var x = document.getElementsByClassName("autocomplete-items");
      for (var i = 0; i < x.length; i++) {
        if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }
  /* выполнение функции, когда кто-то щелкает в документе: */
  document.addEventListener("click", function (e) {
      closeAllLists(e.target);
  });
}
//var countries = ["Johny","Albania","Algeria","Andorra","Angola","Anguilla","Antigua & Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia & Herzegovina","Botswana","Brazil","British Virgin Islands","Brunei","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Cayman Islands","Central Arfrican Republic","Chad","Chile","China","Colombia","Congo","Cook Islands","Costa Rica","Cote D Ivoire","Croatia","Cuba","Curacao","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Ethiopia","Falkland Islands","Faroe Islands","Fiji","Finland","France","French Polynesia","French West Indies","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guam","Guatemala","Guernsey","Guinea","Guinea Bissau","Guyana","Haiti","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey","Jordan","Kazakhstan","Kenya","Kiribati","Kosovo","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Montserrat","Morocco","Mozambique","Myanmar","Namibia","Nauro","Nepal","Netherlands","Netherlands Antilles","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","North Korea","Norway","Oman","Pakistan","Palau","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Puerto Rico","Qatar","Reunion","Romania","Russia","Rwanda","Saint Pierre & Miquelon","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","St Kitts & Nevis","St Lucia","St Vincent","Sudan","Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor L'Este","Togo","Tonga","Trinidad & Tobago","Tunisia","Turkey","Turkmenistan","Turks & Caicos","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States of America","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Virgin Islands (US)","Yemen","Zambia","Zimbabwe"];
autocomplete(document.getElementById("myInput"), <?php $arr = json_encode($arr); echo $arr; ?>);
</script>
</body>
</html>