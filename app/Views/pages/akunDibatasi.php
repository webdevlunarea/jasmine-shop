<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten buyer-help-page">
 <div class="container buyer-help-container">
  <header class="buyer-help-heading"><p class="buyer-help-eyebrow">Informasi akun</p><h1>Akun sedang dibatasi</h1><p><?= esc($msg ?? 'Akses masuk dan transaksi akun ini untuk sementara dinonaktifkan. Jika Anda merasa ini keliru atau ingin meminta peninjauan, hubungi kami melalui halaman bantuan.'); ?></p></header>
  <article class="buyer-help-content"><p>Pesanan yang sudah dibuat tetap diproses sesuai statusnya. Pembatasan akun tidak otomatis membatalkan pembayaran atau pesanan.</p><p><a href="/contact">Hubungi Lunarea</a></p></article>
 </div>
</main>
<?= $this->endSection(); ?>
