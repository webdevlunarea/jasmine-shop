<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<script src="https://cdn.tiny.cloud/1/<?= $tinyMCE; ?>/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<div class="konten">
    <div class="container">
        <form action="/actioneditvoucher/<?= $voucher['id']; ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <h3 class="">Edit Voucher</h3>
            <?php if ($msg) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= $msg; ?>
                </div>
            <?php } ?>
            <div class="mb-2">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="nama" required value="<?= $voucher['nama']; ?>">
            </div>
            <div class="d-flex mb-2 gap-1">
                <div style="flex: 1">
                    <label class="form-label">Nominal</label>
                    <input type="number" class="form-control" name="nominal" required value="<?= $voucher['nominal']; ?>">
                </div>
                <div style="flex: 1">
                    <label class="form-label">Satuan</label>
                    <select name="satuan" class="form-select" value="<?= $voucher['satuan']; ?>">
                        <option value="persen" selected>Persen</option>
                        <option value="rupiah">Rupiah</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Jenis</label>
                <select name="jenis" class="form-select" value="<?= $voucher['jenis']; ?>">
                    <option value="member" selected>Member</option>
                    <option value="cashback">Cashback</option>
                    <option value="potongan">Potongan</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Kuota Customer</label>
                <p class="text-secondary mb-1" style="font-size: small;">Jika tak terbatas isi dengan "-1"</p>
                <input type="number" class="form-control" value="<?= $voucher['kuota']; ?>" name="kuota">
            </div>
            <div class="d-flex gap-1 mb-2">
                <div style="flex: 1;">
                    <label class="form-label m-0">Durasi Voucher</label>
                    <p class="text-secondary mb-1" style="font-size: small;">Masa aktif voucher setelah di klaim</p>
                    <select name="durasi" class="form-select" value="<?= $voucher['durasi']; ?>">
                        <option value="null" selected>Tak hingga</option>
                        <option value="+3 days">3 Hari</option>
                        <option value="+1 month">1 Bulan</option>
                        <option value="+1 year">1 Tahun</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label class="form-label m-0">Durasi Poin</label>
                    <p class="text-secondary mb-1" style="font-size: small;">Masa aktif poin yang di dapat dari voucher cashback</p>
                    <select name="durasi-poin" class="form-select" value="<?= $voucher['durasi_poin']; ?>">
                        <option value="null" selected>Tak hingga</option>
                        <option value="+3 days">3 Hari</option>
                        <option value="+1 month">1 Bulan</option>
                        <option value="+1 year">1 Tahun</option>
                    </select>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Poster</label>
                <img class="d-block mb-1" src="/imgvoucher/<?= $voucher['id']; ?>" alt="" height="100px">
                <input type="file" class="form-control" name="poster">
            </div>
            <div class="mb-2">
                <label class="form-label">Keterangan</label>
                <textarea class="form-control" name="keterangan"><?= $voucher['keterangan']; ?></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Email Customer</label>
                <p class="text-secondary mb-1" style="font-size: small;">List email yang dapat mengklaim voucher</p>
                <textarea class="form-control mb-1 <?= $voucher['all_user'] ? 'd-none' : ''; ?>" name="email" placeholder="Pisahkan dengan tanda ampersand (&)"><?= array_reduce($voucher['code'], function ($prev, $cur) {
                                                                                                                                                                    return $prev . ($prev == "" ? "" : "&") . $cur["email_user"];
                                                                                                                                                                }, ""); ?></textarea>
                <div class="d-flex gap-1">
                    <input type="checkbox" onchange="handleChangeAllUser(event)" name="set-all-user-voucher" id="checkbox1" <?= $voucher['all_user'] ? 'checked' : ''; ?>>
                    <label for="checkbox1">Tambahkan akses klaim ke seluruh customer</label>
                </div>
                <div class="d-none gap-1">
                    <input type="checkbox" name="set-redeem" id="checkbox3">
                    <label for="checkbox3">Berikan code redeem</label>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label m-0">Syarat & Ketentuan</label>
                <p class="text-secondary mb-1" style="font-size: small;">Jika tidak ada biarkan kosong</p>
                <textarea class="form-control" name="syarat-ketentuan"><?= $voucher['syarat_ketentuan']; ?></textarea>
            </div>
            <hr>
            <h5 class="m-0">Penjadwalan</h5>
            <p class="text-secondary mb-1" style="font-size: small;">*kosongin jika tanpa penjadwalan</p>
            <div class="baris-ke-kolom gap-1">
                <div style="flex: 1">
                    <p class="m-0">Dari</p>
                    <input name="jadwal1" type="date" class="form-control" value="<?= $voucher['jadwal'] ? explode('@', $voucher['jadwal'])[0] : ''; ?>" min="<?= date('Y-m-d', strtotime('+7 hours')); ?>">
                </div>
                <div style="flex: 1">
                    <p class="m-0">Sampai</p>
                    <input name="jadwal2" type="date" class="form-control" value="<?= $voucher['jadwal'] ? explode('@', $voucher['jadwal'])[1] : ''; ?>" min="<?= date('Y-m-d', strtotime('+7 hours')); ?>">
                </div>
            </div>
            <hr>
            <h5 class="m-0">Broadcast Email Customer</h5>
            <p class="text-secondary mb-1" style="font-size: small;">Memberikan informasi ke customer mengenai voucher ini</p>
            <div class="d-flex gap-1 mb-2">
                <input onchange="handleChangeBroadcast(event)" <?= $voucher['isi_email'] ? 'checked' : ''; ?> type="checkbox" name="broadcast" id="broadcast-checkbox">
                <label for="broadcast-checkbox">Broadcast ke customer</label>
            </div>
            <div id="form-broadcast" class="<?= $voucher['isi_email'] ? '' : 'd-none'; ?>">
                <div class="mb-2">
                    <label class="form-label m-0">Poster Email</label>
                    <img class="d-block mb-1" src="/imgvoucher/email/<?= $voucher['id']; ?>" alt="" height="100px">
                    <input type="file" class="form-control" name="poster-email">
                </div>
                <label class="form-label">Isi Email</label>
                <div class="d-flex gap-2 mb-2">
                    <div style="flex: 1">
                        <textarea class="form-control" name="isi-email"><?= $voucher['isi_email_input']; ?></textarea>
                    </div>
                    <div style="flex: 1; box-shadow: 0 0 5px rgba(0,0,0,0.2)" class="p-2">
                        <p class="text-secondary m-0">Preview isi Email</p>
                        <hr class="my-1">
                        <ul class="m-0 text-secondary">
                            <li>Hanya boleh tag Paragraph, Header 1, div</li>
                            <li>Untuk membuat tombol, gunakan tag div</li>
                            <li>Font color yang diperbolehkan : Green dan Gray</li>
                        </ul>
                        <hr class="my-1">
                        <table>
                            <tbody id="preview-isi-email">
                                <?= $voucher['isi_email']; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr>
            <div class="mb-2">
                <p class="m-0">Lain-lain</p>
                <div class="d-flex gap-1">
                    <input type="checkbox" name="auto-claimed" id="checkbox2" <?= $voucher['auto_claimed'] ? 'checked' : ''; ?>>
                    <label for="checkbox2">Auto klaim ketika customer registrasi</label>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-dark">Simpan</button>
        </form>
    </div>
