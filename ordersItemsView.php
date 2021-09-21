<?php
    session_start();
    require_once 'lib/main.lib.php';
    $link = connect();
    $root = array(0 => "ok", 1 => "accept");//здесь должны быть права для того, кто создает заказы и добавляет серийные номера
    $root = checkRoot($link, $root, true);
    if (preg_match('/^[0-9]{7}$/',$_POST['serial']) == false)
        echo (json_encode(array("err" => 1.1)));
    else{
        $result = mysqli_query($link, "select `status` from `orders` where `id` = '".$_POST['serial']."'");
        if (!$result)
                echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
        else{
            $status = mysqli_fetch_row($result)[0];
            if ($root == "accept")//здесь должны быть права для того, кто создает заказы
                    $root = 1;
            else if ($root == "otk")//здесь должны быть права для того, кто вносит серийнийки
                $root = 2;
            if ($status == 'created' || $root == "accept")//здесь должны быть права для того, кто создает заказы
                $result = mysqli_query($link, "select type, name, count(type) as duplicates from `order-items`  where `order_id` = '".$_POST['serial']."' group by type, name");
            else if ($status == "accept")//здесь должны быть права для того, кто вносит серийнийки
                $result = mysqli_query($link, "select `type`, `name`, `serial` from `order-items` where `order_id` = '".$_POST['serial']."'");
            if (!$result)
                echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
            else
                echo (json_encode(array("status" => $status, "user" => $root, "items" => mysqli_fetch_all($result))));
        }
        /*
            $result = mysqli_query($link, "(select `type`, `name` from `order-items` where `order_id` = '".$_POST['serial']."'");
        if (!$result)
            echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
        else{
            echo (json_encode($row = mysqli_fetch_all($result)));
        }*/
        
        
        
    }
    /*if (!empty($_POST['uid']['year']) && !empty($_POST['uid']['order']) && !empty($_POST['deadline']) && !empty($_POST['recipient']))
        {
            if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false)
                echo (json_encode(array("err" => 1.1)));
            if (preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false)
                echo (json_encode(array("err" => 1.2)));
            else
            {
                if (preg_match('/^[0-9]{1}$/',$_POST['uid']['order']) == true)
                    $_POST['uid']['order'] = '00'.$_POST['uid']['order'];
                else if (preg_match('/^[0-9]{2}$/',$_POST['uid']['order']) == true)
                    $_POST['uid']['order'] = '0'.$_POST['uid']['order'];
                $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."'");
                if (!$result)
                    echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
                else if (mysqli_num_rows($result) == 0)
                {
                    $emptyTypeFromDB = array();
                    $j = 0;
                    for ($i = 0; !empty($_POST['type'][$i]); $i++)
                    {
                        $result = mysqli_query($link, "select `type` from list_of_products where `type` = '".mysqli_real_escape_string($link,$_POST['type'][$i])."'");
                        $row = mysqli_num_rows($result);
                        if ($row == 0)
                            $emptyTypeFromDB[$j++] = $i;
                    }
                    $emptyNameFromDB = array();
                    $j = 0;
                    for ($i = 0; !empty($_POST['name'][$i]); $i++)
                    {
                        $result = mysqli_query($link, "select `name` from list_of_products where `name` = '".mysqli_real_escape_string($link,$_POST['name'][$i])."'");
                        $row = mysqli_num_rows($result);
                        if ($row == 0)
                            $emptyNameFromDB[$j++] = $i;
                    }
                    if (!empty($emptyNameFromDB) || !empty($emptyTypeFromDB))
                        echo (json_encode(array('type' => $emptyTypeFromDB, 'name' => $emptyNameFromDB)));
                    else
                    {
                        $result = mysqli_query($link, "insert into `orders` (`id`, `date`, `deadline`, `recipient`) values ('".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."', NOW(), '".mysqli_real_escape_string($link,$_POST['deadline'])."', '".mysqli_real_escape_string($link,$_POST['recipient'])."')");
                        if (!$result)
                            echo json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link)));
                        else
                        {
                            $str = 'insert into `order-items` (`order_id`, `type`, `name`) values ';
                            $i = 0;
                            while (!empty($_POST['type'][$i]))
                            {
                                $str = $str . ' ( \''.mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order']).'\', \''.mysqli_real_escape_string($link,$_POST['type'][$i]).'\', \''.mysqli_real_escape_string($link,$_POST['name'][$i]).'\')';
                                if ($_POST['lot'][$i] > 1)
                                    $_POST['lot'][$i]--;
                                else
                                    $i++;
                                if (!empty($_POST['type'][$i]))
                                    $str = $str . ',';
                                }
                                if (!(mysqli_query($link, $str))){
                                    echo json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link)));
                                }
                                else
                                    echo json_encode(array("save" => "ok"));
                        }
                    }
                }
                else if (mysqli_num_rows($result) == 1)
                    echo (json_encode(array("err" => 2.1)));//в базе такой номер уже существует
                else if (mysqli_num_rows($result) > 1)
                    echo (json_encode(array("err" => 2.2)));//в базе таких номеров несколько
            }
        }
    /*
    require_once 'lib/main.lib.php';
    $link = connect();
  	
      $emptyNameFromDB = array();
      $j = 0;
      for ($i = 0; isset($_POST['name'][$i]); $i++)
      {
          $result = mysqli_query($link, "select `name` from list_of_products where `name` = '".$_POST['name'][$i]."'");
          $row = mysqli_num_rows($result);
          if ($row == 0)
              $emptyNameFromDB[$j++] = $i;
      }
        if (!empty($emptyNameFromDB) || !empty($emptyTypeFromDB))
            echo json_encode (array('type' => $emptyTypeFromDB, 'name' => $emptyNameFromDB));/*
        else
            {
                $str = 'insert into `order_items` (`order_id`, `type`, `name`) values ';
                $i = 0;
                while (!empty($_POST['type'][$i]))
                {
                    $str = $str . ' ( \''.$_POST['uid'].'\', \''.$_POST['type'][$i].'\', \''.$_POST['name'][$i].'\')';
                    if ($_POST['lot'][$i] > 1)
                        $_POST['lot'][$i]--;
                    else
                        $i++;
                    if (!empty($_POST['type'][$i]))
                        $str = $str . ',';
                }
                if (!(mysqli_query($link, $str))){
                    echo json_encode(array("str" => "errRecord"));
                }
                else
                    echo json_encode(array("str" => "ok"));
            }*/
        
?>