<?php
/**
 * pesan.php — Form pemesanan
 *
 * Alur halaman ini:
 *   1. Pengunjung mengisi form (nama, WhatsApp, alamat, jumlah barang).
 *   2. Data dikirim dengan method POST ke halaman ini juga.
 *   3. PHP memeriksa isian. Kalau ada yang salah, form ditampilkan lagi
 *      lengkap dengan pesan kesalahan dan isian sebelumnya.
 *   4. Kalau benar, pesanan disimpan ke tabel pesanan + pesanan_item,
 *      lalu halaman dialihkan ke ?sukses=KODE supaya pesanan tidak
 *      tersimpan dua kali saat pengunjung menekan tombol muat ulang.
 */

require_once __DIR__ . '/konfigurasi.php';

$judul     = 'Form Pemesanan — ' . atur('nama_depot', 'AA WATER');
$deskripsi = 'Pesan galon air dan gas LPG 3kg lewat form. Pesanan langsung masuk ke depot ' . atur('nama_depot') . '.';
$halaman   = 'pesan';

$daftarProduk  = ambilProduk();
$daftarWilayah = ambilWilayah();
$ongkirPerItem = (int) atur('ongkir_per_item', '3000');

$kesalahan = [];   // daftar pesan kesalahan
$isian     = [];   // isian pengunjung, dipakai untuk mengisi ulang form

