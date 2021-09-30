<?php
    session_start();
    require_once 'lib/main.lib.php';
    $link = connect();
    $root = array(0 => "addserial", 1 => "createorder");//здесь должны быть права для того, кто создает заказы и добавляет серийные номера
    $root = checkRoot($link, $root, true);
    if (preg_match('/^[0-9]{7}$/',$_POST['serial']) == false)
        echo (json_encode(array("err" => 1.1)));
    else{
        $result = mysqli_query($link, "select `status` from `orders` where `id` = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
        if (!$result)
                echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
        else{
            $status = mysqli_fetch_row($result)[0];
            if ($root == "createorder")//здесь должны быть права для того, кто создает заказы
                    $root = 1;
            else if ($root == "addserial")//здесь должны быть права для того, кто вносит серийнийки
                $root = 2;
            if ($status == 'created' || $root == 1)//здесь должны быть права для того, кто создает заказы{
                $result = mysqli_query($link, "select type, name, count(type) as duplicates from `order-items`  where `order_id` = '".mysqli_real_escape_string($link, $_POST['serial'])."' group by type, name");
            else if ($status == "accept")//здесь должны быть права для того, кто вносит серийнийки
                $result = mysqli_query($link, "select `type`, `name`, `serial` from `order-items` where `order_id` = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
            if (!$result)
                echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
            else
                echo (json_encode(array("status" => $status, "user" => $root, "items" => mysqli_fetch_all($result))));
        }
    }
?>