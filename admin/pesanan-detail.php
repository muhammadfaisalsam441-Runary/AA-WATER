<?php
/**
 * admin/pesanan-detail.php — Rincian satu pesanan
 */

require_once __DIR__ . '/auth.php';
wajibLogin();

$id = (int) ($_GET['id'] ?? 0);

$pesanan = ambilSatu(
    'SELECT p.*, w.nama AS nama_wilayah, w.estimasi
       FROM pesanan p LEFT JOIN wilayah w ON w.id = p.wilayah_id
      WHERE p.id = ?',
    [$id]
);

if (!$pesanan) {
    simpanPesan('Pesanan tidak ditemukan.', 'gagal');
    header('Location: index.php');
    exit;
}

$itemPesanan = ambilSemua('SELECT * FROM pesanan_item WHERE pesanan_id = ?', [$id]);

$warnaStatus = [
    'baru'     => ['#FFF3BF', '#D9480F'],
    'diproses' => ['#E7F5FF', '#1864AB'],
    'selesai'  => ['#D3F9D8', '#2B8A3E'],
    'batal'    => ['#FFE3E3', '#C92A2A'],
];
[$latar, $tulisan] = $warnaStatus[$pesanan['status']] ?? ['#EDF6FD', '#13324D'];

$judul     = 'Rincian ' . $pesanan['kode_pesanan'];
$menuAktif = 'pesanan';
include __DIR__ . '/partials/atas.php';
?>

      <a href="index.php" style="font-size:0.9rem; color:var(--color-ink-muted);">&larr; Kembali ke daftar pesanan</a>

      <h1 class="section-title" style="margin:0.75rem 0 0.25rem;"><?= e($pesanan['kode_pesanan']) ?></h1>
      <p class="text-muted" style="margin-bottom:1.5rem;">
        Masuk <?= date('d/m/Y H:i', strtotime($pesanan['dibuat_pada'])) ?> WITA
        &nbsp;•&nbsp;
        <span style="display:inline-block; padding:0.2rem 0.6rem; border-radius:var(--radius-sm); font-weight:800; font-size:0.8rem; background:<?= $latar ?>; color:<?= $tulisan ?>;">
          <?= e(strtoupper($pesanan['status'])) ?>
        </span>
      </p>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(300px, 100%), 1fr)); gap:1.5rem;">

        <!-- Data pemesan -->
        <div class="category-box">
          <div class="category-header">
            <div class="category-title"><div>Data Pemesan</div></div>
          </div>

          <div style="line-height:1.9; font-size:0.95rem;">
            <strong>Nama:</strong> <?= e($pesanan['nama']) ?><br>
            <strong>WhatsApp:</strong>
            <a href="https://wa.me/62<?= e(ltrim($pesanan['whatsapp'], '0')) ?>" target="_blank" style="color:var(--color-primary); font-weight:700;">
              <?= e($pesanan['whatsapp']) ?>
            </a><br>
            <strong>Alamat:</strong> <?= e($pesanan['alamat']) ?><br>
            <?php if ($pesanan['patokan']): ?>
              <strong>Patokan:</strong> <?= e($pesanan['patokan']) ?><br>
            <?php endif; ?>
            <?php if ($pesanan['nama_wilayah']): ?>
              <strong>Wilayah:</strong> <?= e($pesanan['nama_wilayah']) ?> (<?= e($pesanan['estimasi']) ?>)<br>
            <?php endif; ?>
            <strong>Pembayaran:</strong> <?= e(strtoupper($pesanan['metode_bayar'])) ?>
            <?php if ($pesanan['catatan']): ?>
              <br><strong>Catatan:</strong> <?= e($pesanan['catatan']) ?>
            <?php endif; ?>
          </div>

          <a href="https://wa.me/62<?= e(ltrim($pesanan['whatsapp'], '0')) ?>?text=<?= rawurlencode('Halo ' . $pesanan['nama'] . ', pesanan Anda ' . $pesanan['kode_pesanan'] . ' sedang kami siapkan.') ?>"
             target="_blank" class="btn btn-wa" style="width:100%; margin-top:1.25rem;">
            Hubungi Pemesan
          </a>
        </div>

        <!-- Rincian barang -->
        <div class="category-box">
          <div class="category-header">
            <div class="category-title"><div>Barang Dipesan</div></div>
          </div>

          <div class="table-responsive">
            <table class="utility-table">
              <tbody>
                <?php foreach ($itemPesanan as $item): ?>
                  <tr>
                    <td><?= e($item['nama_produk']) ?></td>
                    <td style="white-space:nowrap;"><?= (int) $item['jumlah'] ?> x <?= rupiah((int) $item['harga']) ?></td>
                    <td style="text-align:right; white-space:nowrap;"><strong><?= rupiah((int) $item['subtotal']) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
                <tr>
                  <td colspan="2">Subtotal barang</td>
                  <td style="text-align:right;"><?= rupiah((int) $pesanan['subtotal']) ?></td>
                </tr>
                <tr>
                  <td colspan="2">Ongkos kirim</td>
                  <td style="text-align:right;"><?= rupiah((int) $pesanan['ongkir']) ?></td>
                </tr>
                <tr>
                  <td colspan="2"><strong>Total</strong></td>
                  <td style="text-align:right;"><strong style="font-size:1.15rem;"><?= rupiah((int) $pesanan['total']) ?></strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

<?php include __DIR__ . '/partials/bawah.php'; ?>
