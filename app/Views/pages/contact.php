<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten buyer-contact-page" aria-labelledby="contact-title">
    <div class="container">
        <nav class="contact-breadcrumb" aria-label="Breadcrumb"><a href="/">Beranda</a><span aria-hidden="true">/</span><span aria-current="page">Hubungi kami</span></nav>
        <header class="contact-heading">
            <p class="contact-eyebrow">Lunarea Furniture</p>
            <h1 id="contact-title">Ada yang bisa kami bantu?</h1>
            <p>Mulai dari memilih furnitur hingga bantuan pesanan, temukan cara yang tepat untuk menghubungi kami.</p>
        </header>
        <div class="contact-layout">
            <section class="contact-channels" aria-labelledby="contact-channels-title">
                <h2 id="contact-channels-title">Hubungi tim kami</h2>
                <p class="contact-section-note">Layanan pelanggan tersedia Senin–Sabtu pada jam kerja.</p>
                <a class="contact-channel" href="https://api.whatsapp.com/send?phone=<?= LUNAREA_CS_WHATSAPP_E164; ?>&amp;text=Halo%20CS%20Lunarea%2C%20saya%20ingin%20bertanya%20tentang%20produk%20atau%20pesanan.">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 13v-2a8 8 0 0 1 16 0v2M4 11H2v7h4v-7H4Zm16 0h2v7h-4v-7h2Zm0 7a4 4 0 0 1-4 4h-4"/></svg>
                    <span><strong>Customer Service</strong><small>Informasi produk, pembelian, pembayaran, dan bantuan umum.</small><span class="contact-channel-action">Chat Customer Service <span aria-hidden="true">↗</span></span></span>
                </a>
                <a class="contact-channel" href="https://api.whatsapp.com/send?phone=<?= LUNAREA_RETURN_WHATSAPP_E164; ?>&amp;text=Halo%20Tim%20Retur%20dan%20Komplain%20Lunarea%2C%20saya%20butuh%20bantuan%20terkait%20pesanan.">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 8h16v13H4V8Zm-1 0 3-5h12l3 5M9 12l-3 3 3 3m-3-3h8a3 3 0 0 1 3 3"/></svg>
                    <span><strong>Retur &amp; komplain</strong><small>Bantuan untuk kendala barang, pengajuan retur, dan layanan setelah pembelian.</small><span class="contact-channel-action">Ajukan bantuan pesanan <span aria-hidden="true">↗</span></span></span>
                </a>
                <a class="contact-channel" href="mailto:cs@lunareafurniture.com">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 5h18v14H3V5Zm0 0 9 8 9-8"/></svg>
                    <span><strong>Kirim melalui email</strong><small>cs@lunareafurniture.com</small><span class="contact-channel-action">Tulis email <span aria-hidden="true">↗</span></span></span>
                </a>
            </section>
            <aside class="contact-help" aria-labelledby="contact-help-title">
                <h2 id="contact-help-title">Temukan jawaban lebih cepat</h2>
                <p>Beberapa pertanyaan yang sering ditanyakan pelanggan.</p>
                <a class="contact-faq-link" href="/faq?a=5#flush-collapse5">Apakah tersedia diskon gratis ongkir?<span aria-hidden="true">→</span></a>
                <a class="contact-faq-link" href="/faq?a=6#flush-collapse6">Bagaimana jika produk tidak sesuai pesanan?<span aria-hidden="true">→</span></a>
                <a class="contact-text-link" href="/faq">Lihat semua FAQ <span aria-hidden="true">→</span></a>
                <div class="contact-write">
                    <h3>Lebih nyaman menulis pesan?</h3>
                    <p>Sampaikan pertanyaan Anda melalui formulir kontak.</p>
                    <a class="contact-button" href="/form">Tulis pesan</a>
                </div>
            </aside>
        </div>
        <?php if (!empty($artikel)) { ?>
        <section class="contact-reading" aria-labelledby="contact-reading-title">
            <h2 id="contact-reading-title">Informasi untuk Anda</h2>
            <div class="contact-articles">
                <?php foreach ($artikel as $a) { ?>
                <article>
                    <h3><?= esc(ucwords((string)($a['judul'] ?? ''))); ?></h3>
                    <p><?= esc($a['excerpt'] ?? ''); ?></p>
                    <a class="contact-text-link" href="<?= esc('/article/' . ($a['path'] ?? ''), 'attr'); ?>">Baca selengkapnya <span aria-hidden="true">→</span></a>
                </article>
                <?php } ?>
            </div>
        </section>
        <?php } ?>
    </div>
</main>
<?= $this->endSection(); ?>
