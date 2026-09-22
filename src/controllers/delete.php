<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

csrf_verify();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    flash_set('error', 'ID produk tidak valid.');
    header('Location: index.php');
    exit;
}

$pdo  = get_pdo();
$stmt = $pdo->prepare('SELECT name FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Produk tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute([':id' => $id]);

flash_set('success', "Produk \"" . $product['name'] . "\" berhasil dihapus.");
header('Location: index.php');
exit;
