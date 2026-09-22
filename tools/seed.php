<?php
// Заполняет базу тестовыми данными: 5 категорий, 4000 товаров,
// 20 000 отзывов, 3 пользователя. Все данные вымышлены.
// Запуск: php tools/seed.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config.php';
mt_srand(42); // одинаковые данные при каждом запуске

foreach (['users', 'reviews', 'products', 'categories'] as $t) {
    mysqli_query($link, "TRUNCATE TABLE `$t`");
}

$types = [
    'Инструмент' => ['Дрель', 'Шуруповёрт', 'Перфоратор', 'Лобзик', 'Болгарка'],
    'Крепёж'     => ['Саморез', 'Дюбель', 'Анкер', 'Болт', 'Гайка'],
    'Сантехника' => ['Смеситель', 'Сифон', 'Кран шаровой', 'Подводка гибкая', 'Душевой шланг'],
    'Электрика'  => ['Кабель', 'Розетка', 'Выключатель', 'Автомат', 'Удлинитель'],
    'Лакокраска' => ['Краска', 'Грунтовка', 'Лак', 'Шпаклёвка', 'Эмаль'],
];
$brands = ['Зубр', 'Bosch', 'Makita', 'Интерскол', 'Ресанта',
           'Kraftool', 'Tikkurila', 'Legrand', 'Grohe', 'Sormat'];
$uses   = ['для дома', 'для дачи', 'для ремонта квартиры',
           'для профессионального использования', 'для гаража'];

mysqli_begin_transaction($link);

// Категории
$sc = mysqli_prepare($link, "INSERT INTO categories (name) VALUES (?)");
foreach (array_keys($types) as $name) {
    mysqli_stmt_bind_param($sc, 's', $name);
    mysqli_stmt_execute($sc);
}

// Товары
$catNames = array_keys($types);
$sp = mysqli_prepare($link,
    "INSERT INTO products (cat_id, name, descr, price, art) VALUES (?, ?, ?, ?, ?)");
for ($i = 1; $i <= 4000; $i++) {
    $catId = mt_rand(1, 5);
    $items = $types[$catNames[$catId - 1]];
    $name  = $items[mt_rand(0, 4)] . ' ' . $brands[mt_rand(0, 9)]
           . ' ' . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . '-' . mt_rand(100, 999);
    $descr = 'Надёжный товар ' . $uses[mt_rand(0, 4)] . '. Гарантия '
           . mt_rand(6, 36) . ' мес. В наличии на складе.';
    $price = mt_rand(50, 25000);
    $art   = sprintf('SM-%05d', $i);
    mysqli_stmt_bind_param($sp, 'issis', $catId, $name, $descr, $price, $art);
    mysqli_stmt_execute($sp);
}

// Отзывы
$authors = ['Алексей', 'Ирина', 'Сергей', 'Ольга', 'Дмитрий',
            'Наталья', 'Павел', 'Елена', 'Андрей', 'Татьяна'];
$texts   = ['Хороший товар, рекомендую.', 'Работает отлично, доставили быстро.',
            'За свои деньги нормально.', 'Качество среднее, но пользоваться можно.',
            'Брал второй раз, всё устраивает.', 'Упаковка помята, сам товар целый.'];
$sr = mysqli_prepare($link,
    "INSERT INTO reviews (product_id, author, text, created) VALUES (?, ?, ?, ?)");
for ($i = 1; $i <= 20000; $i++) {
    $pid  = mt_rand(1, 4000);
    $a    = $authors[mt_rand(0, 9)];
    $t    = $texts[mt_rand(0, 5)];
    $date = date('Y-m-d', mt_rand(strtotime('2012-01-01'), strtotime('2014-03-01')));
    mysqli_stmt_bind_param($sr, 'isss', $pid, $a, $t, $date);
    mysqli_stmt_execute($sr);
}

// Пользователи: пароли открытым текстом, как в исходном сайте.
// После заполнения запустите tools/hash_passwords.php
$users = [
    ['admin',   'admin123', 'admin@stroymarket.test'],
    ['manager', 'qwerty',   'manager@stroymarket.test'],
    ['buh',     '12345',    'buh@stroymarket.test'],
];
$su = mysqli_prepare($link, "INSERT INTO users (login, pass, email) VALUES (?, ?, ?)");
foreach ($users as [$l, $p, $e]) {
    mysqli_stmt_bind_param($su, 'sss', $l, $p, $e);
    mysqli_stmt_execute($su);
}

mysqli_commit($link);
echo "Seeded: 5 categories, 4000 products, 20000 reviews, 3 users\n";