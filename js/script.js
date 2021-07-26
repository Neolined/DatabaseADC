function show(show, hide, cont) {
    document.getElementById(cont).setAttribute("style", "opacity:1; transition: 1s;");
    document.getElementById(show).setAttribute("style", "display: none");
    document.getElementById(hide).setAttribute("style", "display: block");
    }
    function hide(show, hide, cont) {
    document.getElementById(cont).setAttribute("style", "display: none");
    document.getElementById(hide).setAttribute("style", "display: none"); 
    document.getElementById(show).setAttribute("style", "display: block");
    }

    function showCheckboxes(obj) {
      var checkboxes = document.getElementById(obj);
      if(getComputedStyle(checkboxes).display == 'none')
      {
        checkboxes.style.display = "block";
      }
      else
      {
        checkboxes.style.display = "none";
      }
    }
function checkAddress(checkbox, sort)
{
  if (checkbox.checked == true) { //если включаем чекбокс
    var check = document.getElementsByClassName(sort);
    for (var i=0; i<check.length; i++) { //проходим по всем чекбоксам
      check[i].checked = false; //выключаем их
    }
    checkbox.checked = true; //и включаем текущий (потому что до этого выключили все)
	}
}
function dateReg(datte){
  if (datte.match(/^\d{2}$/) !== null) {
    this.value = v + '/';
  } else if (datte.match(/^\d{2}\/\d{2}$/) !== null) {
    this.value = v + '/';
  }
}

function clearFilter()
{
    var check = document.getElementsByClassName('filter');
    for (var i=0; i<check.length; i++) { //проходим по всем чекбоксам
      check[i].checked = false; //выключаем их
    }
}  
function show_item(id, status)
{
  if (status==0)	$('#'+id).animate({ height: "hide"}, "hide");
  else $('#'+id).animate({ height: "show" }, "slow");
}
function tranPost(idInp,ser,post) 
{
  document.getElementById(idInp).value = ser;
  document.getElementById(post).submit();
}
jQuery(function($){
  $(document).mouseup(function (e){ // отслеживаем событие клика по веб-документу
    var block = $(".optionClassOrder"); // определяем элемент, к которому будем применять условия (можем указывать ID, класс либо любой другой идентификатор элемента)
    if (!block.is(e.target) // проверка условия если клик был не по нашему блоку
      && !$(".overSelect").is(e.target)) { // проверка условия если клик не по его дочерним элементам
      block.css("display","none"); // если условия выполняются - скрываем наш элемент
    }
  });
});