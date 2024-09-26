<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1 class="mb-3">Tambah Artikel</h1>
        <form method="post" action="/editarticle/<?= $artikel['id']; ?>" enctype="multipart/form-data">
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
            <div class="mt-3">
                <h5>Isi Artikel</h5>
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
                    <?php foreach ($isi as $ind_i => $i) { ?>
                        <div class="py-2">
                            <?php switch ($i['tag']) {
                                case 'p': ?>
                                    <input type="text" name="tag<?= $ind_i + 1 ?>" value="p">
                                    <textarea class="w-100 mt-1" placeholder="teks" name="teks<?= $ind_i + 1 ?>"><?= $i['teks']; ?></textarea>
                                    <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                <?php break;
                                case 'h2': ?>
                                    <input type="text" name="tag<?= $ind_i + 1 ?>" value="h2">
                                    <textarea class="w-100 mt-1" placeholder="teks" name="teks<?= $ind_i + 1 ?>"><?= $i['teks']; ?></textarea>
                                    <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                <?php break;
                                case 'h4': ?>
                                    <input type="text" name="tag<?= $ind_i + 1 ?>" value="h2">
                                    <textarea class="w-100 mt-1" placeholder="teks" name="teks<?= $ind_i + 1 ?>"><?= $i['teks']; ?></textarea>
                                    <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                <?php break;
                                case 'a': ?>
                                    <input type="text" name="tag<?= $ind_i + 1 ?>" value="a">
                                    <input type="text" name="link<?= $ind_i + 1 ?>" placeholder="link" value="<?= $i['link']; ?>">
                                    <input type="text" name="teks<?= $ind_i + 1 ?>" placeholder="teks" value="<?= $i['teks']; ?>">
                                    <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                <?php break;
                                case 'img': ?>
                                    <input type="text" name="tag<?= $ind_i + 1 ?>" value="img">
                                    <input type="file" name="file<?= $ind_i + 1 ?>">
                                    <input type="text" name="style<?= $ind_i + 1 ?>" placeholder="style" value="<?= $i['style']; ?>">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                                <?php break;
                                case 'space': ?>
                                    <input type="text" name="tag<?= $ind_i + 1 ?>" value="space">
                                    <button type="button" onclick="hapusIsi('<?= $ind_i + 1 ?>')">hapus</button>
                            <?php break;
                                default:
                                    # code...
                                    break;
                            } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="d-flex gap-2 border-top py-3">
                    <select id="select-tag">
                        <option value="" selected>-- pilih tag --</option>
                        <option value="h2">h2</option>
                        <option value="h4">h3</option>
                        <option value="p">p</option>
                        <!-- <option value="img">img</option> -->
                        <option value="a">a</option>
                        <option value="space">space</option>
                    </select>
                    <button type="button" onclick="tambahIsi()">Tambahkan</button>
                </div>
            </div>
            <div class="d-flex justify-content-center mt-3">
                <button class="btn btn-primary1" type="submit">Simpan</button>
            </div>
            <input type="text" style="display: none;" name="arrCounter" value="<?= $arrCounter; ?>">
        </form>
    </div>
</div>
<script>
    const tagSelectElm = document.getElementById("select-tag");
    const containerIsiElm = document.getElementById("container-isi");
    const arrCounterElm = document.querySelector('input[name="arrCounter"]');
    let counterIsi = <?= $counterIsi; ?>;
    let arrCounterIsi = JSON.parse('<?= $arrCounterIsi; ?>');

    function tambahIsi() {
        if (tagSelectElm.value != '') {
            counterIsi++;
            let initItemIsi = '';
            if (tagSelectElm.value == 'h2' || tagSelectElm.value == 'h4' || tagSelectElm.value == 'p') {
                initItemIsi = '<div class="py-2"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><textarea class="w-100 mt-1" placeholder="teks" name="teks' + counterIsi + '"></textarea><input type="text" name="style' + counterIsi + '" placeholder="style"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button></div>'
            } else if (tagSelectElm.value == 'a') {
                initItemIsi = '<div class="py-2"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><input type="text" name="link' + counterIsi + '" placeholder="link"><input type="text" name="teks' + counterIsi + '" placeholder="teks"><input type="text" name="style' + counterIsi + '" placeholder="style" value="d-inline link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button></div>'
            } else if (tagSelectElm.value == 'img') {
                initItemIsi = '<div class="py-2"><input type="text" name="tag' + counterIsi + '" value="' + tagSelectElm.value + '"><input type="file" name="file' + counterIsi + '"><input type="text" name="style' + counterIsi + '" placeholder="style"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button></div>'
            } else if (tagSelectElm.value == 'space') {
                initItemIsi = '<div class="py-2"><input type="text" name="tag' + counterIsi + '" value="space"><button type="button" onclick="hapusIsi(' + counterIsi + ')">hapus</button></div>'
            }
            containerIsiElm.innerHTML += initItemIsi;
            arrCounterIsi.push(counterIsi);
            arrCounterElm.value = arrCounterIsi.join(",");
        }
    }

    function hapusIsi(urutan) {
        console.log(arrCounterIsi, urutan)
        const indexnya = arrCounterIsi.indexOf(Number(urutan))
        console.log(containerIsiElm.children);
        console.log(indexnya)
        containerIsiElm.removeChild(containerIsiElm.children[indexnya]);
        arrCounterIsi.splice(indexnya, 1);
        arrCounterElm.value = arrCounterIsi.join(",");
    }
</script>
<?= $this->endSection(); ?>