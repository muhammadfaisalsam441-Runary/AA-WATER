<?php
/**
 * index.php — Halaman beranda AA WATER
 *
 * Harga, wilayah, nomor WA, dan jam buka diambil dari database.
 */

require_once __DIR__ . '/konfigurasi.php';

$judul     = atur('nama_depot', 'AA WATER') . ' — Antar Galon & Gas | Sejam Sampe Borong Raya';
$deskripsi = 'AA WATER: Antar galon dan gas LPG 3kg area Jl. Borong Raya, Kel. Borong Raya, Batua, Toddopuli. Antar < ' . atur('sla_menit', '60') . ' menit, ongkir ' . rupiah((int) atur('ongkir_per_item', '3000')) . '/item, harga jujur.';
$halaman   = 'beranda';

// Data dari database
$produkGalon = ambilProduk('galon');
$produkGas   = ambilProduk('gas');
$daftarWilayah = ambilWilayah();

include __DIR__ . '/partials/header.php';
?>
  <main>
    <!-- SECTION 1: HERO SECTION -->
    <section class="hero-section">
      <div class="container">
        <div class="hero-badge-row">
          <div class="sla-pill-big">
            ⚡ ANTAR &lt; <?= e(atur("sla_menit")) ?> MENIT
          </div>
          <div class="area-pill-hero">
            📍 Borong Raya, Batua & Toddopuli
          </div>
        </div>

        <h1 class="hero-headline">
          Galon dan gas LPG sampai depan rumah
        </h1>

        <p class="hero-sub">
          Harga di bawah, area di bawah — kalau masuk, tinggal WA. Tetangga andalan Anda di Borong Raya. Galon higienis bersegel & tabung gas 3kg resmi teruji aman. Ongkir jelas <?= rupiah((int) atur("ongkir_per_item")) ?> / item.
        </p>

        <div class="hero-cta-group">
          <a href="<?= e(linkWa("Halo " . atur("nama_depot") . ", saya mau order galon/gas sekarang!")) ?>" target="_blank" class="btn btn-wa hero-cta-wa js-wa-direct">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            Kirim Format WA Cepat
          </a>
          <a href="#hargaSection" class="btn btn-outline-primary">
            Lihat Harga &darr;
          </a>
        </div>

        <!-- Trust Signals Bar -->
        <div class="hero-trust-bar">
          <div class="hero-trust-item">
            <svg class="hero-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Ongkir Murah <?= rupiah((int) atur("ongkir_per_item")) ?> / Item</span>
          </div>
          <div class="hero-trust-item">
            <svg class="hero-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Tanpa Minimal Order</span>
          </div>
          <div class="hero-trust-item">
            <svg class="hero-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Hujan Tetap Diantar</span>
          </div>
          <div class="hero-trust-item">
            <svg class="hero-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Uji Air Sabun Gratis di Tempat</span>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 2: HARGA (SIGNATURE) -->
    <section class="section section-white" id="hargaSection">
      <div class="container">
        <div class="text-center" style="margin-bottom: 2rem;">
          <div class="section-tag">Transparansi Mutlak</div>
          <h2 class="section-title">HARGA</h2>
          <p class="section-sub" style="margin-left: auto; margin-right: auto;">
            Tukar vs Beli Baru — Kami jelasin biar ga bingung. Harga jujur + Ongkir <?= rupiah((int) atur("ongkir_per_item")) ?> per item sampai depan pintu Anda.
          </p>
        </div>

        <!-- Card Harga -->
        <div class="signature-card">
          <div class="signature-badge">Harga Pas & Jujur</div>

          <div class="price-grid-2col">
            <?php
            // Dua kolom: galon di kiri, gas di kanan.
            $kolom = [
              ['judul' => 'Galon Air Minum',  'ikon' => '💧', 'kelas' => 'water', 'ket' => '19 Liter',           'produk' => $produkGalon],
              ['judul' => 'Gas LPG 3KG (Melon)', 'ikon' => '🔥', 'kelas' => 'gas', 'ket' => 'Resmi & Bersegel', 'produk' => $produkGas],
            ];
            ?>
            <?php foreach ($kolom as $isiKolom): ?>
            <div class="category-box">
              <div class="category-header">
                <div class="category-title">
                  <div class="category-icon <?= $isiKolom['kelas'] ?>"><?= $isiKolom['ikon'] ?></div>
                  <div><?= e($isiKolom['judul']) ?></div>
                </div>
                <span style="font-size:0.8rem; font-weight:700; color:var(--color-primary-dark);"><?= e($isiKolom['ket']) ?></span>
              </div>

              <?php foreach ($isiKolom['produk'] as $produk): ?>
                <?php
                // Produk sistem tukar diberi bingkai penanda "paling laris"
                $kelasKartu = '';
                if ($produk['tipe'] === 'tukar') {
                    $kelasKartu = $produk['kategori'] === 'galon' ? ' highlight' : ' highlight-gas';
                }
                $kelasLabel = $produk['tipe'] === 'tukar' ? 'tag-tukar' : 'tag-beli';
                ?>
                <div class="price-row-card<?= $kelasKartu ?>">
                  <div class="row-top">
                    <div class="item-info">
                      <span class="item-tag <?= $kelasLabel ?>"><?= e($produk['label']) ?></span>
                      <div class="item-name"><?= e($produk['nama']) ?></div>
                      <div class="item-desc"><?= e($produk['deskripsi']) ?></div>
                    </div>
                    <div class="item-price-wrap">
                      <div class="price-figure" style="color:#000000;"><?= rupiah((int) $produk['harga']) ?></div>
                      <div class="price-unit"><?= e($produk['satuan']) ?></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <!-- Penjelasan Tukar vs Beli -->
          <div class="explain-box">
            <div class="explain-icon">💡</div>
            <div class="explain-text">
              <h4>Tukar vs Beli: Kami Jelasin Biar Ga Bingung</h4>
              <p>
                <strong>Sistem Tukar:</strong> Anda sudah punya wadah galon atau tabung kosong di rumah, jadi Anda <em>cuma bayar isinya saja</em> (Galon Rp5.000 / Gas Rp22.000) + ongkir <?= rupiah((int) atur("ongkir_per_item")) ?>/item.<br>
                <strong>Beli Baru:</strong> Jika Anda belum punya wadah sama sekali atau ingin nambah tabung cadangan, Anda membeli tabung/galonnya sebagai aset hak milik Anda selamanya. Pengisian berikutnya cukup pakai sistem tukar yang super hemat!
              </p>
            </div>
          </div>
        </div>

        <!-- QUICK ORDER BUILDER (INTERAKTIF JQUERY) -->
        <div class="order-builder-box" id="orderBuilder">
          <div class="builder-header">
            <div>
              <div class="builder-title">⚡ Hitung & Pesan Instan via WhatsApp</div>
              <div class="builder-sub">Pilih jumlah kebutuhan di bawah, ongkir <?= rupiah((int) atur("ongkir_per_item")) ?>/item dan total terhitung otomatis!</div>
            </div>
            <div style="font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); background:var(--color-primary-light); padding:0.4rem 0.85rem; border-radius:var(--radius-full);">
              Antar Segera &lt; <?= e(atur("sla_menit")) ?> Menit
            </div>
          </div>

          <!-- Grid Tombol Plus-Minus -->
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

          <!-- Summary & Kirim WA Button -->
          <div class="builder-summary-card">
            <div>
              <div class="builder-total-label" id="builderItemCount">0 item dipilih • Ongkir Rp0</div>
              <div class="builder-total-price" id="builderSubtotal">Rp0</div>
              <div class="builder-total-desc" id="builderSummaryDesc">Pilih jumlah di atas, atau langsung klik tombol untuk order via WA</div>
            </div>
            <div class="builder-action-btns">
              <button type="button" class="btn btn-wa btn-lg" id="btnSendOrderWA">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                Pesan via WhatsApp Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 3: CARA ORDER 2 LANGKAH -->
    <section class="section section-bg">
      <div class="container">
        <div class="text-center" style="margin-bottom: 2.5rem;">
          <div class="section-tag accent">Gampang Banget</div>
          <h2 class="section-title">Cara Order 2 Langkah</h2>
          <p class="section-sub" style="margin-left: auto; margin-right: auto;">
            Ga perlu download aplikasi aneh-aneh. Cukup kirim WA satu baris, langsung kami gas!
          </p>
        </div>

        <div class="order-steps-grid">
          <!-- Langkah 1 -->
          <div class="step-card">
            <div class="step-num-badge">1</div>
            <div class="step-title">Kirim Format WA Singkat</div>
            <div class="step-body">
              Ketik pesanan Anda dan alamat rumah. Bisa langsung salin format siap kirim ini:
            </div>
            <div class="format-box">
              <span>nama dan jumlah item + [nama] + [alamat]</span>
              <button type="button" class="btn-copy-format js-copy-btn" data-copy-text="nama dan jumlah item + [nama] + [alamat]">
                Salin Format
              </button>
            </div>
          </div>

          <!-- Langkah 2 -->
          <div class="step-card">
            <div class="step-num-badge accent">2</div>
            <div class="step-title">Kurir Gaspol &lt; <?= e(atur("sla_menit")) ?> Menit, Bayar di Tempat</div>
            <div class="step-body">
              Kurir langsung jalan bawa pesanan. Bisa bayar tunai (COD), scan QRIS, atau transfer saat barang sampai di depan pintu Anda. Sesimpel itu!
            </div>
            <div style="background:#FFFFFF; border:1px solid var(--color-border); border-radius:var(--radius-md); padding:0.85rem 1rem; font-size:0.875rem; color:var(--color-ink); font-weight:600;">
              💵 Cash di tempat • 📲 QRIS Semua E-Wallet • 💳 Transfer Bank
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 4: AREA TERLAYANI (KHUSUS 4 WILAYAH) -->
    <section class="section section-white" id="areaSection">
      <div class="container">
        <div style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom: 1.5rem;">
          <div class="section-tag">Jangkauan Wilayah</div>
          <h2 class="section-title">Area Terlayani Kurir Kami</h2>
          <p class="section-sub">
            Fokus melayani 4 wilayah prioritas agar jaminan waktu antar &lt; 60 menit selalu terpenuhi (Ongkir <?= rupiah((int) atur("ongkir_per_item")) ?> / item):
          </p>
        </div>

        <!-- Interactive Area Filter Input -->
        <div class="area-checker-input-wrap">
          <input type="text" id="areaSearchInput" class="input-utility" placeholder="Cek alamat: ketik Borong, Batua, atau Toddopuli...">
          <button type="button" class="btn btn-primary" id="btnCekArea">Cek Area</button>
        </div>

        <!-- Area Search Result Notification (jQuery slide) -->
        <div id="areaSearchResult" class="area-check-result"></div>

        <!-- Chips 4 Kelurahan Prioritas -->
        <div class="chips-container">
          <?php foreach ($daftarWilayah as $nomor => $wilayah): ?>
            <div class="area-chip<?= $nomor === 0 ? ' active' : '' ?>" data-area="<?= e($wilayah['nama']) ?>"><span class="chip-dot"></span> <?= e($wilayah['nama']) ?></div>
          <?php endforeach; ?>
        </div>

        <!-- Di Luar Itu Banner -->
        <div class="outside-area-banner">
          <div>
            <div class="outside-banner-text">📍 Rumah Anda di luar 4 wilayah utama di atas?</div>
            <div class="outside-banner-sub">Tanya kurir dulu via WhatsApp, siapa tahu rute kurir hari ini lagi searah dengan jalan rumah Anda!</div>
          </div>
          <a href="<?= e(linkWa("Halo " . atur("nama_depot") . ", mau tanya apakah bisa antar ke alamat saya ini?")) ?>" target="_blank" class="btn btn-sm btn-accent js-wa-direct">
            Tanya Kurir via WA &rarr;
          </a>
        </div>
      </div>
    </section>

    <!-- SECTION 5: FAQ RINGKAS -->
    <section class="section section-bg">
      <div class="container">
        <div class="text-center" style="margin-bottom: 2rem;">
          <div class="section-tag">Jawaban Pasti</div>
          <h2 class="section-title">Pertanyaan Sering Ditanyakan</h2>
          <p class="section-sub" style="margin-left: auto; margin-right: auto;">
            Jawaban to-the-point tanpa basa-basi untuk kenyamanan Anda.
          </p>
        </div>

        <div class="faq-grid">
          <div class="faq-card">
            <div class="faq-q"><span class="faq-q-icon">?</span> Berapa ongkos kirimnya?</div>
            <div class="faq-a"><strong>Ongkos kirim hanya <?= rupiah((int) atur("ongkir_per_item")) ?> per item!</strong> Tarif flat berlaku untuk wilayah Jl. Borong Raya, Kel. Borong Raya, Batua, dan Toddopuli.</div>
          </div>

          <div class="faq-card">
            <div class="faq-q"><span class="faq-q-icon">?</span> Minimal order berapa?</div>
            <div class="faq-a"><strong>Tidak ada minimal order!</strong> Pesan cuma 1 galon atau cuma 1 gas 3kg tetap kami antar sampai depan pintu rumah Anda.</div>
          </div>

          <div class="faq-card">
            <div class="faq-q"><span class="faq-q-icon">?</span> Kalau hujan tetap diantar?</div>
            <div class="faq-a"><strong>Iya, hujan badai tetap diantar.</strong> Armada kami dilengkapi mantel & terpal penutup khusus galon & tabung gas agar tetap bersih.</div>
          </div>

          <div class="faq-card">
            <div class="faq-q"><span class="faq-q-icon">?</span> Bagaimana kalau tabung gas mendesis / bocor?</div>
            <div class="faq-a"><strong>Garansi tukar baru di tempat gratis!</strong> Kurir kami selalu membawa karet seal cadangan dan siap tes air sabun langsung di hadapan Anda.</div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 6: CTA BAND BIRU -->
    <section class="cta-band">
      <div class="container cta-band-content">
        <h2 class="cta-band-title">Jangan nunggu tetes terakhir.</h2>
        <p class="cta-band-sub">
          Tengah masak api mati? Galon dispenser kering pas haus? Langsung WA sekarang, kurir AA WATER langsung meluncur &lt; 60 menit!
        </p>
        <a href="<?= e(linkWa("Halo " . atur("nama_depot") . ", saya mau order galon/gas sekarang!")) ?>" target="_blank" class="btn btn-wa cta-band-btn js-wa-direct">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
          </svg>
          Order Sekarang via WhatsApp
        </a>
      </div>
    </section>
  </main>

<?php include __DIR__ . "/partials/footer.php"; ?>
