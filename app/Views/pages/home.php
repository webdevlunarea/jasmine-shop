<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
.item-slider {
    display: block;
    height: 10px;
    width: 10px;
    border-radius: 20px;
    background-color: #dddddd;
    transition: 0.2s;
}

.item-slider.active {
    width: 20px;
    background-color: var(--hijau);
    transition: 0.2s;
}

.container-notif-voucher {
    width: fit-content;
    background-color: white;
}

.container-notif-voucher img {
    height: 60svh;
    aspect-ratio: 1 / 1;
}

@media (orientation: portrait) {
    .container-notif-voucher {
        width: 80%;
    }

    .container-notif-voucher img {
        width: 100%;
        height: auto;
    }
}

.btn-next {
    border: none;
    outline: none;
    background-color: white;
    /* width: 30px;
        height: 30px; */
    border-radius: 2em;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    padding: 0;
    color: grey;
}

.btn-next:hover {
    background-color: var(--hijau);
    color: white;
}
</style>
<?php
$counterVoucher = 0;
if ($msg_active) { ?>
<div id="modal-voucher" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; z-index: 99;"
    class="d-none justify-content-center align-items-center modal-voucher">
    <div style="border-radius: 10px; overflow: hidden; background-color: white; width: 80%; max-width: 500px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);"
        class="p-5">
        <h1 class="teks-sedang m-0" style="color: var(--hijau);">Selamat!</h1>
        <h1 class="mb-3">Voucher diskon 5% sudah Kamu klaim!</h1>
        <p class="text-secondary">*S&K diskon ini hanya berlaku 1 bulan sejak menjadi member kami</p>
        <hr>
        <?php if ($counterEvent > 1) { ?>
        <div class="d-flex gap-1 justify-content-center mb-3">
            <?php for ($i = 0; $i < $counterEvent; $i++) { ?>
            <div class="item-slider <?= $i == $counterVoucher ? 'active' : ''; ?>"></div>
            <?php } ?>
        </div>
        <?php } ?>
        <a href="/all" class="btn btn-primary1 w-100 text-center mb-2">Beli Produk</a>
        <button class="btn-teks-aja mx-auto" onclick="closeModalVoucher(<?= $counterVoucher; ?>)">Selanjutnya</button>
    </div>
</div>
<?php
    $counterVoucher++;
} ?>
<?php if ($msg_event) { ?>
<?php foreach ($msg_event['voucherClaimed'] as $ind => $v) { ?>
<?php if ($v['id_voucher'] == 2) { ?>
<div id="modal-voucher" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; z-index: 99;"
    class="d-none justify-content-center align-items-center modal-voucher">
    <div
        style="position:relative; border-radius: 10px; overflow: hidden; background-color: white; width: 80%; max-width: 500px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);">
        <div class="d-flex justify-content-end w-100 p-2" style="position: absolute;">
            <i class="material-icons" onclick="closeModalVoucherAll(<?= $counterVoucher; ?>)"
                style="cursor: pointer; color: rgba(0, 0, 0, 0.5)">close</i>
        </div>
        <div class="p-5">
            <h1 class="m-0 text-center">🎉🎉🎉</h1>
            <h1 class="m-0 text-center">Selamat Ulang Tahun <?= ucfirst(explode(" ", session()->get('nama'))[0]); ?>!
            </h1>
            <div class="d-flex my-3 justify-content-center">
                <img src="<?= session()->get('foto'); ?>" alt=""
                    style="width: 200px; height: 200px; object-fit:cover; border-radius: 400px">
            </div>
            <p class="m-0 text-center">Rayakan pertambahan usia & pakai voucher untuk mendapatkan cashback <b
                    style="color: var(--hijau);">Rp25.000</b></p>
            <p class="m-0 text-secondary text-center" style="font-size: small;">**promo sesuai dengan S&K berlaku</p>

            <hr>
            <div class="d-flex gap-2 align-items-center justify-content-center mb-3">
                <?php if ($counterEvent > 1) { ?>
                <div class="d-flex gap-1 justify-content-center">
                    <?php for ($i = 0; $i < $counterEvent; $i++) { ?>
                    <div class="item-slider <?= $i == $counterVoucher ? 'active' : ''; ?>"></div>
                    <?php } ?>
                </div>
                <button class="btn-next" onclick="closeModalVoucher(<?= $counterVoucher; ?>)"><i
                        class="material-icons">chevron_right</i></button>
                <?php } ?>
            </div>
            <a href="/voucher" class="btn btn-primary1 w-100 text-center mb-2">Klaim sekarang</a>
        </div>
    </div>
</div>
<?php } else { ?>
<div id="modal-voucher" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; z-index: 99;"
    class="d-none justify-content-center align-items-center modal-voucher">
    <div
        style="position:relative; border-radius: 10px; overflow: hidden; background-color: white; width: 80%; max-width: 500px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);">
        <div class="d-flex justify-content-end w-100 p-2" style="position: absolute;">
            <i class="material-icons" onclick="closeModalVoucherAll(<?= $counterVoucher; ?>)"
                style="cursor: pointer; color: rgba(0, 0, 0, 0.5)">close</i>
        </div>
        <div class="p-5">
            <h1 class="mb-3">Gunakan voucher <b style="color: var(--hijau);"><?= $v['nama']; ?></b> sebelum kadaluarsa!
            </h1>
            <p class="text-secondary"><?= $v['keterangan']; ?></p>
            <hr>
            <div class="d-flex gap-2 align-items-center justify-content-center mb-3">
                <?php if ($counterEvent > 1) { ?>
                <div class="d-flex gap-1 justify-content-center">
                    <?php for ($i = 0; $i < $counterEvent; $i++) { ?>
                    <div class="item-slider <?= $i == $counterVoucher ? 'active' : ''; ?>"></div>
                    <?php } ?>
                </div>
                <button class="btn-next" onclick="closeModalVoucher(<?= $counterVoucher; ?>)"><i
                        class="material-icons">chevron_right</i></button>
                <?php } ?>
            </div>
            <a href="/all" class="btn btn-primary1 w-100 text-center mb-2">Beli Produk</a>
        </div>
    </div>
</div>
<?php } ?>
<?php $counterVoucher++; ?>
<?php } ?>
<?php foreach ($msg_event['voucherNoClaimed'] as $ind => $v) { ?>
<div id="modal-voucher" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; z-index: 99;"
    class="d-none justify-content-center align-items-center modal-voucher">
    <?php if ($v['poster']) { ?>
    <div class="container-notif-voucher"
        style="border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3); position:relative">
        <div class="d-flex justify-content-end w-100 p-2" style="position: absolute;">
            <i class="material-icons" onclick="closeModalVoucherAll(<?= $counterVoucher; ?>)"
                style="cursor: pointer; color: rgba(0, 0, 0, 0.5)">close</i>
        </div>
        <img src="/imgvoucher/<?= $v['id']; ?>" alt="">
        <div class="px-5 py-4 position-relative">
            <div class="d-flex gap-2 align-items-center justify-content-center mb-3">
                <?php if ($counterEvent > 1) { ?>
                <div class="d-flex gap-1 justify-content-center">
                    <?php for ($i = 0; $i < $counterEvent; $i++) { ?>
                    <div class="item-slider <?= $i == $counterVoucher ? 'active' : ''; ?>"></div>
                    <?php } ?>
                </div>
                <button class="btn-next" onclick="closeModalVoucher(<?= $counterVoucher; ?>)"><i
                        class="material-icons">chevron_right</i></button>
                <?php } ?>
            </div>
            <a href="/voucher" class="btn btn-primary1 w-100 text-center mb-2">Coba sekarang</a>
        </div>
    </div>
    <?php } else { ?>
    <div
        style="position:relative; border-radius: 10px; overflow: hidden; background-color: white; width: 80%; max-width: 500px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);">
        <div class="d-flex justify-content-end w-100 p-2" style="position: absolute;">
            <i class="material-icons" onclick="closeModalVoucherAll(<?= $counterVoucher; ?>)"
                style="cursor: pointer; color:rgba(0, 0, 0, 0.5)">close</i>
        </div>
        <div class="p-5">
            <h3 class="m-0 text-center">Klaim voucher</h3>
            <h1 class="m-0 text-center" style="color: var(--hijau);"><?= $v['nama']; ?></h1>
            <p class="mb-3 text-center">untuk mendapatkan <?= $v['jenis'] == 'member' ? 'potongan' : $v['jenis']; ?>!
            </p>
            <p class="text-secondary text-center"><?= $v['keterangan']; ?></p>
            <hr>
            <div class="d-flex gap-2 align-items-center justify-content-center mb-3">
                <?php if ($counterEvent > 1) { ?>
                <div class="d-flex gap-1 justify-content-center">
                    <?php for ($i = 0; $i < $counterEvent; $i++) { ?>
                    <div class="item-slider <?= $i == $counterVoucher ? 'active' : ''; ?>"></div>
                    <?php } ?>
                </div>
                <button class="btn-next" onclick="closeModalVoucher(<?= $counterVoucher; ?>)"><i
                        class="material-icons">chevron_right</i></button>
                <?php } ?>
            </div>
            <a href="/voucher" class="btn btn-primary1 w-100 text-center mb-2">Klaim Sekarang</a>
        </div>
    </div>
    <?php } ?>
</div>
<?php $counterVoucher++; ?>
<?php } ?>
<?php foreach ($msg_event['codeRedeem'] as $ind => $v) { ?>
<div id="modal-voucher" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100svh; z-index: 99;"
    class="d-none justify-content-center align-items-center modal-voucher">
    <div
        style="position:relative; border-radius: 10px; overflow: hidden; background-color: white; width: 80%; max-width: 500px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);">
        <div class="d-flex justify-content-end w-100 p-2" style="position: absolute;">
            <i class="material-icons" onclick="closeModalVoucherAll(<?= $counterVoucher; ?>)"
                style="cursor: pointer; color:rgba(0, 0, 0, 0.5)">close</i>
        </div>
        <div class="p-5">
            <h1 class="m-0 text-center">Pakai kode promo</h1>
            <h1 class="m-0 text-center" style="color: var(--hijau);"><?= $v['code']; ?></h1>
            <p class="mb-3 text-center">dan dapatkan <?= $v['jenis']; ?>
                <?= $v['satuan'] == 'rupiah' ? 'Rp ' . $v['nominal'] : $v['nominal'] . ' ' . $v['satuan']; ?></p>
            <p class="text-secondary text-center">*berlaku untuk minimal order Rp. 250.000</p>
            <hr>
            <?php if ($counterEvent > 1) { ?>
            <div class="d-flex gap-1 justify-content-center mb-3">
                <?php for ($i = 0; $i < $counterEvent; $i++) { ?>
                <div class="item-slider <?= $i == $counterVoucher ? 'active' : ''; ?>"></div>
                <?php } ?>
            </div>
            <?php } ?>
            <a href="/all" class="btn btn-primary1 w-100 text-center mb-2">Beli Produk</a>
            <button class="btn-teks-aja mx-auto" onclick="closeModalVoucher(<?= $counterVoucher; ?>)">Nanti</button>
        </div>
    </div>
</div>
<?php $counterVoucher++; ?>
<?php } ?>
<?php } ?>
<script>
const modalVoucherElm = document.querySelectorAll('.modal-voucher');
if (!window.localStorage.getItem('notifVoucher')) {
    if (modalVoucherElm.length > 0) {
        modalVoucherElm[0].classList.add('d-flex')
        modalVoucherElm[0].classList.remove('d-none')
    }
}
modalVoucherElm.forEach((e, ind_e) => {
    e.addEventListener('click', () => {
        closeModalVoucherAll(ind_e);
    })
    e.children[0].addEventListener('click', (event) => {
        event.stopPropagation()
    })
})

