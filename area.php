<?php
/**
 * area.php — Wilayah yang dilayani
 *
 * Daftar wilayah diambil dari tabel wilayah di database.
 */

require_once __DIR__ . '/konfigurasi.php';

$judul     = 'Cakupan Area Pengiriman — ' . atur('nama_depot', 'AA WATER') . ' Borong Raya';
$deskripsi = 'Wilayah layanan ' . atur('nama_depot') . ': Jl. Borong Raya, Kel. Borong Raya, Batua, dan Toddopuli. Ongkir ' . rupiah((int) atur('ongkir_per_item', '3000')) . ' per item.';
$halaman   = 'area';

$daftarWilayah = ambilWilayah();

include __DIR__ . '/partials/header.php';
?>
  <main>
    <section class="section section-bg">
      <div class="container">
        <div class="section-tag">Radius Fokus & Cepat</div>
        <h1 class="section-title">Wilayah Pengantaran & Estimasi Waktu</h1>
        <p class="section-sub">
          Pangkalan kami berlokasi di <strong>Jl. Borong Raya 1 lr 1 No. 12 A</strong>. Kami membatasi jangkauan khusus pada 4 wilayah terdekat agar pengantaran selalu kilat di bawah 60 menit dengan ongkir terjangkau <strong><?= rupiah((int) atur("ongkir_per_item")) ?> / item</strong>.
        </p>

        <!-- Area Search Box -->
        <div style="background:white; border:2px solid var(--color-border); border-radius:var(--radius-xl); padding:1.5rem; margin-bottom: 2rem;">
          <label for="areaSearchInput" style="display:block; font-weight:800; font-size:1.05rem; margin-bottom:0.5rem; color:var(--color-ink);">
            🔍 Cek Status Alamat Anda:
          </label>
          <div class="area-checker-input-wrap" style="max-width:100%; margin-bottom:1rem;">
            <input type="text" id="areaSearchInput" class="input-utility" placeholder="Ketik Jl. Borong Raya, Kel. Borong, Batua, atau Toddopuli...">
            <button type="button" class="btn btn-primary" id="btnCekArea">Cek Status</button>
          </div>
          <div id="areaSearchResult" class="area-check-result"></div>

          <div style="font-size:0.85rem; color:var(--color-ink-muted); margin-top:0.5rem;">
            💡 <em>Klik salah satu chip wilayah di bawah untuk cek estimasi kurir:</em>
          </div>
          <div class="chips-container" style="margin-top:0.75rem; margin-bottom:0;">
            <?php foreach ($daftarWilayah as $nomor => $wilayah): ?>
              <div class="area-chip<?= $nomor === 0 ? ' active' : '' ?>" data-area="<?= e($wilayah['nama']) ?>"><span class="chip-dot"></span> <?= e($wilayah['nama']) ?></div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tabel 4 Wilayah & Estimasi Waktu Kurir -->
        <div class="table-responsive" style="margin-bottom: 2.5rem; box-shadow: var(--shadow-md);">
          <table class="utility-table">
            <thead>
              <tr>
                <th>Wilayah / Kelurahan</th>
                <th>Status Wilayah</th>
                <th>Estimasi Tiba (SLA)</th>
                <th>Ongkos Kirim</th>
                <th>Aksi Cepat</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($daftarWilayah as $wilayah): ?>
                <tr>
                  <td><strong><?= e($wilayah['nama']) ?></strong></td>
                  <td><span class="item-tag tag-tukar"><?= e($wilayah['ring']) ?></span></td>
                  <td><strong style="color:var(--color-primary);">⚡ <?= e($wilayah['estimasi']) ?></strong></td>
                  <td><strong style="color:#000000;"><?= rupiah((int) $wilayah['ongkir']) ?> / item</strong></td>
                  <td>
                    <a href="<?= e(linkWa('Halo ' . atur('nama_depot') . ', saya di ' . $wilayah['nama'] . '. Pesan sekarang!')) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Order &rarr;</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Outside Area Banner -->
        <div class="outside-area-banner">
          <div>
            <div class="outside-banner-text">📍 Lokasi Anda di luar 4 wilayah utama di atas?</div>
            <div class="outside-banner-sub">Tanya dulu lewat WhatsApp, siapa tahu kurir kami sedang melintasi area Anda!</div>
          </div>
          <a href="<?= e(linkWa("Halo " . atur("nama_depot") . ", mau tanya apakah bisa antar ke alamat saya ini?")) ?>" target="_blank" class="btn btn-accent js-wa-direct">
            Tanya Kurir via WA
          </a>
        </div>
      </div>
    </section>
  </main>

<?php include __DIR__ . "/partials/footer.php"; ?>
