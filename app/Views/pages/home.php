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
    <h1 class="visually-hidden">Lunarea Furniture</h1>
    <div class="container home-hero-container">
        <?= view('partials/home_carousel', ['banner' => $banner ?? []], ['saveData' => false]); ?>
    </div>
    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="jdl-section mb-1">Kategori</h5>
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
                <div class="kategori-wrap has-dropdown">
                    <a class="kategori" href="/all">
                        <img src="<?= esc($categoryImages['all'] ?? '/img/logo icon.png'); ?>" alt="Semua Kategori" width="50px">
                        <p>Semua Kategori</p>
                    </a>
                    <button type="button" class="kategori-arrow" aria-label="Buka semua kategori" aria-expanded="false">
                        <i class="material-icons" aria-hidden="true">keyboard_arrow_down</i>
                    </button>
                    <div class="kategori-dropdown-menu">
                        <a href="/all"><img src="<?= esc($categoryImages['all'] ?? '/img/logo icon.png'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Semua Produk</span></a>
                        <a href="/all/lemari-dewasa"><img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari Dewasa</span></a>
                        <a href="/all/lemari-anak"><img src="<?= esc($categoryImages['lemari-anak'] ?? '/img/logokategori/Lemari_Anak.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari Anak</span></a>
                        <a href="/all/meja-rias"><img src="<?= esc($categoryImages['meja-rias'] ?? '/img/logokategori/Meja_Rias.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Rias</span></a>
                        <a href="/all/meja-belajar"><img src="<?= esc($categoryImages['meja-belajar'] ?? '/img/logokategori/Meja_Belajar.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Belajar</span></a>
                        <a href="/all/meja-tv"><img src="<?= esc($categoryImages['meja-tv'] ?? '/img/logokategori/Meja_TV.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja TV</span></a>
                        <a href="/all/meja-tulis"><img src="<?= esc($categoryImages['meja-tulis'] ?? '/img/logokategori/Meja_Tulis.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Tulis</span></a>
                        <a href="/all/meja-komputer"><img src="<?= esc($categoryImages['meja-komputer'] ?? '/img/logokategori/Meja_Komputer.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Komputer</span></a>
                        <a href="/all/rak-sepatu"><img src="<?= esc($categoryImages['rak-sepatu'] ?? '/img/logokategori/Rak_Sepatu.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Rak Sepatu</span></a>
                        <a href="/all/rak-besi"><img src="<?= esc($categoryImages['rak-besi'] ?? '/img/logokategori/Rak_Besi.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Rak Besi</span></a>
                        <a href="/all/rak-serbaguna"><img src="<?= esc($categoryImages['rak-serbaguna'] ?? '/img/logokategori/Rak_Serbaguna.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Rak Serbaguna</span></a>
                        <a href="/all/kursi"><img src="<?= esc($categoryImages['kursi'] ?? '/img/logokategori/Kursi.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Kursi</span></a>
                    </div>
                </div>
                <div class="kategori-wrap has-dropdown">
                    <a class="kategori" href="/all/lemari-dewasa">
                        <img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="lemari dewasa" width="50px">
                        <p>Lemari Dewasa</p>
                    </a>
                    <button type="button" class="kategori-arrow" aria-label="Buka sub kategori lemari dewasa" aria-expanded="false">
                        <i class="material-icons" aria-hidden="true">keyboard_arrow_down</i>
                    </button>
                    <div class="kategori-dropdown-menu">
                        <a href="/all/lemari-dewasa"><img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari 1 Pintu</span></a>
                        <a href="/all/lemari-dewasa"><img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari 2 Pintu</span></a>
                        <a href="/all/lemari-dewasa"><img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari 3 Pintu</span></a>
                        <a href="/all/lemari-dewasa"><img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari 4 Pintu</span></a>
                        <a href="/all/lemari-dewasa"><img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari 5 Pintu</span></a>
                        <a href="/all/lemari-dewasa"><img src="<?= esc($categoryImages['lemari-dewasa'] ?? '/img/logokategori/Lemari_Dewasa.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Lemari 6 Pintu</span></a>
                    </div>
                </div>
                <a class="kategori" href="/all/lemari-anak">
                    <img src="<?= esc($categoryImages['lemari-anak'] ?? '/img/logokategori/Lemari_Anak.webp'); ?>" alt="lemari anak" width="50px">
                    <p>Lemari Anak</p>
                </a>
                <a class="kategori" href="/all/meja-rias">
                    <img src="<?= esc($categoryImages['meja-rias'] ?? '/img/logokategori/Meja_Rias.webp'); ?>" alt="meja rias" width="50px">
                    <p>Meja Rias</p>
                </a>
                <div class="kategori-wrap has-dropdown">
                    <a class="kategori" href="/all/meja-belajar">
                        <img src="<?= esc($categoryImages['meja-belajar'] ?? '/img/logokategori/Meja_Belajar.webp'); ?>" alt="meja belajar" width="50px">
                        <p>Meja Belajar</p>
                    </a>
                    <button type="button" class="kategori-arrow" aria-label="Buka sub kategori meja" aria-expanded="false">
                        <i class="material-icons" aria-hidden="true">keyboard_arrow_down</i>
                    </button>
                    <div class="kategori-dropdown-menu">
                        <a href="/all/meja-belajar"><img src="<?= esc($categoryImages['meja-belajar'] ?? '/img/logokategori/Meja_Belajar.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Belajar</span></a>
                        <a href="/all/meja-tv"><img src="<?= esc($categoryImages['meja-tv'] ?? '/img/logokategori/Meja_TV.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja TV</span></a>
                        <a href="/all/meja-tulis"><img src="<?= esc($categoryImages['meja-tulis'] ?? '/img/logokategori/Meja_Tulis.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Tulis</span></a>
                        <a href="/all/meja-komputer"><img src="<?= esc($categoryImages['meja-komputer'] ?? '/img/logokategori/Meja_Komputer.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Komputer</span></a>
                        <a href="/all/meja-rias"><img src="<?= esc($categoryImages['meja-rias'] ?? '/img/logokategori/Meja_Rias.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Meja Rias</span></a>
                    </div>
                </div>
                <a class="kategori" href="/all/meja-tv">
                    <img src="<?= esc($categoryImages['meja-tv'] ?? '/img/logokategori/Meja_TV.webp'); ?>" alt="meja tv" width="50px">
                    <p>Meja TV</p>
                </a>
                <a class="kategori" href="/all/meja-tulis">
                    <img src="<?= esc($categoryImages['meja-tulis'] ?? '/img/logokategori/Meja_Tulis.webp'); ?>" alt="meja tulis" width="50px">
                    <p>Meja Tulis</p>
                </a>
                <a class="kategori" href="/all/meja-komputer">
                    <img src="<?= esc($categoryImages['meja-komputer'] ?? '/img/logokategori/Meja_Komputer.webp'); ?>" alt="meja komputer" width="50px">
                    <p>Meja Komputer</p>
                </a>
                <div class="kategori-wrap has-dropdown">
                    <a class="kategori" href="/all/rak-serbaguna">
                        <img src="<?= esc($categoryImages['rak-serbaguna'] ?? '/img/logokategori/Rak_Serbaguna.webp'); ?>" alt="rak serbaguna" width="50px">
                        <p>Rak Serbaguna</p>
                    </a>
                    <button type="button" class="kategori-arrow" aria-label="Buka sub kategori rak" aria-expanded="false">
                        <i class="material-icons" aria-hidden="true">keyboard_arrow_down</i>
                    </button>
                    <div class="kategori-dropdown-menu">
                        <a href="/all/rak-sepatu"><img src="<?= esc($categoryImages['rak-sepatu'] ?? '/img/logokategori/Rak_Sepatu.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Rak Sepatu</span></a>
                        <a href="/all/rak-besi"><img src="<?= esc($categoryImages['rak-besi'] ?? '/img/logokategori/Rak_Besi.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Rak Besi</span></a>
                        <a href="/all/rak-serbaguna"><img src="<?= esc($categoryImages['rak-serbaguna'] ?? '/img/logokategori/Rak_Serbaguna.webp'); ?>" alt="" style="width:24px;height:24px;object-fit:contain;background:transparent;flex:0 0 24px;"><span>Rak Serbaguna</span></a>
                    </div>
                </div>
                <a class="kategori" href="/all/rak-sepatu">
                    <img src="<?= esc($categoryImages['rak-sepatu'] ?? '/img/logokategori/Rak_Sepatu.webp'); ?>" alt="rak sepatu" width="50px">
                    <p>Rak Sepatu</p>
                </a>
                <a class="kategori" href="/all/rak-besi">
                    <img src="<?= esc($categoryImages['rak-besi'] ?? '/img/logokategori/Rak_Besi.webp'); ?>" alt="rak besi" width="50px">
                    <p>Rak Besi</p>
                </a>
                <a class="kategori" href="/all/kursi">
                    <img src="<?= esc($categoryImages['kursi'] ?? '/img/logokategori/Kursi.webp'); ?>" alt="kursi" width="50px">
                    <p>Kursi</p>
                </a>
            </div>
        </div>
    </div>
    <?= view('partials/home_flash_sale', ['flashSaleProducts' => $flashSaleProducts ?? [], 'flashSaleSettings' => $flashSaleSettings ?? [], 'flashSaleEnd' => $flashSaleEnd ?? null], ['saveData' => false]); ?>
    <?= view('partials/home_product_section', ['sectionId' => 'home-popular-products', 'sectionTitle' => 'Produk Terpopuler', 'sectionProducts' => $produkPopuler ?? []], ['saveData' => false]); ?>
    <?= view('partials/home_product_section', ['sectionId' => 'home-latest-products', 'sectionTitle' => 'Produk Terbaru', 'sectionProducts' => $produkTerbaru ?? []], ['saveData' => false]); ?>
    <?= $this->include('partials/home_benefits'); ?>
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

