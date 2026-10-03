<?php
/**
 * harga.php — Daftar harga lengkap
 *
 * Isi tabel dan kalkulator diambil dari tabel produk di database.
 */

require_once __DIR__ . '/konfigurasi.php';

$judul     = 'Daftar Harga Lengkap — ' . atur('nama_depot', 'AA WATER') . ' (Tukar vs Beli)';
$deskripsi = 'Katalog harga lengkap galon air & tabung gas 3kg ' . atur('nama_depot') . '. Harga jelas tanpa biaya siluman, ongkir ' . rupiah((int) atur('ongkir_per_item', '3000')) . '/item.';
$halaman   = 'harga';

$semuaProduk = ambilProduk();

include __DIR__ . '/partials/header.php';
?>
  <main>
    <section class="section section-bg">
      <div class="container">
        <div class="section-tag">Katalog Resmi & Akurat</div>
        <h1 class="section-title">Harga: Tanpa Biaya Siluman</h1>
        <p class="section-sub">
          Di AA WATER, semua harga kami buka terang-benderang. Harga barang pas + Ongkir tetap <?= rupiah((int) atur("ongkir_per_item")) ?> per item sampai depan pintu Anda.
        </p>

        <!-- Tabel Lengkap Harga Transparan -->
        <div class="table-responsive" style="margin-bottom: 2.5rem; box-shadow: var(--shadow-md);">
          <table class="utility-table">
            <thead>
              <tr>
                <th>Kategori Produk</th>
                <th>Tipe Pesanan</th>
                <th>Harga Barang</th>
                <th>Ongkos Kirim</th>
                <th>Keterangan / Yang Didapat</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($semuaProduk as $produk): ?>
                <tr>
                  <td><strong><?= $produk['kategori'] === 'galon' ? '💧 Galon Air Minum' : '🔥 Gas LPG 3KG' ?></strong></td>
                  <td><span class="item-tag <?= $produk['tipe'] === 'tukar' ? 'tag-tukar' : 'tag-beli' ?>"><?= e($produk['label']) ?></span></td>
                  <td><strong style="color:#000000; font-size:1.2rem;"><?= rupiah((int) $produk['harga']) ?></strong></td>
                  <td><span style="color:#000000; font-weight:700;"><?= rupiah((int) atur('ongkir_per_item', '3000')) ?> / item</span></td>
                  <td><?= e($produk['deskripsi']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Edukasi Kepemilikan Tabung & Galon -->
        <div style="background:white; border:2px solid var(--color-border); border-radius:var(--radius-xl); padding:1.75rem; margin-bottom: 2.5rem;">
          <h2 style="font-size:1.35rem; font-weight:800; color:var(--color-ink); margin-bottom:1rem;">
            📌 Kenapa Tabung Gas & Galon Pertama Kali Lebih Mahal?
          </h2>
          <p style="font-size:0.95rem; color:var(--color-ink-muted); line-height:1.6; margin-bottom:1rem;">
            Banyak warga baru yang kaget saat pertama kali membeli gas atau galon karena harganya ratusan ribu. <strong>Penjelasannya sederhana:</strong>
          </p>
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(280px, 100%), 1fr)); gap:1rem;">
            <div style="background:var(--color-section); border-radius:var(--radius-md); padding:1.2rem; border-left:4px solid var(--color-primary);">
              <h4 style="font-size:1.05rem; font-weight:800; color:var(--color-primary-dark); margin-bottom:0.35rem;">1. Wadah Adalah Aset Milik Anda</h4>
              <p style="font-size:0.875rem; color:var(--color-ink-muted);">
                Saat Anda membeli "Tabung Baru" seharga Rp175.000 atau "Galon Baru" seharga Rp30.000, fisik tabung besi atau galon plastik tersebut adalah hak milik pribadi Anda seumur hidup.
              </p>
            </div>
            <div style="background:var(--color-section); border-radius:var(--radius-md); padding:1.2rem; border-left:4px solid var(--color-accent);">
              <h4 style="font-size:1.05rem; font-weight:800; color:var(--color-accent-dark); margin-bottom:0.35rem;">2. Seterusnya Cuma Bayar Isinya</h4>
              <p style="font-size:0.875rem; color:var(--color-ink-muted);">
                Setelah memiliki wadah tabung/galon di rumah, setiap kali habis Anda tinggal menukarnya ke AA WATER. Anda hanya perlu bayar Rp5.000 untuk galon dan Rp22.000 untuk gas (+ ongkir <?= rupiah((int) atur("ongkir_per_item")) ?>/item)!
              </p>
            </div>
          </div>
        </div>

        <!-- KALKULATOR PESANAN INTERAKTIF -->
        <div class="order-builder-box" id="orderBuilder">
          <div class="builder-header">
            <div>
              <div class="builder-title">Kalkulator Pesanan Instan</div>
              <div class="builder-sub">Pilih jumlah item Anda di bawah untuk langsung menghitung biaya barang + ongkir <?= rupiah((int) atur("ongkir_per_item")) ?>/item:</div>
            </div>
            <div style="font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); background:var(--color-primary-light); padding:0.4rem 0.85rem; border-radius:var(--radius-full);">
              Tanpa Minimal Order
            </div>
          </div>

          <div class="builder-items-grid">
            <?php foreach (produkKalkulator() as $produk): ?>
              <div class="builder-row">
                <div class="builder-row-info">
                  <div class="builder-row-title"><?= $produk['kategori'] === 'galon' ? '💧' : '🔥' ?> <?= e($produk['nama']) ?></div>
                  <div class="builder-row-price"><?= rupiah((int) $produk['harga']) ?> <?= e($produk['satuan']) ?> (+ ongkir <?= rupiah((int) atur('ongkir_per_item', '3000')) ?>)</div>
                </div>
                <div class="qty-control">
                  <button type="button" class="qty-btn js-qty-minus" data-item="<?= e($produk['kode']) ?>" aria-label="Kurangi <?= e($produk['nama']) ?>">-</button>
                  <span class="qty-display js-qty-val" data-item="<?= e($produk['kode']) ?>">0</span>
                  <button type="button" class="qty-btn js-qty-plus" data-item="<?= e($produk['kode']) ?>" aria-label="Tambah <?= e($produk['nama']) ?>">+</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="builder-summary-card">
            <div>
              <div class="builder-total-label" id="builderItemCount">0 item dipilih • Ongkir Rp0</div>
              <div class="builder-total-price" id="builderSubtotal">Rp0</div>
              <div class="builder-total-desc" id="builderSummaryDesc">Pilih jumlah di atas, atau langsung klik tombol untuk order via WA</div>
            </div>
            <div class="builder-action-btns">
              <button type="button" class="btn btn-wa btn-lg" id="btnSendOrderWA">
                Pesan via WhatsApp Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php include __DIR__ . "/partials/footer.php"; ?>