// ---------------------------------------------------------------------------
// A. Memproses form yang dikirim
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil isian, buang spasi di awal dan akhir
    $isian = [
        'nama'         => trim($_POST['nama'] ?? ''),
        'whatsapp'     => trim($_POST['whatsapp'] ?? ''),
        'alamat'       => trim($_POST['alamat'] ?? ''),
        'patokan'      => trim($_POST['patokan'] ?? ''),
        'wilayah_id'   => (int) ($_POST['wilayah_id'] ?? 0),
        'metode_bayar' => $_POST['metode_bayar'] ?? 'cash',
        'catatan'      => trim($_POST['catatan'] ?? ''),
        'jumlah'       => $_POST['jumlah'] ?? [],
    ];

    // --- Pemeriksaan 1: nama ---
    if ($isian['nama'] === '') {
        $kesalahan['nama'] = 'Nama penerima wajib diisi.';
    } elseif (mb_strlen($isian['nama']) > 100) {
        $kesalahan['nama'] = 'Nama terlalu panjang, maksimal 100 huruf.';
    }

    // --- Pemeriksaan 2: nomor WhatsApp ---
    $nomorBersih = preg_replace('/[^0-9]/', '', $isian['whatsapp']);
    if ($nomorBersih === '') {
        $kesalahan['whatsapp'] = 'Nomor WhatsApp wajib diisi.';
    } elseif (strlen($nomorBersih) < 9 || strlen($nomorBersih) > 15) {
        $kesalahan['whatsapp'] = 'Nomor WhatsApp tidak wajar, isi 9 sampai 15 angka.';
    }

    // --- Pemeriksaan 3: alamat ---
    if ($isian['alamat'] === '') {
        $kesalahan['alamat'] = 'Alamat pengantaran wajib diisi.';
    } elseif (mb_strlen($isian['alamat']) < 10) {
        $kesalahan['alamat'] = 'Alamat terlalu singkat. Tulis nama jalan, lorong, dan nomor rumah.';
    }

    // --- Pemeriksaan 4: wilayah harus salah satu yang dilayani ---
    $wilayahDipilih = null;
    foreach ($daftarWilayah as $w) {
        if ((int) $w['id'] === $isian['wilayah_id']) {
            $wilayahDipilih = $w;
        }
    }
    if ($wilayahDipilih === null) {
        $kesalahan['wilayah_id'] = 'Pilih salah satu wilayah yang kami layani.';
    }

    // --- Pemeriksaan 5: metode bayar ---
    if (!in_array($isian['metode_bayar'], ['cash', 'qris', 'transfer'], true)) {
        $kesalahan['metode_bayar'] = 'Metode pembayaran tidak dikenal.';
    }

    // --- Pemeriksaan 6: barang yang dipesan ---
    // Harga diambil dari database, BUKAN dari form. Kalau harga ikut dikirim
    // lewat form, pengunjung bisa mengubahnya lewat browser.
    $barang     = [];
    $subtotal   = 0;
    $totalItem  = 0;

    foreach ($daftarProduk as $produk) {
        $jumlah = (int) ($isian['jumlah'][$produk['kode']] ?? 0);

        if ($jumlah < 0) {
            $jumlah = 0;
        }
        if ($jumlah > 99) {
            $kesalahan['jumlah'] = 'Jumlah tiap barang maksimal 99. Untuk pesanan besar, hubungi kami langsung.';
            $jumlah = 99;
        }

        if ($jumlah > 0) {
            $hargaBarang = (int) $produk['harga'];
            $barang[] = [
                'produk_id' => (int) $produk['id'],
                'nama'      => $produk['nama'],
                'harga'     => $hargaBarang,
                'jumlah'    => $jumlah,
                'subtotal'  => $hargaBarang * $jumlah,
            ];
            $subtotal  += $hargaBarang * $jumlah;
            $totalItem += $jumlah;
        }
    }

    if (empty($barang)) {
        $kesalahan['jumlah'] = 'Pilih minimal 1 barang yang ingin dipesan.';
    }

    $ongkir = $totalItem * $ongkirPerItem;
    $total  = $subtotal + $ongkir;

    // --- Semua pemeriksaan lolos: simpan ke database ---
    if (empty($kesalahan)) {

        // Nomor pesanan, contoh: AA-20260101-001
        $hariIni      = date('Ymd');
        $jumlahHariIni = (int) ambilSatu(
            'SELECT COUNT(*) AS n FROM pesanan WHERE DATE(dibuat_pada) = CURDATE()'
        )['n'];
        $kodePesanan = 'AA-' . $hariIni . '-' . str_pad($jumlahHariIni + 1, 3, '0', STR_PAD_LEFT);

        // Transaksi: kalau salah satu perintah gagal, semuanya dibatalkan,
        // supaya tidak ada pesanan yang tersimpan tanpa rincian barang.
        mysqli_begin_transaction($koneksi);

        try {
            query(
                'INSERT INTO pesanan
                   (kode_pesanan, nama, whatsapp, alamat, patokan, wilayah_id,
                    metode_bayar, subtotal, ongkir, total, catatan)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $kodePesanan,
                    $isian['nama'],
                    $nomorBersih,
                    $isian['alamat'],
                    $isian['patokan'],
                    (int) $wilayahDipilih['id'],
                    $isian['metode_bayar'],
                    $subtotal,
                    $ongkir,
                    $total,
                    $isian['catatan'],
                ]
            );

            $idPesanan = idTerakhir();

            foreach ($barang as $item) {
                query(
                    'INSERT INTO pesanan_item
                       (pesanan_id, produk_id, nama_produk, harga, jumlah, subtotal)
                     VALUES (?,?,?,?,?,?)',
                    [
                        $idPesanan,
                        $item['produk_id'],
                        $item['nama'],
                        $item['harga'],
                        $item['jumlah'],
                        $item['subtotal'],
                    ]
                );
            }

            mysqli_commit($koneksi);

            // Pindah halaman supaya pesanan tidak tersimpan dua kali
            header('Location: pesan.php?sukses=' . urlencode($kodePesanan));
            exit;

        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            error_log('Gagal menyimpan pesanan: ' . $e->getMessage());
            $kesalahan['umum'] = 'Pesanan gagal disimpan. Silakan coba lagi atau pesan lewat WhatsApp.';
        }
    }
}

// ---------------------------------------------------------------------------
// B. Halaman berhasil (dibuka setelah pesanan tersimpan)
// ---------------------------------------------------------------------------
$pesananSukses = null;
$itemSukses    = [];

