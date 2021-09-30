<?php
    session_start();
    require_once 'lib/main.lib.php';
    $link = connect();
    $root = array(0 => "addserial", 1 => "createorder");//заменить на реальные права
    $root = checkRoot($link, $root, true);
    if (!empty($_POST['uid']['year']) && !empty($_POST['uid']['order']))
        {
            if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false || preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false){
                if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false)
                    echo (json_encode(array("err" => 1.1)));//Красным подсвечены с недоп.значением
                if (preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false)
                    echo (json_encode(array("err" => 1.2)));//Красным подсвечены с недоп.значением
            }
            else{
                if($_POST["action"] != "create"){//заменить на реальные права (vereshagyen order)
                    if ($root == 'createorder' && $_POST["action"] == 'change')
                        $result = mysqli_query($link, "select `status` from `orders` where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['oldYear'].$_POST['uid']['oldOrder'])."'");
                    else
                        $result = mysqli_query($link, "select `status` from `orders` where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                    if (!$result)
                        die (json_encode(array("errDB" => 'Ошибка запроса 1: mysqli_query '.mysqli_error($link))));
                    else
                        $status = mysqli_fetch_row($result)[0];
                }
                $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."'");
                if (!$result)
                    die (json_encode(array("errDB" => 'Ошибка запроса 2: mysqli_query '.mysqli_error($link))));
                else{
                    $id_lot = mysqli_num_rows($result);
                    if ($root == "createorder"  && (!empty($_POST['type'][0]) && !empty($_POST['name'][0]))){//заменить на реальные права (vereshagyen order)
                        if ($_POST["action"] == "change" && $status == "created" && ($_POST['uid']['oldYear'] != $_POST['uid']['year'] || $_POST['uid']['oldOrder'] != $_POST['uid']['order'])){
                            $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['oldYear'].$_POST['uid']['oldOrder'])."'");
                            if (!$result)
                                die (json_encode(array("errDB" => 'Ошибка запроса 3: mysqli_query '.mysqli_error($link))));
                            else{
                                $id_lot_old = mysqli_num_rows($result);
                                if ($id_lot_old != 1)
                                    die (json_encode(array("err" => 1.3)));//Сериного номера, который вы пытаетесь изменить либо не существует в базе, либо таких номеров несколько. Просьба обратиться к разработчику
                                if ($id_lot != 0)
                                    die (json_encode(array("err" => 1.4)));//Серийный номер, на который вы хотите изменить существующий уже существует в базе заказов
                            }
                        }
                        if (($_POST["action"] == "change" && $status == "created") || $id_lot == 0){
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
                                echo (json_encode(array('user' => 1, 'type' => $emptyTypeFromDB, 'name' => $emptyNameFromDB)));//Красным цветом
                            else{
                                if ($_POST["action"] == "change"){
                                    if (isset($id_lot_old)){
                                        $result = mysqli_query($link, "delete from `order-items` where `order_id` = '".mysqli_real_escape_string($link, $_POST['uid']['oldYear'].$_POST['uid']['oldOrder'])."'");
                                        if (!$result)
                                            die (json_encode(array("errDB" => 'Ошибка запроса 4: mysqli_query '.mysqli_error($link))));
                                        else{
                                            $result = mysqli_query($link, "update `orders` SET `deadline` = '".mysqli_real_escape_string($link, $_POST['deadline'])."', `recipient` = '".mysqli_real_escape_string($link, $_POST['recipient'])."', `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'  where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['oldYear'].$_POST['uid']['oldOrder'])."'");
                                            if (!$result)
                                                die (json_encode(array("errDB" => 'Ошибка запроса 5: mysqli_query '.mysqli_error($link))));
                                        }
                                    }
                                    else{
                                        $result = mysqli_query($link, "delete from `order-items` where `order_id` = '".mysqli_real_escape_string($link, $_POST['uid']['oldYear'].$_POST['uid']['oldOrder'])."'");
                                        if (!$result)
                                            die (json_encode(array("errDB" => 'Ошибка запроса 6: mysqli_query '.mysqli_error($link))));
                                        else
                                            $result = mysqli_query($link, "update `orders` SET `deadline` = '".mysqli_real_escape_string($link, $_POST['deadline'])."', `recipient` = '".mysqli_real_escape_string($link, $_POST['recipient'])."' where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                                    }
                                }
                                else if ($_POST["action"] == "create")
                                    $result = mysqli_query($link, "insert into `orders` (`id`, `datetime`, `deadline`, `recipient`) values ('".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."', NOW(), '".mysqli_real_escape_string($link,$_POST['deadline'])."', '".mysqli_real_escape_string($link,$_POST['recipient'])."')");
                                if (!$result){
                                    die (json_encode(array("errDB" => 'Ошибка запроса 7: mysqli_query '.mysqli_error($link))));
                                }
                                $str = 'insert into `order-items` (`order_id`, `type`, `name`) values ';
                                $i = 0;
                                while (!empty($_POST['type'][$i])){
                                    $str = $str . ' ( \''.mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order']).'\', \''.mysqli_real_escape_string($link,$_POST['type'][$i]).'\', \''.mysqli_real_escape_string($link,$_POST['name'][$i]).'\')';
                                    if ($_POST['lot'][$i] > 1)
                                        $_POST['lot'][$i]--;
                                    else
                                        $i++;
                                    if (!empty($_POST['type'][$i]))
                                        $str = $str . ',';
                                }
                                if (!(mysqli_query($link, $str)))
                                    die (json_encode(array("errDB" => 'Ошибка запроса 8: mysqli_query '.mysqli_error($link))));
                                else
                                    echo (json_encode(array("result" => "ok", "action" => $_POST["action"], "user" => 1)));
                            }
                        }
                        else if ($_POST['action'] == 'create' && $id_lot == 1)
                            echo (json_encode(array("err" => 1.5)));//в базе такой номер уже существует
                        else if ($_POST['action'] == 'create' && $id_lot > 0)
                            echo (json_encode(array("err" => 1.6)));//в базе таких номеров несколько. Просьба обратиться к разработчику
                        else if ($_POST["action"] == "change" && $status == "accept")
                                echo (json_encode(array("err" => 1.7)));//Заказ был принят производством
                    }
                    else if ($root == 'addserial' && $id_lot == 1){//заменить на реальные права (olga order)
                        if ($_POST['action'] == 'accept'){
                            $result = mysqli_query($link, "select `status` from `orders` where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                            if (!$result)
                                die (json_encode(array("errDB" => 'Ошибка запроса 9: mysqli_query '.mysqli_error($link))));
                            else if ($status == 'created'){
                                $result = mysqli_query($link, "update `orders` set `status` =  'accept' where `id` = '".$_POST['uid']['year'].$_POST['uid']['order']."'");
                                if (!$result)
                                    die (json_encode(array("errDB" => 'Ошибка запроса 10: mysqli_query '.mysqli_error($link))));
                                else
                                    die (json_encode(array("result" => "ok")));
                            }
                            else
                                die (json_encode(array("err" => 1.8)));//Статус заказа не позволяет его принять
                        }
                        else if ($_POST['action'] == 'change'){
                            $emptySerialFromDB = array();
                            $j = 0;
                            for ($i = 0; !empty($_POST['type'][$i]); $i++)
                            {
                                if (!empty($_POST['serial'])){
                                $result = mysqli_query($link, "select `serial` from products where `serial` = '".mysqli_real_escape_string($link,$_POST['serial'][$i])."'");
                                $row = mysqli_num_rows($result);
                                if ($row == 0)
                                    $emptySerialFromDB[$j++] = $i;
                                }
                            }
                            if (!empty($emptySerialFromDB))
                                echo (json_encode(array('user' => 2, 'serial' => $emptySerialFromDB)));
                            else if ($status == 'accept'){
                                $result = mysqli_query($link, "delete from `order-items` where order_id = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                                if (!$result)
                                    echo (json_encode(array("errDB" => 'Ошибка запроса 11: mysqli_query '.mysqli_error($link))));
                                else{
                                    $str = 'insert into `order-items` (`order_id`, `type`, `name`, `serial`) values ';
                                    $i = 0;
                                    while (!empty($_POST['type'][$i])){
                                        if (empty($_POST['serial'][$i]))
                                            $str = $str . ' ( \''.mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order']).'\', \''.mysqli_real_escape_string($link,$_POST['type'][$i]).'\', \''.mysqli_real_escape_string($link,$_POST['name'][$i]).'\', NULL)';
                                        else
                                            $str = $str . ' ( \''.mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order']).'\', \''.mysqli_real_escape_string($link,$_POST['type'][$i]).'\', \''.mysqli_real_escape_string($link,$_POST['name'][$i]).'\', \''.mysqli_real_escape_string($link,$_POST['serial'][$i]).'\')';
                                        $i++;
                                        if (!empty($_POST['type'][$i]))
                                            $str = $str . ',';
                                    }
                                    if (!(mysqli_query($link, $str))){
                                        echo (json_encode(array("errDB" => 'Ошибка запроса 12: mysqli_query '.mysqli_error($link))));
                                    }
                                    else
                                        echo (json_encode(array('user' => 2, "result" => "ok")));
                                }
                            }
                            else
                                echo (json_encode(array("err" => 1.9)));//Статус заказа не позволяет изменить его данные
                        }
                    }
                }
            }
        }
?>