<?php
include __DIR__ . '/../config.php';

$err = null;

// Вход: запрос с параметрами, проверка хеша, новая сессия вместо куки auth=1
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $l = trim((string)($_POST['login'] ?? ''));
    $p = (string)($_POST['pass'] ?? '');

    $stmt = mysqli_prepare($link, "SELECT id, login, pass FROM users WHERE login = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $l);
    mysqli_stmt_execute($stmt);
    $u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($u && password_verify($p, $u['pass'])) {
        // Хеш устаревшего формата — обновляем незаметно для пользователя
        if (password_needs_rehash($u['pass'], PASSWORD_DEFAULT)) {
            $h  = password_hash($p, PASSWORD_DEFAULT);
            $id = (int)$u['id'];
            $s  = mysqli_prepare($link, "UPDATE users SET pass = ? WHERE id = ?");
            mysqli_stmt_bind_param($s, 'si', $h, $id);
            mysqli_stmt_execute($s);
        }
        session_regenerate_id(true);
        $_SESSION['admin_id']    = (int)$u['id'];
        $_SESSION['admin_login'] = $u['login'];
        header('Location: index.php');
        exit;
    }

    sleep(1); // тормозим перебор паролей
    $err = 'Неверный логин или пароль';
}

// Выход только со своей страницы (ссылка с токеном)
if (isset($_GET['logout']) && hash_equals(csrf_token(), (string)($_GET['t'] ?? ''))) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

$logged = !empty($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Админка — СтройМаркет</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; }
.wrap { width: 700px; margin: 40px auto; background: #fff; padding: 20px; }
table { border-collapse: collapse; width: 100%; }
td, th { border: 1px solid #ddd; padding: 6px; font-size: 13px; }
.err { color: #c00; }
</style>
</head>
<body>
<div class="wrap">
<?php if (!$logged): ?>
  <h2>Вход в панель управления</h2>
  <?php if ($err): ?><p class="err"><?= e($err) ?></p><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <p>Логин:<br><input type="text" name="login"></p>
    <p>Пароль:<br><input type="password" name="pass"></p>
    <p><input type="submit" value="Войти"></p>
  </form>
<?php else: ?>
  <h2>Панель управления</h2>
  <p>Вы вошли как <b><?= e($_SESSION['admin_login']) ?></b> |
     <a href="?logout=1&amp;t=<?= e(csrf_token()) ?>">выйти</a></p>

  <h3>Пользователи</h3>
  <table><tr><th>ID</th><th>Логин</th><th>Пароль</th><th>E-mail</th></tr>
  <?php
  $u = mysqli_query($link, "SELECT id, login, email FROM users ORDER BY id");
  while ($row = mysqli_fetch_assoc($u)): ?>
    <tr><td><?= (int)$row['id'] ?></td><td><?= e($row['login']) ?></td>
    <td>••••••</td><td><?= e($row['email']) ?></td></tr>
  <?php endwhile; ?>
  </table>

  <h3>Последние отзывы</h3>
  <table><tr><th>Товар</th><th>Автор</th><th>Текст</th></tr>
  <?php
  $r = mysqli_query($link, "SELECT product_id, author, text FROM reviews ORDER BY id DESC LIMIT 20");
  while ($row = mysqli_fetch_assoc($r)): ?>
    <tr><td><?= (int)$row['product_id'] ?></td><td><?= e($row['author']) ?></td>
    <td><?= e($row['text']) ?></td></tr>
  <?php endwhile; ?>
  </table>
<?php endif; ?>
</div>
</body>
</html>