<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1 class="mb-3">Edit Produk</h1>
        <form method="post" action="/editproduct/<?= $produk['id']; ?>" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <div class="baris-ke-kolom">
                <div class="limapuluh-ke-seratus">
                    <table class="table-input w-100">
                        <tbody>
                            <tr>
                                <td>Nama</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['nama']; ?>" name="nama" required onchange="isiPencarian(event)"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Harga</td>
                                <td>
                                    <div class="baris"><input type="number" class="form-control" value="<?= $produk['harga']; ?>" name="harga" required></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Diskon</td>
                                <td>
                                    <div class="baris">
                                        <div class="input-group mb-3">
                                            <input type="number" class="form-control" value="<?= $produk['diskon']; ?>" name="diskon" step="any" required onchange="isiPencarian(event)">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Dimensi (cm)</td>
                                <td>
                                    <div class="baris"><input value="<?= $produk['dimensi']; ?>" type="text" class="form-control" name="dimensi" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Berat (kg)</td>
                                <td>
                                    <div class="baris"><input value="<?= $produk['berat']; ?>" type="number" class="form-control" name="berat" step="any" required>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Stok</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['stok']; ?>" name="stok" required></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['kategori']; ?>" name="kategori" required onchange="isiPencarian(event)"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Sub Kategori</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['subkategori']; ?>" name="subkategori" required onchange="isiPencarian(event)"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Varian</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $varian; ?>" name="varian" required onchange="isiPencarian(event)"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Jumlah Varian</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['jml_varian']; ?>" name="jml_varian" required></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Link Shopee</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['shopee']; ?>" name="shopee"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Link Tokopedia</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['tokped']; ?>" name="tokped"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Link Tiktok Shop</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['tiktok']; ?>" name="tiktok"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Link Youtube</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['youtube']; ?>" name="youtube"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Deskripsi</td>
                                <td>
                                    <div class="baris"><textarea type="text" class="form-control" name="deskripsi" required><?= $produk['deskripsi']; ?></textarea></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Deskripsi Nonhtml</td>
                                <td>
                                    <div class="baris"><textarea type="text" class="form-control" name="deskripsi_nonhtml" required><?= $produk['deskripsi_nonhtml']; ?></textarea></div>
                                </td>
                            </tr>
                            <tr>
                                <td>Pencarian</td>
                                <td>
                                    <div class="baris"><input type="text" class="form-control" value="<?= $produk['pencarian']; ?>" name="pencarian" required>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="show-ke-hide mt-2">
                        <button class="btn btn-primary1" type="submit">Simpan</button>
                        <a class="btn btn-outline-dark" href="/listproduct">Batal</a>
                    </div>
                </div>
                <div class="limapuluh-ke-seratus">
                    <h5 class="jdl-section">Gambar Produk</h5>
                    <div class="add-gambar mb-1">
                        <p style="position: absolute; transform: translate(15px, 10px); color: rgba(0, 0, 0, 0.5)">
                            Preview</p>
                        <img src="/img/nopic.jpg" id="addProduct_PreviewUtama">
                    </div>
                    <div id="foto-varian" class="d-flex gap-2" style="overflow-y: auto; width:100%">
                    </div>
                </div>
            </div>
            <div class="justify-content-center hide-ke-show-flex gap-2 mt-3">
                <button class="btn btn-primary1" type="submit">Simpan</button>
                <a class="btn btn-outline-dark" href="/listproduct">Batal</a>
            </div>
        </form>
    </div>
</div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    const elmFotoVarian = document.getElementById('foto-varian');
    const elmKategori = document.querySelector('input[name="kategori"]');
    const elmSubkategori = document.querySelector('input[name="subkategori"]');
    const elmPencarian = document.querySelector('input[name="pencarian"]');
    const elmVarian = document.querySelector('input[name="varian"]');
    const elmJmlvarian = document.querySelector('input[name="jml_varian"]');
    const ambilVarian = "<?= count(json_decode($produk['varian'], true)); ?>"
    const ambilJmlvarian = "<?= $produk['jml_varian'] ?>"
    let varian = Number(ambilVarian);
    let jmlVarian = Number(ambilJmlvarian);
    let hasilVarian = jmlVarian + varian - 1
    console.log(varian, jmlVarian, hasilVarian, ambilVarian, ambilJmlvarian)
    inputElement(hasilVarian);

    function isiPencarian(e) {
        console.log(e);
        if (e.srcElement.value != '') {
            if (e.srcElement.name == 'nama') {
                elmPencarian.value += `${e.srcElement.value} `
            } else if (e.srcElement.name == 'kategori') {
                elmPencarian.value += `${e.srcElement.value} elegan ${e.srcElement.value} simpel ${e.srcElement.value} minimalis ${e.srcElement.value} estetik ${e.srcElement.value} modern `
            } else if (e.srcElement.name == 'subkategori') {
                const subkategorinya = e.srcElement.value.replace(/-/g, ' ')
                elmPencarian.value += `${subkategorinya} elegan ${subkategorinya} simpel ${subkategorinya} minimalis ${subkategorinya} estetik ${subkategorinya} modern `
            } else if (e.srcElement.name == 'varian') {
                const arrVar = e.srcElement.value.split(",");
                const subkategorinya = elmSubkategori.value.replace(/-/g, ' ')
                arrVar.forEach((va) => {
                    elmPencarian.value += `${elmKategori.value} ${va} ${subkategorinya} ${va} `
                })
            } else if (e.srcElement.name == 'diskon') {
                const subkategorinya = elmSubkategori.value.replace(/-/g, ' ')
                if (Number(e.srcElement.value) > 0) elmPencarian.value += `${elmKategori.value} promo ${elmKategori.value} diskon ${subkategorinya} promo ${subkategorinya} diskon `
            }
        }
    }

    elmVarian.addEventListener("change", (e) => {
        const varianArray = e.target.value.split(",");
        console.log(varianArray);
        varian = varianArray.length;
        console.log(varian);
        hasilVarian = jmlVarian + varian - 1;
        console.log(hasilVarian);
        inputElement(hasilVarian);
    });

    elmJmlvarian.addEventListener("change", (e) => {
        jmlVarian = Number(e.target.value);
        hasilVarian = jmlVarian + varian - 1;
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
            // cardinput.setAttribute('required', '');
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
    // const addProduct_inputGambar = document.querySelectorAll(".input-gambar");
    // const addProduct_previewGambar = document.querySelectorAll(".addProduct_Preview");
    // const addProduct_input = document.querySelectorAll(".addProduct_Input");
    // const addProduct_previewUtama = document.getElementById("addProduct_PreviewUtama");
    // const addProduct_form = document.querySelector("form");

    // addProduct_inputGambar.forEach((item, index) => {
    //     item.addEventListener("change", () => {
    //         addProduct_inputGambar.forEach((element, ind) => {
    //             if (ind < 3) element.required = true;
    //         });
    //         const file = addProduct_inputGambar[index].files[0];
    //         const blobFile = new Blob([file], {
    //             type: file.type
    //         });
    //         var blobUrl = URL.createObjectURL(blobFile);
    //         addProduct_previewGambar[index].src = blobUrl;
    //         addProduct_previewUtama.src = blobUrl;
    //         addProduct_previewGambar[index].style.display = "block";
    //         addProduct_input[index].style.display = 'none';
    //     })
    // })
</script>
<?= $this->endSection(); ?>