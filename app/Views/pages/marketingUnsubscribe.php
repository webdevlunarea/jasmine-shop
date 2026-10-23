<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="container py-5" style="max-width:680px;min-height:50vh">
    <section class="account-card p-4 p-md-5" aria-labelledby="marketing-unsubscribe-title">
        <p class="small text-secondary mb-2">Preferensi Lunarea</p>
        <h1 class="h3 fw-bold" id="marketing-unsubscribe-title">Berhenti menerima email promosi</h1>
        <?php if (!empty($result)) { ?>
            <p class="mb-3" role="status"><?= esc($result); ?></p>
        <?php } elseif (!empty($valid)) { ?>
            <p class="text-secondary">Konfirmasi untuk menghentikan email voucher dan promosi. Email penting tentang pesanan dan keamanan akun tidak terpengaruh.</p>
            <form method="post" action="/promosi/berhenti/<?= esc(rawurlencode($token)); ?>">
                <?= csrf_field(); ?>
                <button class="btn btn-primary1" type="submit">Berhenti menerima promosi</button>
            </form>
        <?php } else { ?>
            <p class="text-secondary">Tautan ini sudah digunakan atau tidak lagi berlaku. Anda dapat mengatur pilihan promosi melalui akun Lunarea.</p>
            <a class="btn btn-primary1" href="/account">Buka akun</a>
        <?php } ?>
    </section>
</main>
<?= $this->endSection(); ?>
