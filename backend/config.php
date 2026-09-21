<?php
/**
 * config.php
 * -----------------------------------------------------------
 * LETAKKAN FILE INI DI: pembayaran_spp/backend/config.php
 * (SEJAJAR dengan kirim_wa.php, satu folder dengan "petugas" dan "admin")
 *
 * File ini isinya SEMUA data rahasia (token API, dsb).
 * JANGAN PERNAH file ini ikut di-zip, di-upload ke GitHub publik,
 * atau dikirim ke siapapun (termasuk ke AI/chat manapun).
 * -----------------------------------------------------------
 *
 * CARA ISI BARIS DI BAWAH INI:
 * 1. Buka Fonnte -> menu Device -> klik tombol "Token"
 * 2. Copy kode acak yang muncul (huruf+angka campur, tanpa spasi)
 * 3. Ganti SELURUH tulisan ISI_TOKEN_DARI_FONNTE_DISINI di bawah
 *    (tanda kutip di kanan-kirinya JANGAN ikut kehapus)
 *    dengan kode yang kamu copy tadi.
 *
 * CONTOH kalau token asli kamu misalnya "aB3xY9kLmNoPqRs2":
 *   SALAH  : define('FONNTE_TOKEN', 'ISI_TOKEN_DARI_FONNTE_DISINI');
 *   SALAH  : define('FONNTE_TOKEN', aB3xY9kLmNoPqRs2);
 *   BENAR  : define('FONNTE_TOKEN', 'aB3xY9kLmNoPqRs2');
 */

define('FONNTE_TOKEN', 'EXRcw4EjZjZdjwrco7od');
define('FONNTE_API_URL', 'https://api.fonnte.com/send');