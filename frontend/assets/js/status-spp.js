/* frontend/assets/js/status-spp.js — Tabel riwayat: lipat 5 baris + tombol "Tampilkan semua"
   LETAKKAN DI: pembayaran_spp/frontend/assets/js/status-spp.js */

(function () {
  var tabel  = document.getElementById('tabel-riwayat');
  var tombol = document.getElementById('btn-riwayat');
  if (!tabel || !tombol) { return; }

  // Awalnya dilipat (baris ke-6 dst. disembunyikan lewat CSS class "lipat")
  tabel.classList.add('lipat');
  tombol.style.display = 'block';

  tombol.addEventListener('click', function () {
    var terlipat = tabel.classList.toggle('lipat');
    tombol.textContent = terlipat ? 'Tampilkan semua (' + tombol.dataset.total + ')' : 'Tutup';
  });

  // Saat dicetak, semua baris harus kelihatan. Setelah cetak, kembalikan seperti semula.
  var dilipatSebelumCetak = false;
  window.addEventListener('beforeprint', function () {
    dilipatSebelumCetak = tabel.classList.contains('lipat');
    tabel.classList.remove('lipat');
  });
  window.addEventListener('afterprint', function () {
    if (dilipatSebelumCetak) { tabel.classList.add('lipat'); }
  });
})();