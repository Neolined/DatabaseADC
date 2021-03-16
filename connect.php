<?php
$host = 'localhost';
$database = 'adcproducts';
$user = 'root';
$password = 'qwerty123';
$link = mysqli_connect($host, $user, $password, $database);
if (!$link) 
{
die('Ошибка при подключении к базе данных: ' . mysqli_connect_error($link));
}
?>