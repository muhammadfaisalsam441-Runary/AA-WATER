/**
 * AA WATER — "Antar Galon & Gas"
 * Interactive Logic using JavaScript & jQuery (v3.7.1)
 *
 * Konfigurasi & Ketentuan:
 * - Ongkir: Rp3.000 per item
 * - Alamat Depot: Jl. Borong Raya 1 lr 1 No. 12 A, Kelurahan Borong, Kec. Manggala, Makassar
 * - Area Jangkauan: Jl. Borong Raya, Kelurahan Borong Raya, Batua, Toddopuli
 */

// Konfigurasi Bisnis
const AA_CONFIG = {
  phone: '6282188990011',
  displayPhone: '0821-8899-0011',
  address: 'Jl. Borong Raya 1 lr 1 No. 12 A, Kelurahan Borong, Kec. Manggala, Makassar',
  openHour: 8,   // 08:00
  closeHour: 21, // 21:00
  feePerItem: 3000, // Ongkir Rp3.000 per item
  deliveryArea: [
    { name: 'Jl. Borong Raya', eta: '10 - 20 menit', fee: 'Rp3.000 / item', status: 'prioritas' },
    { name: 'Kelurahan Borong Raya', eta: '15 - 25 menit', fee: 'Rp3.000 / item', status: 'prioritas' },
    { name: 'Batua', eta: '20 - 30 menit', fee: 'Rp3.000 / item', status: 'terjangkau' },
    { name: 'Toddopuli', eta: '20 - 35 menit', fee: 'Rp3.000 / item', status: 'terjangkau' }
  ],
  pricing: {
    galon_tukar: { name: 'Galon (Tukar Isi)', price: 5000, type: 'tukar' },
    galon_baru: { name: 'Galon Baru (Tanpa Isi)', price: 30000, type: 'beli' },
    galon_isi_baru: { name: 'Galon + Isi Baru', price: 35000, type: 'beli' },
    gas_tukar: { name: 'Gas 3kg (Tukar Tabung)', price: 22000, type: 'tukar' },
    gas_tabung_baru: { name: 'Beli Tabung Gas Baru (Kosong)', price: 175000, type: 'beli' },
    gas_tabung_isi: { name: 'Beli Tabung + Gas 3kg', price: 197000, type: 'beli' }
  }
};

// Keranjang Pesanan Real-time
let orderCart = {
  galon_tukar: 1,      // Default 1 galon tukar
  gas_tukar: 1,        // Default 1 gas tukar
  galon_baru: 0,
  galon_isi_baru: 0,
  gas_tabung_baru: 0,
  gas_tabung_isi: 0
};

// Helper Format Rupiah
function formatRupiah(number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(number);
}

// Inisialisasi jQuery saat DOM Siap
$(document).ready(function() {
  console.log('AA WATER App Ready with jQuery ' + $.fn.jquery);

  // 1. Cek Status Operasional (08.00 - 21.00)
  updateStoreStatus();
  setInterval(updateStoreStatus, 60000);

  // 2. Inisialisasi Kalkulator Pesanan
  initOrderCalculator();

  // 3. Inisialisasi Mobile Navigation Drawer
  initMobileNav();

  // 4. Inisialisasi Interactive Area Checker
  initAreaChecker();

  // 5. Inisialisasi Salin Cepat Format WA
  initCopyFormatButtons();

  // 6. Inisialisasi FAQ Accordion
  initFaqAccordion();

  // 7. Update tombol WA global dengan link aktif
  updateGlobalWhatsAppLinks();
});

/**
 * 1. LOGIKA STATUS OPERASIONAL TOKO (08.00 - 21.00)
 */
