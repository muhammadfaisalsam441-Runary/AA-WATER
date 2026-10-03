<?php
/**
 * admin/index.php — Daftar pesanan masuk
 *
 * Bisa disaring berdasarkan status, dan status tiap pesanan bisa diubah
 * langsung dari daftar ini.
 */

require_once __DIR__ . '/auth.php';
wajibLogin();

$daftarStatus = ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Batal'];

// ---------------------------------------------------------------------------
// Mengubah status pesanan
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ubah_status') {
    periksaToken();

    $idPesanan  = (int) ($_POST['id'] ?? 0);
    $statusBaru = $_POST['status'] ?? '';

    if (!isset($daftarStatus[$statusBaru])) {
        simpanPesan('Status tidak dikenal.', 'gagal');
    } else {
        query('UPDATE pesanan SET status = ? WHERE id = ?', [$statusBaru, $idPesanan]);
        simpanPesan('Status pesanan berhasil diubah menjadi ' . $daftarStatus[$statusBaru] . '.');
    }

    // Kembali ke halaman yang sama beserta saringan yang sedang dipakai
    $kembali = 'index.php';
    if (!empty($_POST['saringan'])) {
        $kembali .= '?status=' . urlencode($_POST['saringan']);
    }
    header('Location: ' . $kembali);
    exit;
}

// ---------------------------------------------------------------------------
// Mengambil data pesanan
// ---------------------------------------------------------------------------
$saringan = $_GET['status'] ?? '';

if (isset($daftarStatus[$saringan])) {
    $pesanan = ambilSemua(
        'SELECT p.*, w.nama AS nama_wilayah
           FROM pesanan p LEFT JOIN wilayah w ON w.id = p.wilayah_id
          WHERE p.status = ?
          ORDER BY p.dibuat_pada DESC',
        [$saringan]
    );
} else {
    $saringan = '';
    $pesanan = ambilSemua(
        'SELECT p.*, w.nama AS nama_wilayah
           FROM pesanan p LEFT JOIN wilayah w ON w.id = p.wilayah_id
          ORDER BY p.dibuat_pada DESC'
    );
}

// Ringkasan angka di bagian atas
$ringkasan = ambilSatu(
    "SELECT
       COUNT(*) AS jumlah_pesanan,
       COALESCE(SUM(CASE WHEN status = 'baru'     THEN 1 ELSE 0 END), 0) AS pesanan_baru,
       COALESCE(SUM(CASE WHEN status = 'selesai'  THEN total ELSE 0 END), 0) AS omzet_selesai,
       COALESCE(SUM(CASE WHEN DATE(dibuat_pada) = CURDATE() THEN 1 ELSE 0 END), 0) AS pesanan_hari_ini
     FROM pesanan"
);

$judul     = 'Pesanan Masuk';
$menuAktif = 'pesanan';
include __DIR__ . '/partials/atas.php';
?>

      <h1 class="section-title" style="margin-bottom:0.25rem;">Pesanan Masuk</h1>
      <p class="text-muted" style="margin-bottom:1.5rem;">Pesanan terbaru tampil paling atas.</p>

      <!-- Ringkasan -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(200px, 100%), 1fr)); gap:1rem; margin-bottom:2rem;">
        <?php
        $kartuRingkasan = [
            ['label' => 'Total pesanan',     'nilai' => (int) $ringkasan['jumlah_pesanan']],
            ['label' => 'Belum diproses',    'nilai' => (int) $ringkasan['pesanan_baru']],
            ['label' => 'Masuk hari ini',    'nilai' => (int) $ringkasan['pesanan_hari_ini']],
            ['label' => 'Omzet (selesai)',   'nilai' => rupiah((int) $ringkasan['omzet_selesai'])],
        ];
        foreach ($kartuRingkasan as $kartu): ?>
          <div class="category-box">
            <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; color:var(--color-ink-muted); letter-spacing:0.04em;">
              <?= e($kartu['label']) ?>
            </div>
            <div style="font-family:var(--font-display); font-size:1.6rem; font-weight:900; color:var(--color-ink); margin-top:0.35rem;">
              <?= e((string) $kartu['nilai']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Saringan status -->
      <div class="chips-container">
        <a href="index.php" class="area-chip<?= $saringan === '' ? ' active' : '' ?>">Semua</a>
        <?php foreach ($daftarStatus as $kode => $label): ?>
          <a href="index.php?status=<?= $kode ?>" class="area-chip<?= $saringan === $kode ? ' active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($pesanan)): ?>
        <div class="category-box" style="text-align:center; padding:2.5rem 1rem;">
          <div style="font-size:2rem; margin-bottom:0.5rem;">📭</div>
          <strong>Belum ada pesanan<?= $saringan ? ' dengan status ' . e($daftarStatus[$saringan]) : '' ?>.</strong>
          <div class="text-muted" style="font-size:0.9rem; margin-top:0.35rem;">
            Pesanan yang dikirim lewat form di website akan muncul di sini.
          </div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="utility-table">
            <thead>
              <tr>
                <th>Kode / Waktu</th>
                <th>Pemesan</th>
                <th>Alamat</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pesanan as $baris): ?>
                <tr>
                  <td>
                    <strong><?= e($baris['kode_pesanan']) ?></strong><br>
                    <span style="font-size:0.8rem; color:var(--color-ink-muted);">
                      <?= date('d/m/Y H:i', strtotime($baris['dibuat_pada'])) ?>
                    </span>
                  </td>
                  <td>
                    <?= e($baris['nama']) ?><br>
                    <a href="https://wa.me/62<?= e(ltrim($baris['whatsapp'], '0')) ?>" target="_blank"
                       style="font-size:0.8rem; color:var(--color-primary);">
                      <?= e($baris['whatsapp']) ?>
                    </a>
                  </td>
                  <td style="max-width:260px;">
                    <?= e($baris['alamat']) ?>
                    <?php if ($baris['nama_wilayah']): ?>
                      <br><span style="font-size:0.8rem; color:var(--color-ink-muted);">🛵 <?= e($baris['nama_wilayah']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td><strong><?= rupiah((int) $baris['total']) ?></strong></td>
                  <td>
                    <form method="post" action="index.php" style="display:flex; gap:0.35rem; align-items:center;">
                      <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
                      <input type="hidden" name="aksi" value="ubah_status">
                      <input type="hidden" name="id" value="<?= (int) $baris['id'] ?>">
                      <input type="hidden" name="saringan" value="<?= e($saringan) ?>">
                      <select name="status" class="input-utility" style="padding:0.35rem 0.5rem; font-size:0.85rem;">
                        <?php foreach ($daftarStatus as $kode => $label): ?>
                          <option value="<?= $kode ?>" <?= $baris['status'] === $kode ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                    </form>
                  </td>
                  <td>
                    <a href="pesanan-detail.php?id=<?= (int) $baris['id'] ?>" class="btn btn-sm btn-outline-primary">Rincian</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

<?php include __DIR__ . '/partials/bawah.php'; ?>
