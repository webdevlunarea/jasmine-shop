<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1 class="mb-3">Edit Produk</h1>
        <form method="post" action="/editproduct/<?= $produk['id']; ?>" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="baris-ke-kolom">
                <div>
                    <table class="table-input">
                        <tbody>
                            <tr>
                                <td>Nama</td>
                                <td><div class="baris"><input type="text" class="form-control" value="<?= $produk['nama']; ?>" name="nama" required></div></td>
                            </tr>
                            <tr>
                                <td>Harga</td>
                                <td><div class="baris"><input type="number" class="form-control" value="<?= $produk['harga']; ?>" name="harga" required></div></td>
                            </tr>
                            <tr>
                                <td>Diskon</td>
                                <td><div class="baris">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" value="<?= $produk['diskon']; ?>" name="diskon" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div></td>
                            </tr>
                            <tr>
                                <td>Stok</td>
                                <td><div class="baris"><input type="number" class="form-control" value="<?= $produk['stok']; ?>" name="stok" required></div></td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td><div class="baris"><input type="text" class="form-control" value="<?= $produk['kategori']; ?>" name="kategori" required></div></td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td><div class="baris"><textarea type="text" class="form-control" name="deskripsi" required><?= $produk['deskripsi']; ?></textarea></div></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="show-ke-hide mt-2">
                        <button class="btn btn-danger" type="submit">Simpan</button>
                        <a class="btn btn-outline-dark" href="/listproduct">Batal</a>
                    </div>
                </div>
                <div style="flex: 1;">
                    <h5 class="jdl-section">Gambar Produk</h5>
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputGroupFile01">Upload</label>
                        <input type="file" class="form-control" id="editProduct_InputGambar" name="gambar">
                    </div>
                    <div class="add-gambar">
                        <img src="data:image/jpeg;base64,<?= base64_encode($produk['gambar']); ?>" alt="" id="editProduct_PreviewGambar">
                    </div>
                </div>
            </div>
            <div class="justify-content-center hide-ke-show-flex gap-2 mt-3">
                <button class="btn btn-danger" type="submit">Simpan</button>
                <a class="btn btn-outline-dark" href="/listproduct">Batal</a>
            </div>
        </form>
    </div>
</div>
<script>
    const editProduct_inputGambar = document.getElementById("editProduct_InputGambar")
    const editProduct_PreviewGambar = document.getElementById("editProduct_PreviewGambar")
    editProduct_inputGambar.addEventListener("change", (e) => {
        const file = editProduct_inputGambar.files[0];
        const blobFile = new Blob([file], { type: file.type });
        var blobUrl = URL.createObjectURL(blobFile);
        editProduct_PreviewGambar.src = blobUrl;
    });
</script>
<?= $this->endSection(); ?>