const kategoriDropdownWraps = document.querySelectorAll('.kategori-wrap.has-dropdown');
window.addEventListener('buyer-navigation-open', () => closeKategoriDropdowns());

function syncKategoriDropdownState() {
    document.body.classList.toggle('kategori-dropdown-open', document.querySelector('.kategori-wrap.show') !== null);
}

function closeKategoriDropdowns(exceptWrap = null) {
    kategoriDropdownWraps.forEach(wrap => {
        if (wrap !== exceptWrap) {
            wrap.classList.remove('show');
            wrap.querySelector('.kategori-arrow')?.setAttribute('aria-expanded', 'false');
        }
    });
    syncKategoriDropdownState();
}

function positionKategoriDropdown(wrap) {
    const menu = wrap.querySelector('.kategori-dropdown-menu');
    const rect = wrap.getBoundingClientRect();
    const gap = 6;
    const viewport = window.visualViewport;
    const viewportTop = viewport?.offsetTop || 0;
    const viewportBottom = viewportTop + (viewport?.height || window.innerHeight);
    const mobileHeader = document.querySelector('.buyer-mobile-header');
    const headerBottom = mobileHeader && getComputedStyle(mobileHeader).display !== 'none'
        ? mobileHeader.getBoundingClientRect().bottom : viewportTop;
    const bottomNav = document.querySelector('.mobile-bottom-nav');
    const bottomLimit = bottomNav && getComputedStyle(bottomNav).display !== 'none'
        ? Math.min(viewportBottom, bottomNav.getBoundingClientRect().top) - 12 : viewportBottom - 12;
    const topLimit = Math.max(viewportTop + 12, headerBottom + gap);
    const menuWidth = Math.min(menu.offsetWidth || 235, window.innerWidth - 24);
    const left = Math.max(12, Math.min(rect.left, window.innerWidth - menuWidth - 12));
    const naturalHeight = Math.min(menu.scrollHeight, 320);
    const spaceBelow = bottomLimit - rect.bottom - gap;
    const spaceAbove = rect.top - gap - topLimit;
    const placeAbove = spaceBelow < Math.min(naturalHeight, 160) && spaceAbove > spaceBelow;
    const maxHeight = Math.max(0, Math.min(naturalHeight, placeAbove ? spaceAbove : spaceBelow));
    const top = placeAbove ? rect.top - gap - maxHeight : Math.max(topLimit, rect.bottom + gap);

    menu.style.left = `${left}px`;
    menu.style.top = `${top}px`;
    menu.style.maxHeight = `${maxHeight}px`;
}

