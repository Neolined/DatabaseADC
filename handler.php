<?php
    session_start();
    require_once 'lib/main.lib.php';
    $link = connect();
    $root = array(0 => "ok", 1 => "accept");//заменить на реальные права
    $root = checkRoot($link, $root, true);
    if (!empty($_POST['uid']['year']) && !empty($_POST['uid']['order']) && !empty($_POST['type'][0]) && !empty($_POST['name'][0]))
        {
            if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false || preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false){
                if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false)
                    echo (json_encode(array("err" => 1.1)));
                if (preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false)
                    echo (json_encode(array("err" => 1.2)));
            }
            else{
                if ($root == "accept" && $_POST["action"] == "create"){
                    if (preg_match('/^[0-9]{1}$/',$_POST['uid']['order']) == true)
                        $_POST['uid']['order'] = '00'.$_POST['uid']['order'];
                    else if (preg_match('/^[0-9]{2}$/',$_POST['uid']['order']) == true)
                        $_POST['uid']['order'] = '0'.$_POST['uid']['order'];
                    $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."'");
                    if (!$result)
                        echo (json_encode(array("errDB" => 'Ошибка запроса 1: mysqli_query '.mysqli_error($link))));
                }
                $helpVar = 1;
                if ($root == "accept" && $_POST["action"] == "change" && $status == "created")){
                    $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."'");
                    if (!$result){
                        echo (json_encode(array("errDB" => 'Ошибка запроса 2: mysqli_query '.mysqli_error($link))));
                        $helpVar = 0;
                    }
                    else {
                        $new_order_id = mysqli_num_rows($result);
                        $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['oldYear'].$_POST['uid']['oldOrder'])."'");
                    }
                }
                else
                    $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."'");
                if (!$result)
                    echo (json_encode(array("errDB" => 'Ошибка запроса 2: mysqli_query '.mysqli_error($link))));
                else if ($helpVar == 1){
                    $id_lot = mysqli_num_rows($result);
                    $result = mysqli_query($link, "select `status` from `orders` where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                    if (!$result)
                        echo (json_encode(array("errDB" => 'Ошибка запроса 3: mysqli_query '.mysqli_error($link))));
                    else{
                        if($root != "accept" && $_POST["action"] != "create")
                            $status = mysqli_fetch_row($result)[0];
                        if ($root == "accept"){//заменить на реальные права (vereshagyen order)
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
                                    echo (json_encode(array('type' => $emptyTypeFromDB, 'name' => $emptyNameFromDB)));
                                else{
                                    if ($_POST["action"] == "change"){
                                        $result = mysqli_query($link, "update `orders` SET `deadline` = '".mysqli_real_escape_string($link, $_POST['deadline'])."', `recipient` = '".mysqli_real_escape_string($link, $_POST['recipient'])."', `id` = '".mysqli_real_escape_string($link, $_POST['uid']['oldYear'].$_POST['uid']['oldOrder'])."'  where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                                        if (!$result){
                                            echo (json_encode(array("errDB" => 'Ошибка запроса 4: mysqli_query '.mysqli_error($link))));
                                            $faleResultDelOrders = 1;
                                        }
                                    }
                                    if (!isset($faleResultDelOrders)){
                                        if 
                                        $result = mysqli_query($link, "insert into `orders` (`id`, `date`, `deadline`, `recipient`) values ('".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."', NOW(), '".mysqli_real_escape_string($link,$_POST['deadline'])."', '".mysqli_real_escape_string($link,$_POST['recipient'])."')");
                                        if (!$result)
                                            echo (json_encode(array("errDB" => 'Ошибка запроса 4.1: mysqli_query '.mysqli_error($link))));
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
                                            echo (json_encode(array("errDB" => 'Ошибка запроса 5: mysqli_query '.mysqli_error($link))));
                                        else
                                            echo (json_encode(array("result" => "ok", "action" => $_POST["action"])));
                                    }
                                }
                            }
                            else if ($id_lot == 1)
                                echo (json_encode(array("err" => 2.1)));//в базе такой номер уже существует
                            else if ($id_lot == 2)
                                echo (json_encode(array("err" => 2.2)));//в базе таких номеров несколько
                            else if ($_POST["action"] == "change" && $status == "accept")
                                echo (json_encode(array("err" => 2.3)));//Заказ был принят производством
                        }
                        else if ($root == 'otk' && $id_lot == 1){//заменить на реальные права (olga order)
                            if ($_POST['action'] == 'accept'){
                                $result = mysqli_query($link, "select `status` from `orders` where `id` = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
                                if (!$result)
                                    echo (json_encode(array("errDB" => 'Ошибка запроса 6: mysqli_query '.mysqli_error($link))));
                                else if ($status == 'created'){
                                    $result = mysqli_query($link, "update `orders` set `status` =  'accept' where `id` = '".$_POST['serial']."'");
                                    if (!$result)
                                        echo (json_encode(array("errDB" => 'Ошибка запроса 7: mysqli_query '.mysqli_error($link))));
                                    else
                                        echo (json_encode(array("accept" => "ok", "action" => "accept")));
                                }
                                else
                                    echo (json_encode(array("err" => 2.4)));//Статус заказа не позволяет его принять
                            }
                            else if ($_POST['action'] == 'change'){
                                if ($status == 'accept'){
                                    $result = mysqli_query($link, "delete from `order-items` where order_id = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                                    if (!$result)
                                        echo (json_encode(array("errDB" => 'Ошибка запроса 8: mysqli_query '.mysqli_error($link))));
                                    else{
                                        $str = 'insert into `order-items` (`order_id`, `type`, `name`, `serial`) values ';
                                        $i = 0;
                                        while (!empty($_POST['type'][$i])){
                                            $str = $str . ' ( \''.mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order']).'\', \''.mysqli_real_escape_string($link,$_POST['type'][$i]).'\', \''.mysqli_real_escape_string($link,$_POST['name'][$i]).'\', \''.mysqli_real_escape_string($link,$_POST['serial'][$i]).'\')';
                                            $i++;
                                            if (!empty($_POST['type'][$i]))
                                                $str = $str . ',';
                                        }
                                        if (!(mysqli_query($link, $str))){
                                            echo (json_encode(array("errDB" => 'Ошибка запроса 9: mysqli_query '.mysqli_error($link))));
                                        }
                                        else
                                            echo (json_encode(array("change" => "ok")));
                                    }
                                }
                            }
                        }
                        else if ($root == 'otk' && $id_lot == 0)
                            echo (json_encode(array("err" => 2.5)));//В базе нет такого заказа
                        else if ($root == 'otk' && $id_lot > 1)
                            echo (json_encode(array("err" => 2.5)));//В базе таких заказов несколько. Просьба сообщить разработчику
                    }
                }
            }
        }
?>