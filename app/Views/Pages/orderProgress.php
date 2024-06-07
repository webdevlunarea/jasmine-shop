<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-2 my-5">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">access_time</i></div>
                <p class="m-0 text-center fw-bold" style="line-height: 20px; color: var(--hijau)">Menunggu<br>Pembayaran</p>
            </div>
            <div style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">local_shipping</i></div>
            <div style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">done</i></div>
        </div>
        <p class="my-auto text-secondary text-sm-start mb-4 limapuluh-ke-seratus">*Simpan URL halaman ini untuk melihat status pesanan. Atau dapat login sebagai member kami agar dapat melihat seluruh riwayat pesanan Anda.</p>
        <div class="baris-ke-kolom">
            <div class="limapuluh-ke-seratus">
                <div class="d-flex justify-content-between mb-3">
                    <div class="flex-grow-1">
                        <?php if ($dataMid['payment_type'] == 'bank_transfer') { ?>
                            <p class="m-0">Nomor Virtual Account</p>
                            <h5><?= strtoupper($bank); ?> <?= $va_number; ?></h5>
                        <?php } else if ($dataMid['payment_type'] == 'qris') { ?>
                            <p class="m-0">QR Code</p>
                            <img src="<?= $va_number; ?>" alt="" width="150px" height="150px">
                        <?php } ?>
                    </div>
                    <div class="flex-grow-1">
                        <p class="m-0">Nominal</p>
                        <h5>Rp <?= number_format($dataMid['gross_amount'], 0, ",", "."); ?></h5>
                    </div>
                </div>
                <div class="mb-3">
                    <p class="m-0">Waktu Kadaluarsa</p>
                    <h5><?= $waktuExpire; ?></h5>
                </div>
                <span class="garis mb-3"></span>
                <div class="mb-5">
                    <p class="m-0">Sisa Waktu Pembayaran</p>
                    <h5 id="waktu" style="color: var(--hijau);"><?= $waktu; ?></h5>
                </div>
                <a class="btn btn-primary1" href="/order/<?= $dataMid['order_id']; ?>">Saya Sudah Bayar</a>
            </div>
            <div class="limapuluh-ke-seratus">
                <p class="fw-bold">Cara Pembayaran</p>
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse1" aria-expanded="false" aria-controls="flush-collapse1">
                                Transfer ATM
                            </button>
                        </h2>
                        <div id="flush-collapse1" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <p class="mb-0 fw-bold">Bagaimana cara mengetahui barang ready?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Ketersediaan stok bisa langsung dengan cara mengecek pada kolom kuantitas di
                                            spesifikasi produk. Apabila telah berhasil melakukan checkout, dapat
                                            dipastikan ketersediaan produk tersebut untuk Anda.
                                        </p>
                                    </li>
                                </ul>
                                <p class="mb-0 mt-1 fw-bold">Bagaimana cara melihat promosi terupdate?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Promo produk yang lagi diadakan selalu tersedia dan dapat dilihat pada
                                            website Kami. Selain itu, promosi juga selalu Kami update di akun sosial
                                            media dan juga katalog yang Kami beri di WhatsApp serta email saat Anda
                                            berlangganan dengan layanan email dan WhatsApp Kami.
                                        </p>
                                    </li>
                                </ul>
                                <p class="mb-0 mt-1 fw-bold">Darimana pengiriman produk Jasmine Furniture?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Pengiriman produk Jasmine Furniture berasal dari Kota Semarang, Jawa Tengah
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse2" aria-expanded="false" aria-controls="flush-collapse2">
                                Mobile Banking
                            </button>
                        </h2>
                        <div id="flush-collapse2" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <p class="mb-0 fw-bold">Bagaimana cara mengetahui barang ready?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Ketersediaan stok bisa langsung dengan cara mengecek pada kolom kuantitas di
                                            spesifikasi produk. Apabila telah berhasil melakukan checkout, dapat
                                            dipastikan ketersediaan produk tersebut untuk Anda.
                                        </p>
                                    </li>
                                </ul>
                                <p class="mb-0 mt-1 fw-bold">Bagaimana cara melihat promosi terupdate?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Promo produk yang lagi diadakan selalu tersedia dan dapat dilihat pada
                                            website Kami. Selain itu, promosi juga selalu Kami update di akun sosial
                                            media dan juga katalog yang Kami beri di WhatsApp serta email saat Anda
                                            berlangganan dengan layanan email dan WhatsApp Kami.
                                        </p>
                                    </li>
                                </ul>
                                <p class="mb-0 mt-1 fw-bold">Darimana pengiriman produk Jasmine Furniture?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Pengiriman produk Jasmine Furniture berasal dari Kota Semarang, Jawa Tengah
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse3" aria-expanded="false" aria-controls="flush-collapse3">
                                Teller Bank
                            </button>
                        </h2>
                        <div id="flush-collapse3" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <p class="mb-0 fw-bold">Bagaimana cara mengetahui barang ready?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Ketersediaan stok bisa langsung dengan cara mengecek pada kolom kuantitas di
                                            spesifikasi produk. Apabila telah berhasil melakukan checkout, dapat
                                            dipastikan ketersediaan produk tersebut untuk Anda.
                                        </p>
                                    </li>
                                </ul>
                                <p class="mb-0 mt-1 fw-bold">Bagaimana cara melihat promosi terupdate?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Promo produk yang lagi diadakan selalu tersedia dan dapat dilihat pada
                                            website Kami. Selain itu, promosi juga selalu Kami update di akun sosial
                                            media dan juga katalog yang Kami beri di WhatsApp serta email saat Anda
                                            berlangganan dengan layanan email dan WhatsApp Kami.
                                        </p>
                                    </li>
                                </ul>
                                <p class="mb-0 mt-1 fw-bold">Darimana pengiriman produk Jasmine Furniture?</p>
                                <ul>
                                    <li>
                                        <p class="mb-0">
                                            Pengiriman produk Jasmine Furniture berasal dari Kota Semarang, Jawa Tengah
                                        </p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const expiryTimeElm = document.getElementById("waktu");
    const de = new Date('<?= $dataMid['expiry_time']; ?>');
    const expireTime = de.getTime();
    const dc = new Date();

    setInterval(() => {
        const currTime = new Date().getTime();
        let dselisih = expireTime - currTime;

        const hours = String(Math.floor(dselisih / (1000 * 60 * 60))).padStart(2, '0');
        dselisih %= (1000 * 60 * 60);
        const minutes = String(Math.floor(dselisih / (1000 * 60))).padStart(2, '0');
        dselisih %= (1000 * 60);
        const seconds = String(Math.floor(dselisih / 1000)).padStart(2, '0');

        expiryTimeElm.innerHTML = `${hours}: ${minutes}: ${seconds}`;
    }, 1000);

    function copytext(teks) {
        navigator.clipboard.writeText(teks);
    }
</script>
<?= $this->endSection(); ?>