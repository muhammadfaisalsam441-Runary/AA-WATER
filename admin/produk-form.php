<?php
/**
 * admin/produk-form.php — Tambah atau ubah satu produk
 *
 * Dibuka tanpa id  → form tambah produk baru
 * Dibuka dengan id → form ubah produk yang sudah ada
 */

require_once __DIR__ . '/auth.php';
wajibLogin();

$id        = (int) ($_GET['id'] ?? 0);
$modeUbah  = $id > 0;
$kesalahan = [];

/**
 * Membuat kode produk otomatis dari nama produk.
 *
 * "Gas 12 KG (Tukar)"  →  gas_12_kg_tukar
 *
 * Kalau kode itu sudah dipakai produk lain, ditambahkan angka di belakangnya
 * (gas_12_kg_tukar_2, gas_12_kg_tukar_3, dan seterusnya).
 *
 * @param int|null $kecualiId Id produk yang sedang diubah, supaya kodenya
 *                            sendiri tidak dianggap bentrok.
 */
function buatKodeOtomatis(string $nama, ?int $kecualiId = null): string
{
    // Huruf besar jadi kecil, lalu semua tanda baca diganti garis bawah
    $dasar = strtolower($nama);
    $dasar = preg_replace('/[^a-z0-9]+/', '_', $dasar);
    $dasar = trim($dasar, '_');

    if ($dasar === '') {
        $dasar = 'produk';
    }

    // Cari kode yang belum dipakai
    $kode  = $dasar;
    $nomor = 1;

    while (true) {
        $bentrok = $kecualiId
            ? ambilSatu('SELECT id FROM produk WHERE kode = ? AND id != ?', [$kode, $kecualiId])
            : ambilSatu('SELECT id FROM produk WHERE kode = ?', [$kode]);

        if (!$bentrok) {
            return $kode;
        }

        $nomor++;
        $kode = $dasar . '_' . $nomor;
    }
}

// Isi awal form
$isian = [
    'kode'      => '',
    'kategori'  => 'galon',
    'tipe'      => 'tukar',
    'nama'      => '',
    'label'     => '',
    'deskripsi' => '',
    'harga'     => '',
    'satuan'    => 'per item',
    'urutan'    => 0,
    'aktif'     => 1,
];

if ($modeUbah) {
    $produkLama = ambilSatu('SELECT * FROM produk WHERE id = ?', [$id]);

    if (!$produkLama) {
        simpanPesan('Produk tidak ditemukan.', 'gagal');
        header('Location: produk.php');
        exit;
    }

    $isian = $produkLama;
}

