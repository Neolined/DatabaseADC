<?php
$array = array ( "uid", "type", "name", "perfomance", "serial", "enter", "date", "owner", "software", "location", "otk", "comment");

$row = array ("PHP" => "hol",
"MySQL" => "lol",
"Apache" => "lola");

$zamena = array ("ok" => "Прошло успешно",
"fail" => "Не успешно",
"notest" => "Не проверялось");

$i = 0;
while (!empty($array[$i]))
{
    if (!empty($zamena[$row[$array[$i]]]))
    echo $zamena[$row[$array[$i]]];
    else 
    $row[$array[$i]];
    echo " ";
    $i++;
}
/*
sortSelect("uid", "UID", "По возрастанию", "По убыванию");
sortSelect("type", "Тип", "А-Я", "Я-А");
sortSelect("name", "Имя", "A-Z", "Z-A");
sortSelect("perfomance", "Исполнение", "A-Z", "Z-A");
sortSelect("serial", "Серийный номер", "Прямой порядок", "Обратный порядок");
sortSelect("enter", "Вхождение", "Прямой порядок", "Обратный порядок");
sortSelect("date", "Дата", "Сначала", "С конца");
sortSelect("owner", "Владелец", "А-Я", "Я-А");
sortSelect("software", "ПО", "Прямой порядок", "Обратный порядок");
sortSelect("location", "Местонахождение", "А-Я", "Я-А");
sortSelect("otk", "ОТК", "А-Я", "Я-А");
sortSelect("comment", "Комментарий", "А-Я", "Я-А");*/
?>