<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
$root = array(0 => "ordercreate", 1 => "accept");
$root = checkRoot($link, $root, true);
$columnName = array ( "id", "date", "deadline", "status", "recipient");
$columnNameRu = array ( "№ Заказа", "Дата создания", "Срок исполнения", "Статус", "Получатель");
$replace = array ("no" => "Нет", "yes" => "Да");
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
				<form method="post" align="left" class="form1" style = "display:flex">
					<div id="priem_name" align="center">Формирование заказа<div class = "createOrderCloser">✕</div></div>
					<?php
						echo '<div class="serial_lot" style="width: 100%;">';
						echo '<div class = "inputLabel" ><label>Год</label><input style="width: 12em;" class = "year" type="text" name="uid[year]" maxlength="4" value = "'.date ( 'Y' ).'"  readonly/> </div>';
						echo '<div class = "inputLabel"><label>Номер заказа</label><input type="number" class = "order" name="uid[order]" maxlength="3"  required/></div>';
						echo '</div>';
						echo '<div class="serial_lot" style="width: 100%;">';
						echo '<div class = "inputLabel" ><label>Сроки исполнения</label><input style="width: 12em;" type="date" class = "deadline" name="deadline"  required/></div>';
						echo '<div class = "inputLabel"><label>Получатель</label><input type="text" class="recipient" name="recipient" maxlength="100" required/></div>';
						echo '</div>';
						echo '<div id = "contCreateOrder">';
						echo '</div>';
						echo '<button type = "button" id = "addBtn"><p>+</p><p id = "addItemsCreateOrder">Добавить</p></button>';
						if ($root == "ordercreate" || $root ==  "accept"){//заменить на реальные права
							echo '<input class = "orders_hide_input" name = "action"></input>';
							echo '<div class = "inputLabel"><button type = "submit" id="savedata">Сохранить данные</button></div>';
						}
						echo "<p class=\"msg_orderItems\" id = \"err1\" style = \"display:none\">Красным цветом подсвечены поля с недопустимым значением</p>";
						echo "<p class=\"msg_orderItems\" id = \"err2\" style = \"display:none\">Серийный номер уже существует в базе</p>";
						echo "<p class=\"msg_orderItems\" id = \"err3\" style = \"display:none\">В базе существует несколько таких серийных номеров.<br>Просьба обратиться к разработчику</p>";
						echo "<p class=\"msg_orderItems\" id = \"err4\"></p>";
						echo "<p class=\"msg1_orderItems\" id = \"ok\" style = \"display:none\">Данные успешно занесены</p>";
					?>
				</form>
			</div>
			<table class="table" align="center" style = "width:unset">
				<caption>Заказы</caption>
				<?php
					echo '<tr><td colspan = "5" id = "createNewOrder"><span id = "createNewOrderPlus">+</span><span id = "createNewOrderText">Создать заказ<span></td></tr>';
					echo '<tr>';
					for ($i = 0; (!empty($columnNameRu[$i])); $i++)
						echo '<td>'.$columnNameRu[$i].'</td>';
					echo '</tr>';
					$result = mysqli_query($link, "select * from orders");
					paintRowOrder($result, $columnName, $replace, false);
					echo "<tr style = 'height: 5px;' ></tr>";
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
					select:function(){
						console.log("aaaa");
					},
					delay: 10,
					minChars:0,
					matchSubset:1,
					autoFill:true,
					maxItemsToShow:10
				}
			);
		}
	})
	function addItems(val){
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
	$("#addBtn").click(function (){
		addItems(false);
	});
	$(document).on("focus", "input.lotCrOrder", function (){
		$(this).val('');
	});
	$(document).on("click", "#createNewOrder", function(){
		addItems(false);
		$(".orders_hide_input").val('save');
		$("#createOrderForm").css({"display":"block"});
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
		$("#createOrderForm").css({"display":"none"});
		$("#contCreateOrder").children().remove();
		$(".msg_orderItems, .msgOrderItems1").css({"display":"none"});
		$(".year").val((new Date).getFullYear());
		$(".order, .deadline, .recipient").val("");
		$(".order, .deadline, .recipient").prop("readonly", true);
		$('input').css({"border": "1px solid #CED4DA"});
	});
	$(document).on("click", ".orderItemsView", function(){
		indexThisRow = $(this).parent().parent().index();
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
						addItems(jsonData.items[i]);
					if (jsonData.status == 'created'){
						$("#addBtn, #savedata").css({"display":"block"});
						$("input").prop("readonly", false);
					}
					else{
						$("#addBtn, .delBtn, #savedata").css({"display":"none"});
						$("input").prop("readonly", true);
					}
				}
				$(".year").val($(".table tr:eq("+indexThisRow+") td:eq(0)").text().substr(0,4));
				$(".order").val($(".table tr:eq("+indexThisRow+") td:eq(0)").text().substr(4,7));
				$(".deadline").val($(".table tr:eq("+indexThisRow+") td:eq(2)").text());
				$(".recipient").val($(".table tr:eq("+indexThisRow+") td:eq(4)").text());
				$(".order, .deadline, .recipient").prop("readonly", true);
				$("#createOrderForm").css({"display":"block"});
			}
		})
	})
	$(".form1").on("submit", function(e){
		$('.msg').css({'display':'none'});
		$('.msg1').css({'display':'none'});
		$('input').css({"border": "1px solid #CED4DA"});
		$.ajax({
			type: "POST",
			url: 'handler.php',
			data: $(".form1").serialize(),
			success: function(response){
				console.log(response);
				var jsonData = JSON.parse(response);
				if (jsonData.save == "ok")
				{
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
				else if (jsonData.err == 1.1)
				{
					$('#err1').css({'display':'block'});
					$('.year').css({'border': ' 0.1em solid red'});
				}
				else if (jsonData.err == 1.2)
				{
					$('#err1').css({'display':'block'});
					$('.order').css({'border': ' 0.1em solid red'});
				}
				else if (jsonData.err == 2.1)
				{
					$('#err2').css({'display':'block'});
					$('.order').css({'border': ' 0.1em solid red'});
				}
				else if (jsonData.err == 2.2)
				{
					$('#err3').css({'display':'block'});
					$('.order').css({'border': ' 0.1em solid red'});
				}
				else if (jsonData.errDB)
				{
					$('#err4').text(jsonData.errDB);
					$('#err4').css({'display':'block'});
				}
			}
		})
		e.preventDefault();
	})
	</script>
</html>