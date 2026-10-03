<?php
/**
 * kontak.php — Kontak dan lokasi depot
 */

require_once __DIR__ . '/konfigurasi.php';

$judul     = 'Kontak & Lokasi Depot — ' . atur('nama_depot', 'AA WATER') . ' Makassar';
$deskripsi = 'Hubungi ' . atur('nama_depot') . ' di ' . atur('nomor_tampil') . '. Depot: ' . atur('alamat_depot') . '.';
$halaman   = 'kontak';

include __DIR__ . '/partials/header.php';
?>
  <main>
    <section class="section section-bg">
      <div class="container">
        <div class="section-tag">Pusat Layanan & Depot</div>
        <h1 class="section-title">Kontak & Alamat Pangkalan</h1>
        <p class="section-sub">
          Punya pertanyaan seputar stok, pengantaran mendesak, atau mau ambil langsung di pangkalan kami? Hubungi kami langsung.
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(320px, 100%), 1fr)); gap:2rem; margin-bottom: 3rem;">
          
          <!-- Informasi Depot & Hotline -->
          <div style="background:white; border:2px solid var(--color-border); border-radius:var(--radius-2xl); padding:2rem; box-shadow:var(--shadow-md);">
            <h3 style="font-size:1.35rem; font-weight:800; margin-bottom:1.5rem; color:var(--color-ink);">
              📍 Pangkalan AA WATER
            </h3>

            <div style="display:flex; flex-direction:column; gap:1.25rem;">
              <div style="display:flex; gap:1rem; align-items:flex-start;">
                <div style="font-size:1.5rem; color:var(--color-primary);">🏠</div>
                <div>
                  <div style="font-weight:800; font-size:1rem; color:var(--color-ink);">Alamat Depot Resmi:</div>
                  <div style="font-size:0.95rem; color:var(--color-primary-dark); font-weight:700; margin-top:0.25rem; line-height:1.5;">
                    <?= e(atur("alamat_depot")) ?>.
                  </div>
                </div>
              </div>

              <div style="display:flex; gap:1rem; align-items:flex-start;">
                <div style="font-size:1.5rem; color:var(--color-accent);">🕒</div>
                <div>
                  <div style="font-weight:800; font-size:1rem; color:var(--color-ink);">Jam Operasional Pengantaran:</div>
                  <div style="font-size:0.925rem; color:var(--color-ink-muted); margin-top:0.25rem; line-height:1.5;">
                    <strong>Setiap Hari: <?= sprintf("%02d.00", (int) atur("jam_buka", "8")) ?> – <?= sprintf("%02d.00", (int) atur("jam_tutup", "21")) ?> WITA</strong><br>
                    Hari Minggu, tanggal merah & hari libur nasional tetap melayani pengantaran!
                  </div>
                </div>
              </div>

              <div style="display:flex; gap:1rem; align-items:flex-start;">
                <div style="font-size:1.5rem; color:#25D366;">📱</div>
                <div>
                  <div style="font-weight:800; font-size:1rem; color:var(--color-ink);">Hotline WhatsApp & Telepon:</div>
                  <div style="font-size:1.15rem; font-weight:800; color:var(--color-ink); margin-top:0.25rem;">
                    <?= e(atur("nomor_tampil")) ?>
                  </div>
                  <div style="font-size:0.85rem; color:var(--color-ink-muted);">Respon cepat oleh admin pangkalan & kurir siaga.</div>
                </div>
              </div>
            </div>

            <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--color-border-subtle);">
              <a href="<?= e(linkWa("Halo " . atur("nama_depot") . ", mau tanya info pemesanan")) ?>" target="_blank" class="btn btn-wa btn-lg" style="width:100%;">
                Chat WhatsApp Sekarang
              </a>
            </div>
          </div>

          <!-- Metode Pembayaran & Keamanan -->
          <div style="background:white; border:2px solid var(--color-border); border-radius:var(--radius-2xl); padding:2rem; box-shadow:var(--shadow-md);">
            <h3 style="font-size:1.35rem; font-weight:800; margin-bottom:1.5rem; color:var(--color-ink);">
              💳 Metode Pembayaran
            </h3>

            <div style="display:flex; flex-direction:column; gap:1.25rem;">
              <div style="background:var(--color-section); border-radius:var(--radius-lg); padding:1rem; border:1px solid var(--color-border);">
                <div style="font-weight:800; font-size:1rem; color:var(--color-ink); margin-bottom:0.25rem;">
                  1. Tunai (COD di Tempat)
                </div>
                <div style="font-size:0.875rem; color:var(--color-ink-muted);">
                  Bayar langsung ke kurir saat tabung/galon tiba di rumah Anda. Kurir selalu sedia uang kembalian pas.
                </div>
              </div>

              <div style="background:var(--color-section); border-radius:var(--radius-lg); padding:1rem; border:1px solid var(--color-border);">
                <div style="font-weight:800; font-size:1rem; color:var(--color-ink); margin-bottom:0.25rem;">
                  2. QRIS Statis & Dinamis
                </div>
                <div style="font-size:0.875rem; color:var(--color-ink-muted);">
                  Kurir membawa kartu barcode QRIS resmi. Terima semua bank (BCA, BRI, Mandiri, BNI) & semua e-wallet (GoPay, OVO, Dana, ShopeePay).
                </div>
              </div>

              <div style="background:var(--color-section); border-radius:var(--radius-lg); padding:1rem; border:1px solid var(--color-border);">
                <div style="font-weight:800; font-size:1rem; color:var(--color-ink); margin-bottom:0.25rem;">
                  3. Transfer Bank Langsung
                </div>
                <div style="font-size:0.875rem; color:var(--color-ink-muted);">
                  Tersedia rekening BCA, BRI, dan Mandiri an. Depot AA WATER. Kirim bukti transfer via chat WhatsApp.
                </div>
              </div>
            </div>

            <!-- Jaminan Keamanan -->
            <div style="margin-top:1.5rem; background:#FFF4E6; border:1.5px solid #FFD8A8; border-radius:var(--radius-lg); padding:1rem;">
              <div style="font-weight:800; color:#D9480F; font-size:0.95rem; margin-bottom:0.25rem;">
                🛡️ Garansi Tabung & Air Higienis
              </div>
              <div style="font-size:0.825rem; color:#536E82; line-height:1.5;">
                Jika tabung gas mendesis atau timbul bau gas setelah dipasang, kurir wajib menukar dengan tabung lain secara gratis saat itu juga!
              </div>
            </div>
          </div>

        </div>

        <!-- FAQ Lengkap -->
        <div style="margin-top:3rem;">
          <div class="text-center" style="margin-bottom:2rem;">
            <div class="section-tag">Tanya Jawab</div>
            <h2 class="section-title">Pertanyaan Terkait Layanan</h2>
          </div>

          <div class="faq-grid">
            <div class="faq-card">
              <div class="faq-q"><span class="faq-q-icon">?</span> Berapa biaya ongkos kirimnya?</div>
              <div class="faq-a">Ongkos kirim hanya <strong>Rp3.000 per item</strong> untuk seluruh 4 area layanan utama kami: Jl. Borong Raya, Kel. Borong Raya, Batua, dan Toddopuli.</div>
            </div>

            <div class="faq-card">
              <div class="faq-q"><span class="faq-q-icon">?</span> Berapa lama estimasi kurir sampai?</div>
              <div class="faq-a">Untuk area Jl. Borong Raya rata-rata 10–20 menit. Maksimal garansi SLA kami &lt; 60 menit dari saat konfirmasi pesanan via WA.</div>
            </div>

            <div class="faq-card">
              <div class="faq-q"><span class="faq-q-icon">?</span> Kalau galon merek saya bukan merek AA WATER, bisa ditukar?</div>
              <div class="faq-a">Bisa! Asalkan jenisnya galon polikarbonat / PET standar 19 Liter dalam kondisi utuh tidak pecah/retak.</div>
            </div>

            <div class="faq-card">
              <div class="faq-q"><span class="faq-q-icon">?</span> Kurir bantu angkat sampai ke dispenser / dapur?</div>
              <div class="faq-a">Pasti dibantu! Kurir kami siap membantu mengangkat galon ke atas dispenser atau memasangkan regulator gas ke tabung bila Anda membutuhkan bantuan.</div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>

<?php include __DIR__ . "/partials/footer.php"; ?>