if (isset($_GET['sukses'])) {
    $pesananSukses = ambilSatu(
        'SELECT p.*, w.nama AS nama_wilayah, w.estimasi
           FROM pesanan p
           LEFT JOIN wilayah w ON w.id = p.wilayah_id
          WHERE p.kode_pesanan = ?',
        [$_GET['sukses']]
    );

    if ($pesananSukses) {
        $itemSukses = ambilSemua(
            'SELECT * FROM pesanan_item WHERE pesanan_id = ?',
            [(int) $pesananSukses['id']]
        );

        $judul = 'Pesanan ' . $pesananSukses['kode_pesanan'] . ' Terkirim — ' . atur('nama_depot', 'AA WATER');
    }
}

include __DIR__ . '/partials/header.php';
?>

  <main>
    <section class="section section-bg">
      <div class="container">

<?php if ($pesananSukses): ?>
        <!-- ============ TAMPILAN SETELAH PESANAN TERSIMPAN ============ -->
        <?php
        // Susun pesan WhatsApp berisi rincian pesanan yang baru tersimpan
        $barisPesan = [];
        foreach ($itemSukses as $item) {
            $barisPesan[] = '- ' . $item['nama_produk'] . ': ' . $item['jumlah'] . ' buah (' . rupiah((int) $item['subtotal']) . ')';
        }
        $pesanWa = "Halo " . atur('nama_depot') . ", saya baru kirim pesanan lewat website.\n"
                 . "Nomor pesanan: " . $pesananSukses['kode_pesanan'] . "\n"
                 . "Nama: " . $pesananSukses['nama'] . "\n\n"
                 . implode("\n", $barisPesan) . "\n\n"
                 . "Total: " . rupiah((int) $pesananSukses['total']) . "\n"
                 . "Alamat: " . $pesananSukses['alamat'];
        ?>

        <div class="text-center" style="margin-bottom: 2rem;">
          <div class="section-tag">Pesanan Terkirim</div>
          <h1 class="section-title">Terima kasih, <?= e($pesananSukses['nama']) ?>!</h1>
          <p class="section-sub" style="margin-left:auto; margin-right:auto;">
            Pesanan Anda sudah masuk ke depot kami. Simpan nomor pesanan di bawah ini.
          </p>
        </div>

        <div class="signature-card" style="max-width:620px; margin:0 auto;">
          <div class="signature-badge">Nomor Pesanan</div>

          <div style="font-family:var(--font-display); font-size:1.6rem; font-weight:900; color:var(--color-primary-dark); margin-bottom:1.25rem;">
            <?= e($pesananSukses['kode_pesanan']) ?>
          </div>

          <div class="table-responsive" style="margin-bottom:1.25rem;">
          <table class="utility-table">
            <tbody>
              <?php foreach ($itemSukses as $item): ?>
                <tr>
                  <td><?= e($item['nama_produk']) ?></td>
                  <td style="text-align:right; white-space:nowrap;"><?= (int) $item['jumlah'] ?> x <?= rupiah((int) $item['harga']) ?></td>
                  <td style="text-align:right; white-space:nowrap;"><strong><?= rupiah((int) $item['subtotal']) ?></strong></td>
                </tr>
              <?php endforeach; ?>
              <tr>
                <td colspan="2">Ongkos kirim</td>
                <td style="text-align:right;"><?= rupiah((int) $pesananSukses['ongkir']) ?></td>
              </tr>
              <tr>
                <td colspan="2"><strong>Total bayar</strong></td>
                <td style="text-align:right;"><strong style="font-size:1.15rem;"><?= rupiah((int) $pesananSukses['total']) ?></strong></td>
              </tr>
            </tbody>
          </table>
          </div>

          <div style="font-size:0.95rem; color:var(--color-ink-muted); line-height:1.7; margin-bottom:1.5rem;">
            📍 <?= e($pesananSukses['alamat']) ?><br>
            <?php if ($pesananSukses['nama_wilayah']): ?>
              🛵 <?= e($pesananSukses['nama_wilayah']) ?> — perkiraan tiba <?= e($pesananSukses['estimasi']) ?><br>
            <?php endif; ?>
            💳 Pembayaran: <?= e(strtoupper($pesananSukses['metode_bayar'])) ?>
          </div>

          <div class="explain-box" style="margin-top:0">
            <div class="explain-icon">💬</div>
            <div class="explain-text">
              <h4>Satu langkah lagi</h4>
              <p>Klik tombol di bawah untuk mengabari kurir lewat WhatsApp, supaya pesanan Anda langsung diproses.</p>
            </div>
          </div>

          <a href="<?= e(linkWa($pesanWa)) ?>" target="_blank" class="btn btn-wa btn-lg" style="width:100%; margin-top:1.25rem;">
            Kabari Kurir via WhatsApp
          </a>

          <a href="index.php" class="btn btn-outline-primary" style="width:100%; margin-top:0.75rem;">
            Kembali ke Beranda
          </a>
        </div>

