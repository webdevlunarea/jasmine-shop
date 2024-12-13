<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    #container-isi>div:nth-child(odd) {
        background-color: var(--hijaumuda2);
    }
</style>
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
        </div>
        <div class="container mt-3">
            <h5>Isi Artikel</h5>
        </div>
        <div id="container-isi" class="d-flex flex-column gap-2">
            <!-- <div class="py-2">
                <input type="text" name="tag1" value="p">
                <textarea class="w-100 mt-1" placeholder="teks" name="teks1">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Sint odit animi, nam placeat hic voluptatem dolores, autem blanditiis aperiam, minus exercitationem magnam similique. Blanditiis, culpa! Nobis, exercitationem corporis. Esse exercitationem perspiciatis eligendi? Molestiae debitis nulla beatae dolores minima quaerat? Est rerum sunt labore possimus doloribus repellat doloremque quos fugit minus!</textarea>
                <input type="text" name="style1" placeholder="style" value="d-flex flex-column">
                <button type="button" onclick="hapusIsi('1')">hapus</button>
            </div>
            <div class="py-2">
                <input type="text" name="tag2" value="a">
                <input type="text" name="link2" placeholder="link" value="https://jasminefurniture.store">
                <input type="text" name="teks2" placeholder="teks" value="Jasmine Furniture">
                <input type="text" name="style2" placeholder="style" value="d-flex flex-column">
                <button type="button" onclick="hapusIsi('2')">hapus</button>
            </div>
            <div class="py-2">
                <input type="text" name="tag3" value="img">
                <input type="file" name="file3">
                <input type="text" name="style3" placeholder="style" value="d-flex flex-column">
                <button type="button" onclick="hapusIsi('3')">hapus</button>
            </div>
            <div class="py-2">
                <input type="text" name="tag4" value="space">
                <button type="button" onclick="hapusIsi('3')">hapus</button>
            </div> -->
        </div>
        <hr>
        <div class="container">
            <div class="d-flex gap-2 border-top py-3">
                <select id="select-tag">
                    <option value="" selected>-- pilih tag --</option>
                    <option value="h2">h2</option>
                    <option value="h4">h3</option>
                    <option value="p">p</option>
                    <option value="img">img</option>
                    <option value="a">a</option>
                    <option value="space">space</option>
                    <option value="div">div</option>
                </select>
                <button type="button" onclick="tambahIsi()">Tambahkan</button>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <button class="btn btn-primary1" type="submit">Simpan</button>
            </div>
            <input type="text" class="d-none" name="arrCounter">
        </div>
    </form>
</div>
<script>
    const tagSelectElm = document.getElementById("select-tag");
    const arrCounterElm = document.querySelector('input[name="arrCounter"]');
    const containerIsiElm = document.getElementById("container-isi");
    let counterIsi = 0;
    let arrCounterIsi = [];

    function tambahIsi() {
        if (tagSelectElm.value != '') {
            counterIsi++;
            let initItemIsi = '';
            if (tagSelectElm.value == 'h2' || tagSelectElm.value == 'h4' || tagSelectElm.value == 'p') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><textarea class="w-100 mt-1" placeholder="teks" name="teks' + counterIsi + '"></textarea><input type="text" name="style' + counterIsi + '" placeholder="style"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            } else if (tagSelectElm.value == 'a') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><input type="text" name="link' + counterIsi + '" placeholder="link"><input type="text" name="teks' + counterIsi + '" placeholder="teks"><input type="text" name="style' + counterIsi + '" placeholder="style" value="d-inline link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            } else if (tagSelectElm.value == 'img') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><input type="file" name="file' + counterIsi + '"><input type="text" name="style' + counterIsi + '" placeholder="style"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            } else if (tagSelectElm.value == 'space') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="space"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            } else if (tagSelectElm.value == 'div') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="space"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            }
            // const initItemIsiElm = new DOMParser().parseFromString(initItemIsi, "text/xml");
            // containerIsiElm.innerHTML += initItemIsi;
            const initItemIsiElm = document.createRange().createContextualFragment(initItemIsi)
            containerIsiElm.appendChild(initItemIsiElm)
            arrCounterIsi.push(counterIsi);
            arrCounterElm.value = arrCounterIsi.join(",");
        }
    }

    function hapusIsi(urutan) {
        // console.log(arrCounterIsi, urutan)
        const indexnya = arrCounterIsi.indexOf(Number(urutan))
        // console.log(containerIsiElm.children);
        // console.log(indexnya)
        containerIsiElm.removeChild(containerIsiElm.children[indexnya]);
        arrCounterIsi.splice(indexnya, 1);
        arrCounterElm.value = arrCounterIsi.join(",");
    }

    function geserAtas(urutan) {
        const indexnya = arrCounterIsi.indexOf(Number(urutan))
        if (indexnya > 0) {
            const elmTujuan = containerIsiElm.children[indexnya - 1]
            containerIsiElm.removeChild(containerIsiElm.children[indexnya - 1])
            const arrCounterIsiTujuan = arrCounterIsi[indexnya - 1]
            arrCounterIsi.splice(indexnya - 1, 1);
            // console.log(elmTujuan)
            // console.log(arrCounterIsiTujuan)
            containerIsiElm.insertBefore(elmTujuan, containerIsiElm.children[indexnya]);
            arrCounterIsi.splice(indexnya, 0, arrCounterIsiTujuan);
            arrCounterElm.value = arrCounterIsi.join(",");
            // console.log(arrCounterIsi)
        }
    }

    function geserBawah(urutan) {
        const indexnya = arrCounterIsi.indexOf(Number(urutan))
        if (indexnya < arrCounterIsi.length - 1) {
            const elmTujuan = containerIsiElm.children[indexnya + 1]
            containerIsiElm.removeChild(containerIsiElm.children[indexnya + 1])
            const arrCounterIsiTujuan = arrCounterIsi[indexnya + 1]
            arrCounterIsi.splice(indexnya + 1, 1);
            containerIsiElm.insertBefore(elmTujuan, containerIsiElm.children[indexnya]);
            arrCounterIsi.splice(indexnya, 0, arrCounterIsiTujuan);
            arrCounterElm.value = arrCounterIsi.join(",");
        }
    }
</script>
<?= $this->endSection(); ?>