<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    #container-isi>div:nth-child(odd) {
        background-color: var(--hijaumuda2);
    }
</style>
<script src="https://cdn.tiny.cloud/1/<?= $tinymce; ?>/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<div class="konten">
    <form method="post" action="/addarticle" enctype="multipart/form-data">
        <div class="container">
            <h1 class="mb-3">Tambah Artikel</h1>
            <?= csrf_field(); ?>
            <div>
                <table class="table-input w-100">
                    <tbody>
                        <tr>
                            <td>Judul</td>
                            <td>
                                <div class="baris"><input type="text" class="form-control" name="judul" required>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Penulis</td>
                            <td>
                                <div class="baris"><input type="text" class="form-control" name="penulis" required>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>
                                <div class="baris d-flex gap-1">
                                    <select name="kategori-barang" class="form-select">
                                        <option value="lemari dewasa" selected>Lemari Dewasa</option>
                                        <option value="lemari anak">Lemari Anak</option>
                                        <option value="lemari hias">Lemari Hias</option>
                                        <option value="meja rias">Meja Rias</option>
                                        <option value="meja belajar">Meja Belajar</option>
                                        <option value="meja tv">Meja TV</option>
                                        <option value="meja tulis">Meja Tulis</option>
                                        <option value="meja komputer">Meja Komputer</option>
                                        <option value="rak sepatu">Rak Sepatu</option>
                                        <option value="rak besi">Rak Besi</option>
                                        <option value="rak serbaguna">Rak Serbaguna</option>
                                        <option value="kursi">Kursi</option>
                                    </select>
                                    <select name="kategori" class="form-select">
                                        <option value="edukasi" selected>Edukasi</option>
                                        <option value="tips & trik">Tips & Trik</option>
                                        <option value="rekomendasi">Rekomendasi</option>
                                        <option value="plus minus">Plus & Minus</option>
                                    </select>
                                </div>
                            </td>
                        <tr>
                            <td>Tanggal Ubah</td>
                            <td>
                                <div class="baris"><input type="datetime-local" class="form-control" name="waktu" required>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Gambar Header</td>
                            <td>
                                <div class="baris"><input type="file" class="form-control" name="header" required>
                                </div>
                            </td>
                        </tr>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h5 class="mt-2">Isi Artikel</h5>
            <textarea name="isi" id="content"></textarea>
            <button class="btn btn-primary1 mt-2 w-100" type="submit">Simpan</button>
        </div>
    </form>
</div>
<script>
    tinymce.init({
        selector: "#content",
        plugins: [
            "image",
            "link",
            "lists",
            "media",
            "table",
        ],
        toolbar: "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat",
    });
</script>
<?= $this->endSection(); ?>