<?php
/**
 * admin/pengaturan.php — Ubah nomor WA, jam buka, ongkir, dan alamat
 *
 * Nilai di sini dipakai di seluruh halaman website.
 */

require_once __DIR__ . '/auth.php';
wajibLogin();

// Daftar pengaturan yang boleh diubah lewat halaman ini,
// beserta cara memeriksanya.
$daftarIsian = [
    'nama_depot'      => ['label' => 'Nama usaha',                 'tipe' => 'text',   'bantuan' => 'Tampil di logo dan footer.'],
    'nomor_wa'        => ['label' => 'Nomor WhatsApp (untuk link)', 'tipe' => 'text',  'bantuan' => 'Diawali 62, tanpa tanda apa pun. Contoh: 6281342460279'],
    'nomor_tampil'    => ['label' => 'Nomor yang ditampilkan',     'tipe' => 'text',   'bantuan' => 'Contoh: 0813-4246-0279'],
    'alamat_depot'    => ['label' => 'Alamat depot',               'tipe' => 'text',   'bantuan' => 'Tampil di footer dan halaman kontak.'],
    'ongkir_per_item' => ['label' => 'Ongkir per item (rupiah)',   'tipe' => 'angka',  'bantuan' => 'Tulis angka saja, contoh: 3000'],
    'jam_buka'        => ['label' => 'Jam buka',                   'tipe' => 'jam',    'bantuan' => 'Format 24 jam, contoh: 8'],
    'jam_tutup'       => ['label' => 'Jam tutup',                  'tipe' => 'jam',    'bantuan' => 'Format 24 jam, contoh: 21'],
    'sla_menit'       => ['label' => 'Janji antar (menit)',        'tipe' => 'angka',  'bantuan' => 'Contoh: 60'],
];

$kesalahan = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    periksaToken();

    $nilaiBaru = [];

    foreach ($daftarIsian as $nama => $aturan) {
        $nilai = trim($_POST[$nama] ?? '');

        if ($nilai === '') {
            $kesalahan[$nama] = $aturan['label'] . ' wajib diisi.';
            continue;
        }

        if ($aturan['tipe'] === 'angka' && !ctype_digit($nilai)) {
            $kesalahan[$nama] = $aturan['label'] . ' harus berupa angka.';
            continue;
        }

        if ($aturan['tipe'] === 'jam') {
            if (!ctype_digit($nilai) || (int) $nilai < 0 || (int) $nilai > 23) {
                $kesalahan[$nama] = $aturan['label'] . ' harus angka 0 sampai 23.';
                continue;
            }
        }

        $nilaiBaru[$nama] = $nilai;
    }

    // Nomor WA untuk link harus angka saja dan diawali 62
    if (isset($nilaiBaru['nomor_wa'])) {
        $nomor = preg_replace('/[^0-9]/', '', $nilaiBaru['nomor_wa']);

        if (strpos($nomor, '0') === 0) {
            $nomor = '62' . substr($nomor, 1);   // 0813... diubah jadi 62813...
        }
        if (strpos($nomor, '62') !== 0) {
            $kesalahan['nomor_wa'] = 'Nomor WhatsApp harus diawali 62 atau 0.';
        } elseif (strlen($nomor) < 10 || strlen($nomor) > 15) {
            $kesalahan['nomor_wa'] = 'Panjang nomor WhatsApp tidak wajar.';
        } else {
            $nilaiBaru['nomor_wa'] = $nomor;
        }
    }

    // Jam tutup harus setelah jam buka
    if (isset($nilaiBaru['jam_buka'], $nilaiBaru['jam_tutup'])
        && (int) $nilaiBaru['jam_tutup'] <= (int) $nilaiBaru['jam_buka']) {
        $kesalahan['jam_tutup'] = 'Jam tutup harus lebih besar dari jam buka.';
    }

    if (empty($kesalahan)) {
        foreach ($nilaiBaru as $nama => $nilai) {
            query('UPDATE pengaturan SET nilai = ? WHERE nama_pengaturan = ?', [$nilai, $nama]);
        }

        simpanPesan('Pengaturan berhasil disimpan dan langsung berlaku di website.');
        header('Location: pengaturan.php');
        exit;
    }

    // Kalau ada kesalahan, tampilkan isian terakhir yang diketik admin
    foreach ($daftarIsian as $nama => $aturan) {
        $pengaturan[$nama] = $_POST[$nama] ?? '';
    }
}

$judul     = 'Pengaturan';
$menuAktif = 'pengaturan';
include __DIR__ . '/partials/atas.php';
?>

      <h1 class="section-title" style="margin-bottom:0.25rem;">Pengaturan Website</h1>
      <p class="text-muted" style="margin-bottom:1.5rem;">
        Nilai di sini dipakai di semua halaman. Mengganti nomor WhatsApp cukup di sini saja.
      </p>

      <?php if (!empty($kesalahan)): ?>
        <div style="background:#FFF3BF; color:#D9480F; font-weight:700; padding:0.9rem 1.15rem; border-radius:var(--radius-xl); margin-bottom:1.5rem;">
          ⚠️ Ada <?= count($kesalahan) ?> isian yang perlu diperbaiki.
        </div>
      <?php endif; ?>

      <form method="post" class="signature-card" style="max-width:720px;">
        <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">

        <?php foreach ($daftarIsian as $nama => $aturan): ?>
          <div style="margin-bottom:1.25rem;">
            <label for="<?= $nama ?>" style="display:block; font-weight:700; font-size:0.9rem; margin-bottom:0.35rem;">
              <?= e($aturan['label']) ?>
            </label>
            <input type="<?= $aturan['tipe'] === 'text' ? 'text' : 'number' ?>"
                   id="<?= $nama ?>" name="<?= $nama ?>" class="input-utility" style="width:100%"
                   value="<?= e(atur($nama)) ?>"
                   <?= $aturan['tipe'] === 'jam' ? 'min="0" max="23"' : '' ?>
                   <?= $aturan['tipe'] === 'angka' ? 'min="0"' : '' ?>>
            <div style="font-size:0.8rem; color:var(--color-ink-muted); margin-top:0.25rem;"><?= e($aturan['bantuan']) ?></div>
            <?php if (isset($kesalahan[$nama])): ?>
              <div style="color:var(--color-accent-dark); font-size:0.85rem; margin-top:0.25rem;"><?= e($kesalahan[$nama]) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary btn-lg" style="margin-top:0.5rem;">Simpan Pengaturan</button>
      </form>

<?php include __DIR__ . '/partials/bawah.php'; ?>