// ---------------------------------------------------------------------------
// Memproses form
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    periksaToken();

    $isian = [
        'kode'      => trim($_POST['kode'] ?? ''),
        'kategori'  => $_POST['kategori'] ?? 'galon',
        'tipe'      => $_POST['tipe'] ?? 'tukar',
        'nama'      => trim($_POST['nama'] ?? ''),
        'label'     => trim($_POST['label'] ?? ''),
        'deskripsi' => trim($_POST['deskripsi'] ?? ''),
        'harga'     => trim($_POST['harga'] ?? ''),
        'satuan'    => trim($_POST['satuan'] ?? ''),
        'urutan'    => (int) ($_POST['urutan'] ?? 0),
        'aktif'     => isset($_POST['aktif']) ? 1 : 0,
    ];

    // --- Kode produk ---
    if ($isian['kode'] === '') {
        if ($modeUbah) {
            // Saat mengubah produk, kode lama dipertahankan. Kode tidak diganti
            // diam-diam karena dipakai sebagai pengenal produk di keranjang.
            $isian['kode'] = $produkLama['kode'];
        } elseif ($isian['nama'] !== '') {
            // Produk baru: kode dibuat otomatis dari nama produk.
            // Contoh: "Gas 12 KG (Tukar)" menjadi gas_12_kg_tukar
            $isian['kode'] = buatKodeOtomatis($isian['nama']);
        }
    }

    if ($isian['kode'] === '') {
        $kesalahan['kode'] = 'Kode produk tidak bisa dibuat. Isi dulu nama produknya.';
    } elseif (!preg_match('/^[a-z0-9_]+$/', $isian['kode'])) {
        $kesalahan['kode'] = 'Kode hanya boleh huruf kecil, angka, dan garis bawah. Contoh: galon_tukar';
    } else {
        // Kode tidak boleh sama dengan produk lain
        $bentrok = $modeUbah
            ? ambilSatu('SELECT id FROM produk WHERE kode = ? AND id != ?', [$isian['kode'], $id])
            : ambilSatu('SELECT id FROM produk WHERE kode = ?', [$isian['kode']]);

        if ($bentrok) {
            $kesalahan['kode'] = 'Kode ini sudah dipakai produk lain.';
        }
    }

    // --- Nama ---
    if ($isian['nama'] === '') {
        $kesalahan['nama'] = 'Nama produk wajib diisi.';
    }

    // --- Harga ---
    if ($isian['harga'] === '' || !ctype_digit((string) $isian['harga'])) {
        $kesalahan['harga'] = 'Harga harus berupa angka, tanpa titik atau Rp.';
    } elseif ((int) $isian['harga'] < 0) {
        $kesalahan['harga'] = 'Harga tidak boleh minus.';
    }

    // --- Kategori & tipe ---
    if (!in_array($isian['kategori'], ['galon', 'gas'], true)) {
        $kesalahan['kategori'] = 'Kategori tidak dikenal.';
    }
    if (!in_array($isian['tipe'], ['tukar', 'beli'], true)) {
        $kesalahan['tipe'] = 'Tipe tidak dikenal.';
    }

    if ($isian['satuan'] === '') {
        $isian['satuan'] = 'per item';
    }

    // --- Simpan ---
    if (empty($kesalahan)) {
        if ($modeUbah) {
            query(
                'UPDATE produk
                    SET kode = ?, kategori = ?, tipe = ?, nama = ?, label = ?,
                        deskripsi = ?, harga = ?, satuan = ?, urutan = ?, aktif = ?
                  WHERE id = ?',
                [
                    $isian['kode'], $isian['kategori'], $isian['tipe'], $isian['nama'], $isian['label'],
                    $isian['deskripsi'], (int) $isian['harga'], $isian['satuan'], $isian['urutan'], $isian['aktif'],
                    $id,
                ]
            );
            simpanPesan('Produk ' . $isian['nama'] . ' berhasil diperbarui.');
        } else {
            query(
                'INSERT INTO produk (kode, kategori, tipe, nama, label, deskripsi, harga, satuan, urutan, aktif)
                 VALUES (?,?,?,?,?,?,?,?,?,?)',
                [
                    $isian['kode'], $isian['kategori'], $isian['tipe'], $isian['nama'], $isian['label'],
                    $isian['deskripsi'], (int) $isian['harga'], $isian['satuan'], $isian['urutan'], $isian['aktif'],
                ]
            );
            simpanPesan('Produk ' . $isian['nama'] . ' berhasil ditambahkan.');
        }

        header('Location: produk.php');
        exit;
    }
}

