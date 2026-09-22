<?php
// Utilitas global
declare(strict_types=1);

// Escape string untuk output HTML aman
function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token dari sesi
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verifikasi CSRF token pada request POST
function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        exit('403 Forbidden — token CSRF tidak valid.');
    }
}

// Validasi field input produk
function validate_product(string $name, string $category, string $price, string $stock): array
{
    $errors = [];

    $name = trim($name);
    if (mb_strlen($name) < 3) {
        $errors[] = 'Nama produk minimal 3 karakter.';
    } elseif (mb_strlen($name) > 23) {
        $errors[] = 'Nama produk maksimal 23 karakter.';
    }

    if (trim($category) === '') {
        $errors[] = 'Kategori tidak boleh kosong.';
    }

    if (!is_numeric($price) || (float) $price <= 0) {
        $errors[] = 'Harga harus lebih dari 0.';
    }

    if (!ctype_digit($stock) || (int) $stock < 0 || (int) $stock > 20) {
        $errors[] = 'Stok harus antara 0 dan 20.';
    }

    return $errors;
}

// Simpan pesan flash ke sesi
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Ambil dan hapus pesan flash dari sesi
function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
