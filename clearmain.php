<?php
session_start();
require_once 'lib/main.lib.php';
unset($_SESSION['filter']);
unset($_SESSION['request']);
unset($_SESSION['order']);
unset($_SESSION['lot']);
header('Location: main.php');
?>