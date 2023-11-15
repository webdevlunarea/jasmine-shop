<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1 class="mb-3">Edit Produk</h1>
        <form>
            <div class="d-flex gap-5">
                <div>
                    <table class="table-input">
                        <tbody>
                            <tr>
                                <td>Nama</td>
                                <td><div class="baris"><input type="text" class="form-control" value="<?= $produk['nama']; ?>"></div></td>
                            </tr>
                            <tr>
                                <td>Harga</td>
                                <td><div class="baris"><input type="number" class="form-control" value="<?= $produk['harga']; ?>"></div></td>
                            </tr>
                            <tr>
                                <td>Diskon</td>
                                <td><div class="baris">
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" value="<?= $produk['diskon']; ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div></td>
                            </tr>
                            <tr>
                                <td>Stok</td>
                                <td><div class="baris"><input type="number" class="form-control" value="<?= $produk['stok']; ?>"></div></td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td><div class="baris"><input type="text" class="form-control" value="<?= $produk['kategori']; ?>"></div></td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td><div class="baris"><textarea type="text" class="form-control"><?= $produk['deskripsi']; ?></textarea></div></td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-danger" type="submit">Simpan</button>
                </div>
                <div style="flex: 1;">
                    <h5 class="jdl-section">Gambar Produk</h5>
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputGroupFile01">Upload</label>
                        <input type="file" class="form-control" id="addProduct_InputGambar">
                    </div>
                    <div class="add-gambar">
                        <img src="data:image/jpeg;base64,<?= base64_encode($produk['gambar']); ?>" alt="" id="addProduct_PreviewGambar">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    const addProduct_inputGambar = document.getElementById("addProduct_InputGambar")
    const addProduct_PreviewGambar = document.getElementById("addProduct_PreviewGambar")
    addProduct_inputGambar.addEventListener("change", (e) => {
        const file = addProduct_inputGambar.files[0];
        const blobFile = new Blob([file], { type: file.type });
        var blobUrl = URL.createObjectURL(blobFile);
        addProduct_PreviewGambar.src = blobUrl;
    });
</script>
<?= $this->endSection(); ?>