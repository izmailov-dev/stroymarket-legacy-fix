<?php
// Однократно: перевести пароли пользователей из открытого текста в хеши.
// Запуск только из командной строки, из браузера не сработает.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config.php';

$res = mysqli_query($link, "SELECT id, pass FROM users");
$upd = mysqli_prepare($link, "UPDATE users SET pass = ? WHERE id = ?");
$n = 0;

while ($u = mysqli_fetch_assoc($res)) {
    // Уже хеш — пропускаем, скрипт можно запускать повторно
    if (password_get_info($u['pass'])['algoName'] !== 'unknown') {
        continue;
    }
    $hash = password_hash($u['pass'], PASSWORD_DEFAULT);
    $id = (int)$u['id'];
    mysqli_stmt_bind_param($upd, 'si', $hash, $id);
    mysqli_stmt_execute($upd);
    $n++;
}

echo "Hashed: $n\n";