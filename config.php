<?php
// Настройки сайта "СтройМаркет"
// Последнее изменение: 12.03.2014

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'patient';

$admin_login = 'admin';
$admin_pass  = 'admin123';

$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$link) {
    die('Ошибка подключения: ' . mysqli_error($link));
}
mysqli_query($link, "SET NAMES utf8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
