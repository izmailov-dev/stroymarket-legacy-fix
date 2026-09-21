<?php
include 'config.php';
$t_start = microtime(true);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Товар не найден');
}

// Новый отзыв: проверка формы, параметры вместо склейки строк
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = mb_substr(trim((string)($_POST['author'] ?? '')), 0, 100);
    $t = trim((string)($_POST['text'] ?? ''));
    if ($a !== '' && $t !== '') {
        $d = date('Y-m-d');
        $stmt = mysqli_prepare($link,
            "INSERT INTO reviews (product_id, author, text, created) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $id, $a, $t, $d);
        mysqli_stmt_execute($stmt);
    }
    header("Location: product.php?id=$id");
    exit;
}

$stmt = mysqli_prepare($link, "SELECT id, art, name, descr, price FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$p) {
    http_response_code(404);
    exit('Товар не найден');
}

$stmt = mysqli_prepare($link,
    "SELECT author, text, created FROM reviews WHERE product_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$rev = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= e($p['name']) ?> — СтройМаркет</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
.wrap { width: 800px; margin: 0 auto; background: #fff; padding: 20px; }
h1 { color: #c00; }
.price { font-size: 24px; color: #090; }
.rev { border-top: 1px solid #eee; padding: 8px 0; }
.foot { margin-top: 20px; color: #888; font-size: 12px; }
input, textarea { width: 300px; }
</style>
</head>
<body>
<div class="wrap">
<p><a href="index.php">&larr; в каталог</a></p>
<h1><?= e($p['name']) ?></h1>
<p>Артикул: <?= e($p['art']) ?></p>
<p><?= nl2br(e($p['descr'])) ?></p>
<p class="price"><?= (int)$p['price'] ?> руб.</p>

<h3>Отзывы</h3>
<?php while ($r = mysqli_fetch_assoc($rev)): ?>
<div class="rev"><b><?= e($r['author']) ?></b> <i><?= e($r['created']) ?></i><br>
<?= nl2br(e($r['text'])) ?></div>
<?php endwhile; ?>

<h3>Оставить отзыв</h3>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <p>Имя:<br><input type="text" name="author" maxlength="100"></p>
  <p>Отзыв:<br><textarea name="text" rows="4"></textarea></p>
  <p><input type="submit" value="Отправить"></p>
</form>

<div class="foot">
Страница сгенерирована за <?= round(microtime(true) - $t_start, 3) ?> сек.
</div>
</div>
</body>
</html>