<?php
/**
 * admin/auth.php — Pengaman halaman admin
 *
 * Dipanggil paling atas di setiap file dalam folder admin:
 *   require_once __DIR__ . '/auth.php';
 *
 * Isinya: session, pengecekan login, dan token anti-CSRF.
 */

// Session harus dimulai sebelum ada tulisan apa pun dikirim ke browser
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../konfigurasi.php';

/**
 * Mengembalikan data admin yang sedang login, atau null kalau belum login.
 */
function adminSekarang(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    return [
        'id'       => (int) $_SESSION['admin_id'],
        'nama'     => $_SESSION['admin_nama'] ?? '',
        'username' => $_SESSION['admin_username'] ?? '',
    ];
}

/**
 * Dipanggil di halaman yang hanya boleh dibuka setelah login.
 * Kalau belum login, pengunjung dilempar ke halaman login.
 */
function wajibLogin(): void
{
    if (adminSekarang() === null) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Token anti-CSRF.
 *
 * Token ini disisipkan di setiap form admin. Saat form dikirim, token
 * diperiksa lagi. Gunanya supaya perintah hapus atau ubah data tidak bisa
 * dipicu dari website lain tanpa sepengetahuan admin.
 */
function tokenCsrf(): string
{
    if (empty($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['token_csrf'];
}

/**
 * Memeriksa token yang ikut terkirim bersama form.
 * Kalau tidak cocok, proses dihentikan.
 */
function periksaToken(): void
{
    $token = $_POST['token'] ?? '';

    if (!hash_equals($_SESSION['token_csrf'] ?? '', $token)) {
        http_response_code(400);
        exit('Permintaan ditolak: token tidak cocok. Silakan muat ulang halaman.');
    }
}

/**
 * Menyimpan pesan singkat yang akan ditampilkan sekali di halaman berikutnya.
 * Contoh: "Harga berhasil disimpan."
 */
function simpanPesan(string $teks, string $jenis = 'sukses'): void
{
    $_SESSION['pesan'] = ['teks' => $teks, 'jenis' => $jenis];
}

/**
 * Mengambil pesan tadi, sekaligus menghapusnya supaya tidak muncul dua kali.
 */
function ambilPesan(): ?array
{
    if (empty($_SESSION['pesan'])) {
        return null;
    }

    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']);

    return $pesan;
}
