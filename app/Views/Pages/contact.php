<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <h1 class="my-3">Hubungi Kami</h1>
        <div class="baris-ke-kolom mb-5">
            <div class="empatpuluh-ke-seratus">
                <img style="width: 100%; aspect-ratio: 8/7; object-fit: cover;" src="<?= base_url('img/CS Lunarea.webp'); ?>" alt="">
            </div>
            <div style="flex: 1;">
                <div class="h-100 d-flex justify-content-between flex-column">
                    <div>
                        <h3>Customer Service Lunarea</h3>
                        <p>Ajukan pertanyaan Anda dengan menghubungi layanan pelanggan Lunarea Furniture atau dapatkan jawabannya di bawah ini.</p>
                        <h3>Temukan jawabannya sekarang:</h3>
                        <a href="/faq?a=5#flush-collapse5" class="text-dark d-block" style="text-decoration: underline;">Apakah saya bisa mendapatkan diskon gratis ongkir?</a>
                        <a href="/faq?a=6#flush-collapse6" class="text-dark d-block" style="text-decoration: underline;">Apakah saya bisa mengembalikan produk yang tidak sesuai dengan pesanan?</a>
                        <a href="/faq" style="color: var(--hijau);" class="d-block link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Lihat semua FAQ</a>
                    </div>
                    <div class="mt-3">
                        <p class="fw-bold mb-1">Layanan Pelanggan Lunarea</p>
                        <a href="https://api.whatsapp.com/send?phone=628112938160&text=Halo%20CS%20*Lunarea*%2C%20saya%20mau%20membeli%20furniture....." style="text-decoration: none; color: black">
                            <p class="m-0">Telephone : 08112938160</p>
                        </a>
                        <a href="mailto:cs@lunareafurniture.com" style="text-decoration: none; color: black">
                            <p class=" m-0">Email : cs@lunareafurniture.com</p>
                        </a>
                        <a class="m-0" style="text-decoration: none; color: black">
                            <p class=" m-0">Senin sampai Sabtu di jam kerja</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="baris-ke-kolom gap-2 w-100">
            <?php foreach ($artikel as $a) { ?>
                <div style="flex: 1">
                    <p class="fw-bold mb-1"><?= ucwords($a['judul']); ?></p>
                    <div style="height: 40px; overflow: hidden; position: relative;">
                        <div style="background-image: linear-gradient(to top, white, transparent); width: 100%; height: 100%; position: absolute;"></div>
                        <p class="m-0"><?= $a['isi'][0]['teks']; ?></p>
                    </div>
                    <a href="/article/<?= $a['path']; ?>" style="text-decoration: none; color: var(--hijau)">baca selengkapnya...</a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>