<?php
include 'config.php';
$t_start = microtime(true);

$q   = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$cat = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT);

$cats = mysqli_query($link, "SELECT id, name FROM categories ORDER BY id");

// Один запрос вместо 101: категория через JOIN, отзывы подсчётом по индексу
$base = "SELECT p.id, p.art, p.name, p.price, c.name AS cat_name,
                (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) AS cnt
         FROM products p
         LEFT JOIN categories c ON c.id = p.cat_id";

// Данные пользователя идут в запрос только через параметры, не склейкой строк
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $stmt = mysqli_prepare($link, "$base WHERE p.name LIKE ? OR p.descr LIKE ?");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
} elseif ($cat) {
    $stmt = mysqli_prepare($link, "$base WHERE p.cat_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $cat);
} else {
    $stmt = mysqli_prepare($link, "$base ORDER BY p.id LIMIT 50");
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>СтройМаркет — каталог</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
.wrap { width: 1000px; margin: 0 auto; background: #fff; padding: 20px; }
h1 { color: #c00; }
.cat { display: inline-block; margin-right: 12px; }
table { border-collapse: collapse; width: 100%; margin-top: 15px; }
td, th { border: 1px solid #ddd; padding: 6px; font-size: 13px; }
th { background: #eee; }
.foot { margin-top: 20px; color: #888; font-size: 12px; }
</style>
</head>
<body>
<div class="wrap">
<h1>СтройМаркет</h1>

<form method="get">
    Поиск: <input type="text" name="q" value="<?= e($q) ?>" size="40">
  <input type="submit" value="Найти">
</form>

<p>
<?php while ($c = mysqli_fetch_assoc($cats)): ?>
    <span class="cat"><a href="?cat=<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a></span>
<?php endwhile; ?>
</p>

<table><tr><th>Артикул</th><th>Наименование</th><th>Категория</th><th>Отзывов</th><th>Цена</th></tr>
<?php while ($row = mysqli_fetch_assoc($res)): ?>
<tr>
<td><?= e($row['art']) ?></td>
<td><a href="product.php?id=<?= (int)$row['id'] ?>"><?= e($row['name']) ?></a></td>
<td><?= e($row['cat_name']) ?></td>
<td><?= (int)$row['cnt'] ?></td>
<td><?= (int)$row['price'] ?> руб.</td>
</tr>
<?php endwhile; ?>
</table>

<div class="foot">
Страница сгенерирована за <?= round(microtime(true) - $t_start, 3) ?> сек.
</div>
</div>
</body>
</html>