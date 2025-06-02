<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    #container-isi>div:nth-child(odd) {
        background-color: var(--hijaumuda2);
    }
</style>
<script src="https://cdn.tiny.cloud/1/<?= $tinymce; ?>/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<div class="konten">
    <form method="post" action="/editarticle/<?= $artikel['id']; ?>" enctype="multipart/form-data">
        <div class="container">
            <h1 class="mb-3">Edit Artikel</h1>
            <?= csrf_field(); ?>
            <div>
                <table class="table-input w-100">
                    <tbody>
                        <tr>
                            <td>Judul</td>
                            <td>
                                <div class="baris"><input type="text" class="form-control" name="judul" required value="<?= $artikel['judul']; ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Penulis</td>
                            <td>
                                <div class="baris"><input type="text" class="form-control" name="penulis" required value="<?= $artikel['penulis']; ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>
                                <div class="baris d-flex gap-1">
                                    <select name="kategori-barang" class="form-select">
                                        <option value="lemari dewasa" <?= explode(',', $artikel['kategori'])[0] == 'lemari dewasa' ? 'selected' : ''; ?>>Lemari Dewasa</option>
                                        <option value="lemari anak" <?= explode(',', $artikel['kategori'])[0] == 'lemari anak' ? 'selected' : ''; ?>>Lemari Anak</option>
                                        <option value="lemari hias" <?= explode(',', $artikel['kategori'])[0] == 'lemari hias' ? 'selected' : ''; ?>>Lemari Hias</option>
                                        <option value="meja rias" <?= explode(',', $artikel['kategori'])[0] == 'meja rias' ? 'selected' : ''; ?>>Meja Rias</option>
                                        <option value="meja belajar" <?= explode(',', $artikel['kategori'])[0] == 'meja belajar' ? 'selected' : ''; ?>>Meja Belajar</option>
                                        <option value="meja tv" <?= explode(',', $artikel['kategori'])[0] == 'meja tv' ? 'selected' : ''; ?>>Meja TV</option>
                                        <option value="meja tulis" <?= explode(',', $artikel['kategori'])[0] == 'meja tulis' ? 'selected' : ''; ?>>Meja Tulis</option>
                                        <option value="meja komputer" <?= explode(',', $artikel['kategori'])[0] == 'meja komputer' ? 'selected' : ''; ?>>Meja Komputer</option>
                                        <option value="rak sepatu" <?= explode(',', $artikel['kategori'])[0] == 'rak sepatu' ? 'selected' : ''; ?>>Rak Sepatu</option>
                                        <option value="rak besi" <?= explode(',', $artikel['kategori'])[0] == 'rak besi' ? 'selected' : ''; ?>>Rak Besi</option>
                                        <option value="rak serbaguna" <?= explode(',', $artikel['kategori'])[0] == 'rak serbaguna' ? 'selected' : ''; ?>>Rak Serbaguna</option>
                                        <option value="kursi" <?= explode(',', $artikel['kategori'])[0] == 'kursi' ? 'selected' : ''; ?>>Kursi</option>
                                    </select>
                                    <select name="kategori" class="form-select">
                                        <option value="edukasi" <?= explode(',', $artikel['kategori'])[1] == 'edukasi' ? 'selected' : ''; ?>>Edukasi</option>
                                        <option value="tips & trik" <?= explode(',', $artikel['kategori'])[1] == 'tips & trik' ? 'selected' : ''; ?>>Tips & Trik</option>
                                        <option value="rekomendasi" <?= explode(',', $artikel['kategori'])[1] == 'rekomendasi' ? 'selected' : ''; ?>>Rekomendasi</option>
                                        <option value="plus minus" <?= explode(',', $artikel['kategori'])[1] == 'plus minus' ? 'selected' : ''; ?>>Plus & Minus</option>
                                    </select>
                                </div>
                            </td>
                        <tr>
                            <td>Tanggal Ubah</td>
                            <td>
                                <div class="baris"><input type="datetime-local" class="form-control" name="waktu" required value="<?= $waktu; ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Gambar Header</td>
                            <td>
                                <div class="baris"><input type="file" class="form-control" name="header">
                                </div>
                            </td>
                        </tr>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h5 class="mt-2">Isi Artikel</h5>
            <textarea name="isi" id="content"><?= $artikel['isi']; ?></textarea>
            <button class="btn btn-primary1 mt-2 w-100" type="submit">Simpan</button>
        </div>
        <div class="container mt-3">
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