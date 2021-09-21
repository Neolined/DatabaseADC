<?php
    session_start();
    require_once 'lib/main.lib.php';
    $link = connect();
    $root = array(0 => "ok", 1 => "accept");//заменить на реальные права
    $root = checkRoot($link, $root, true);
    print_r($_POST);
    if ($root == "accept"){//заменить на реальные права
        if (!empty($_POST['uid']['year']) && !empty($_POST['uid']['order']) && !empty($_POST['deadline']) && !empty($_POST['recipient']))
        {
            if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false || preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false){
                if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false)
                    echo (json_encode(array("err" => 1.1)));
                if (preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false)
                    echo (json_encode(array("err" => 1.2)));
            }
            else
            {
                if ($_POST["action"] == "create"){
                if (preg_match('/^[0-9]{1}$/',$_POST['uid']['order']) == true)
                    $_POST['uid']['order'] = '00'.$_POST['uid']['order'];
                else if (preg_match('/^[0-9]{2}$/',$_POST['uid']['order']) == true)
                    $_POST['uid']['order'] = '0'.$_POST['uid']['order'];
                $result = mysqli_query($link, "select `id` from `orders` where `id` = '".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."'");
                if (!$result)
                    echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
                }
                if ($_POST["action"] == "change" || (isset($result) && mysqli_num_rows($result) == 0))
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
                    {   if ($_POST["action"] == "create")
                            $result = mysqli_query($link, "insert into `orders` (`id`, `date`, `deadline`, `recipient`) values ('".mysqli_real_escape_string($link,$_POST['uid']['year'].$_POST['uid']['order'])."', NOW(), '".mysqli_real_escape_string($link,$_POST['deadline'])."', '".mysqli_real_escape_string($link,$_POST['recipient'])."')");
                        else if ($_POST["action"] == "change"){
                            $result = mysqli_query($link, "update `orders` SET `deadline` = '".mysqli_real_escape_string($link, $_POST['deadline'])."', `recipient` = '".mysqli_real_escape_string($link, $_POST['recipient'])."' where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                            $result = mysqli_query($link, "delete from `order-items` where order_id = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                        }
                        if (!$result)
                            echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
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
                                    echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
                                }
                                else
                                    echo (json_encode(array("save" => "ok")));
                        }
                    }
                }
                else if (mysqli_num_rows($result) == 1)
                    echo (json_encode(array("err" => 2.1)));//в базе такой номер уже существует
                else if (mysqli_num_rows($result) > 1)
                    echo (json_encode(array("err" => 2.2)));//в базе таких номеров несколько
            }
        }
    }
    else if ($root == "accept" && $_POST["action"] == "change"){//заменить на реальные права
        if (!empty($_POST['uid']['year']) && !empty($_POST['uid']['order']) && !empty($_POST['deadline']) && !empty($_POST['recipient'])){
            if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false || preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false){
                if (preg_match('/^[0-9]{4}$/',$_POST['uid']['year']) == false)
                    echo (json_encode(array("err" => 1.1)));
                if (preg_match('/^[0-9]{1,3}$/',$_POST['uid']['order']) == false)
                    echo (json_encode(array("err" => 1.2)));
            }
            else{
                $result = mysqli_query($link, "update `orders` SET `deadline` = '".mysqli_real_escape_string($link, $_POST['deadline'])."', `recipient` = '".mysqli_real_escape_string($link, $_POST['recipient'])."' where `id` = '".mysqli_real_escape_string($link, $_POST['uid']['year'].$_POST['uid']['order'])."'");
                if (!$result)
                    echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
                else{
                    $result = mysqli_query()
                }
                    echo (json_encode(array("update" => 'ok')));
            }
        }
    }
    else if ($root == 'otk' && $_POST["action"] == "accept"){//заменить на реальные права $rooot!
        if ($_POST['action'] == 'accept'){
            $result = mysqli_query($link, "select `status` from `orders` where `id` = '".mysqli_real_escape_string($link, $_POST['serial'])."'");
            if (!$result)
                echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
            if (mysqli_fetch_row($result)[0] == 'created'){
                $result = mysqli_query($link, "update `orders` set `status` =  'accept' where `id` = '".$_POST['serial']."'");
                if (!$result)
                    echo (json_encode(array("errDB" => 'Ошибка запроса: mysqli_query '.mysqli_error($link))));
                else
                    echo (json_encode(array("accept" => "ok")));
            }
        }
    }
?>