kategoriDropdownWraps.forEach(wrap => {
    const toggle = wrap.querySelector('.kategori-arrow');
    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        const willOpen = !wrap.classList.contains('show');
        closeKategoriDropdowns(wrap);
        wrap.classList.toggle('show', willOpen);
        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

        if (willOpen) {
            containeKategoriScrollElm.dataset.lockScrollLeft = containeKategoriScrollElm.scrollLeft;
            positionKategoriDropdown(wrap);
        }
        syncKategoriDropdownState();
    });
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('.kategori-wrap') && !event.target.closest('.kategori-dropdown-menu')) {
        closeKategoriDropdowns();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeKategoriDropdowns();
    }
});

window.addEventListener('resize', () => {
    document.querySelectorAll('.kategori-wrap.show').forEach(positionKategoriDropdown);
});

containeKategoriScrollElm?.addEventListener('wheel', (event) => {
    if (document.body.classList.contains('kategori-dropdown-open')) {
        event.preventDefault();
    }
}, { passive: false });

containeKategoriScrollElm?.addEventListener('touchmove', (event) => {
    if (document.body.classList.contains('kategori-dropdown-open')) {
        event.preventDefault();
    }
}, { passive: false });


function preventPageScrollWhenKategoriOpen(event) {
    if (!document.body.classList.contains('kategori-dropdown-open')) return;
    if (event.target.closest('.kategori-dropdown-menu')) return;
    event.preventDefault();
}

