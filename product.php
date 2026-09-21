<?php
include 'config.php';
$t_start = microtime(true);

$id = $_GET['id'];

if (isset($_POST['author'])) {
    $a = $_POST['author'];
    $t = $_POST['text'];
    mysqli_query($link, "INSERT INTO reviews (product_id, author, text, created) VALUES ($id, '$a', '$t', '" . date('Y-m-d') . "')");
    header("Location: product.php?id=$id");
    exit;
}

$res = mysqli_query($link, "SELECT * FROM products WHERE id = $id");
$p = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $p['name']; ?> — СтройМаркет</title>
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
<h1><?php echo $p['name']; ?></h1>
<p>Артикул: <?php echo $p['art']; ?></p>
<p><?php echo $p['descr']; ?></p>
<p class="price"><?php echo $p['price']; ?> руб.</p>

<h3>Отзывы</h3>
<?php
$rev = mysqli_query($link, "SELECT * FROM reviews WHERE product_id = $id ORDER BY id DESC");
while ($r = mysqli_fetch_assoc($rev)) {
    echo '<div class="rev"><b>' . $r['author'] . '</b> <i>' . $r['created'] . '</i><br>';
    echo $r['text'] . '</div>';
}
?>

<h3>Оставить отзыв</h3>
<form method="post">
  <p>Имя:<br><input type="text" name="author"></p>
  <p>Отзыв:<br><textarea name="text" rows="4"></textarea></p>
  <p><input type="submit" value="Отправить"></p>
</form>

<div class="foot">
Страница сгенерирована за <?php echo round(microtime(true) - $t_start, 3); ?> сек.
</div>
</div>
</body>
</html>
