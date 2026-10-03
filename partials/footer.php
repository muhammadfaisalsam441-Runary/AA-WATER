<?php
/**
 * partials/footer.php — Bagian bawah semua halaman
 *
 * Dipanggil di baris paling akhir setiap halaman:
 *   include __DIR__ . '/partials/footer.php';
 */

require_once __DIR__ . '/../konfigurasi.php';

$pesanOrder = 'Halo ' . atur('nama_depot', 'AA WATER') . ', saya mau order galon/gas sekarang!';

// Data untuk JavaScript: harga, wilayah, nomor WA, dan jam buka.
// Dikirim dari database supaya JavaScript tidak perlu menulis ulang harganya.
$dataUntukJs = [
    'phone'       => atur('nomor_wa'),
    'displayPhone'=> atur('nomor_tampil'),
    'address'     => atur('alamat_depot'),
    'openHour'    => (int) atur('jam_buka', '8'),
    'closeHour'   => (int) atur('jam_tutup', '21'),
    'feePerItem'  => (int) atur('ongkir_per_item', '3000'),
    'pricing'     => [],
    'deliveryArea'=> [],
];

foreach (ambilProduk() as $produk) {
    $dataUntukJs['pricing'][$produk['kode']] = [
        'name'  => $produk['nama'],
        'price' => (int) $produk['harga'],
        'type'  => $produk['tipe'],
    ];
}

foreach (ambilWilayah() as $wilayah) {
    $dataUntukJs['deliveryArea'][] = [
        'name' => $wilayah['nama'],
        'eta'  => $wilayah['estimasi'],
        'fee'  => rupiah((int) $wilayah['ongkir']) . ' / item',
        'kata' => $wilayah['kata_kunci'],
    ];
}
?>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <h3><?= e(atur('nama_depot', 'AA WATER')) ?></h3>
          <div class="footer-tagline">Galon dan gas LPG sampai depan rumah</div>
          <p class="footer-desc">
            Layanan antar galon isi ulang higienis &amp; tabung gas 3kg resmi terpercaya untuk rumah tangga dan warung di area Jl. Borong Raya, Kel. Borong Raya, Batua, dan Toddopuli.
          </p>
        </div>
        <div>
          <div class="footer-col-title">Navigasi Halaman</div>
          <ul class="footer-links">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="harga.php">Harga</a></li>
            <li><a href="pesan.php">Form Pemesanan</a></li>
            <li><a href="area.php">Cek Area Layanan</a></li>
            <li><a href="kontak.php">Kontak &amp; Lokasi Depot</a></li>
          </ul>
        </div>
        <div>
          <div class="footer-col-title">Pangkalan &amp; Jam Buka</div>
          <p style="font-size:0.875rem; margin-bottom:0.5rem; color:#A5D8FF;">
            📍 <?= e(atur('alamat_depot')) ?><br>
            🕒 <?= sprintf('%02d.00', (int) atur('jam_buka', '8')) ?> – <?= sprintf('%02d.00', (int) atur('jam_tutup', '21')) ?> WITA (Buka Setiap Hari)<br>
            ⚡ Garansi Antar &lt; <?= e(atur('sla_menit', '60')) ?> Menit • Ongkir <?= rupiah((int) atur('ongkir_per_item', '3000')) ?>/item
          </p>
          <a href="<?= e(linkWa()) ?>" target="_blank" style="display:inline-block; margin-top:0.5rem; color:#25D366; font-weight:700; font-size:0.95rem;">
            📱 WA: <?= e(atur('nomor_tampil')) ?>
          </a>
        </div>
      </div>

      <div class="footer-bottom">
        <div>&copy; <?= date('Y') ?> NGALIR. Hak cipta dilindungi undang-undang.</div>
        <div class="footer-bottom-brand"><?= e(atur('nama_depot', 'AA WATER')) ?> • <?= e(atur('alamat_depot')) ?></div>
      </div>
    </div>
  </footer>

  <!-- MOBILE STICKY WA FLOATING BAR (99% ORDER DARI HP) -->
  <div class="mobile-floating-bar">
    <div class="mobile-floating-info">
      <span class="floating-sla">⚡ ANTAR &lt; <?= e(atur('sla_menit', '60')) ?> MENIT</span>
      <span class="floating-status js-floating-status">
        <?php if (depotBuka()): ?>
          🟢 <strong>Buka</strong> (Siap Kirim)
        <?php else: ?>
          🔴 <strong>Tutup</strong> (Pesan utk besok)
        <?php endif; ?>
      </span>
    </div>
    <a href="<?= e(linkWa($pesanOrder)) ?>" target="_blank" class="btn btn-wa mobile-floating-btn js-wa-direct">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
      </svg>
      Order Sekarang
    </a>
  </div>

  <!-- Data dari database untuk dipakai JavaScript -->
  <script>
    window.AA_DATA = <?= json_encode($dataUntukJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  </script>

  <!-- JQUERY & JAVASCRIPT APP (Lokal & Cepat) -->
  <!-- Angka di belakang app.js adalah waktu file terakhir diubah. Gunanya
       supaya browser pengunjung mengambil versi terbaru, bukan versi lama
       yang tersimpan di cache. -->
  <script src="js/jquery-3.7.1.min.js"></script>
  <script src="js/app.js?v=<?= filemtime(__DIR__ . '/../js/app.js') ?>"></script>
</body>
</html>
