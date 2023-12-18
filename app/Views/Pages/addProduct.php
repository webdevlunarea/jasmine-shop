<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1 class="mb-3">Tambah Produk</h1>
        <form method="post" action="/addproduct" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="baris-ke-kolom">
                <div>
                    <table class="table-input">
                        <tbody>
                            <tr>
                                <td>Nama</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" name="nama" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Harga</td>
                                <td>
                                    <div class="baris"><input type="number" class="form-control" name="harga" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Diskon</td>
                                <td>
                                    <div class="baris">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="diskon" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Stok</td>
                                <td>
                                    <div class="baris"><input type="number" class="form-control" name="stok" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" name="kategori" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Sub Kategori</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" name="subkategori" required></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td>
                                    <div class="baris"><textarea type="text" class="form-control" name="deskripsi" required></textarea></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-danger show-ke-hide" type="submit">Simpan</button>
                </div>
                <div style="flex: 1;">
                    <h5 class="jdl-section">Gambar Produk</h5>
                    <div class="add-gambar mb-1">
                        <p style="position: absolute; transform: translate(15px, 10px); color: rgba(0, 0, 0, 0.5)">Preview</p>
                        <img src="img/nopic.jpg" id="addProduct_PreviewUtama">
                    </div>
                    <div class="d-flex gap-2" style="overflow-y: auto;">
                        <div class="input-group-gambar">
                            <div id="addProduct_Input" class="addProduct_Input" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Wajib diisi">
                                <label class="input-gambar-label" for="addProduct_InputGambar"><i class="material-icons">add</i></label>
                                <input type="file" class="input-gambar" id="addProduct_InputGambar" name="gambar" required>
                            </div>
                            <img src="img/nopic.jpg" id="addProduct_PreviewGambar" class="addProduct_Preview">
                        </div>
                        <div class="input-group-gambar">
                            <div id="addProduct_Input1" class="addProduct_Input" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Wajib diisi">
                                <label class="input-gambar-label" for="addProduct_InputGambar1"><i class="material-icons">add</i></label>
                                <input type="file" class="input-gambar" id="addProduct_InputGambar1" name="gambar1" required>
                            </div>
                            <img src="img/nopic.jpg" id="addProduct_PreviewGambar1" class="addProduct_Preview">
                        </div>
                        <div class="input-group-gambar">
                            <div id="addProduct_Input2" class="addProduct_Input" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Wajib diisi">
                                <label class="input-gambar-label" for="addProduct_InputGambar2"><i class="material-icons">add</i></label>
                                <input type="file" class="input-gambar" id="addProduct_InputGambar2" name="gambar2" required>
                            </div>
                            <img src="img/nopic.jpg" id="addProduct_PreviewGambar2" class="addProduct_Preview">
                        </div>
                        <div class="input-group-gambar">
                            <div id="addProduct_Input3" class="addProduct_Input">
                                <label class="input-gambar-label" for="addProduct_InputGambar3"><i class="material-icons">add</i></label>
                                <input type="file" class="input-gambar" id="addProduct_InputGambar3" name="gambar3">
                            </div>
                            <img src="img/nopic.jpg" id="addProduct_PreviewGambar3" class="addProduct_Preview">
                        </div>
                        <div class="input-group-gambar">
                            <div id="addProduct_Input4" class="addProduct_Input">
                                <label class="input-gambar-label" for="addProduct_InputGambar4"><i class="material-icons">add</i></label>
                                <input type="file" class="input-gambar" id="addProduct_InputGambar4" name="gambar4">
                            </div>
                            <img src="img/nopic.jpg" id="addProduct_PreviewGambar4" class="addProduct_Preview">
                        </div>
                    </div>
                </div>
            </div>
            <div class="hide-ke-show-flex justify-content-center mt-3">
                <button class="btn btn-danger" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    const addProduct_inputGambar = document.querySelectorAll(".input-gambar");
    const addProduct_previewGambar = document.querySelectorAll(".addProduct_Preview");
    const addProduct_labelInput = document.querySelectorAll(".input-gambar-label");
    const addProduct_input = document.querySelectorAll(".addProduct_Input");
    const addProduct_previewUtama = document.getElementById("addProduct_PreviewUtama");
    const addProduct_form = document.querySelector("form");

    addProduct_inputGambar.forEach((item, index) => {
        item.addEventListener("change", () => {
            const file = addProduct_inputGambar[index].files[0];
            const blobFile = new Blob([file], {
                type: file.type
            });
            var blobUrl = URL.createObjectURL(blobFile);
            addProduct_previewGambar[index].src = blobUrl;
            addProduct_previewUtama.src = blobUrl;
            addProduct_previewGambar[index].style.display = "block";
            addProduct_input[index].style.display = 'none';
        })
    })
</script>
<?= $this->endSection(); ?>