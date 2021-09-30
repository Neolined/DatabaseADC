<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
$root = array(0 => "addserial", 1 => "createorder");//заменить на реальные права
$root = checkRoot($link, $root, true);
$columnName = array ( "id", "date", "deadline", "status", "recipient");
$columnNameRu = array ( "№ Заказа", "Дата создания", "Срок исполнения", "Статус", "Получатель");
$replace = array ("no" => "Нет", "yes" => "Да", "created" => "Создан", "accept" => "Принят", "completed" => "Сформирован","verify" => "Проверен","otk" => "ОТК", "shipped" => "Отгружен");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset=utf-8">
		<link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
		<script src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
		<title>Заказы</title>
	</head>
	<body>
		<?php createHeader($link)?>
		<div id="forma" style = "margin:0em !important"><!-- Попробовать убрать из main.css и посмотреть, что будет с другими интерфейсами-->
			<div id = "createOrderForm" style = "display:none">
				<form method="post" action = "orders.php" id = "myform" align="left" style = "display:none"><></form>
				<form method="post" align="left" class="form1" style = "display:flex">
					<div id="priem_name" align="center">Формирование заказа<div class = "createOrderCloser">✕</div></div>
					<?php
						echo '<div class="serial_lot" style="width: 100%;">';
						echo '<div class = "inputLabel" ><label>Год</label><input style="width: 12em;" class = "year" type="text" name="uid[year]" maxlength="4" value = "'.date ( 'Y' ).'"  readonly/> </div>';
						echo '<div class = "inputLabel"><label>Номер заказа</label><input type="number" class = "order" name="uid[order]" maxlength="3" required/></div>';
						echo '</div>';
						echo '<div class="serial_lot" style="width: 100%;">';
						echo '<div class = "inputLabel" ><label>Сроки исполнения</label><input style="width: 12em;" type="date" class = "deadline" name="deadline"  required/></div>';
						echo '<div class = "inputLabel"><label>Получатель</label><input type="text" class="recipient" name="recipient" maxlength="100" required/></div>';
						echo '</div>';
						echo '<div id = "contCreateOrder">';
						echo '</div>';
						if ($root ==  "createorder"){//заменить на реальные права того, кто создает заказ
							echo '<button type = "button" id = "addBtn"><p>+</p><p id = "addItemsCreateOrder">Добавить</p></button>';
							echo '<input class = "orders_hide_input_old_year" name = "uid[oldYear]" style = "display:none"></input>';
							echo '<input class = "orders_hide_input_old_order" name = "uid[oldOrder]"style = "display:none"></input>';
						}
						if ($root == "addserial")//заменить на реальные права того, кто принимает заказ и вписывает серийные номера
							echo '<div class = "inputLabel"><button type = "button" id = "orders_acceptBtn" style = "display:none">Принять</button></div>';
						echo '<input class = "orders_hide_input" name = "action" style = "display:none"></input>';
						echo '<div class = "inputLabel"><button type = "submit" id="savedata" style = "display:none">Сохранить данные</button></div>';
						echo "<p class=\"msg_orderItems\" id = \"err1\" style = \"display:none\">Красным цветом подсвечены поля с недопустимым значением</p>";
						echo "<p class=\"msg_orderItems\" id = \"err2\" style = \"display:none\">Сериный номер, который вы пытаетесь изменить <br> либо не существует в базе, либо таких номеров <br>несколько. Просьба обратиться к разработчику</p>";
						echo "<p class=\"msg_orderItems\" id = \"err3\">Номер закакза, на который вы хотите заменить текущий,<br> уже существует в базе заказов</p>";
						echo "<p class=\"msg_orderItems\" id = \"err4\">В базе такой номер уже существует</p>";	
						echo "<p class=\"msg_orderItems\" id = \"err5\">В базе таких номеров несколько. Просьба обратиться к разработчику</p>";
						echo "<p class=\"msg_orderItems\" id = \"err6\">Заказ был принят производством</p>";
						echo "<p class=\"msg_orderItems\" id = \"err7\">Статус заказа не позволяет его принять</p>";
						echo "<p class=\"msg_orderItems\" id = \"err8\">Статус заказа не позволяет изменить его данные</p>";
						echo "<p class=\"msg_orderItems\" id = \"err9\"></p>";
						echo "<p class=\"msg1_orderItems\" id = \"ok\" style = \"display:none\">Данные успешно занесены</p>";
					?>
				</form>
			</div>
			<table class="table" align="center" style = "width:unset">
				<caption>Заказы</caption>
				<?php
						if (!isset($_POST['page']))
							$_POST['page'] = 1;
						if (empty($_POST['maxrows'])){
							if (empty($_POST['maxrowsHide']))
								$_POST['maxrows'] = 20;
							else
								$_POST['maxrows'] = $_POST['maxrowsHide'];
						}
						echo '<input type = "hidden" name = "maxrowsHide" form = "myform" value = "'.htmlspecialchars($_POST['maxrows']).'" >';
						$result = mysqli_query($link, "select count(*) as lot from `orders`");
						$all_rows = mysqli_fetch_row($result)[0];
						$pages = ((floor($all_rows/$_POST['maxrows'])) + 1);
						if($all_rows%$_POST['maxrows'] == 0)
							$pages = floor($all_rows/$_POST['maxrows']);
						if ($_POST['page'] == 1)
							$view_rows = 0;
						else 
							$view_rows = ($_POST['page'] - 1) * $_POST['maxrows'];
						$arrMaxrows = array("20", "30", "50", "100");
						if ($root ==  "createorder")//заменить на реальные права того, кто создает заказ
							echo '<tr><td colspan = "5" id = "createNewOrder"><span id = "createNewOrderPlus">+</span><span id = "createNewOrderText">Создать заказ<span></td></tr>';
						echo '<tr>';
						for ($i = 0; (!empty($columnNameRu[$i])); $i++)
							echo '<td>'.$columnNameRu[$i].'</td>';
						echo '</tr>';
						$result = mysqli_query($link, "SELECT id, DATE(`datetime`) as date, deadline, status, recipient FROM `orders` order by datetime desc limit ".$view_rows.", ".$_POST['maxrows']."");
						paintRowOrder($result, $columnName, $replace, false);
				?>
			
				<?php
				echo '<tr><td  colspan = "5" style = "background:white; border: none;">';
					echo '<div class= "pagination">';
						for ($j = 1; $j <= $pages; $j++)
						{
							if(($j==1)||($j==$pages)||(abs($_POST['page']-$j) < 6))
							{
								echo '<input type = "submit" name = "page" form = "myform" class = "pagBtn" value = "'.htmlspecialchars($j).'"';
							if ($_POST['page'] == $j)
								echo ' id = "pagBtnActive"';
							echo '">';
							}
							if(($j!=1)&&($j!=$pages)&&(abs($_POST['page']-$j) == 6)) echo ' <a> ... </a> ';
						}
						echo '</div>';
						echo '<div id = "maxrows">';
						for ($i=0; isset($arrMaxrows[$i]); $i++)
						{
							echo '<button type = "submit" form = "myform" class = "maxrowsBtn" name = "maxrows"';
							if (isset($_POST['maxrows']) && $_POST['maxrows'] == $arrMaxrows[$i])
								echo 'id = "activeMaxRows"';
							echo 'value = "'.htmlspecialchars($arrMaxrows[$i]).'">'.htmlspecialchars($arrMaxrows[$i]).'</button>|';
						}
					echo '</div></td></tr>';
				?>	
				</table>
		</div>
		<?php createFooter();?>
	</body>
	<script>
	$(document).on("focus",".type",function(e) {
		if ( !$(this).data("autocomplete")){
			$(this).data("autocomplete", true);
			$(this).autocompleteArray(
			<?php
				$result = mysqli_query($link, "select distinct `type` from `list_of_products` where `type` != ''");
				$type = mysqli_fetch_all($result);
				echo json_encode($type); 
			?>
			,
				{
					delay:10,
					minChars:1,
					matchSubset:1,
					autoFill:true,
					maxItemsToShow:10
				}
			);
		}
	})
	$(document).on("focus",".name",function(e) {
		if ( !$(this).data("autocomplete")){
			$(this).data("autocomplete", true);
			$(this).autocompleteArray(
			<?php
			$result = mysqli_query($link, "select distinct `name` from `list_of_products` where `name` != ''");
			$name = mysqli_fetch_all($result);
			echo json_encode($name); 
			?>
			,
				{
					delay: 10,
					minChars:0,
					matchSubset:1,
					autoFill:true,
					maxItemsToShow:10
				}
			);
		}
	})
	function addItems(val, option){
		if (option == 0){
			$("#contCreateOrder").append('<div class = "serial_lot"><div class = "inputLabel">\
			<label>Тип</label><input style="width: 12em;" type="text" class = "type" name = "type[]" maxlength = "100" value = "'+val[0]+'" required/></div>\
			<div class = "inputLabel"><label>Название</label><input type = "text" class = "name" name = "name[]" maxlength = "100" value = "'+val[1]+'" required/></div>\
			<div class = "inputLabel"><label id = "lotCrOrderLab">шт.</label><input class="lotCrOrder" type="text" name="lot[]" maxlength="3" value = "'+val[2]+'" required></div>\
			</div>');
		}
		else if (option == 1){
			if (val == false){
				val = [];
				val[0] = '';
				val[1] = '';
				val[2] = 1;
			}
			$("#contCreateOrder").append('<div class = "serial_lot"><div class = "inputLabel">\
			<label>Тип</label><input style="width: 12em;" type="text" class = "type" name = "type[]" maxlength = "100" value = "'+val[0]+'" required/></div>\
			<div class = "inputLabel"><label>Название</label><input type = "text" class = "name" name = "name[]" maxlength = "100" value = "'+val[1]+'" required/></div>\
			<div class = "inputLabel"><label id = "lotCrOrderLab">шт.</label><input class="lotCrOrder" type="text" name="lot[]" maxlength="3" value = "'+val[2]+'" required></div>\
			<div class = "inputLabel"><button type = "button" class = "delBtn"><img  id="createOrderBascketImg" src="images/basket.png" align="center"></button></div>\
			</div>');
			if ($("#contCreateOrder .serial_lot").eq(-1).index() == 0)
				$("#contCreateOrder .serial_lot:eq(0) button").css({'display':'none'});
			else if ($("#contCreateOrder .serial_lot:eq(0) button").css('display') == 'none')
				$("#contCreateOrder .serial_lot:eq(0) button").css({'display':'block'});
		}
		else if(option == 2){
		if (val[2] == null)
			val[2] = '';
		$("#contCreateOrder").append('<div class = "serial_lot"><div class = "inputLabel">\
			<label>Тип</label><input style="width: 12em;" type="text" class = "type" name = "type[]" maxlength = "100" value = "'+val[0]+'" readonly required/></div>\
			<div class = "inputLabel"><label>Название</label><input type = "text" class = "name" name = "name[]" maxlength = "100" value = "'+val[1]+'" readonly required/></div>\
			<div class = "inputLabel"><label>Серийный номер</label><input type = "text" class = "serial" name = "serial[]" maxlength = "100" value = "'+val[2]+'"/></div>\
			</div>');
		}

	}
	$("#addBtn").click(function (){
		addItems(false, 1);
	});
	$(document).on("focus", "input.lotCrOrder", function (){
		$(this).val('');
	});
	$(document).on("click", "#createNewOrder", function(){
		addItems(false, 1);
		$(".orders_hide_input").val('create');
		$("#createOrderForm").css({"display":"block"});
		$("#savedata").css({"display":"block"});
		$("input").prop("readonly", false);
		$(".year").prop("readonly", true);

	})
	function show_item(id, status){
		if (status==0)	$('#'+id).animate({ height: "hide"}, "hide");
		else $('#'+id).animate({ height: "show" }, "slow");
	}
	$(document).on("click", ".delBtn", function(){
		if ($(this).parent().parent().index() == 0 && $("#contCreateOrder .serial_lot").eq(-1).index() == 1)
			$("#contCreateOrder .serial_lot:eq(1) button").css({'display':'none'});
		else if ($(this).parent().parent().index() == 1 && $("#contCreateOrder .serial_lot").eq(-1).index() == 1)
			$("#contCreateOrder .serial_lot:eq(0) button").css({'display':'none'});
		$(this).parent().parent().remove();
	});
	$(document).on("click", ".createOrderCloser", function(){
		$("#active").removeAttr("id");
		$("#createOrderForm").css({"display":"none"});
		$("#contCreateOrder").children().remove();
		$(".msg_orderItems, .msg1_orderItems").css({"display":"none"});
		$(".year").val((new Date).getFullYear());
		$(".order, .deadline, .recipient").val("");
		$(".order, .deadline, .recipient").prop("readonly", true);
		$('input').css({"border": "1px solid #CED4DA"});
		$("#savedata").css({"display":"none"});
		$("#orders_acceptBtn").css({"display":"none"});
	});
	$(document).on("click", ".orderItemsView", function(){
		indexThisRow = $(this).parent().parent().index();
		$(this).parent().parent().attr("id", "active");
		$(".orders_hide_input").val('change');
		$.ajax({
			type: "POST",
			url: "ordersItemsView.php",
			data: {"serial":$(this).text()},
			success: function(response){
				console.log(response);
				jsonData = JSON.parse(response);
				if (jsonData.user == 1){
					for(i = 0; i != jsonData.items.length; i++)
						addItems(jsonData.items[i], 1);
					if (jsonData.status == 'created'){
						$("#addBtn, #savedata").css({"display":"block"});
						$("input").prop("readonly", false);
						$(".year").prop("readonly", true);
						$(".orders_hide_input_old_year").val($(".table tr:eq("+indexThisRow+") .orderItemsView").text().substr(0,4));
						$(".orders_hide_input_old_order").val($(".table tr:eq("+indexThisRow+") .orderItemsView").text().substr(4,7));
					}
					else{
						$("#addBtn, .delBtn, #savedata").css({"display":"none"});
						$("input").prop("readonly", true);
					}
				}
				else if (jsonData.user == 2 && jsonData.status == "created"){
					for(i = 0; i != jsonData.items.length; i++)
						addItems(jsonData.items[i], 0);
					$("#orders_acceptBtn").css({"display":"block"});
					$("input").prop("readonly", true);
				}
				else if (jsonData.user == 2 && jsonData.status == "accept"){
					for(i = 0; i != jsonData.items.length; i++)
						addItems(jsonData.items[i], 2);
					$("#savedata").css({"display":"block"});
					$("input").prop("readonly", true);
					$(".serial").prop("readonly", false);
				}
				$(".year").val($(".table tr:eq("+indexThisRow+") td:eq(0)").text().substr(0,4));
				$(".order").val(parseInt($(".table tr:eq("+indexThisRow+") td:eq(0)").text().substr(4,7)));
				$(".deadline").val($(".table tr:eq("+indexThisRow+") td:eq(2)").text());
				$(".recipient").val($(".table tr:eq("+indexThisRow+") td:eq(4)").text());
				$("#createOrderForm").css({"display":"block"});
			}
		})
	})
	$("#orders_acceptBtn").on("click", function(){
		$.ajax({
			type: "POST",
			url: 'handler.php',
			data: {"uid[year]":$(".year").val(),
			"uid[order]":$(".order").val(),
			"action":"accept",
			},
			success: function(response){
				console.log(response);
				var jsonData = JSON.parse(response);
				if (jsonData.result == 'ok')
				{
					$("#orders_acceptBtn").css({"display":"none"});
					$("#savedata").css({"display":"block"});
					$.ajax({
						type: "POST",
						url: "ordersItemsView.php",
						data: {"serial":$(".year").val()+$(".order").val()},
						success: function(response){
							console.log(response);
							jsonData = JSON.parse(response);
							if (jsonData.user == 2 && jsonData.status == "accept"){
								$("#contCreateOrder").children().remove();
								for(i = 0; i != jsonData.items.length; i++)
									addItems(jsonData.items[i], 2);
							}
						}
					})
				}
				else if (jsonData.err == 1.8)
					$('#err7').css({'display':'block'});

			}
		})
	})
	$(".form1").on("submit", function(e){
		$('input').css({"border": "1px solid #CED4DA"});
		$(".msg_orderItems, .msg1_orderItems").css({"display":"none"});
		$.ajax({
			type: "POST",
			url: 'handler.php',
			data: $(".form1").serialize(),
			success: function(response){
				console.log(response);
				var jsonData = JSON.parse(response);
				if (jsonData.result == "ok")
					$(".msg1_orderItems").css({"display":"block"});
				if (jsonData.user == 1)
				{
					if (jsonData.result == "ok"){
						if (jsonData.action == "create")
							$(".table tr:eq(1)").after("<tr><td><div class='orderItemsView'>"+$(".year").val()+$(".order").val()+"</div>\
							</td><td>"+new Date().getFullYear()+'-'+((new Date().getMonth()+1)<10 ? '0': '')+(new Date().getMonth()+1)+'-'+(new Date().getDate()<10 ? '0\
							': '')+new Date().getDate()+"</td><td>"+$(".deadline").val()+"</td><td>Создан</td><td>"+$(".recipient").val()+"</td></tr>");
						if (jsonData.action == "change"){
							$("#active .orderItemsView").text($(".year").val()+$(".order").val());
							$("#active td:eq(1)").text(new Date().getFullYear()+'-'+((new Date().getMonth()+1)<10 ? '0': '')+(new Date().getMonth()+1)+'-'+(new Date().getDate()<10 ? '0\
							': '')+new Date().getDate());
							$("#active td:eq(2)").text($(".deadline").val());
							$("#active td:eq(3)").text("Создан");
							$("#active td:eq(3)").text($(".recipient").val());
						$(".orders_hide_input_old_year").val($(".year").val());
						$(".orders_hide_input_old_order").val($(".order").val());
						}
						$("#active").removeAttr("id");
						$('.msg1').css({'display':'block'});
					}
					else if (jsonData.name || jsonData.type){
							$('#err1').css({'display':'block'});
						if (jsonData.name){
							i = 0;
							$('.name').each(function(index){
								if (jsonData.name[i] == index){
									$(this).css({'border': ' 0.1em solid red'});
									i++;
								}
							});
						}
						if (jsonData.type){
							i = 0;
							$('.type').each(function(index){
								if (jsonData.type[i] == index){
									$(this).css({'border': ' 0.1em solid red'});
									i++;
								}
							});
						}
					}
				}
				else if (jsonData.user == 2){
					if (jsonData.result == "ok")
						$('.msg1').css({'display':'block'});
					else if (jsonData.serial){
						i = 0;
						$('.serial').each(function(index){
							if (jsonData.serial[i] == index){
								$(this).css({'border': ' 0.1em solid red'});
								i++;
							}
						});
						$('#err1').css({'display':'block'});
					}
				}
				else if (jsonData.err){
					if (jsonData.err >= 1.2 && jsonData <= 1.6)
						$('.order').css({'border': ' 0.1em solid red'});
					if (jsonData.err == 1.1 || jsonData.err == 1.2)
						$('#err1').css({'display':'block'});
					else if (jsonData.err == 1.4)
						$('#err3').css({'display':'block'});
					else if (jsonData.err == 1.5)
						$('#err4').css({'display':'block'});
					else if (jsonData.err == 1.6)
						$('#err5').css({'display':'block'});
					else if (jsonData.err == 1.7)
						$('#err6').css({'display':'block'});
					else if (jsonData.err == 1.9)
						$('#err8').css({'display':'block'});
					else if (jsonData.errDB){
						$('#err9').text(jsonData.errDB);
						$('#err9').css({'display':'block'});
					}
				}
			}
		})
		e.preventDefault();
	})
	</script>
</html>