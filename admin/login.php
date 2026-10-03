<?php
/**
 * admin/login.php — Halaman masuk admin
 *
 * Password di database disimpan terenkripsi, jadi dicocokkan dengan
 * password_verify(), bukan dibandingkan langsung.
 */

require_once __DIR__ . '/auth.php';

// Kalau sudah login, langsung ke halaman pesanan
if (adminSekarang() !== null) {
    header('Location: index.php');
    exit;
}

$kesalahan = '';
$username  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $kesalahan = 'Username dan password wajib diisi.';
    } else {
        $admin = ambilSatu('SELECT * FROM admin WHERE username = ?', [$username]);

        // Pesan kesalahan sengaja dibuat sama untuk username salah maupun
        // password salah, supaya tidak ketahuan username mana yang ada.
        if ($admin && password_verify($password, $admin['password'])) {
            // Ganti id session setelah login, pengaman dasar session hijacking
            session_regenerate_id(true);

            $_SESSION['admin_id']       = (int) $admin['id'];
            $_SESSION['admin_nama']     = $admin['nama'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: index.php');
            exit;
        }

        $kesalahan = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk Admin — <?= e(atur('nama_depot', 'AA WATER')) ?></title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body style="padding-bottom:0;">

  <section class="section section-bg" style="min-height:100vh; display:flex; align-items:center;">
    <div class="container" style="max-width:420px;">

      <div class="text-center" style="margin-bottom:1.5rem;">
        <div class="section-tag">Halaman Admin</div>
        <h1 class="section-title"><?= e(atur('nama_depot', 'AA WATER')) ?></h1>
        <p class="text-muted" style="font-size:0.95rem;">Masuk untuk mengelola pesanan dan harga.</p>
      </div>

      <form method="post" action="login.php" class="signature-card" style="padding:1.5rem;">

        <?php if ($kesalahan !== ''): ?>
          <div style="background:#FFF3BF; color:#D9480F; font-weight:700; padding:0.8rem 1rem; border-radius:var(--radius-xl); margin-bottom:1.25rem; font-size:0.9rem;">
            ⚠️ <?= e($kesalahan) ?>
          </div>
        <?php endif; ?>

        <div style="margin-bottom:1rem;">
          <label for="username" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Username</label>
          <input type="text" id="username" name="username" class="input-utility" style="width:100%"
                 value="<?= e($username) ?>" autocomplete="username" required autofocus>
        </div>

        <div style="margin-bottom:1.5rem;">
          <label for="password" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">Password</label>
          <input type="password" id="password" name="password" class="input-utility" style="width:100%"
                 autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width:100%">Masuk</button>

        <a href="../index.php" style="display:block; text-align:center; margin-top:1rem; font-size:0.9rem; color:var(--color-ink-muted);">
          &larr; Kembali ke website
        </a>
      </form>

    </div>
  </section>

</body>
</html>