function updateStoreStatus() {
  const now = new Date();
  const currentHour = now.getHours();
  const currentMinute = now.getMinutes();
  const timeInMinutes = currentHour * 60 + currentMinute;
  
  const openTimeMinutes = AA_CONFIG.openHour * 60;
  const closeTimeMinutes = AA_CONFIG.closeHour * 60;

  const isOpen = (timeInMinutes >= openTimeMinutes && timeInMinutes < closeTimeMinutes);

  const $statusPill = $('.js-store-status');
  const $statusText = $('.js-status-text');
  const $floatingStatus = $('.js-floating-status');

  if (isOpen) {
    const minutesLeft = closeTimeMinutes - timeInMinutes;
    const hoursLeft = Math.floor(minutesLeft / 60);
    const minsLeft = minutesLeft % 60;
    const timeLeftStr = hoursLeft > 0 ? `${hoursLeft} jam lagi` : `${minsLeft} menit lagi`;

    $statusPill.removeClass('closed').addClass('open');
    $statusText.html(`<strong>BUKA</strong> • 08.00 – 21.00 WITA (Tutup dlm ${timeLeftStr})`);
    $floatingStatus.html('🟢 <strong>Buka</strong> (Siap Kirim)');
  } else {
    $statusPill.removeClass('open').addClass('closed');
    $statusText.html(`<strong>TUTUP</strong> • Buka besok 08.00 WITA (Bisa jadwalkan pesan sekarang)`);
    $floatingStatus.html('🔴 <strong>Tutup</strong> (Pesan utk besok)');
  }
}

/**
 * 2. INTERACTIVE ORDER CALCULATOR & WA LINK GENERATOR
 * Biaya = Subtotal Barang + (Ongkir Rp3.000 x Jumlah Item)
 */
function initOrderCalculator() {
  const $builder = $('#orderBuilder');
  if ($builder.length === 0) return;

  $('.js-qty-plus').on('click', function(e) {
    e.preventDefault();
    const itemKey = $(this).data('item');
    if (orderCart.hasOwnProperty(itemKey)) {
      orderCart[itemKey] = (orderCart[itemKey] || 0) + 1;
      updateCalculatorDisplay();
    }
  });

  $('.js-qty-minus').on('click', function(e) {
    e.preventDefault();
    const itemKey = $(this).data('item');
    if (orderCart.hasOwnProperty(itemKey) && orderCart[itemKey] > 0) {
      orderCart[itemKey] -= 1;
      updateCalculatorDisplay();
    }
  });

  // Tampilkan kondisi awal
  updateCalculatorDisplay();

  // Tombol Kirim WA dari Kalkulator
  $('#btnSendOrderWA').on('click', function(e) {
    e.preventDefault();
    launchWhatsAppOrder();
  });
}

function updateCalculatorDisplay() {
  let subtotal = 0;
  let totalItems = 0;
  let itemSummary = [];

  for (const key in orderCart) {
    const qty = orderCart[key];
    const $qtyDisplay = $(`.js-qty-val[data-item="${key}"]`);
    if ($qtyDisplay.length) {
      $qtyDisplay.text(qty);
    }

    if (qty > 0) {
      const itemData = AA_CONFIG.pricing[key];
      const itemSubtotal = qty * itemData.price;
      subtotal += itemSubtotal;
      totalItems += qty;
      itemSummary.push(`${itemData.name} x${qty}`);
    }
  }

  // Ongkir Rp3.000 per item
  const deliveryFee = totalItems * AA_CONFIG.feePerItem;
  const grandTotal = subtotal + deliveryFee;

  // Update elemen harga di DOM dengan jQuery
  $('#builderSubtotal').text(formatRupiah(grandTotal));
  $('#builderItemCount').text(`${totalItems} item dipilih • Ongkir ${formatRupiah(deliveryFee)}`);
  
  if (totalItems === 0) {
    $('#builderSummaryDesc').text('Pilih minimal 1 galon atau gas di atas untuk order');
    $('#btnSendOrderWA').addClass('disabled').css('opacity', '0.6');
  } else {
    $('#builderSummaryDesc').text(`${itemSummary.join(' • ')} (Barang: ${formatRupiah(subtotal)} + Ongkir: ${formatRupiah(deliveryFee)})`);
    $('#btnSendOrderWA').removeClass('disabled').css('opacity', '1');
  }
}

