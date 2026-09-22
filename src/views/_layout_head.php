<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($page_title) ?></title>
  <meta name="description" content="<?= h($page_description) ?>">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar navbar-glass sticky-top py-2" aria-label="Navigasi utama">
  <div class="container d-flex align-items-center justify-content-between">
    <a class="navbar-brand d-flex align-items-center gap-2 m-0 text-decoration-none" href="index.php">
      <span class="brand-badge d-flex align-items-center justify-content-center text-white" aria-hidden="true">
        <i class="bi bi-box-seam"></i>
      </span>
      <div>
        <div class="fw-bold fs-6 lh-1 text-light">Product Manager</div>
      </div>
    </a>
    <div><?= $nav_action_html ?></div>
  </div>
</nav>
