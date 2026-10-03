<?php
/**
 * admin/akun.php — Ganti nama tampilan dan password admin
 *
 * Password baru disimpan terenkripsi dengan password_hash(),
 * jadi di database pun password aslinya tidak terbaca.
 */

require_once __DIR__ . '/auth.php';
wajibLogin();

$admin = adminSekarang();
$dataAdmin = ambilSatu('SELECT * FROM admin WHERE id = ?', [$admin['id']]);

$kesalahan = [];
$namaBaru  = $dataAdmin['nama'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    periksaToken();

    $aksi = $_POST['aksi'] ?? '';

    // -----------------------------------------------------------------------
    // A. Ganti nama tampilan
    // -----------------------------------------------------------------------
    if ($aksi === 'ubah_nama') {
        $namaBaru = trim($_POST['nama'] ?? '');

        if ($namaBaru === '') {
            $kesalahan['nama'] = 'Nama wajib diisi.';
        } elseif (mb_strlen($namaBaru) > 100) {
            $kesalahan['nama'] = 'Nama terlalu panjang, maksimal 100 huruf.';
        } else {
            query('UPDATE admin SET nama = ? WHERE id = ?', [$namaBaru, $admin['id']]);
            $_SESSION['admin_nama'] = $namaBaru;

            simpanPesan('Nama berhasil diperbarui.');
            header('Location: akun.php');
            exit;
        }
    }

    // -----------------------------------------------------------------------
    // B. Ganti password
    // -----------------------------------------------------------------------
    if ($aksi === 'ubah_password') {
        $passwordLama  = $_POST['password_lama'] ?? '';
        $passwordBaru  = $_POST['password_baru'] ?? '';
        $passwordUlang = $_POST['password_ulang'] ?? '';

        // 1. Password lama harus benar.
        //    Ini mencegah orang lain mengganti password saat komputer
        //    ditinggal dalam keadaan masih login.
        if (!password_verify($passwordLama, $dataAdmin['password'])) {
            $kesalahan['password_lama'] = 'Password lama salah.';
        }

        // 2. Password baru minimal 8 huruf
        if (mb_strlen($passwordBaru) < 8) {
            $kesalahan['password_baru'] = 'Password baru minimal 8 karakter.';
        } elseif ($passwordBaru === $passwordLama) {
            $kesalahan['password_baru'] = 'Password baru harus berbeda dari password lama.';
        } elseif (in_array(strtolower($passwordBaru), ['admin123', 'password', '12345678', 'qwerty123'], true)) {
            $kesalahan['password_baru'] = 'Password terlalu mudah ditebak, pilih yang lain.';
        }

        // 3. Ketikan ulang harus sama
        if ($passwordBaru !== $passwordUlang) {
            $kesalahan['password_ulang'] = 'Ketikan ulang password tidak sama.';
        }

        if (empty($kesalahan)) {
            $sandiTerenkripsi = password_hash($passwordBaru, PASSWORD_DEFAULT);
            query('UPDATE admin SET password = ? WHERE id = ?', [$sandiTerenkripsi, $admin['id']]);

            simpanPesan('Password berhasil diganti. Silakan masuk lagi dengan password baru.');

            // Keluar paksa, supaya admin memastikan password barunya berfungsi
            $_SESSION = [];
            session_destroy();

            header('Location: login.php');
            exit;
        }
    }
}

$judul     = 'Akun Saya';
$menuAktif = 'akun';
include __DIR__ . '/partials/atas.php';
?>

      <h1 class="section-title" style="margin-bottom:0.25rem;">Akun Saya</h1>
      <p class="text-muted" style="margin-bottom:1.5rem;">
        Masuk sebagai <strong><?= e($dataAdmin['username']) ?></strong>.
      </p>

      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(320px, 100%), 1fr)); gap:1.5rem;">

        <!-- Ganti nama -->
        <form method="post" class="signature-card">
          <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
          <input type="hidden" name="aksi" value="ubah_nama">

          <div class="category-header">
            <div class="category-title"><div>Nama Tampilan</div></div>
          </div>

          <div style="margin-bottom:1.25rem;">
            <label for="nama" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Nama</label>
            <input type="text" id="nama" name="nama" class="input-utility" style="width:100%"
                   value="<?= e($namaBaru) ?>" maxlength="100" required>
            <div style="font-size:0.8rem; color:var(--color-ink-muted); margin-top:0.25rem;">
              Tampil di pojok kanan atas halaman admin.
            </div>
            <?php if (isset($kesalahan['nama'])): ?>
              <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.25rem;"><?= e($kesalahan['nama']) ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-outline-primary">Simpan Nama</button>
        </form>

        <!-- Ganti password -->
        <form method="post" class="signature-card">
          <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
          <input type="hidden" name="aksi" value="ubah_password">

          <div class="category-header">
            <div class="category-title"><div>Ganti Password</div></div>
          </div>

          <div style="margin-bottom:1rem;">
            <label for="password_lama" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Password lama</label>
            <input type="password" id="password_lama" name="password_lama" class="input-utility" style="width:100%"
                   autocomplete="current-password" required>
            <?php if (isset($kesalahan['password_lama'])): ?>
              <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.25rem;"><?= e($kesalahan['password_lama']) ?></div>
            <?php endif; ?>
          </div>

          <div style="margin-bottom:1rem;">
            <label for="password_baru" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Password baru</label>
            <input type="password" id="password_baru" name="password_baru" class="input-utility" style="width:100%"
                   autocomplete="new-password" minlength="8" required>
            <div style="font-size:0.8rem; color:var(--color-ink-muted); margin-top:0.25rem;">Minimal 8 karakter.</div>
            <?php if (isset($kesalahan['password_baru'])): ?>
              <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.25rem;"><?= e($kesalahan['password_baru']) ?></div>
            <?php endif; ?>
          </div>

          <div style="margin-bottom:1.25rem;">
            <label for="password_ulang" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Ketik ulang password baru</label>
            <input type="password" id="password_ulang" name="password_ulang" class="input-utility" style="width:100%"
                   autocomplete="new-password" minlength="8" required>
            <?php if (isset($kesalahan['password_ulang'])): ?>
              <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.25rem;"><?= e($kesalahan['password_ulang']) ?></div>
            <?php endif; ?>
          </div>

          <div class="explain-box" style="margin:0 0 1.25rem;">
            <div class="explain-icon">🔑</div>
            <div class="explain-text">
              <p>Setelah password diganti, Anda otomatis keluar dan perlu masuk lagi dengan password baru.</p>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Ganti Password</button>
        </form>
      </div>

<?php include __DIR__ . '/partials/bawah.php'; ?>
