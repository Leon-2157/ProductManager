<?php
declare(strict_types=1);

$pdo = get_pdo();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
   ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    flash_set('error', 'ID produk tidak valid.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Produk tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name     = trim($_POST['name']     ?? '');
    $category = trim($_POST['category'] ?? '');
    $price    = trim($_POST['price']    ?? '');
    $stock    = trim($_POST['stock']    ?? '');

    $errors = validate_product($name, $category, $price, $stock);

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM products WHERE name = :name AND id != :id LIMIT 1');
        $stmt->execute([':name' => $name, ':id' => $id]);
        if ($stmt->fetch()) {
            $errors[] = 'Nama produk sudah digunakan produk lain.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'UPDATE products SET name = :name, category = :category, price = :price, stock = :stock WHERE id = :id'
        );
        $stmt->execute([
            ':name'     => $name,
            ':category' => $category,
            ':price'    => (float) $price,
            ':stock'    => (int)   $stock,
            ':id'       => $id,
        ]);

        flash_set('success', "Produk \"$name\" berhasil diperbarui.");
        header('Location: index.php');
        exit;
    }

    $product['name']     = $name;
    $product['category'] = $category;
    $product['price']    = $price;
    $product['stock']    = $stock;
}

require __DIR__ . '/../views/edit.view.php';
