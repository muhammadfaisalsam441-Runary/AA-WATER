<?php
/**
 * db.php — Koneksi database AA WATER (MySQLi)
 *
 * File ini di-include di setiap halaman yang butuh data dari database:
 *   require_once __DIR__ . '/db.php';
 *
 * Setelah di-include, variabel $koneksi siap dipakai untuk query.
 */

// ---------------------------------------------------------------------------
// 1. Pengaturan koneksi
//    Nilai di bawah ini adalah bawaan XAMPP. Kalau MySQL Anda pakai password,
//    isi DB_PASS sesuai password tersebut.
// ---------------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aa_water');

// Zona waktu Makassar (WITA), dipakai untuk jam buka/tutup dan waktu pesanan
date_default_timezone_set('Asia/Makassar');

// ---------------------------------------------------------------------------
// 2. Membuat koneksi
// ---------------------------------------------------------------------------
// PHP 8 membuat mysqli otomatis menghentikan program saat ada error.
// Dimatikan supaya error bisa kita periksa dan tangani sendiri di bawah ini.
mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    // Pesan error asli tidak ditampilkan ke pengunjung, hanya dicatat di log
    error_log('Koneksi database gagal: ' . mysqli_connect_error());

    http_response_code(500);
    exit(
        '<h1 style="font-family:sans-serif">Koneksi database gagal</h1>' .
        '<p style="font-family:sans-serif">Pastikan Apache dan MySQL di XAMPP sudah berjalan, ' .
        'dan database <strong>' . DB_NAME . '</strong> sudah dibuat.</p>'
    );
}

// Supaya huruf dan emoji (💧 🔥) tersimpan dengan benar
mysqli_set_charset($koneksi, 'utf8mb4');

// Samakan zona waktu MySQL dengan WITA (+08:00)
mysqli_query($koneksi, "SET time_zone = '+08:00'");

// ---------------------------------------------------------------------------
// 3. Fungsi bantu yang sering dipakai
// ---------------------------------------------------------------------------

/**
 * Menjalankan query dengan aman memakai prepared statement.
 *
 * Nilai yang berasal dari pengguna dikirim lewat $params, jangan disambung
 * langsung ke dalam teks SQL. Tulis tanda ? di posisi nilainya.
 *
 * Contoh:
 *   $hasil = query('SELECT * FROM produk WHERE kategori = ?', ['galon']);
 *
 * @param string $sql    Perintah SQL, pakai tanda ? untuk setiap nilai
 * @param array  $params Nilai pengganti tanda ?, berurutan
 */
function query(string $sql, array $params = [])
{
    global $koneksi;

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        error_log('Query gagal disiapkan: ' . mysqli_error($koneksi) . ' | SQL: ' . $sql);
        exit('Terjadi kesalahan pada database.');
    }

    // Tentukan tipe tiap nilai: i = angka bulat, d = desimal, s = teks
    if (!empty($params)) {
        $tipe = '';
        foreach ($params as $nilai) {
            if (is_int($nilai)) {
                $tipe .= 'i';
            } elseif (is_float($nilai)) {
                $tipe .= 'd';
            } else {
                $tipe .= 's';
            }
        }
        mysqli_stmt_bind_param($stmt, $tipe, ...$params);
    }

    mysqli_stmt_execute($stmt);

    // SELECT mengembalikan hasil, sedangkan INSERT/UPDATE/DELETE tidak
    $hasil = mysqli_stmt_get_result($stmt);

    if ($hasil === false) {
        // Bukan SELECT: kembalikan jumlah baris yang terpengaruh
        $jumlah = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $jumlah;
    }

    return $hasil;
}

/**
 * Mengambil satu baris hasil query, atau null kalau tidak ada.
 *
 * Contoh:
 *   $produk = ambilSatu('SELECT * FROM produk WHERE id = ?', [3]);
 */
function ambilSatu(string $sql, array $params = []): ?array
{
    $hasil = query($sql, $params);
    $baris = mysqli_fetch_assoc($hasil);

    return $baris ?: null;
}

/**
 * Mengambil semua baris hasil query sebagai array.
 *
 * Contoh:
 *   $semuaProduk = ambilSemua('SELECT * FROM produk ORDER BY harga');
 */
function ambilSemua(string $sql, array $params = []): array
{
    $hasil = query($sql, $params);

    return mysqli_fetch_all($hasil, MYSQLI_ASSOC);
}

/**
 * Mengambil id dari baris yang baru saja ditambahkan dengan INSERT.
 */
function idTerakhir(): int
{
    global $koneksi;

    return (int) mysqli_insert_id($koneksi);
}

/**
 * Membersihkan teks sebelum ditampilkan di halaman,
 * supaya tanda < > & tidak merusak HTML.
 *
 * Contoh: <?= e($produk['nama']) ?>
 */
function e(?string $teks): string
{
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka jadi rupiah, misal 250000 menjadi "Rp250.000".
 */
function rupiah(int $angka): string
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}
