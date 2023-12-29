<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1 class="mb-3">Tambah Produk</h1>
        <form method="post" action="/addproduct" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="baris-ke-kolom">
                <div style="width:50%;">
                    <table class="table-input w-100">
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
                                <td>Dimensi</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" name="dimensi" required>
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
                                    <div class="baris"><input type="text" class="form-control" name="subkategori"
                                            required></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Varian</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" name="varian" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Jumlah Varian</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" name="jml_varian"
                                            required></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td>
                                    <div class="baris"><textarea type="text" class="form-control" name="deskripsi"
                                            required></textarea></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-danger show-ke-hide" type="submit">Simpan</button>
                </div>
                <div style="width:50%;">
                    <h5 class="jdl-section">Gambar Produk</h5>
                    <div class="add-gambar mb-1">
                        <p style="position: absolute; transform: translate(15px, 10px); color: rgba(0, 0, 0, 0.5)">
                            Preview</p>
                        <img src="img/nopic.jpg" id="addProduct_PreviewUtama">
                    </div>
                    <div class="d-flex gap-2" id="foto-varian" style="overflow-y: auto;">
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
const elmFotoVarian = document.getElementById('foto-varian');
const elmVarian = document.querySelector('input[name="varian"]');
const elmJmlvarian = document.querySelector('input[name="jml_varian"]');
let varian = 1
let jmlVarian = 1
let hasilVarian = 1

elmVarian.addEventListener("change", (e) => {
    const varianArray = e.target.value.split(",");
    console.log(varianArray);
    varian = varianArray.length;
    console.log(varian);
    hasilVarian = varian * jmlVarian;
    console.log(hasilVarian);
    inputElement(hasilVarian);
});

elmJmlvarian.addEventListener("change", (e) => {
    jmlVarian = Number(e.target.value);
    hasilVarian = varian * jmlVarian;
    console.log(hasilVarian);
    inputElement(hasilVarian);
});

function inputElement(hasilVarian) {
    elmFotoVarian.innerHTML = "";
    for (let i = 1; i <= hasilVarian; i++) {
        const cardVarian = document.createElement('div');
        cardVarian.classList.add('input-group-gambar');
        const cardAnkvarian = document.createElement('div');
        cardAnkvarian.classList.add('addProduct_Input');
        cardAnkvarian.setAttribute('id', 'addProduct_Input' + i);
        cardAnkvarian.setAttribute('data-bs-toggle', 'tooltip');
        cardAnkvarian.setAttribute('data-bs-placement', 'top');
        cardAnkvarian.setAttribute('data-bs-title', 'Wajib diisi');
        const cardlabel = document.createElement('label');
        cardlabel.classList.add('input-gambar-label');
        cardlabel.setAttribute('for', 'addProduct_InputGambar' + i);
        const cardIlabel = document.createElement('i');
        cardIlabel.classList.add('material-icons');
        cardIlabel.innerHTML = "add";
        const cardinput = document.createElement('input');
        cardinput.classList.add('input-gambar');
        cardinput.setAttribute('type', 'file');
        cardinput.setAttribute('id', 'addProduct_InputGambar' + i);
        cardinput.setAttribute('name', 'gambar' + i);
        cardinput.setAttribute('required', '');
        const cardImg = document.createElement('img');
        cardImg.src = "img/nopic.jpg";
        cardImg.setAttribute('id', 'addProduct_PreviewGambar' + i);
        cardImg.classList.add('addProduct_Preview');
        cardlabel.appendChild(cardIlabel);
        cardAnkvarian.appendChild(cardlabel);
        cardAnkvarian.appendChild(cardinput);
        cardVarian.appendChild(cardAnkvarian);
        cardVarian.appendChild(cardImg);
        elmFotoVarian.appendChild(cardVarian);
    }
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
}
</script>
<?= $this->endSection(); ?>