function generateWhatsAppMessage() {
  let itemsText = [];
  let itemSubtotal = 0;
  let totalItems = 0;

  for (const key in orderCart) {
    const qty = orderCart[key];
    if (qty > 0) {
      const item = AA_CONFIG.pricing[key];
      itemsText.push(`- ${item.name}: ${qty} buah (${formatRupiah(qty * item.price)})`);
      itemSubtotal += (qty * item.price);
      totalItems += qty;
    }
  }

  const deliveryFee = totalItems * AA_CONFIG.feePerItem;
  const grandTotal = itemSubtotal + deliveryFee;

  if (itemsText.length === 0) {
    return `Halo AA WATER, saya mau order Galon & Gas ke alamat saya:
Nama:
Alamat:
Patokan:
Pembayaran: (Cash/QRIS/Transfer)`;
  }

  return `Halo AA WATER, saya mau order:
${itemsText.join('\n')}

Subtotal Barang: ${formatRupiah(itemSubtotal)}
Ongkir (${totalItems} item @ Rp3.000): ${formatRupiah(deliveryFee)}
Total Pembayaran: ${formatRupiah(grandTotal)}

Alamat pengantaran: [Tulis nama jalan / lorong / nomor rumah]
Patokan: [Contoh: Depan pos ronda / samping masjid]
Metode bayar: (Cash / QRIS / Transfer)
Antar segera < 60 menit ya bang!`;
}

function launchWhatsAppOrder() {
  let totalItems = 0;
  for (const key in orderCart) {
    totalItems += orderCart[key];
  }

  if (totalItems === 0) {
    alert('Silakan pilih minimal 1 galon atau tabung gas terlebih dahulu.');
    return;
  }

  const message = generateWhatsAppMessage();
  const waUrl = `https://wa.me/${AA_CONFIG.phone}?text=${encodeURIComponent(message)}`;
  window.open(waUrl, '_blank');
}

/**
 * 3. GLOBAL WHATSAPP SHORTCUT LINKS
 */
function updateGlobalWhatsAppLinks() {
  const defaultMsg = `Halo AA WATER! Saya mau order Galon / Gas 3kg.
Alamat saya: 
Patokan: 
Kirim sekarang ya bang!`;
  const defaultUrl = `https://wa.me/${AA_CONFIG.phone}?text=${encodeURIComponent(defaultMsg)}`;

  $('.js-wa-direct').attr('href', defaultUrl).attr('target', '_blank');
}

/**
 * 4. MOBILE NAVIGATION DRAWER
 */
function initMobileNav() {
  const $toggleBtn = $('#mobileMenuToggle');
  const $drawer = $('#mobileNavDrawer');

  $toggleBtn.on('click', function(e) {
    e.preventDefault();
    $drawer.stop().slideToggle(200, function() {
      if ($drawer.is(':visible')) {
        $toggleBtn.html('&times;');
      } else {
        $toggleBtn.html('&#9776;');
      }
    });
  });

  $('.mobile-nav-link').on('click', function() {
    $drawer.slideUp(150);
    $toggleBtn.html('&#9776;');
  });
}

/**
 * 5. INTERACTIVE AREA CHECKER (Khusus 4 Wilayah: Jl. Borong Raya, Kel. Borong Raya, Batua, Toddopuli)
 */
