<?php
$host = 'localhost';
$database = 'adcproducts';
$user = 'adcuser';
$password = 'vbq7-oi5tw';
$link = mysqli_connect($host, $user, $password, $database);
if (!$link) 
{
die('Ошибка при подключении к базе данных: ' . mysqli_connect_error($link));
}
?>