<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten artikel">
    <div class="container">
        <div class="container-nav-kategori-artikel mb-2">
            <div class="d-flex gap-5">
                <a class="nav-kategori-artikel" href="/article">SEMUA</a>
                <a class="nav-kategori-artikel" href="/article/category/lemari-dewasa">LEMARI DEWASA</a>
                <a class="nav-kategori-artikel" href="/article/category/lemari-anak">LEMARI ANAK</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-rias">MEJA RIAS</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-belajar">MEJA BELAJAR</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-tv">MEJA TV</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-tulis">MEJA TULIS</a>
                <a class="nav-kategori-artikel" href="/article/category/meja-komputer">MEJA KOMPUTER</a>
                <a class="nav-kategori-artikel" href="/article/category/rak-sepatu">RAK SEPATU</a>
                <a class="nav-kategori-artikel" href="/article/category/rak-besi">RAK BESI</a>
                <a class="nav-kategori-artikel" href="/article/category/rak-serbaguna">RAK SERBAGUNA</a>
                <a class="nav-kategori-artikel" href="/article/category/kursi">KURSI</a>
            </div>
        </div>
        <div class="mb-4">
            <div class="p-5" style="width: 40%; height: 50svh; position: relative; margin-bottom: -50svh; display: flex; flex-direction: column; justify-content: end;">
                <h1 class="text-light mb-1" style="font-size: 50px; line-height: 52px">Welcome to Jasmine's Article</h1>
                <p class="text-light mb-3">Informasi yang selalu kami perbarui untuk mengajak Anda mengenal lebih dekat terkait furniture</p>
                <div class="d-flex gap-2">
                    <a href="/all" class="btn btn-primary1">Pergi ke Toko</a>
                </div>
            </div>
            <img class="d-block rounded" src="https://images.unsplash.com/photo-1613575831056-0acd5da8f085?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="" style="width: 100%; height: 50svh; object-fit: cover;">
        </div>
        <div class="mb-4">
            <h5 class="jdl-section mb-3">Artikel Baru</h5>
            <?php if (count($artikel) > 6) { ?>
                <div class="d-flex gap-4 container-card-artikel">
                    <div class="flex-grow-1">
                        <div class="card-artikel-besar">
                            <img class="rounded" src="https://images.unsplash.com/photo-1600494603989-9650cf6ddd3d?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
                            <p class="m-0 judul">Mau Kamarmu Jadi Terlihat Aesthetic? Baca Artikel Ini Agar Tahu Caranya!</p>
                            <div class="flex-grow-1">
                                <p class="m-0 isi">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae assumenda voluptatibus sed maiores! Architecto, tempore!</p>
                            </div>
                            <p class="m-0 fw-bold" style="font-size: smaller;">Novita Meilina</p>
                            <p class="m-0" style="font-size: smaller; color: #888;">24 Mar 2024</p>
                        </div>
                    </div>
                    <div class="d-flex flex-grow-1 flex-column gap-4">
                        <div class="card-artikel-kecil">
                            <div class="img">
                                <img class="rounded" src="https://images.unsplash.com/photo-1600494603989-9650cf6ddd3d?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul">Mau Kamarmu Jadi Terlihat Aesthetic? Baca Artikel Ini Agar Tahu Caranya!</p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae assumenda voluptatibus sed maiores! Architecto, tempore!</p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;">Novita Meilina</p>
                                <p class="m-0" style="font-size: smaller; color: #888;">24 Mar 2024</p>
                            </div>
                        </div>
                        <div class="card-artikel-kecil">
                            <div class="img">
                                <img class="rounded" src="https://images.unsplash.com/photo-1600494603989-9650cf6ddd3d?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul">Mau Kamarmu Jadi Terlihat Aesthetic? Baca Artikel Ini Agar Tahu Caranya!</p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae assumenda voluptatibus sed maiores! Architecto, tempore!</p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;">Novita Meilina</p>
                                <p class="m-0" style="font-size: smaller; color: #888;">24 Mar 2024</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-grow-1 flex-column gap-4">
                        <div class="card-artikel-kecil">
                            <div class="img">
                                <img class="rounded" src="https://images.unsplash.com/photo-1600494603989-9650cf6ddd3d?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul">Mau Kamarmu Jadi Terlihat Aesthetic? Baca Artikel Ini Agar Tahu Caranya!</p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae assumenda voluptatibus sed maiores! Architecto, tempore!</p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;">Novita Meilina</p>
                                <p class="m-0" style="font-size: smaller; color: #888;">24 Mar 2024</p>
                            </div>
                        </div>
                        <div class="card-artikel-kecil">
                            <div class="img">
                                <img class="rounded" src="https://images.unsplash.com/photo-1600494603989-9650cf6ddd3d?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul">Mau Kamarmu Jadi Terlihat Aesthetic? Baca Artikel Ini Agar Tahu Caranya!</p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae assumenda voluptatibus sed maiores! Architecto, tempore!</p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;">Novita Meilina</p>
                                <p class="m-0" style="font-size: smaller; color: #888;">24 Mar 2024</p>
                            </div>
                        </div>
                        <div class="card-artikel-kecil">
                            <div class="img">
                                <img class="rounded" src="https://images.unsplash.com/photo-1600494603989-9650cf6ddd3d?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="">
                            </div>
                            <div class="flex-grow-1 d-flex flex-column">
                                <p class="m-0 judul">Mau Kamarmu Jadi Terlihat Aesthetic? Baca Artikel Ini Agar Tahu Caranya!</p>
                                <div class="flex-grow-1">
                                    <p class="m-0 isi">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae assumenda voluptatibus sed maiores! Architecto, tempore!</p>
                                </div>
                                <p class="m-0 fw-bold" style="font-size: smaller;">Novita Meilina</p>
                                <p class="m-0" style="font-size: smaller; color: #888;">24 Mar 2024</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php foreach ($artikel as $ind_a => $a) {
            if (fmod($ind_a, 3) == 0) { ?>
                <div class="d-flex gap-4 container-card-artikel">
                    <?php for ($i = $ind_a; $i < $ind_a + 3; $i++) {
                        if (isset($artikel[$i])) { ?>
                            <div class="flex-grow-1">
                                <div class="card-artikel-besar" onclick="pergiKeArtikel(`<?= str_replace(' ', '-', $artikel[$i]['judul']); ?>`)">
                                    <img class="rounded" src="<?= $artikel[$i]['header']; ?>" alt="">
                                    <p class="m-0 judul"><?= $artikel[$i]['judul']; ?></p>
                                    <div class="flex-grow-1">
                                        <p class="m-0 isi"><?= $artikel[$i]['isi'][0]['teks']; ?></p>
                                    </div>
                                    <p class="m-0 fw-bold" style="font-size: smaller;"><?= $artikel[$i]['penulis']; ?></p>
                                    <p class="m-0" style="font-size: smaller; color: #888;"><?= $artikel[$i]['waktu']; ?></p>
                                </div>
                            </div>
                    <?php }
                    } ?>
                </div>
        <?php }
        } ?>
    </div>
</div>
<script>
    function pergiKeArtikel(judulArtikel) {
        console.log(judulArtikel)
        window.location.href = '/article/' + judulArtikel
    }
</script>
<?= $this->endSection(); ?>