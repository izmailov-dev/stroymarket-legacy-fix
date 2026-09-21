<?php
include 'config.php';
$t_start = microtime(true);
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
    Поиск: <input type="text" name="q" value="<?php if (isset($_GET['q'])) echo $_GET['q']; ?>" size="40">
  <input type="submit" value="Найти">
</form>

<p>
<?php
$cats = mysqli_query($link, "SELECT * FROM categories");
while ($c = mysqli_fetch_assoc($cats)) {
    echo '<span class="cat"><a href="?cat=' . $c['id'] . '">' . $c['name'] . '</a></span>';
}
?>
</p>

<?php
if (isset($_GET['q'])) {
    $q = $_GET['q'];
    $sql = "SELECT * FROM products WHERE name LIKE '%$q%' OR descr LIKE '%$q%'";
} elseif (isset($_GET['cat'])) {
    $cat = $_GET['cat'];
    $sql = "SELECT * FROM products WHERE cat_id = $cat";
} else {
    $sql = "SELECT * FROM products LIMIT 50";
}

$res = mysqli_query($link, $sql);
if (!$res) {
    echo '<p style="color:red">Ошибка запроса: ' . mysqli_error($link) . '</p>';
    echo '<p style="color:#999">SQL: ' . $sql . '</p>';
} else {
    echo '<table><tr><th>Артикул</th><th>Наименование</th><th>Категория</th><th>Отзывов</th><th>Цена</th></tr>';
    while ($row = mysqli_fetch_assoc($res)) {

        $cq = mysqli_query($link, "SELECT name FROM categories WHERE id = " . $row['cat_id']);
        $cat_row = mysqli_fetch_assoc($cq);

        $rq = mysqli_query($link, "SELECT COUNT(*) as cnt FROM reviews WHERE product_id = " . $row['id']);
        $rev = mysqli_fetch_assoc($rq);

        echo '<tr>';
        echo '<td>' . $row['art'] . '</td>';
        echo '<td><a href="product.php?id=' . $row['id'] . '">' . $row['name'] . '</a></td>';
        echo '<td>' . $cat_row['name'] . '</td>';
        echo '<td>' . $rev['cnt'] . '</td>';
        echo '<td>' . $row['price'] . ' руб.</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?>

<div class="foot">
Страница сгенерирована за <?php echo round(microtime(true) - $t_start, 3); ?> сек.
</div>
</div>
</body>
</html>
