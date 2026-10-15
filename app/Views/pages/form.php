<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten buyer-contact-page buyer-contact-form-page" aria-labelledby="contact-title">
    <div class="container">
        <nav class="contact-breadcrumb" aria-label="Breadcrumb"><a href="/">Beranda</a><span aria-hidden="true">/</span><a href="/contact">Hubungi kami</a><span aria-hidden="true">/</span><span aria-current="page">Tulis pesan</span></nav>
        <header class="contact-heading">
            <p class="contact-eyebrow">Kami siap membantu</p>
            <h1 id="contact-title">Mari berbicara.</h1>
            <p>Punya pertanyaan tentang furnitur atau pesanan? Tinggalkan pesan untuk tim Lunarea.</p>
        </header>
        <div class="contact-form-layout">
            <section class="contact-form-panel" aria-labelledby="contact-form-title">
                <h2 id="contact-form-title">Tulis pesan Anda</h2>
                <p class="contact-section-note">Nama dan nomor HP wajib diisi.</p>
                <?php if (!empty($val['msg'])) { ?>
                <div class="contact-form-notice" role="status"><?= esc($val['msg']); ?></div>
                <?php } ?>
                <form id="contact-message-form" action="/actionform" method="post">
                    <?= csrf_field(); ?>
                    <div class="contact-field-row">
                        <div class="contact-field"><label for="contact-name">Nama lengkap</label><input id="contact-name" type="text" class="form-control" name="nama" autocomplete="name" placeholder="Nama Anda" required></div>
                        <div class="contact-field"><label for="contact-phone">Nomor HP</label><input id="contact-phone" type="tel" inputmode="tel" class="form-control" name="nohp" autocomplete="tel" placeholder="Contoh: 0812…" required></div>
                    </div>
                    <div class="contact-field"><label for="contact-address">Alamat <span>(opsional)</span></label><input id="contact-address" type="text" class="form-control" name="alamat" autocomplete="street-address" placeholder="Alamat Anda"></div>
                    <div class="contact-field"><label for="contact-message">Pesan <span>(opsional)</span></label><textarea id="contact-message" class="form-control" name="pesan" rows="5" placeholder="Ceritakan hal yang ingin Anda tanyakan">Hai CS Lunarea, saya ingin menanyakan mengenai ...</textarea></div>
                    <div class="contact-form-footer"><p>Informasi Anda digunakan untuk menindaklanjuti pertanyaan ini. Baca <a href="/kebijakan-privasi">Kebijakan Privasi</a>.</p><button type="submit" disabled class="contact-button" id="btn-kirim">Kirim pesan <span aria-hidden="true">→</span></button></div>
                </form>
            </section>
            <aside class="contact-form-guide" aria-labelledby="contact-guide-title">
                <h2 id="contact-guide-title">Ceritakan kebutuhan Anda</h2>
                <p>Lengkapi nama dan nomor HP agar tim kami dapat menghubungi Anda kembali.</p>
                <dl><div><dt>Bantuan pesanan</dt><dd>Jika sudah berbelanja, sertakan nomor pesanan di dalam pesan.</dd></div><div><dt>Jam layanan</dt><dd>Senin–Sabtu pada jam kerja.</dd></div></dl>
                <a class="contact-text-link" href="/contact">Lihat pilihan kontak lainnya <span aria-hidden="true">→</span></a>
            </aside>
        </div>
    </div>
</main>
<script>
(() => {
    const form = document.getElementById('contact-message-form');
    const button = document.getElementById('btn-kirim');
    const name = form.elements.nama;
    const phone = form.elements.nohp;
    let submitting = false;
    const sync = () => { button.disabled = submitting || !name.value.trim() || !phone.value.trim(); };
    form.addEventListener('input', sync);
    form.addEventListener('change', sync);
    window.addEventListener('pageshow', () => { submitting = false; button.textContent = 'Kirim pesan'; sync(); });
    form.addEventListener('submit', event => {
        if (submitting) { event.preventDefault(); return; }
        if (!name.value.trim() || !phone.value.trim() || !form.checkValidity()) {
            event.preventDefault(); sync(); return;
        }
        submitting = true;
        button.disabled = true;
        button.textContent = 'Mengirim pesan…';
    });
    sync();
})();
</script>
<?= $this->endSection(); ?>