<?php else: ?>
        <!-- ============ FORM PEMESANAN ============ -->
        <div class="text-center" style="margin-bottom: 2rem;">
          <div class="section-tag">Pesan Tanpa Chat</div>
          <h1 class="section-title">Form Pemesanan</h1>
          <p class="section-sub" style="margin-left:auto; margin-right:auto;">
            Isi form di bawah, pesanan langsung masuk ke depot kami. Ongkir <?= rupiah($ongkirPerItem) ?> per item.
          </p>
        </div>

        <?php if (isset($kesalahan['umum'])): ?>
          <div class="outside-area-banner" style="margin-bottom:1.5rem;">
            <div class="outside-banner-text">⚠️ <?= e($kesalahan['umum']) ?></div>
          </div>
        <?php elseif (!empty($kesalahan)): ?>
          <div class="outside-area-banner" style="margin-bottom:1.5rem;">
            <div>
              <div class="outside-banner-text">⚠️ Ada <?= count($kesalahan) ?> isian yang perlu diperbaiki</div>
              <div class="outside-banner-sub">Silakan periksa bagian yang ditandai merah di bawah.</div>
            </div>
          </div>
        <?php endif; ?>

        <form method="post" action="pesan.php" class="order-builder-box" style="margin-top:0">

          <!-- Bagian 1: barang yang dipesan -->
          <div class="builder-header">
            <div>
              <div class="builder-title">1. Pilih barang</div>
              <div class="builder-sub">Isi jumlah barang yang Anda butuhkan.</div>
            </div>
          </div>

          <?php if (isset($kesalahan['jumlah'])): ?>
            <div style="color:var(--color-accent-dark); font-weight:700; font-size:0.9rem; margin-bottom:1rem;">
              <?= e($kesalahan['jumlah']) ?>
            </div>
          <?php endif; ?>

          <div class="builder-items-grid">
            <?php foreach (produkKalkulator() as $produk): ?>
              <?php $jumlahLama = (int) ($isian['jumlah'][$produk['kode']] ?? 0); ?>
              <div class="builder-row">
                <div class="builder-row-info">
                  <label for="jml_<?= e($produk['kode']) ?>" class="builder-row-title">
                    <?= $produk['kategori'] === 'galon' ? '💧' : '🔥' ?> <?= e($produk['nama']) ?>
                  </label>
                  <div class="builder-row-price"><?= rupiah((int) $produk['harga']) ?> <?= e($produk['satuan']) ?></div>
                </div>
                <input type="number"
                       id="jml_<?= e($produk['kode']) ?>"
                       name="jumlah[<?= e($produk['kode']) ?>]"
                       class="input-utility"
                       style="width:80px; text-align:center; padding:0.5rem;"
                       value="<?= $jumlahLama ?>"
                       min="0" max="99" step="1">
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Bagian 2: data pengantaran -->
          <div class="builder-header" style="margin-top:2rem;">
            <div>
              <div class="builder-title">2. Data pengantaran</div>
              <div class="builder-sub">Supaya kurir tidak salah alamat.</div>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(260px, 100%), 1fr)); gap:1rem;">

            <div>
              <label for="nama" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
                Nama penerima <span style="color:var(--color-accent-dark)">*</span>
              </label>
              <input type="text" id="nama" name="nama" class="input-utility" style="width:100%"
                     value="<?= e($isian['nama'] ?? '') ?>" maxlength="100" required>
              <?php if (isset($kesalahan['nama'])): ?>
                <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.3rem;"><?= e($kesalahan['nama']) ?></div>
              <?php endif; ?>
            </div>

            <div>
              <label for="whatsapp" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
                Nomor WhatsApp <span style="color:var(--color-accent-dark)">*</span>
              </label>
              <input type="tel" id="whatsapp" name="whatsapp" class="input-utility" style="width:100%"
                     value="<?= e($isian['whatsapp'] ?? '') ?>" placeholder="08xxxxxxxxxx" required>
              <?php if (isset($kesalahan['whatsapp'])): ?>
                <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.3rem;"><?= e($kesalahan['whatsapp']) ?></div>
              <?php endif; ?>
            </div>

            <div>
              <label for="wilayah_id" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
                Wilayah <span style="color:var(--color-accent-dark)">*</span>
              </label>
              <select id="wilayah_id" name="wilayah_id" class="input-utility" style="width:100%" required>
                <option value="">— Pilih wilayah —</option>
                <?php foreach ($daftarWilayah as $wilayah): ?>
                  <option value="<?= (int) $wilayah['id'] ?>"
                    <?= (int) ($isian['wilayah_id'] ?? 0) === (int) $wilayah['id'] ? 'selected' : '' ?>>
                    <?= e($wilayah['nama']) ?> (<?= e($wilayah['estimasi']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($kesalahan['wilayah_id'])): ?>
                <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.3rem;"><?= e($kesalahan['wilayah_id']) ?></div>
              <?php endif; ?>
            </div>

            <div>
              <label for="metode_bayar" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
                Metode pembayaran
              </label>
              <select id="metode_bayar" name="metode_bayar" class="input-utility" style="width:100%">
                <?php
                $metode = ['cash' => 'Bayar tunai di tempat', 'qris' => 'QRIS', 'transfer' => 'Transfer'];
                foreach ($metode as $kode => $label): ?>
                  <option value="<?= $kode ?>" <?= ($isian['metode_bayar'] ?? 'cash') === $kode ? 'selected' : '' ?>>
                    <?= e($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="margin-top:1rem;">
            <label for="alamat" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
              Alamat lengkap <span style="color:var(--color-accent-dark)">*</span>
            </label>
            <textarea id="alamat" name="alamat" class="input-utility" style="width:100%; min-height:90px;"
                      placeholder="Nama jalan, lorong, nomor rumah" required><?= e($isian['alamat'] ?? '') ?></textarea>
            <?php if (isset($kesalahan['alamat'])): ?>
              <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.3rem;"><?= e($kesalahan['alamat']) ?></div>
            <?php endif; ?>
          </div>

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(260px, 100%), 1fr)); gap:1rem; margin-top:1rem;">
            <div>
              <label for="patokan" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
                Patokan
              </label>
              <input type="text" id="patokan" name="patokan" class="input-utility" style="width:100%"
                     value="<?= e($isian['patokan'] ?? '') ?>" placeholder="Depan pos ronda / samping masjid" maxlength="255">
            </div>
            <div>
              <label for="catatan" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
                Catatan untuk kurir
              </label>
              <input type="text" id="catatan" name="catatan" class="input-utility" style="width:100%"
                     value="<?= e($isian['catatan'] ?? '') ?>" placeholder="Contoh: tolong bawa galon kosong sekalian">
            </div>
          </div>

          <!-- Bagian 3: kirim -->
          <div class="builder-summary-card" style="margin-top:2rem;">
            <div>
              <div class="builder-total-label">Ongkir <?= rupiah($ongkirPerItem) ?> per item</div>
              <div class="builder-total-price">Kirim Pesanan</div>
              <div class="builder-total-desc">Total akan dihitung dan ditampilkan setelah pesanan terkirim.</div>
            </div>
            <div class="builder-action-btns">
              <button type="submit" class="btn btn-wa btn-lg">Kirim Pesanan</button>
            </div>
          </div>
        </form>
<?php endif; ?>

      </div>
    </section>
  </main>

<?php include __DIR__ . '/partials/footer.php'; ?>
