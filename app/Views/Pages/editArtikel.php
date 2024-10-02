<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    #container-isi>div:nth-child(odd) {
        background-color: var(--hijaumuda2);
    }
</style>
<div class="konten">
    <form method="post" action="/editarticle/<?= $artikel['id']; ?>" enctype="multipart/form-data">
        <div class="container">
            <h1 class="mb-3">Tambah Artikel</h1>
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
                                <div class="baris"><input type="text" class="form-control" name="kategori" placeholder="pisahkan dengan koma" required value="<?= $artikel['kategori']; ?>">
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
        </div>
        <div class="container mt-3">
            <h5>Isi Artikel</h5>
        </div>
        <div id="container-isi" class="d-flex flex-column gap-2">
            <?php foreach ($isi as $ind_i => $i) { ?>
                <div class="py-3">
                    <div class="container">
                        <?php switch ($i['tag']) {
                            case 'p': ?>
                                <input type="text" name="tag<?= $ind_i + 1 ?>" value="p">
                                <textarea class="w-100 mt-1" placeholder="teks" name="teks<?= $ind_i + 1 ?>"><?= $i['teks']; ?></textarea>
                                <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>" class="mb-1">
                                <div class="d-flex gap-1">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                    <button type="button" onclick="geserAtas('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_less</i></button>
                                    <button type="button" onclick="geserBawah('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_more</i></button>
                                </div>
                            <?php break;
                            case 'h2': ?>
                                <input type="text" name="tag<?= $ind_i + 1 ?>" value="h2">
                                <textarea class="w-100 mt-1" placeholder="teks" name="teks<?= $ind_i + 1 ?>"><?= $i['teks']; ?></textarea>
                                <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                <div class="d-flex gap-1">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                    <button type="button" onclick="geserAtas('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_less</i></button>
                                    <button type="button" onclick="geserBawah('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_more</i></button>
                                </div>
                            <?php break;
                            case 'h4': ?>
                                <input type="text" name="tag<?= $ind_i + 1 ?>" value="h2">
                                <textarea class="w-100 mt-1" placeholder="teks" name="teks<?= $ind_i + 1 ?>"><?= $i['teks']; ?></textarea>
                                <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                <div class="d-flex gap-1">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                    <button type="button" onclick="geserAtas('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_less</i></button>
                                    <button type="button" onclick="geserBawah('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_more</i></button>
                                </div>
                            <?php break;
                            case 'a': ?>
                                <input type="text" name="tag<?= $ind_i + 1 ?>" value="a">
                                <input type="text" name="link<?= $ind_i + 1 ?>" placeholder="link" value="<?= $i['link']; ?>">
                                <input type="text" name="teks<?= $ind_i + 1 ?>" placeholder="teks" value="<?= $i['teks']; ?>">
                                <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                <div class="d-flex gap-1">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                    <button type="button" onclick="geserAtas('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_less</i></button>
                                    <button type="button" onclick="geserBawah('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_more</i></button>
                                </div>
                            <?php break;
                            case 'img': ?>
                                <input type="text" name="tag<?= $ind_i + 1 ?>" value="img">
                                <input type="file" name="file<?= $ind_i + 1 ?>">
                                <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                <div class="d-flex gap-1">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                    <button type="button" onclick="geserAtas('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_less</i></button>
                                    <button type="button" onclick="geserBawah('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_more</i></button>
                                </div>
                            <?php break;
                            case 'space': ?>
                                <input type="text" name="tag<?= $ind_i + 1 ?>" value="space">
                                <div class="d-flex gap-1">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                    <button type="button" onclick="geserAtas('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_less</i></button>
                                    <button type="button" onclick="geserBawah('<?= $ind_i + 1 ?>')"><i class="material-icons m-0">expand_more</i></button>
                                </div>
                        <?php break;
                            default:
                                # code...
                                break;
                        } ?>
                    </div>
                </div>
            <?php } ?>
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
                </select>
                <button type="button" onclick="tambahIsi()">Tambahkan</button>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <button class="btn btn-primary1" type="submit">Simpan</button>
            </div>
            <input type="text" class="d-none" name="arrCounter" value="<?= $arrCounter; ?>">
            <!-- <input type="text" class="d-none" name="checkerImg"> -->
        </div>
    </form>
</div>
<script>
    const tagSelectElm = document.getElementById("select-tag");
    const arrCounterElm = document.querySelector('input[name="arrCounter"]');
    const containerIsiElm = document.getElementById("container-isi");
    let counterIsi = <?= $counterIsi; ?>;
    let arrCounterIsi = JSON.parse('<?= $arrCounterIsi; ?>');
    // const checkerImgElm = document.querySelector('input[name="checkerImg"]');
    // let arrCheckerImg = []

    function tambahIsi() {
        if (tagSelectElm.value != '') {
            counterIsi++;
            let initItemIsi = '';
            if (tagSelectElm.value == 'h2' || tagSelectElm.value == 'h4' || tagSelectElm.value == 'p') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><textarea class="w-100 mt-1" placeholder="teks" name="teks' + counterIsi + '"></textarea><input type="text" name="style' + counterIsi + '" placeholder="style"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            } else if (tagSelectElm.value == 'a') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><input type="text" name="link' + counterIsi + '" placeholder="link"><input type="text" name="teks' + counterIsi + '" placeholder="teks"><input type="text" name="style' + counterIsi + '" placeholder="style" value="d-inline link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            } else if (tagSelectElm.value == 'img') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><input type="file" name="file' + counterIsi + '" required><input type="text" name="style' + counterIsi + '" placeholder="style"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            } else if (tagSelectElm.value == 'space') {
                initItemIsi = '<div class="py-2"><div class="container"><input type="text" name="tag' + counterIsi + '" value="space"><div class="d-flex gap-1"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button><button type="button" onclick="geserAtas(' + counterIsi + ')"><i class="material-icons m-0">expand_less</i></button><button type="button" onclick="geserBawah(' + counterIsi + ')"><i class="material-icons m-0">expand_more</i></button></div></div></div>'
            }
            const initItemIsiElm = document.createRange().createContextualFragment(initItemIsi)
            containerIsiElm.appendChild(initItemIsiElm)
            arrCounterIsi.push(counterIsi);
            arrCounterElm.value = arrCounterIsi.join(",");
        }
    }

    function hapusIsi(urutan) {
        // console.log(arrCounterIsi, urutan)
        const indexnya = arrCounterIsi.indexOf(Number(urutan))
        // if (urutan <= <?= $counterIsi; ?> && containerIsiElm.children[indexnya].children[0].children[0].value == 'img') {
        //     arrCheckerImg.push(urutan);
        //     checkerImgElm.value = arrCheckerImg.join(",");
        // }

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