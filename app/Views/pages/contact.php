<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <h1 class="m-0">Hubungi Kami</h1>
    </div>
    <img src="/img/pic contact.png" alt="" style="width: 100%;" class="my-4">
    <div class="container">
        <div class="h-100 d-flex justify-content-between flex-column mb-5">
            <div>
                <h3>Customer Service Lunarea</h3>
                <p>Ajukan pertanyaan Anda dengan menghubungi layanan pelanggan Lunarea Furniture atau dapatkan
                    jawabannya di bawah ini.</p>
                <h3>Temukan Solusi Cepat</h3>
                <a href="/faq?a=5#flush-collapse5" class="text-dark d-block" style="text-decoration: underline;">Apakah
                    saya bisa mendapatkan diskon gratis ongkir?</a>
                <a href="/faq?a=6#flush-collapse6" class="text-dark d-block" style="text-decoration: underline;">Apakah
                    saya bisa mengembalikan produk yang tidak sesuai dengan pesanan?</a>
                <a href="/faq" style="color: var(--hijau);"
                    class="d-block link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Lihat semua
                    FAQ</a>
            </div>
            <div class="mt-3">
                <p class="fw-bold mb-2">Layanan Pelanggan Lunarea</p>
                <div class="contact-channel-grid">
                    <a href="https://api.whatsapp.com/send?phone=<?= LUNAREA_CS_WHATSAPP_E164; ?>&text=Halo%20CS%20*Lunarea*%2C%20saya%20mau%20membeli%20furniture....."
                        class="contact-channel-card">
                        <span class="contact-channel-card__icon"><i class="material-icons">support_agent</i></span>
                        <span>
                            <span class="contact-channel-card__label">Customer Service</span>
                            <strong><?= LUNAREA_CS_WHATSAPP_DISPLAY; ?></strong>
                            <small>Untuk info produk, pesanan, pembayaran, dan bantuan umum.</small>
                        </span>
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=<?= LUNAREA_RETURN_WHATSAPP_E164; ?>&text=Halo%20Tim%20Retur%20%26%20Komplain%20*Lunarea*%2C%20saya%20butuh%20bantuan%20terkait%20pesanan%20saya....."
                        class="contact-channel-card">
                        <span class="contact-channel-card__icon contact-channel-card__icon--warning"><i class="material-icons">assignment_return</i></span>
                        <span>
                            <span class="contact-channel-card__label">Retur / Komplain</span>
                            <strong><?= LUNAREA_RETURN_WHATSAPP_DISPLAY; ?></strong>
                            <small>Untuk kendala barang, retur, klaim, dan after-sales.</small>
                        </span>
                    </a>
                    <a href="mailto:cs@lunareafurniture.com" class="contact-channel-card">
                        <span class="contact-channel-card__icon"><i class="material-icons">email</i></span>
                        <span>
                            <span class="contact-channel-card__label">Email</span>
                            <strong>cs@lunareafurniture.com</strong>
                            <small>Alternatif jika ingin mengirim detail secara tertulis.</small>
                        </span>
                    </a>
                </div>
                <a class="m-0" style="text-decoration: none; color: black">
                    <p class=" m-0">Senin sampai Sabtu di jam kerja</p>
                </a>
            </div>
        </div>
        <hr class="my-4">
        <div class="baris-ke-kolom gap-2 w-100">
            <?php foreach ($artikel as $a) { ?>
            <div style="flex: 1">
                <p class="fw-bold mb-1"><?= ucwords($a['judul']); ?></p>
                <div style="height: 40px; overflow: hidden; position: relative;">
                    <div
                        style="background-image: linear-gradient(to top, white, transparent); width: 100%; height: 100%; position: absolute;">
                    </div>
                    <p class="m-0"><?= esc($a['excerpt'] ?? ''); ?></p>
                </div>
                <a href="/article/<?= $a['path']; ?>" style="text-decoration: none; color: var(--hijau)">baca
                    selengkapnya...</a>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
