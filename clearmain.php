<?php
session_start();
unset($_SESSION['filter']);
unset($_SESSION['order']);
header('Location: main.php');
?>