document.addEventListener('wheel', preventPageScrollWhenKategoriOpen, { passive: false, capture: true });
document.addEventListener('touchmove', preventPageScrollWhenKategoriOpen, { passive: false, capture: true });

containeKategoriScrollElm?.addEventListener('scroll', () => {
    if (document.body.classList.contains('kategori-dropdown-open')) {
        containeKategoriScrollElm.scrollLeft = containeKategoriScrollElm.dataset.lockScrollLeft || containeKategoriScrollElm.scrollLeft;
    }
});

document.querySelectorAll('[data-flash-countdown]').forEach((timer) => {
    const endMs = Number(timer.dataset.end || 0) * 1000;
    const hoursEl = timer.querySelector('[data-unit="hours"]');
    const minutesEl = timer.querySelector('[data-unit="minutes"]');
    const secondsEl = timer.querySelector('[data-unit="seconds"]');
    const pad = (value) => String(Math.max(0, value)).padStart(2, '0');

    function renderFlashCountdown() {
        const diff = Math.max(0, endMs - Date.now());
        const totalSeconds = Math.floor(diff / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (hoursEl) hoursEl.textContent = pad(hours);
        if (minutesEl) minutesEl.textContent = pad(minutes);
        if (secondsEl) secondsEl.textContent = pad(seconds);
    }

    renderFlashCountdown();
    setInterval(renderFlashCountdown, 1000);
});

document.querySelectorAll('.flash-sale-products-wrap').forEach((wrap) => {
    const scroller = wrap.querySelector('[data-flash-products]');
    const prev = wrap.querySelector('[data-flash-prev]');
    const next = wrap.querySelector('[data-flash-next]');

    if (!scroller) return;

    const move = (direction) => {
        const amount = Math.max(260, Math.floor(scroller.clientWidth * 0.8));
        scroller.scrollBy({
            left: direction * amount,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
        });
    };

    const updateFlashNavigation = () => {
        if (prev) prev.disabled = scroller.scrollLeft <= 1;
        if (next) next.disabled = scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth - 1;
    };
    scroller.addEventListener('scroll', updateFlashNavigation, { passive: true });
    window.addEventListener('resize', updateFlashNavigation);
    updateFlashNavigation();

    prev?.addEventListener('click', () => move(-1));
    next?.addEventListener('click', () => move(1));
});
</script>
<?= $this->endSection(); ?>

