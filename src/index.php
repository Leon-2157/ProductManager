<?php
// Entry Point
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

$action = filter_input(INPUT_GET, 'action') ?: 'read';

$routes = [
    'read'   => __DIR__ . '/controllers/read.php',
    'create' => __DIR__ . '/controllers/create.php',
    'edit'   => __DIR__ . '/controllers/edit.php',
    'delete' => __DIR__ . '/controllers/delete.php',
];

if (isset($routes[$action])) {
    require $routes[$action];
} else {
    http_response_code(404);
    flash_set('error', 'Halaman tidak ditemukan.');
    header('Location: index.php');
    exit;
}
