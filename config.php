<?php
// Настройки сайта "СтройМаркет"
// Доступы к базе вынесены за пределы сайта: C:\laragon\secrets\patient.php

// Ошибки пишем в лог, посетителю не показываем
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$secrets = require dirname(__DIR__, 2) . '/secrets/patient.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $link = mysqli_connect(
        $secrets['db_host'],
        $secrets['db_user'],
        $secrets['db_pass'],
        $secrets['db_name']
    );
    mysqli_set_charset($link, 'utf8');
} catch (mysqli_sql_exception $e) {
    error_log('DB connect: ' . $e->getMessage());
    http_response_code(500);
    exit('Сайт временно недоступен');
}
unset($secrets);

// Сессия: кука недоступна из JavaScript и не уходит на чужие сайты
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

// Экранирование вывода — защита от XSS
function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Защита форм от подделки запросов (CSRF)
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Форма устарела, обновите страницу');
    }
}