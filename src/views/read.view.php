<?php
declare(strict_types=1);

$page_title       = 'Product Manager — Daftar Produk';
$page_description = 'Kelola inventaris produk.';
$nav_action_html  = '<a href="index.php?action=create" class="btn btn-primary btn-sm" id="btn-tambah-produk"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>';

require_once __DIR__ . '/_layout_head.php';
?>

<main class="container py-4" id="main-content">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 fw-bold text-light mb-1">Daftar Produk</h1>
      <p class="text-secondary small mb-0">Total <?= h($total_products) ?> produk terdaftar.</p>
    </div>
  </div>

  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
    <div><?= h($flash['message']) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
  <?php endif; ?>

  <!-- Ringkasan Inventaris -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-success bg-opacity-10 text-success">
            <i class="bi bi-box-seam"></i>
          </div>
          <div>
            <div class="text-secondary small fw-medium text-uppercase">Total Produk</div>
            <div class="fs-4 fw-bold text-light" id="stat-total"><?= h($total_products) ?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-info bg-opacity-10 text-info">
            <i class="bi bi-archive"></i>
          </div>
          <div>
            <div class="text-secondary small fw-medium text-uppercase">Total Stok</div>
            <div class="fs-4 fw-bold text-light" id="stat-stok"><?= h($total_stock) ?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="stat-icon bg-warning bg-opacity-10 text-warning">
            <i class="bi bi-currency-dollar"></i>
          </div>
          <div>
            <div class="text-secondary small fw-medium text-uppercase">Nilai Inventaris</div>
            <div class="fs-5 fw-bold text-light" id="stat-nilai">
              Rp <?= h(number_format($total_value, 0, ',', '.')) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Grid Produk -->
  <?php if (empty($products)): ?>
  <div class="card text-center p-5">
    <div class="fs-1 text-secondary mb-3"><i class="bi bi-inbox"></i></div>
    <h2 class="h5 text-light">Belum ada produk</h2>
    <p class="text-secondary small mb-3">Tambahkan produk pertama ke sistem.</p>
    <div>
      <a href="index.php?action=create" class="btn btn-primary" id="btn-tambah-pertama">
        <i class="bi bi-plus-lg me-1"></i> Tambah Produk Pertama
      </a>
    </div>
  </div>
  <?php else: ?>
  <section class="row g-3" aria-label="Daftar produk">
    <?php foreach ($products as $product):
      $stock     = (int) $product['stock'];
      $stock_pct = min(100, $stock * 5);
      $bar_color = $stock <= 3 ? 'bg-danger' : ($stock <= 10 ? 'bg-warning' : 'bg-success');
    ?>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <article class="card card-product h-100 d-flex flex-column" aria-label="Produk <?= h($product['name']) ?>">
        <div class="card-body d-flex flex-column pb-2">
          <div class="mb-2">
            <span class="badge bg-secondary-subtle text-secondary-emphasis">
              <i class="bi bi-tag-fill me-1"></i><?= h($product['category']) ?>
            </span>
          </div>
          <h2 class="card-title h6 fw-semibold text-light mb-3"><?= h($product['name']) ?></h2>

          <div class="row g-2 mb-3 mt-auto">
            <div class="col-6">
              <span class="text-secondary small d-block">Harga</span>
              <span class="fw-bold text-success">
                Rp <?= h(number_format((float)$product['price'], 0, ',', '.')) ?>
              </span>
            </div>
            <div class="col-6">
              <span class="text-secondary small d-block">Stok</span>
              <span class="fw-semibold text-light"><?= h($product['stock']) ?> unit</span>
            </div>
          </div>

          <div class="stock-meter mb-2" role="meter" aria-valuenow="<?= h($stock) ?>" aria-valuemin="0" aria-valuemax="20">
            <div class="stock-meter-fill <?= $bar_color ?>" style="width: <?= h($stock_pct) ?>%"></div>
          </div>

          <div class="text-secondary small" style="font-size: 0.72rem;">ID #<?= h($product['id']) ?></div>
        </div>

        <div class="card-footer bg-transparent border-top d-flex gap-2 p-2">
          <a href="index.php?action=edit&id=<?= h($product['id']) ?>" class="btn btn-outline-secondary btn-sm flex-fill" id="btn-edit-<?= h($product['id']) ?>">
            <i class="bi bi-pencil-square me-1"></i> Edit
          </a>
          <form method="POST" action="index.php?action=delete" class="flex-fill" onsubmit="return confirm('Hapus produk \'<?= h(addslashes($product['name'])) ?>\'?')">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= h($product['id']) ?>">
            <button type="submit" class="btn btn-outline-danger btn-sm w-100" id="btn-hapus-<?= h($product['id']) ?>">
              <i class="bi bi-trash me-1"></i> Hapus
            </button>
          </form>
        </div>
      </article>
    </div>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/_layout_foot.php'; ?>