function closeModalVoucher(index) {
    modalVoucherElm[index].classList.add('d-none')
    modalVoucherElm[index].classList.remove('d-flex')
    if (index + 1 < modalVoucherElm.length) {
        modalVoucherElm[index + 1].classList.add('d-flex')
        modalVoucherElm[index + 1].classList.remove('d-none')
    } else {
        window.localStorage.setItem('notifVoucher', true);
    }
}

function closeModalVoucherAll(index) {
    modalVoucherElm[index].classList.add('d-none')
    modalVoucherElm[index].classList.remove('d-flex')
    window.localStorage.setItem('notifVoucher', true);
}
</script>
<div class="konten pb-0">
    <div class="container">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" 
                    aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                    aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" class="active" 
                    aria-current="true" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3"
                    aria-label="Slide 4"></button>
                <!-- <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="4"
                    aria-label="Slide 5"></button> -->
            </div>
            <div class="carousel-inner" style="border-radius: 0.5em;">
                <div class=" carousel-item active">
                    <img src="img/banner3.webp?v=188" class="show-ke-hide" style="width: 100%; height: auto;"
                        alt="Lunarea Furniture">
                    <img src="img/banner3 hp.webp?v=188" class="hide-ke-show-block" style="width: 100%; height: auto;"
                        alt="Lunarea Furniture">
                </div>
                <div class="carousel-item">
                    <img loading="lazy" src="img/banner2.webp?v=188" class="show-ke-hide"
                        style="width: 100%; height: auto;" alt="Lunarea Furniture Gratis Ongkir">
                    <img loading="lazy" src="img/banner2 hp.webp?v=188" class="hide-ke-show-block"
                        style="width: 100%; height: auto;" alt="Lunarea Furniture Gratis Ongkir">
                </div>
                <div class="carousel-item">
                    <img loading="lazy" src="img/alamak1.webp?v=188" class="show-ke-hide"
                        style="width: 100%; height: auto;" alt="Lunarea Special Sale Discount 5%">
                    <img loading="lazy" src="img/alamak1 hp.webp?v=188" class="hide-ke-show-block"
                        style="width: 100%; height: auto;" alt="Lunarea Special Sale Discount 5%">
                </div>
                <div class="carousel-item">
                    <img loading="lazy" src="img/udin.webp?v=188" class="show-ke-hide"
                        style="width: 100%; height: auto;" alt="Lunarea Minimalist Furniture">
                    <img loading="lazy" src="img/udin hp hp.webp?v=188" class="hide-ke-show-block"
                        style="width: 100%; height: auto;" alt="Lunarea Minimalist Furniture">
                </div>
                <!-- <div class="carousel-item">
                    <img loading="lazy" src="img/banner5.webp?v=022026" class="show-ke-hide"
                        style="width: 100%; height: auto;" alt="Ramadan Barang lunarea">
                    <img loading="lazy" src="img/banner5 hp.webp?v=022026" class="hide-ke-show-block"
                        style="width: 100%; height: auto;" alt="Lunarea Minimalist Furniture">
                </div> -->
                <!-- <div class="carousel-item">
                    <a href="/all/diskon">
                        <img loading="lazy" src="img/banner5.webp?v=123" class="show-ke-hide"
                            style="width: 100%; height: auto;" alt="Lunarea 15% Furniture">
                        <img loading="lazy" src="img/banner5 hp.webp?v=123" class="hide-ke-show-block"
                            style="width: 100%; height: auto;" alt="Lunarea 15% Furniture">
                    </a>
                </div> -->
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <div class="container my-3">
        <h5 class="jdl-section">Kategori</h5>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-1">Kategori Produk</h1>
            <div class="gap-2 show-flex-ke-hide">
                <div class="btn-primary1 btn-geser-kategori" id="kiri"
                    style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i
                        class="material-icons">chevron_left</i></div>
                <div class="btn-primary1 btn-geser-kategori" id="kanan"
                    style="padding: 0.5em; border-radius: 1.5em; cursor: default;"><i
                        class="material-icons">chevron_right</i></div>
            </div>
        </div>
        <div class="container-kategori-scroll" id="section-produk-populer">
            <div class="container-kategori">
                <!-- <a class="kategori" href="/all/diskon">
                    <img src="/img/logokategori/Diskon15.webp" alt="Diskon" width="50px">
                    <p>Big Sale up to 15%</p>
                </a> -->
                <a class="kategori" href="/all/lemari-dewasa">
                    <img src="/img/logokategori/Lemari_Dewasa.webp" alt="lemnari dewasa" width="50px">
                    <p>Lemari Dewasa</p>
                </a>
                <a class="kategori" href="/all/lemari-anak">
                    <img src="/img/logokategori/Lemari_Anak.webp" alt="lemari anak" width="50px">
                    <p>Lemari Anak</p>
                </a>
                <!--<a class="kategori" href="/all/lemari-hias">-->
                <!--    <img src="/img/logokategori/Lemari_Hias.webp" alt="lemari hias" width="50px">-->
                <!--    <p>Lemari Hias</p>-->
                <!--</a>-->
                <a class="kategori" href="/all/meja-rias">
                    <img src="/img/logokategori/Meja_Rias.webp" alt="" width="50px">
                    <p>Meja Rias</p>
                </a>
                <a class="kategori" href="/all/meja-belajar">
                    <img src="/img/logokategori/Meja_Belajar.webp" alt="meja belajar" width="50px">
                    <p>Meja Belajar</p>
                </a>
                <a class="kategori" href="/all/meja-tv">
                    <img src="/img/logokategori/Meja_TV.webp" alt="meja tv" width="50px">
                    <p>Meja TV</p>
                </a>
                <a class="kategori" href="/all/meja-tulis">
                    <img src="/img/logokategori/Meja_Tulis.webp" alt="meja tulis" width="50px">
                    <p>Meja Tulis</p>
                </a>
                <a class="kategori" href="/all/meja-komputer">
                    <img src="/img/logokategori/Meja_Komputer.webp" alt="meja kompuiter" width="50px">
                    <p>Meja Komputer</p>
                </a>
                <a class="kategori" href="/all/rak-sepatu">
                    <img src="/img/logokategori/Rak_Sepatu.webp" alt="rak sepatu" width="50px">
                    <p>Rak Sepatu</p>
                </a>
                <a class="kategori" href="/all/rak-besi">
                    <img src="/img/logokategori/Rak_Besi.webp" alt="rak besi" width="50px">
                    <p>Rak Besi</p>
                </a>
                <a class="kategori" href="/all/rak-serbaguna">
                    <img src="/img/logokategori/Rak_Serbaguna.webp" alt="rak serbaguna" width="50px">
                    <p>Rak Serbaguna</p>
                </a>
                <a class="kategori" href="/all/kursi">
                    <img src="/img/logokategori/Kursi.webp" alt="kursi" width="50px">
                    <p>Kursi</p>
                </a>
            </div>
        </div>
    </div>
    <div class="container my-3">
        <?php
        $window_width = "<script type='text/javascript'>document.write(window.innerWidth);</script>";
        ?>
        <h5 class="jdl-section">Rekomendasi</h5>
        <h1 class="mb-1">Produk Terpopuler</h1>
        <div class="card-group1 no-scroll">
            <?php foreach ($produkBaru as $p) { ?>
            <a class="card1" href="/product/<?= $p['path']; ?>">
                <?php if ($p['diskon']) { ?>
                <p class="diskon my-3">-<?= (int)$p['diskon']; ?>%</p>
                <?php } ?>
                <div style="position: relative; width: 100%; aspect-ratio: 1 / 1;">
                    <img class="img-card1-wm" src="<?= base_url('img/WM Black 300.webp'); ?>" alt="Watermark Lunarea">
                    <img class="img-card1" src="data:image/webp;base64,<?= base64_encode($p['gambar']); ?>"
                        alt="<?= $p['nama']; ?>">
                </div>
                <div>
                    <h5 class="mb-0"><?= $p['nama']; ?></h5>
                    <div class="container-varian my-2 pb-1">
                        <div>
                            <?php foreach (json_decode($p['varian'], true) as $v) { ?>
                            <p class="mb-0 varian"><?= $v ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- <p class="mb-0 varian"><?= implode(" - ", json_decode($p['varian'], true)); ?></p> -->
                    <?php if ($p['diskon']) { ?>
                    <span class="d-flex gap-1 align-items-center">
                        <p class="mb-0 diskon-coret"
                            style="text-decoration: line-through; color: grey; width:fit-content;">
                            Rp
                            <?= number_format($p['harga'], 0, ",", "."); ?></p>
                        <p class="diskon-bwh">-<?= (int)$p['diskon']; ?>%</p>
                    </span>
                    <p class="mb-0 harga">Rp
                        <?php
                                $persen = (100 - $p['diskon']) / 100;
                                $hasil = $persen * $p['harga'];
                                echo number_format($hasil, 0, ",", ".");
                                ?></p>
                    <?php } else { ?>
                    <p class="mb-0 harga">Rp <?= number_format($p['harga'], 0, ",", "."); ?></p>
                    <?php } ?>
                    <!-- <p>★★★☆☆ (<?= $p['rate']; ?>)</p> -->
                </div>
            </a>
            <?php } ?>
        </div>
        <div class="mx-auto mt-2" style="width: fit-content;">
            <a href="/all" class="btn mx-auto btn-primary1" style="width: fit-content;">Lihat Semua Produk</a>
        </div>
    </div>
    <div style="background-color: #c6f1c9; color: #2d7e33;" class="py-5 mt-5">
        <div class="container baris-ke-kolom gap-5">
            <div style="flex: 1" class="d-flex justify-content-start gap-4">
                <img src="../img/logo ongkir.webp" alt="" style="width: 70px; height: 40px">
                <div>
                    <p class="fw-bold mb-1">Gratis ongkir 100%</p>
                    <p>Pengiriman di pulau Jawa, Madura, dan Bali tanpa minimum pembelian</p>
                </div>
            </div>
            <div style="flex: 1" class="d-flex justify-content-start gap-4">
                <img src="../img/logo diskon.webp" alt="" style="width: 70px; height: 70px">
                <div>
                    <p class="fw-bold mb-1">Spesial offer discount 5%</p>
                    <!-- <p>Untuk setiap pembelian pertama yang telah terdaftar sebagai member di akun Lunarea Furniture</p> -->
                    <p>Hanya untuk member Lunarea di setiap pembelian pertama</p>
                </div>
            </div>
            <div style="flex: 1" class="d-flex justify-content-start gap-4">
                <img src="../img/logo location.webp" alt="" style="width: 70px; height: 60px">
                <div>
                    <p class="fw-bold mb-1">Pengiriman ke seluruh Indonesia</p>
                    <!-- <p>Menggunakan jasa ekspedisi terpercaya untuk mengantarkan produk sampai ke alamat tujuan</p> -->
                    <p>Dengan jasa ekspedisi terpercaya yang mengantarkan ke alamat tujuan</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const containeKategoriScrollElm = document.querySelector('.container-kategori-scroll');
const btnGeserKategoriElm = document.querySelectorAll('.btn-geser-kategori');

btnGeserKategoriElm.forEach(btn => {
    btn.addEventListener('click', () => {
        const direction = btn.id === 'kiri' ? -1 : 1;
        const scrollAmount = containeKategoriScrollElm.clientWidth * direction;
        containeKategoriScrollElm.scrollBy({
            left: scrollAmount,
            behavior: "smooth"
        });
    })
})
</script>
<?= $this->endSection(); ?>