$judul     = $modeUbah ? 'Ubah Produk' : 'Tambah Produk';
$menuAktif = 'produk';
include __DIR__ . '/partials/atas.php';
?>

      <a href="produk.php" style="font-size:0.9rem; color:var(--color-ink-muted);">&larr; Kembali ke daftar produk</a>

      <h1 class="section-title" style="margin:0.75rem 0 1.5rem;"><?= $modeUbah ? 'Ubah Produk' : 'Tambah Produk' ?></h1>

      <?php if (!empty($kesalahan)): ?>
        <div style="background:#FFF3BF; color:#D9480F; font-weight:700; padding:0.9rem 1.15rem; border-radius:var(--radius-xl); margin-bottom:1.5rem;">
          ⚠️ Ada <?= count($kesalahan) ?> isian yang perlu diperbaiki.
        </div>
      <?php endif; ?>

      <form method="post" class="signature-card" style="max-width:720px;">
        <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(240px, 100%), 1fr)); gap:1rem;">

          <div>
            <label for="nama" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Nama produk *</label>
            <input type="text" id="nama" name="nama" class="input-utility" style="width:100%" value="<?= e($isian['nama']) ?>" required>
            <?php if (isset($kesalahan['nama'])): ?><div style="color:var(--color-accent-dark); font-size:0.85rem;"><?= e($kesalahan['nama']) ?></div><?php endif; ?>
          </div>

          <div>
            <label for="kode" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
              Kode produk <span style="font-weight:600; color:var(--color-ink-muted);">(terisi otomatis)</span>
            </label>
            <input type="text" id="kode" name="kode" class="input-utility" style="width:100%" value="<?= e($isian['kode']) ?>"
                   placeholder="otomatis dari nama produk">
            <div style="font-size:0.8rem; color:var(--color-ink-muted); margin-top:0.25rem;">
              Nama pengenal di sistem, harus berbeda untuk tiap produk. Biarkan saja, akan dibuatkan dari nama produk.
            </div>
            <?php if (isset($kesalahan['kode'])): ?><div style="color:var(--color-accent-dark); font-size:0.85rem;"><?= e($kesalahan['kode']) ?></div><?php endif; ?>
          </div>

          <div>
            <label for="kategori" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Kategori</label>
            <select id="kategori" name="kategori" class="input-utility" style="width:100%">
              <option value="galon" <?= $isian['kategori'] === 'galon' ? 'selected' : '' ?>>💧 Galon air</option>
              <option value="gas"   <?= $isian['kategori'] === 'gas'   ? 'selected' : '' ?>>🔥 Gas LPG</option>
            </select>
          </div>

          <div>
            <label for="tipe" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Tipe</label>
            <select id="tipe" name="tipe" class="input-utility" style="width:100%">
              <option value="tukar" <?= $isian['tipe'] === 'tukar' ? 'selected' : '' ?>>Tukar (bawa wadah kosong)</option>
              <option value="beli"  <?= $isian['tipe'] === 'beli'  ? 'selected' : '' ?>>Beli (wadah baru)</option>
            </select>
          </div>

          <div>
            <label for="harga" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Harga (rupiah) *</label>
            <input type="number" id="harga" name="harga" class="input-utility" style="width:100%"
                   value="<?= e((string) $isian['harga']) ?>" min="0" step="500" required>
            <div style="font-size:0.8rem; color:var(--color-ink-muted); margin-top:0.25rem;">Tulis angka saja, contoh: 5000</div>
            <?php if (isset($kesalahan['harga'])): ?><div style="color:var(--color-accent-dark); font-size:0.85rem;"><?= e($kesalahan['harga']) ?></div><?php endif; ?>
          </div>

          <div>
            <label for="satuan" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Satuan</label>
            <input type="text" id="satuan" name="satuan" class="input-utility" style="width:100%"
                   value="<?= e($isian['satuan']) ?>" placeholder="per galon">
          </div>

          <div>
            <label for="label" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Label kecil</label>
            <input type="text" id="label" name="label" class="input-utility" style="width:100%"
                   value="<?= e($isian['label'] ?? '') ?>" placeholder="Paling Laris (Tukar Isi)">
          </div>

          <div>
            <label for="urutan" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Urutan tampil</label>
            <input type="number" id="urutan" name="urutan" class="input-utility" style="width:100%"
                   value="<?= (int) $isian['urutan'] ?>" min="0" max="99">
          </div>
        </div>

        <div style="margin-top:1rem;">
          <label for="deskripsi" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Keterangan</label>
          <textarea id="deskripsi" name="deskripsi" class="input-utility" style="width:100%; min-height:80px;"><?= e($isian['deskripsi'] ?? '') ?></textarea>
        </div>

        <label style="display:flex; align-items:center; gap:0.5rem; margin-top:1rem; font-weight:700; font-size:0.95rem;">
          <input type="checkbox" name="aktif" value="1" <?= $isian['aktif'] ? 'checked' : '' ?>>
          Tampilkan produk ini di website
        </label>

        <div style="display:flex; gap:0.75rem; margin-top:1.75rem; flex-wrap:wrap;">
          <button type="submit" class="btn btn-primary btn-lg"><?= $modeUbah ? 'Simpan Perubahan' : 'Tambah Produk' ?></button>
          <a href="produk.php" class="btn btn-outline-primary btn-lg">Batal</a>
        </div>
      </form>

      <script>
        // Mengisi kode produk sambil admin mengetik nama.
        // Kalau kode sudah diisi sendiri oleh admin, pengisian otomatis berhenti.
        (function () {
          var isianNama = document.getElementById('nama');
          var isianKode = document.getElementById('kode');
          var diisiSendiri = isianKode.value.trim() !== '';

          isianKode.addEventListener('input', function () {
            diisiSendiri = true;
          });

          isianNama.addEventListener('input', function () {
            if (diisiSendiri) {
              return;
            }

            isianKode.value = isianNama.value
              .toLowerCase()
              .replace(/[^a-z0-9]+/g, '_')
              .replace(/^_+|_+$/g, '');
          });
        })();
      </script>

<?php include __DIR__ . '/partials/bawah.php'; ?>
