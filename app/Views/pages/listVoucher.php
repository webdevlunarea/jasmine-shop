<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    .item-list-voucher {
        background-color: var(--hijaumuda);
        padding: 1em 2em;
        border-radius: 1em;
    }

    .loading {
        animation: putarputar 2s linear infinite;
    }

    @keyframes putarputar {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
</style>
<div id="modalnya" class="d-none justify-content-center align-items-center" style="z-index: 11; position:fixed; height: 100svh; width: 100vw; top: 0; left: 0; background-color: rgba(0, 0, 0, 0.5);">
    <div style="background-color: white; max-height: 90svh; overflow:scroll;" class="p-4 rounded-2">
        <h4 class="m-0">Email Customer</h4>
        <p class="text-secondary mb-2" style="font-size: small;">Berikut list email customer yang dapat mengklaim</p>
        <form action="/voucher/addmember" method="post">
            <input type="text" name="counter" class="d-none">
            <input type="text" name="idvoucher" class="d-none">
            <div id="container-email" class="mb-2 d-flex flex-column gap-1">
                <!-- <div class="d-flex gap-1">
                    <input name="email1" value="" placeholder="email" type="text" class="form-control" required>
                    <input name="code1" placeholder="xxxx-xxxx-xxxx" type="text" class="form-control">
                </div> -->
            </div>
            <button onclick="addEmail()" type="button" class="btn"><i class="material-icons">add</i></button>
            <div class="d-flex gap-1 justify-content-end">
                <button onclick="closeEmail()" type="button" class="btn btn-outline-dark">Batal</button>
                <button type="submit" class="btn btn-primary1">Simpan</button>
            </div>
        </form>
    </div>
</div>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>List Voucher</h1>
            <a href="/addvoucher" class="btn btn-primary1">Tambah Voucher</a>
        </div>
        <?php if ($msg) { ?>
            <div class="alert alert-primary" role="alert">
                <?= $msg; ?>
            </div>
        <?php } ?>
        <div class="d-flex flex-column gap-1">
            <?php foreach ($voucher as $ind_v => $v) { ?>
                <div class="item-list-voucher">
                    <div class="baris-ke-kolom">
                        <div style="flex: 1;">
                            <p class="m-0"><?= ucfirst($v['jenis']); ?></p>
                            <h5 class="m-0" style="color: var(--hijau);"><?= $v['nama']; ?></h5>
                            <p class="text-secondary m-0">Durasi : <?= $v['durasi'] ? $v['durasi'] : 'Tak hingga'; ?></p>
                            <?php if ($v['jadwal']) { ?>
                                <p class="m-0">Dijadwalkan : <?= $v['jadwal']; ?></p>
                            <?php } ?>
                        </div>
                        <div class="d-flex flex-column align-items-end justify-content-between">
                            <div class="d-flex gap-1">
                                <h5 class="m-0" style="color: var(--hijau);"><?= $v['nominal']; ?></h5>
                                <p class="m-0" style="font-size: small; color: var(--hijau);"><?= $v['satuan']; ?></p>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <div class="bg-light border border-dark rounded-5 p-1 d-flex justify-content-<?= $v['active'] ? 'end' : 'start' ?>" style="width: 60px; height: 20px; cursor:pointer;" onclick="triggerToast('Voucher <?= $v['nama']; ?> akan di<?= $v['active'] ? 'non aktifkan' : 'aktifkan'; ?>?', '/activevoucher/<?= $v['id']; ?>')">
                                    <div class="bg-<?= $v['active'] ? 'success' : 'danger' ?> rounded-2" style="width: 30px; height: 90%"></div>
                                </div>
                                <?php if ($v['active']) { ?>
                                    <div onclick="openEmail('<?= $v['id']; ?>', <?= $ind_v; ?>)" style="cursor: pointer;" class="d-flex justify-content-center align-items-center"><i class="material-icons">people</i></div>
                                    <div onclick="triggerToast('Broadcast ke customer?','/actionbroadcastvoucher/<?= $v['id']; ?>')" style="cursor: pointer;" class="d-flex justify-content-center align-items-center"><i class="material-icons">contact_mail</i></div>
                                    <div onclick="triggerToast('Hapus voucher <?= $v['nama']; ?>?','/deletevoucher/<?= $v['id']; ?>')" style="cursor: pointer;" class="d-flex justify-content-center align-items-center"><i class="material-icons">delete_forever</i></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($broadcast == $v['id']) { ?>
                        <div>
                            <p class="fw-bold m-0">Proses Broadcasting</p>
                            <?php foreach ($emailBroadcast as $e) { ?>
                                <div class="d-flex gap-1">
                                    <i class="material-icons loading">data_usage</i>
                                    <p class="m-0"><?= $e['email_user']; ?></p>
                                </div>
                            <?php } ?>
                            <p id="status-proses-broadcast" class="d-none m-0" style="color: var(--hijau);">Proses broadcast telah selesai</p>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>
    const modalnyaElm = document.getElementById('modalnya');
    const voucherJson = JSON.parse('<?= $voucherJson; ?>');
    console.log(voucherJson)
    const containerEmailElm = document.getElementById('container-email');
    const counterCodeInputElm = document.querySelector('input[name="counter"]');
    const idVoucherInputElm = document.querySelector('input[name="idvoucher"]');
    let jmlCodeCur = 0;

    function closeEmail() {
        containerEmailElm.innerHTML = '';
        modalnyaElm.classList.remove('d-flex')
        modalnyaElm.classList.add('d-none')
    }

    function openEmail(idVoucher, index) {
        idVoucherInputElm.value = idVoucher;
        const codeObj = voucherJson[index].code;
        jmlCodeCur = codeObj.length;
        counterCodeInputElm.value = jmlCodeCur
        codeObj.forEach((c, ind_c) => {
            const inputBaru = `<div class="d-flex gap-1">
            <input name="email${ind_c}" value="${c.email_user}" placeholder="email" type="text" class="form-control" required>
            <input name="code${ind_c}" value="${c.code ? c.code : ''}" placeholder="xxxx-xxxx-xxxx" type="text" class="form-control">
            </div>`;
            const initElm = document.createRange().createContextualFragment(inputBaru)
            containerEmailElm.appendChild(initElm)
        });
        modalnyaElm.classList.add('d-flex')
        modalnyaElm.classList.remove('d-none')
    }

    function addEmail() {
        const inputBaru = `<div class="d-flex gap-1">
        <input name="email${jmlCodeCur}" placeholder="email" type="text" class="form-control" required>
        <input name="code${jmlCodeCur}" placeholder="xxxx-xxxx-xxxx" type="text" class="form-control">
        </div>`;
        const initElm = document.createRange().createContextualFragment(inputBaru)
        containerEmailElm.appendChild(initElm)
        jmlCodeCur++;
        counterCodeInputElm.value = jmlCodeCur
    }
</script>
<?php if ($broadcast) { ?>
    <script>
        const statusProsesBroadcastElm = document.getElementById('status-proses-broadcast');
        const loadingElm = document.querySelectorAll('.loading');
        const emailBroadcast = JSON.parse('<?= $emailBroadcastJson; ?>');
        const idVoucherBroadcast = <?= $broadcast; ?>;
        console.log(emailBroadcast)
        console.log(idVoucherBroadcast)
        async function broadcastSkuy() {
            for (let i = 0; i < emailBroadcast.length; i++) {
                const email = emailBroadcast[i];
                const response = await fetch('/actionbroadcastvoucheremail', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        idVoucher: idVoucherBroadcast
                    }),
                })
                if (response.status == 200) {
                    loadingElm[i].classList.remove('loading')
                    loadingElm[i].innerHTML = 'done'
                }
                const responseJson = await response.json();
            }
            statusProsesBroadcastElm.classList.remove('d-none');
        }
        broadcastSkuy();
    </script>
<?php } ?>
<?= $this->endSection(); ?>