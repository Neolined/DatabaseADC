<?php
session_start();
unset($_SESSION['filter']);
unset($_SESSION['request']);
unset($_SESSION['order']);
unset($_SESSION['lot']);
header('Location: main.php');
?>