function initAreaChecker() {
  const $input = $('#areaSearchInput');
  const $resultBox = $('#areaSearchResult');
  const $chips = $('.area-chip');

  $chips.on('click', function() {
    const areaName = $(this).data('area');
    $chips.removeClass('active');
    $(this).addClass('active');
    $input.val(areaName);
    checkArea(areaName);
  });

  $('#btnCekArea').on('click', function(e) {
    e.preventDefault();
    const query = $input.val().trim();
    if (query.length > 0) {
      checkArea(query);
    } else {
      $input.focus();
    }
  });

  $input.on('keyup input', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const query = $(this).val().trim();
      if (query.length > 0) checkArea(query);
      return;
    }
    const query = $(this).val().trim();
    if (query.length > 1) {
      checkArea(query);
    } else {
      $resultBox.slideUp(150);
    }
  });

  function checkArea(query) {
    const lower = query.toLowerCase();
    const matched = AA_CONFIG.deliveryArea.filter(item => item.name.toLowerCase().includes(lower));

    if (matched.length > 0) {
      const area = matched[0];
      $resultBox.html(`
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
          <div>
            <div style="font-weight:800; color:#1864AB; font-size:1.05rem;">
              ✅ <strong>${area.name}</strong> — Masuk Area Jangkauan Resmi Kami!
            </div>
            <div style="font-size:0.875rem; color:#536E82; margin-top:0.25rem;">
              Estimasi Antar: <strong>⚡ ${area.eta}</strong> • Ongkir: <strong>${area.fee}</strong>
            </div>
          </div>
          <a href="https://wa.me/${AA_CONFIG.phone}?text=${encodeURIComponent('Halo AA WATER, saya di ' + area.name + '. Mau pesan galon/gas sekarang!')}" 
             target="_blank" class="btn btn-sm btn-primary">
            Pesan ke ${area.name} &rarr;
          </a>
        </div>
      `).stop().slideDown(200);
    } else {
      $resultBox.html(`
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
          <div>
            <div style="font-weight:800; color:#D9480F; font-size:1.05rem;">
              📍 Lokasi "<em>${query}</em>" di Luar Jangkauan Utama Kami
            </div>
            <div style="font-size:0.875rem; color:#536E82; margin-top:0.25rem;">
              Jangkauan utama kami: <strong>Jl. Borong Raya, Kelurahan Borong Raya, Batua, & Toddopuli</strong>. Tanya kurir dulu via WA siapa tahu kurir lagi searah!
            </div>
          </div>
          <a href="https://wa.me/${AA_CONFIG.phone}?text=${encodeURIComponent('Halo AA WATER, mau tanya apakah bisa antar ke alamat ini: ' + query + '?')}" 
             target="_blank" class="btn btn-sm btn-accent">
            Tanya Kurir via WA &rarr;
          </a>
        </div>
      `).stop().slideDown(200);
    }
  }
}

/**
 * 6. SALIN FORMAT WA OTOMATIS
 */
function initCopyFormatButtons() {
  $('.js-copy-btn').on('click', function(e) {
    e.preventDefault();
    const textToCopy = $(this).data('copy-text');
    const $btn = $(this);

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(textToCopy).then(function() {
        showCopySuccess($btn);
      }).catch(function() {
        fallbackCopy(textToCopy, $btn);
      });
    } else {
      fallbackCopy(textToCopy, $btn);
    }
  });

  function fallbackCopy(text, $btn) {
    const $temp = $('<textarea>');
    $('body').append($temp);
    $temp.val(text).select();
    document.execCommand('copy');
    $temp.remove();
    showCopySuccess($btn);
  }

  function showCopySuccess($btn) {
    const originalText = $btn.text();
    $btn.text('✓ Tersalin!').css({ 'background-color': '#2B8A3E', 'color': '#ffffff' });
    setTimeout(function() {
      $btn.text(originalText).removeAttr('style');
    }, 2200);
  }
}

/**
 * 7. FAQ ACCORDION INTERAKTIF
 */
function initFaqAccordion() {
  $('.js-faq-toggle').on('click', function() {
    const $target = $(this).next('.js-faq-body');
    const $icon = $(this).find('.faq-chevron');

    $target.stop().slideToggle(200);
    if ($icon.length) {
      $icon.toggleClass('rotated');
    }
  });
}
