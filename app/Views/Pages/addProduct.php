<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1 class="mb-3">Tambah Produk</h1>
        <form method="post" action="/addproduct" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="d-flex gap-5">
                <div>
                    <table class="table-input">
                        <tbody>
                            <tr>
                                <td>Nama</td>
                                <td><div class="baris"><input type="text" class="form-control" name="nama" required></div></td>
                            </tr>
                            <tr>
                                <td>Harga</td>
                                <td><div class="baris"><input type="number" class="form-control" name="harga" required></div></td>
                            </tr>
                            <tr>
                                <td>Diskon</td>
                                <td><div class="baris">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="diskon" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div></td>
                            </tr>
                            <tr>
                                <td>Stok</td>
                                <td><div class="baris"><input type="number" class="form-control" name="stok" required></div></td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td><div class="baris"><input type="text" class="form-control" name="kategori" required></div></td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td><div class="baris"><textarea type="text" class="form-control" name="deskripsi" required></textarea></div></td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-danger" type="submit">Simpan</button>
                </div>
                <div style="flex: 1;">
                    <h5 class="jdl-section">Gambar Produk</h5>
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputGroupFile01">Upload</label>
                        <input type="file" class="form-control" id="addProduct_InputGambar" name="gambar" required>
                    </div>
                    <div class="add-gambar">
                        <img src="img/nopic.jpg" id="addProduct_PreviewGambar">
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