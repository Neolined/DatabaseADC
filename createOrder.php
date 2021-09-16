<?php
session_start();
require_once 'lib/main.lib.php';
$link = connect();
mysqli_set_charset($link, 'utf8');
print_r($_POST);
$error_s1 = 0;
$error_s2 = 0;
$error_s3 = 0;
$error_s4 = 0;
$error_n1 = 1;
$error_t1 = 1;
$suc= 0;
$decSer = 1;
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset=utf-8">
  <link rel="stylesheet" href="css/main.css"<?php echo(microtime(true).rand()); ?>>
  <title>Формирование заказа</title>
  <script src="js/jquery.js"></script>
  <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
 </head>
 <body>
<?php createHeader($link);?>
 <div id="forma">
		<form action="createOrder.php" method="post" align="left" class="form1">
		<div id="priem_name" align="center">Формирование заказа<div class = "createOrderCloser">✕</div></div>
			<?php
				echo '<div class="serial_lot" style="width: 100%;">';
				echo '<div class = "inputLabel" ><label>Год</label><input style="width: 12em;" class = "year" type="text" name="uid[year]" maxlength="4" value = "'.date ( 'Y' ).'"  readonly/> </div>';
				echo '<div class = "inputLabel"><label>Номер заказа</label><input type="number" class = "order" name="uid[order]" maxlength="3"  required/></div>';
				echo '</div>';
				echo '<div class="serial_lot" style="width: 100%;">';
				echo '<div class = "inputLabel" ><label>Сроки исполнения</label><input style="width: 12em;" type="date" name="deadline"  required/></div>';
				echo '<div class = "inputLabel"><label>Получатель</label><input type="text" name="recipient" maxlength="100" required/></div>';
				echo '</div>';
				echo '<div id = "contCreateOrder">';
				echo '<div class = "serial_lot">';
				echo '<div class = "inputLabel" ><label>Тип</label><input style="width: 12em;" type="text" class = "type" name="type[]" maxlength="100" required/></div>';
				echo '<div class = "inputLabel"><label>Название</label><input type="text" class = "name" name="name[]" maxlength="100"  required/></div>';
				echo '<div class = "inputLabel"><label id = "lotCrOrderLab">шт.</label><input class="lotCrOrder" type="text" name="lot[]" maxlength="3" value = "1"  required/></div>';
				echo '<div class = "inputLabel"><button type = "button" class = "delBtn"><img  id="createOrderBascketImg" src="images/basket.png" align="center"></button></div>';
				echo '</div></div>';
				echo '<button type = "button" id = "addBtn"><p>+</p><p id = "addItemsCreateOrder">Добавить</p></button>';
				echo '<div class = "inputLabel"><button type = "submit" id="savedata" name = "save">Сохранить данные</button></div>';
				echo "<p class=\"msg\" id = \"err1\" style = \"display:none\">Красным цветом подсвечены поля с недопустимым значением</p>";
				echo "<p class=\"msg\" id = \"err2\" style = \"display:none\">Серийный номер уже существует в базе</p>";
				echo "<p class=\"msg\" id = \"err3\" style = \"display:none\">В базе существует несколько таких серийных номеров.<br>Просьба обратиться к разработчику</p>";
				echo "<p class=\"msg\" id = \"err4\"></p>";
				echo "<p class=\"msg1\" id = \"ok\" style = \"display:none\">Данные успешно занесены</p>";
			?>
		<form>
 </div>
	<?php createFooter();?>
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
	</script>
		<script>
	$("#addBtn").click(function () {
		if ($(".delBtn:first").css("display") == "none")
			$(".delBtn:first").css({"display":"block"});
		$("#contCreateOrder").append('<div class = "serial_lot"><div class = "inputLabel">\
		<label>Тип</label><input style="width: 12em;" type="text" class = "type" name="type[]" maxlength="100" required/></div>\
		<div class = "inputLabel"><label>Название</label><input type="text" class = "name" name="name[]" maxlength="100"required/></div>\
		<div class = "inputLabel"><label id = "lotCrOrderLab">шт.</label><input class="lotCrOrder" type="text" name="lot[]" maxlength="3" value = 1 required></div>\
		<div class = "inputLabel"><button type = "button" class = "delBtn"><img  id="createOrderBascketImg" src="images/basket.png" align="center"></button></div>\
		</div>');
	});
	$(document).on("focus", "input.lotCrOrder", function (){
		$(this).val('');
	});
function show_item(id, status)
{
	if (status==0)	$('#'+id).animate({ height: "hide"}, "hide");
	else $('#'+id).animate({ height: "show" }, "slow");
}
$(".lotCrOrder").keypress(function(e) {
	if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
	return false;
}
});
$(document).on("click", ".delBtn", function(){
	if ($(this).parent().parent().index() == 0 && $("#contCreateOrder .serial_lot").eq(-1).index() == 1)
		$("#contCreateOrder .serial_lot:eq(1) button").css({'display':'none'});
	else if ($(this).parent().parent().index() == 1 && $("#contCreateOrder .serial_lot").eq(-1).index() == 1)
		$("#contCreateOrder .serial_lot:eq(0) button").css({'display':'none'});
	$(this).parent().parent().remove();
})
</script>
<script type="text/javascript">
$(".form1").on("submit", function(e){
	$('.msg').css({'display':'none'});
	$('.msg1').css({'display':'none'});
	$('input').css({"border": "1px solid #CED4DA"});
	$.ajax({
		type: "POST",
		url: 'handler.php',
		data: $(".form1").serialize(),
		success: function(response)
		{
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
						if (jsonData.name[i] == index)
						{
							$(this).css({'border': ' 0.1em solid red'});
							i++;
						}
					});
				}
				if (jsonData.type){
					i = 0;
					$('.type').each(function(index){
						if (jsonData.type[i] == index)
						{
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
 </body>
</html>