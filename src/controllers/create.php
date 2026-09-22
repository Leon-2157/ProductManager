<?php
declare(strict_types=1);

$errors = [];
$old    = ['name' => '', 'category' => '', 'price' => '', 'stock' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name     = trim($_POST['name']     ?? '');
    $category = trim($_POST['category'] ?? '');
    $price    = trim($_POST['price']    ?? '');
    $stock    = trim($_POST['stock']    ?? '');

    $old    = compact('name', 'category', 'price', 'stock');
    $errors = validate_product($name, $category, $price, $stock);

    if (empty($errors)) {
        $pdo  = get_pdo();
        $stmt = $pdo->prepare('SELECT id FROM products WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $name]);
        if ($stmt->fetch()) {
            $errors[] = 'Nama produk sudah terdaftar. Gunakan nama yang berbeda.';
        }
    }

    if (empty($errors)) {
        $pdo  = get_pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO products (name, category, price, stock) VALUES (:name, :category, :price, :stock)'
        );
        $stmt->execute([
            ':name'     => $name,
            ':category' => $category,
            ':price'    => (float) $price,
            ':stock'    => (int)   $stock,
        ]);

        flash_set('success', "Produk \"$name\" berhasil ditambahkan.");
        header('Location: index.php');
        exit;
    }
}

require __DIR__ . '/../views/create.view.php';
