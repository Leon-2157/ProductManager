<?php
declare(strict_types=1);

$pdo   = get_pdo();
$flash = flash_get();

$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products ORDER BY id DESC');
$stmt->execute();
$products = $stmt->fetchAll();

$total_products = count($products);
$total_stock    = array_sum(array_column($products, 'stock'));
$total_value    = array_sum(array_map(
    fn($p) => (float) $p['price'] * (int) $p['stock'],
    $products
));

require __DIR__ . '/../views/read.view.php';
