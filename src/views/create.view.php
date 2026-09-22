<?php
declare(strict_types=1);

$page_title       = 'Tambah Produk — Product Manager';
$page_description = 'Tambahkan produk baru ke sistem.';
$nav_action_html  = '<a href="index.php" class="btn btn-outline-secondary btn-sm" id="btn-kembali-list"><i class="bi bi-arrow-left me-1"></i> Kembali</a>';

require_once __DIR__ . '/_layout_head.php';
?>

<main class="container py-4" id="main-content">

  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">

      <div class="mb-4">
        <h1 class="h3 fw-bold text-light mb-1">Tambah Produk Baru</h1>
        <p class="text-secondary small mb-0">Isi formulir untuk menambahkan produk.</p>
      </div>

      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger" role="alert" aria-live="assertive">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat kesalahan:</div>
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $err): ?>
          <li><?= h($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body p-4">
          <form method="POST" action="index.php?action=create" novalidate id="form-create-product">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="mb-3">
              <label for="name" class="form-label text-light fw-medium">Nama Produk</label>
              <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                value="<?= h($old['name']) ?>"
                maxlength="23"
                required
                autocomplete="off"
                placeholder="Contoh: Laptop Gaming Pro"
                aria-describedby="name-hint"
              >
              <div class="form-text text-secondary" id="name-hint">3–23 karakter. Harus unik.</div>
            </div>

            <div class="mb-3">
              <label for="category" class="form-label text-light fw-medium">Kategori</label>
              <input
                type="text"
                id="category"
                name="category"
                class="form-control"
                value="<?= h($old['category']) ?>"
                maxlength="100"
                required
                placeholder="Contoh: Elektronik"
                aria-describedby="category-hint"
              >
              <div class="form-text text-secondary" id="category-hint">Kategori produk.</div>
            </div>

            <div class="mb-3">
              <label for="price" class="form-label text-light fw-medium">Harga (Rp)</label>
              <input
                type="number"
                id="price"
                name="price"
                class="form-control"
                value="<?= h($old['price']) ?>"
                min="1"
                step="any"
                required
                placeholder="Contoh: 15000000"
                aria-describedby="price-hint"
              >
              <div class="form-text text-secondary" id="price-hint">Harus lebih dari 0.</div>
            </div>

            <div class="mb-4">
              <label for="stock" class="form-label text-light fw-medium">Stok</label>
              <input
                type="number"
                id="stock"
                name="stock"
                class="form-control"
                value="<?= h($old['stock']) ?>"
                min="0"
                max="20"
                step="1"
                required
                placeholder="0–20"
                aria-describedby="stock-hint"
              >
              <div class="form-text text-secondary" id="stock-hint">Antara 0 dan 20 unit.</div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary" id="btn-simpan-produk">
                <i class="bi bi-floppy me-1"></i> Simpan Produk
              </button>
              <a href="index.php" class="btn btn-outline-secondary" id="btn-batal-create">Batal</a>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

</main>

<?php require_once __DIR__ . '/_layout_foot.php'; ?>
