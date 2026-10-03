<?php
/**
 * admin/produk.php — Daftar produk dan harga
 *
 * Di sini admin bisa menambah, mengubah, menyembunyikan, dan menghapus produk.
 * Perubahan harga langsung terlihat di website.
 */

require_once __DIR__ . '/auth.php';
wajibLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    periksaToken();

    $aksi = $_POST['aksi'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    // --- Sembunyikan / tampilkan produk ---
    if ($aksi === 'ubah_aktif') {
        $produk = ambilSatu('SELECT aktif, nama FROM produk WHERE id = ?', [$id]);

        if ($produk) {
            $aktifBaru = $produk['aktif'] ? 0 : 1;
            query('UPDATE produk SET aktif = ? WHERE id = ?', [$aktifBaru, $id]);
            simpanPesan(
                $produk['nama'] . ($aktifBaru ? ' kembali ditampilkan di website.' : ' disembunyikan dari website.')
            );
        }
    }

    // --- Hapus produk ---
    if ($aksi === 'hapus') {
        $produk = ambilSatu('SELECT nama FROM produk WHERE id = ?', [$id]);

        if ($produk) {
            // Rincian pesanan lama tetap aman: kolom produk_id diisi NULL,
            // sedangkan nama dan harga sudah tersalin saat pesanan dibuat.
            query('DELETE FROM produk WHERE id = ?', [$id]);
            simpanPesan('Produk ' . $produk['nama'] . ' dihapus. Riwayat pesanan lama tidak terpengaruh.');
        }
    }

    header('Location: produk.php');
    exit;
}

$daftarProduk = ambilSemua('SELECT * FROM produk ORDER BY urutan');

$judul     = 'Produk & Harga';
$menuAktif = 'produk';
include __DIR__ . '/partials/atas.php';
?>

      <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;">
        <div>
          <h1 class="section-title" style="margin-bottom:0.25rem;">Produk &amp; Harga</h1>
          <p class="text-muted">Harga yang diubah di sini langsung berlaku di website dan kalkulator.</p>
        </div>
        <a href="produk-form.php" class="btn btn-primary">+ Tambah Produk</a>
      </div>

      <div class="table-responsive">
        <table class="utility-table">
          <thead>
            <tr>
              <th>Urutan</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th>Tipe</th>
              <th>Harga</th>
              <th>Tampil</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($daftarProduk as $produk): ?>
              <tr style="<?= $produk['aktif'] ? '' : 'opacity:0.55;' ?>">
                <td><?= (int) $produk['urutan'] ?></td>
                <td>
                  <strong><?= $produk['kategori'] === 'galon' ? '💧' : '🔥' ?> <?= e($produk['nama']) ?></strong><br>
                  <span style="font-size:0.8rem; color:var(--color-ink-muted);"><?= e($produk['kode']) ?></span>
                </td>
                <td><?= e(ucfirst($produk['kategori'])) ?></td>
                <td>
                  <span class="item-tag <?= $produk['tipe'] === 'tukar' ? 'tag-tukar' : 'tag-beli' ?>">
                    <?= e(ucfirst($produk['tipe'])) ?>
                  </span>
                </td>
                <td><strong style="font-size:1.05rem;"><?= rupiah((int) $produk['harga']) ?></strong><br>
                  <span style="font-size:0.8rem; color:var(--color-ink-muted);"><?= e($produk['satuan']) ?></span>
                </td>
                <td>
                  <form method="post" action="produk.php">
                    <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
                    <input type="hidden" name="aksi" value="ubah_aktif">
                    <input type="hidden" name="id" value="<?= (int) $produk['id'] ?>">
                    <button type="submit" class="btn btn-sm <?= $produk['aktif'] ? 'btn-outline-primary' : 'btn-accent' ?>">
                      <?= $produk['aktif'] ? 'Tampil' : 'Disembunyikan' ?>
                    </button>
                  </form>
                </td>
                <td style="white-space:nowrap;">
                  <a href="produk-form.php?id=<?= (int) $produk['id'] ?>" class="btn btn-sm btn-primary">Ubah</a>
                  <form method="post" action="produk.php" style="display:inline;"
                        onsubmit="return confirm('Hapus produk <?= e(addslashes($produk['nama'])) ?>? Tindakan ini tidak bisa dibatalkan.');">
                    <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int) $produk['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-accent">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

<?php include __DIR__ . '/partials/bawah.php'; ?>
