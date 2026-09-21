<?php
include '../config.php';

if (isset($_POST['login'])) {
    $l = $_POST['login'];
    $p = $_POST['pass'];
    $sql = "SELECT * FROM users WHERE login = '$l' AND pass = '$p'";
    $res = mysqli_query($link, $sql);
    if (mysqli_num_rows($res) > 0) {
        setcookie('auth', '1');
        setcookie('user', $l);
        header('Location: index.php');
        exit;
    } else {
        $err = 'Неверный логин или пароль';
    }
}

if (isset($_GET['logout'])) {
    setcookie('auth', '', time() - 3600);
    header('Location: index.php');
    exit;
}

$logged = (isset($_COOKIE['auth']) && $_COOKIE['auth'] == '1');
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
<?php if (!$logged) { ?>
  <h2>Вход в панель управления</h2>
  <?php if (isset($err)) echo '<p class="err">' . $err . '</p>'; ?>
  <form method="post">
    <p>Логин:<br><input type="text" name="login"></p>
    <p>Пароль:<br><input type="password" name="pass"></p>
    <p><input type="submit" value="Войти"></p>
  </form>
<?php } else { ?>
  <h2>Панель управления</h2>
  <p>Вы вошли как <b><?php echo $_COOKIE['user']; ?></b> | <a href="?logout=1">выйти</a></p>

  <h3>Пользователи</h3>
  <table><tr><th>ID</th><th>Логин</th><th>Пароль</th><th>E-mail</th></tr>
  <?php
  $u = mysqli_query($link, "SELECT * FROM users");
  while ($row = mysqli_fetch_assoc($u)) {
      echo '<tr><td>' . $row['id'] . '</td><td>' . $row['login'] . '</td>';
      echo '<td>' . $row['pass'] . '</td><td>' . $row['email'] . '</td></tr>';
  }
  ?>
  </table>

  <h3>Последние отзывы</h3>
  <table><tr><th>Товар</th><th>Автор</th><th>Текст</th></tr>
  <?php
  $r = mysqli_query($link, "SELECT * FROM reviews ORDER BY id DESC LIMIT 20");
  while ($row = mysqli_fetch_assoc($r)) {
      echo '<tr><td>' . $row['product_id'] . '</td><td>' . $row['author'] . '</td>';
      echo '<td>' . $row['text'] . '</td></tr>';
  }
  ?>
  </table>
<?php } ?>
</div>
</body>
</html>
