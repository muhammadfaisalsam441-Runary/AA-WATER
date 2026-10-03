<?php
/**
 * konfigurasi.php — Data dan fungsi yang dipakai semua halaman
 *
 * Cukup panggil file ini di baris paling atas setiap halaman:
 *   require_once __DIR__ . '/konfigurasi.php';
 *
 * File ini otomatis memanggil db.php, jadi tidak perlu dipanggil dua kali.
 */

require_once __DIR__ . '/db.php';

// ---------------------------------------------------------------------------
// 1. Ambil semua pengaturan dari database sekali saja
//    Hasilnya: $pengaturan['nomor_wa'], $pengaturan['jam_buka'], dan seterusnya
// ---------------------------------------------------------------------------
$pengaturan = [];
foreach (ambilSemua('SELECT nama_pengaturan, nilai FROM pengaturan') as $baris) {
    $pengaturan[$baris['nama_pengaturan']] = $baris['nilai'];
}

/**
 * Membaca satu pengaturan dari database.
 *
 * Contoh: atur('nomor_wa') menghasilkan '6281342460279'
 */
function atur(string $nama, string $bawaan = ''): string
{
    global $pengaturan;

    return $pengaturan[$nama] ?? $bawaan;
}

// ---------------------------------------------------------------------------
// 2. Status buka / tutup depot
//    Dihitung dari jam server, bukan jam HP pengunjung, supaya selalu benar.
// ---------------------------------------------------------------------------

/**
 * Mengecek apakah depot sedang buka saat halaman dibuka.
 */
function depotBuka(): bool
{
    $jamSekarang = (int) date('G');   // 0 - 23
    $jamBuka     = (int) atur('jam_buka', '8');
    $jamTutup    = (int) atur('jam_tutup', '21');

    return $jamSekarang >= $jamBuka && $jamSekarang < $jamTutup;
}

/**
 * Kalimat status untuk ditampilkan di strip atas.
 * Contoh: "BUKA • 08.00 – 21.00 WITA (Kurir Siap Antar)"
 */
function teksStatus(): string
{
    $buka  = sprintf('%02d.00', (int) atur('jam_buka', '8'));
    $tutup = sprintf('%02d.00', (int) atur('jam_tutup', '21'));

    if (depotBuka()) {
        return "<strong>BUKA</strong> • $buka – $tutup WITA (Kurir Siap Antar)";
    }

    return "<strong>TUTUP</strong> • Buka besok $buka WITA (Bisa jadwalkan pesan sekarang)";
}

// ---------------------------------------------------------------------------
// 3. Link WhatsApp
// ---------------------------------------------------------------------------

/**
 * Membuat link WhatsApp lengkap dengan pesan yang sudah terisi.
 *
 * Contoh: linkWa('Halo, mau order galon')
 */
function linkWa(string $pesan = ''): string
{
    $url = 'https://wa.me/' . atur('nomor_wa');

    if ($pesan !== '') {
        $url .= '?text=' . rawurlencode($pesan);
    }

    return $url;
}

// ---------------------------------------------------------------------------
// 4. Data produk dan wilayah
//    Diambil sekali di sini, lalu dipakai berulang di halaman.
// ---------------------------------------------------------------------------

/**
 * Semua produk aktif, urut sesuai kolom urutan.
 * Kalau diisi 'galon' atau 'gas', hanya kategori itu yang diambil.
 */
function ambilProduk(?string $kategori = null): array
{
    if ($kategori === null) {
        return ambilSemua('SELECT * FROM produk WHERE aktif = 1 ORDER BY urutan');
    }

    return ambilSemua(
        'SELECT * FROM produk WHERE aktif = 1 AND kategori = ? ORDER BY urutan',
        [$kategori]
    );
}

/**
 * Semua wilayah yang dilayani.
 */
function ambilWilayah(): array
{
    return ambilSemua('SELECT * FROM wilayah WHERE aktif = 1 ORDER BY urutan');
}

/**
 * Produk untuk kalkulator, disusun selang-seling galon lalu gas,
 * supaya tampilan dua kolomnya seimbang seperti sebelumnya.
 */
function produkKalkulator(): array
{
    $galon = ambilProduk('galon');
    $gas   = ambilProduk('gas');
    $hasil = [];

    $jumlahTerbanyak = max(count($galon), count($gas));

    for ($i = 0; $i < $jumlahTerbanyak; $i++) {
        if (isset($galon[$i])) {
            $hasil[] = $galon[$i];
        }
        if (isset($gas[$i])) {
            $hasil[] = $gas[$i];
        }
    }

    return $hasil;
}
