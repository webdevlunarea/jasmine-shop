<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$provider = in_array($customer['registration_method'] ?? '', ['email', 'google', 'facebook'], true) ? $customer['registration_method'] : 'unknown';
$providerLabels = ['email' => 'Email', 'google' => 'Google', 'facebook' => 'Facebook', 'unknown' => 'Tidak diketahui'];
$registered = null;
if (!empty($customer['registered_at'])) {
    try { $registered = (new DateTimeImmutable($customer['registered_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d M Y, H:i') . ' WIB'; }
    catch (Throwable $error) { $registered = null; }
}
?>
<main class="konten admin-module-page">
 <div class="container customer-directory-page">
  <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Detail pelanggan', 'adminHeadingGroup'=>'Pelanggan', 'adminHeadingDescription'=>'Informasi kontak dasar untuk membantu layanan akun.'], ['saveData'=>false]); ?>
  <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4" aria-labelledby="customer-detail-heading">
   <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><h2 id="customer-detail-heading" class="h5 mb-1"><?= esc($customer['nama'] ?: 'Nama belum diisi'); ?></h2><p class="small text-muted mb-0">Profil pelanggan · <?= (string)$customer['active'] === '1' ? 'Akun aktif' : 'Akun belum aktif'; ?></p></div>
    <a class="btn btn-outline-secondary" href="/customerdirectoryadmin">Kembali ke daftar</a>
   </div>
   <dl class="row g-0 mb-0">
    <dt class="col-12 col-md-4 py-3 border-top small text-secondary">Nama</dt><dd class="col-12 col-md-8 py-3 border-top mb-0 fw-semibold"><?= esc($customer['nama'] ?: 'Belum diisi'); ?></dd>
    <dt class="col-12 col-md-4 py-3 border-top small text-secondary">Email akun</dt><dd class="col-12 col-md-8 py-3 border-top mb-0 text-break"><?= esc($customer['email']); ?></dd>
    <dt class="col-12 col-md-4 py-3 border-top small text-secondary">Nomor ponsel</dt><dd class="col-12 col-md-8 py-3 border-top mb-0"><?= esc($customer['nohp'] ?: 'Belum tersedia'); ?></dd>
    <dt class="col-12 col-md-4 py-3 border-top small text-secondary">Pendaftaran awal</dt><dd class="col-12 col-md-8 py-3 border-top mb-0"><?= esc($providerLabels[$provider]); ?></dd>
    <dt class="col-12 col-md-4 py-3 border-top small text-secondary">Status akun</dt><dd class="col-12 col-md-8 py-3 border-top mb-0"><?= (string)$customer['active'] === '1' ? 'Aktif' : 'Belum aktif'; ?></dd>
    <dt class="col-12 col-md-4 py-3 border-top small text-secondary">Waktu terdaftar</dt><dd class="col-12 col-md-8 py-3 border-top mb-0"><?= esc($registered ?? 'Tanggal belum tercatat'); ?></dd>
   </dl>
   <p class="alert alert-light border small mb-0 mt-3" role="note">Hanya informasi dasar untuk layanan akun yang ditampilkan. Alamat rumah, tanggal lahir, sandi, dan data pembayaran tidak ditampilkan. Permintaan detail dicatat pada log aktivitas admin.</p>
  </section>
 </div>
</main>
<?= $this->endSection(); ?>
