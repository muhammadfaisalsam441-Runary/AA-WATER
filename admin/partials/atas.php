<?php
/**
 * admin/partials/atas.php — Bagian atas semua halaman admin
 *
 * Sebelum memanggil file ini, isi dulu:
 *   $judul   = 'Judul halaman';
 *   $menuAktif = 'pesanan';   // pesanan / produk / wilayah / pengaturan
 */

$judul     = $judul     ?? 'Admin';
$menuAktif = $menuAktif ?? '';
$admin     = adminSekarang();

$menuAdmin = [
    'pesanan'    => ['url' => 'index.php',      'nama' => 'Pesanan'],
    'produk'     => ['url' => 'produk.php',     'nama' => 'Produk & Harga'],
    'pengaturan' => ['url' => 'pengaturan.php', 'nama' => 'Pengaturan'],
    'akun'       => ['url' => 'akun.php',       'nama' => 'Akun Saya'],
];

$pesanKilat = ambilPesan();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($judul) ?> — Admin <?= e(atur('nama_depot', 'AA WATER')) ?></title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body style="padding-bottom:2rem;">

  <div class="top-status-strip">
    <div class="container top-status-content">
      <div class="status-pill-live open">
        <span class="status-indicator-dot"></span>
        <span>HALAMAN ADMIN • <?= e(atur('nama_depot', 'AA WATER')) ?></span>
      </div>
      <div class="top-info-right">
        <span>👤 <?= e($admin['nama'] ?? '') ?></span>
      </div>
    </div>
  </div>

  <header class="site-header">
    <div class="container header-container">
      <a href="index.php" class="brand-logo">
        <div class="brand-icon-box">⚙️</div>
        <div class="brand-title-wrap">
          <div class="brand-name">Admin</div>
          <span class="brand-sub"><?= e(atur('nama_depot', 'AA WATER')) ?></span>
        </div>
      </a>

      <nav class="main-nav" style="display:flex; flex-wrap:wrap;">
        <?php foreach ($menuAdmin as $kode => $item): ?>
          <a href="<?= $item['url'] ?>" class="nav-link<?= $menuAktif === $kode ? ' active' : '' ?>"><?= e($item['nama']) ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="header-actions">
        <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Web</a>
        <a href="keluar.php" class="btn btn-sm btn-accent">Keluar</a>
      </div>
    </div>
  </header>

  <main class="section" style="padding-top:2rem;">
    <div class="container">

      <?php if ($pesanKilat): ?>
        <div style="padding:0.9rem 1.15rem; margin-bottom:1.5rem; border-radius:var(--radius-xl); font-weight:700;
                    background:<?= $pesanKilat['jenis'] === 'sukses' ? '#D3F9D8' : '#FFF3BF' ?>;
                    color:<?= $pesanKilat['jenis'] === 'sukses' ? '#2B8A3E' : '#D9480F' ?>;">
          <?= e($pesanKilat['teks']) ?>
        </div>
      <?php endif; ?>
