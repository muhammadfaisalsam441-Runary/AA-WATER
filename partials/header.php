<?php
/**
 * partials/header.php — Bagian atas semua halaman
 *
 * Sebelum memanggil file ini, isi dulu 3 variabel berikut di halaman:
 *   $judul     = 'Judul untuk tab browser';
 *   $deskripsi = 'Keterangan singkat untuk Google';
 *   $halaman   = 'beranda';   // beranda / harga / area / kontak
 *
 * Contoh:
 *   require_once __DIR__ . '/konfigurasi.php';
 *   $judul = 'AA WATER'; $halaman = 'beranda';
 *   include __DIR__ . '/partials/header.php';
 */

require_once __DIR__ . '/../konfigurasi.php';

// Nilai cadangan kalau halaman lupa mengisinya
$judul     = $judul     ?? atur('nama_depot', 'AA WATER');
$deskripsi = $deskripsi ?? 'Antar galon air dan gas LPG 3kg di Borong Raya, Batua, dan Toddopuli.';
$halaman   = $halaman   ?? '';

// Daftar menu — cukup diubah di sini, keempat halaman ikut berubah
$menu = [
    'beranda' => ['url' => 'index.php',  'nama' => 'Beranda',           'nama_hp' => 'Beranda'],
    'harga'   => ['url' => 'harga.php',  'nama' => 'Harga',             'nama_hp' => 'Harga'],
    'pesan'   => ['url' => 'pesan.php',  'nama' => 'Pesan',             'nama_hp' => 'Form Pemesanan'],
    'area'    => ['url' => 'area.php',   'nama' => 'Area Layanan',      'nama_hp' => 'Cek Area Layanan'],
    'kontak'  => ['url' => 'kontak.php', 'nama' => 'Kontak & Depot',    'nama_hp' => 'Kontak & Alamat Depot'],
];

$pesanOrder = 'Halo ' . atur('nama_depot', 'AA WATER') . ', saya mau order galon/gas sekarang!';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <title><?= e($judul) ?></title>
  <meta name="description" content="<?= e($deskripsi) ?>">

  <!-- CSS Stylesheet -->
  <link rel="stylesheet" href="css/style.css">

  <!-- Favicon SVG -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
</head>
<body>

  <!-- Top Operational Status Strip -->
  <div class="top-status-strip">
    <div class="container top-status-content">
      <div class="status-pill-live js-store-status <?= depotBuka() ? 'open' : 'closed' ?>">
        <span class="status-indicator-dot"></span>
        <span class="js-status-text"><?= teksStatus() ?></span>
      </div>
      <div class="top-info-right">
        <span class="sla-badge-small">⚡ ANTAR &lt; <?= e(atur('sla_menit', '60')) ?> MENIT</span>
        <span>📍 Borong Raya, Batua, Toddopuli • Ongkir <?= rupiah((int) atur('ongkir_per_item', '3000')) ?>/item</span>
      </div>
    </div>
  </div>

  <!-- Header & Navigasi -->
  <header class="site-header">
    <div class="container header-container">
      <a href="index.php" class="brand-logo" aria-label="<?= e(atur('nama_depot')) ?> Beranda">
        <div class="brand-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
          </svg>
        </div>
        <div class="brand-title-wrap">
          <div class="brand-name"><?= e(atur('nama_depot', 'AA WATER')) ?> <span>🔥</span></div>
          <span class="brand-sub">Antar Galon &amp; Gas</span>
        </div>
      </a>

      <!-- Menu Desktop -->
      <nav class="main-nav">
        <?php foreach ($menu as $kode => $item): ?>
          <a href="<?= $item['url'] ?>" class="nav-link<?= $halaman === $kode ? ' active' : '' ?>"><?= e($item['nama']) ?></a>
        <?php endforeach; ?>
      </nav>

      <!-- Tombol Aksi -->
      <div class="header-actions">
        <a href="<?= e(linkWa($pesanOrder)) ?>" target="_blank" class="btn btn-sm btn-wa js-wa-direct">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
          </svg>
          Order Sekarang
        </a>
        <button class="mobile-nav-toggle" id="mobileMenuToggle" aria-label="Buka Menu">&#9776;</button>
      </div>
    </div>

    <!-- Menu HP -->
    <div class="mobile-nav-drawer" id="mobileNavDrawer">
      <?php foreach ($menu as $kode => $item): ?>
        <a href="<?= $item['url'] ?>" class="mobile-nav-link<?= $halaman === $kode ? ' active' : '' ?>"><?= e($item['nama_hp']) ?> <span>&rarr;</span></a>
      <?php endforeach; ?>
    </div>
  </header>
