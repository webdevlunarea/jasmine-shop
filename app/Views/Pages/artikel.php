<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten artikel">
    <div class="container">
        <img src="<?= $artikel['header']; ?>" alt="" class="header">
        <div class="p-5 mx-5" style="background-color: white; box-shadow: 5px 5px 20px rgba(0,0,0,0.1); position: relative; margin-top: -10svh">
            <div class="d-flex gap-1 mb-3">
                <?php foreach ($artikel['kategori'] as $k) { ?>
                    <h5 class="badge rounded-pill text-bg-secondary"><?= ucfirst($k); ?></h5>
                <?php } ?>
            </div>
            <h1 class="judul"><?= $artikel['judul'] ?></h1>
            <div class="d-flex justify-content-between">
                <p class="m-0 text-secondary">Ditulis oleh <?= $artikel['penulis']; ?></p>
                <p class="m-0 text-secondary">Terakhir diubah pada <?= $artikel['waktu']; ?></p>
            </div>
            <span class="garis my-3"></span>
            <?php foreach ($artikel['isi'] as $isi) {
                if ($isi['tag'] == 'h1' || $isi['tag'] == 'h2' || $isi['tag'] == 'p') {
                    echo '<' . $isi['tag'] . ' class="' . $isi['style'] . '">' . $isi['teks'] . '</' . $isi['tag'] . '>';
                } else if ($isi['tag'] == 'a') {
                    echo '<' . $isi['tag'] . ' class="' . $isi['style'] . '" href="' . $isi['link'] . '">' . $isi['teks'] . '</' . $isi['tag'] . '>';
                } else if ($isi['tag'] == 'img') {
                    echo '<' . $isi['tag'] . ' class="w-100 ' . $isi['style'] . '" src="' . $isi['src'] . '">';
                }
            ?>
            <?php } ?>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>