</div>
<script>
    const keteranganElm = document.querySelector('textarea[name="email"]');
    const formBroadcastElm = document.getElementById('form-broadcast')
    const previewIsiEmailElm = document.getElementById('preview-isi-email');

    function handleChangeAllUser(e) {
        const valuenya = e.target.checked;
        if (valuenya) keteranganElm.classList.add('d-none');
        else keteranganElm.classList.remove('d-none');
    }

    function handleChangeBroadcast(e) {
        const valuenya = e.target.checked;
        if (valuenya) formBroadcastElm.classList.remove('d-none');
        else formBroadcastElm.classList.add('d-none');
    }

    tinymce.init({
        selector: `textarea[name="syarat-ketentuan"]`,
        setup: function(editor) {
            editor.on('input', function() {
                console.log(editor.getContent());
            });
        },
        plugins: [
            // "anchor",
            // "autolink",
            // "charmap",
            // "codesample",
            // "emoticons",
            "image",
            "link",
            "lists",
            "media",
            // "searchreplace",
            "table",
            // "visualblocks",
            // "wordcount",
            // "checklist",
            // "mediaembed",
            // "casechange",
            // "export",
            // "formatpainter",
            // "pageembed",
            // "a11ychecker",
            // "tinymcespellchecker",
            // "permanentpen",
            // "powerpaste",
            // "advtable",
            // "advcode",
            // "editimage",
            // "advtemplate",
            // "ai",
            // "mentions",
            // "tinycomments",
            // "tableofcontents",
            // "footnotes",
            // "mergetags",
            // "autocorrect",
            // "typography",
            // "inlinecss",
            // "markdown",
            // "importword",
            // "exportword",
            // "exportpdf",
        ],
    });

    const generateIsiEmail = (str) => {
        const arrlines = str.split('\n');
        let hasil = '';
        arrlines.forEach(line => {
            if (line.includes('&nbsp;')) {
                hasil += `
                        <tr>
                            <td>
                                <span style="display: block; height: 20px"></span>
                            </td>
                        </tr>
                    `
            } else if (line.includes('</div>')) {
                const div = document.createElement('div');
                div.innerHTML = line;
                const anchor = div.querySelector('a');
                if (anchor) {
                    hasil += `
                        <tr>
                            <td>
                                <div style="padding-top: 10px">
                                    <a
                                        href="${anchor.getAttribute('href')}"
                                        style="
                                            text-decoration: none;
                                            color: white;
                                            background-color: #1db954;
                                            padding-left: 20px;
                                            padding-right: 20px;
                                            padding-top: 10px;
                                            padding-bottom: 10px;
                                            border-radius: 7px;
                                            line-height: 40px;
                                            font-weight: 700;
                                        "
                                        >${anchor.innerHTML}</a
                                    >
                                </div>
                            </td>
                        </tr>
                    `
                } else {
                    hasil += `
                        <tr>
                            <td>
                                <span style="display: block; height: 20px"></span>
                            </td>
                        </tr>
                    `
                }
            } else if (line.includes('</h1>')) {
                hasil += `
                    <tr>
                        <td>
                            <span
                                style="
                                    font-weight: 700;
                                    display: block;
                                    font-size: 20px;
                                "
                                >${line
                                .replace('<h1>', '')
                                .replace('</h1>', '')
                                .replaceAll("#2dc26b", "#1db954") // hijau
                                }
                            </span>
                        </td>
                    </tr>
                `
            } else if (line.includes('</p>')) {
                hasil += `
                        <tr>
                            <td>
                                <span>
                                    ${line
                                    .replace('<p>', '')
                                    .replace('</p>', '')
                                    .replaceAll("#2dc26b", "#1db954") // hijau
                                    }
                                </span>
                            </td>
                        </tr>
                    `
            }
        });
        previewIsiEmailElm.innerHTML = hasil;
        console.log('====== PREVIEW ========')
        console.log(hasil)
    }

    tinymce.init({
        selector: `textarea[name="isi-email"]`,
        setup: function(editor) {
            editor.on('input', function() {
                console.log('====== Editor ========')
                console.log(editor.getContent());
                generateIsiEmail(editor.getContent());
                // previewIsiEmailElm.innerHTML = editor.getContent();
            });
        },
        plugins: [
            "link",
        ],
    });
</script>
<?= $this->endSection(); ?>