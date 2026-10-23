<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
    .item-list-voucher {
        background-color: var(--hijaumuda);
        padding: 1em 2em;
        border-radius: 1em;
    }

    .voucher-broadcast-controls { display: flex; align-items: center; gap: .35rem; }
    .voucher-broadcast-controls select, .voucher-broadcast-controls input { min-height: 34px; font-size: .8rem; }
    .voucher-broadcast-controls select { max-width: 190px; }
    .voucher-broadcast-controls input { width: 82px; }
    .voucher-flash-message {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 0 0 14px;
        padding: 11px 14px;
        border: 1px solid var(--admin-border, var(--soft2, #e2ded5));
        border-radius: 8px;
        background: var(--admin-primary-soft, var(--hijaumuda, #f5f3ee));
        color: var(--admin-text, var(--warna-text, #282e29));
        font-size: 12px;
        line-height: 1.65;
    }
    .voucher-flash-message .material-icons { flex: 0 0 auto; margin-top: 1px; color: var(--admin-primary, var(--hijau, #365b46)); font-size: 18px; }
    @media (max-width: 767.98px) {
        .voucher-broadcast-controls { flex-wrap: wrap; justify-content: flex-end; }
        .voucher-broadcast-controls select { max-width: 160px; }
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
            <?= csrf_field(); ?>
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
<div class="konten admin-module-page admin-module-list-voucher">
    <div class="container">
        <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Voucher', 'adminHeadingGroup'=>'Promosi & konten', 'adminHeadingDescription'=>'Kelola potongan, periode berlaku, dan penerima voucher.', 'adminHeadingActions'=>[['label'=>'Tambah voucher', 'href'=>'/addvoucher', 'icon'=>'add', 'primary'=>true, 'external'=>false]]], ['saveData'=>false]); ?>

        <?php if ($msg) { ?>
            <div class="voucher-flash-message" role="status" aria-live="polite">
                <i class="material-icons" aria-hidden="true">info</i>
                <span><?= esc($msg); ?></span>
            </div>
        <?php } ?>
        <p class="small text-secondary mb-3">Broadcast tidak perlu mengisi email satu per satu. Pilih pelanggan aktif atau penerima voucher, lalu tentukan batas penerima (maksimal 500). Email dikirim bertahap, maksimal 25 dalam 24 jam; pelanggan yang berhenti berlangganan dikecualikan.</p>
        <div class="d-flex flex-column gap-1">
            <?php foreach ($voucher as $ind_v => $v) { ?>
                <div class="item-list-voucher">
                    <div class="baris-ke-kolom">
                        <div style="flex: 1;">
                            <p class="m-0"><?= ucfirst($v['jenis']); ?></p>
                            <h5 class="m-0" style="color: var(--hijau);"><?= $v['nama']; ?></h5>
                            <p class="text-secondary m-0">Durasi : <?= $v['durasi'] ? $v['durasi'] : 'Tak hingga'; ?></p>
                            <?php if (!empty($v['max_potongan'])) { ?>
                                <p class="text-secondary m-0">Maks. potongan : Rp <?= number_format((int)$v['max_potongan'], 0, ",", "."); ?></p>
                            <?php } ?>
                            <?php if (!empty($v['tidak_gabung_voucher_baru'])) { ?>
                                <p class="text-secondary m-0">Tidak digabung voucher pengguna baru</p>
                            <?php } ?>
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
                                    <div data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="List customer" onclick="openEmail('<?= $v['id']; ?>', <?= $ind_v; ?>)" style="cursor: pointer;" class="d-flex justify-content-center align-items-center"><i class="material-icons">people</i></div>
                                    <?php if (!isset($voucherEmailCampaigns[(string)$v['id']])) { ?>
                                        <form class="voucher-broadcast-controls" method="post" action="/actionbroadcastvoucher/<?= esc($v['id'], 'attr'); ?>" data-confirm-submit data-confirm-message="Jadwalkan email voucher sesuai pilihan penerima? Pelanggan yang berhenti berlangganan dikecualikan. Maksimal 25 email dikirim dalam 24 jam.">
                                            <?= csrf_field(); ?>
                                            <label class="visually-hidden" for="broadcast-audience-<?= esc($v['id'], 'attr'); ?>">Penerima email</label>
                                            <select class="form-select form-select-sm" id="broadcast-audience-<?= esc($v['id'], 'attr'); ?>" name="audience" aria-label="Pilih penerima email">
                                                <option value="active_customers">Pelanggan aktif</option>
                                                <option value="voucher_recipients">Penerima voucher ini</option>
                                            </select>
                                            <label class="visually-hidden" for="broadcast-limit-<?= esc($v['id'], 'attr'); ?>">Batas jumlah email</label>
                                            <input class="form-control form-control-sm" id="broadcast-limit-<?= esc($v['id'], 'attr'); ?>" type="number" name="recipient_limit" min="1" max="500" value="100" aria-label="Batas penerima, maksimal 500">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Jadwalkan broadcast email"><i class="material-icons" aria-hidden="true">contact_mail</i><span class="visually-hidden">Jadwalkan email</span></button>
                                        </form>
                                    <?php } ?>
                                <?php } ?>
                                <?php if (isset($voucherEmailCampaigns[(string)$v['id']])) { $campaign = $voucherEmailCampaigns[(string)$v['id']]; ?>
                                    <span class="small text-secondary" role="status" title="Email terkirim <?= (int)$campaign['sent']; ?> dari <?= (int)$campaign['total']; ?>; antrean <?= (int)$campaign['pending']; ?>; gagal <?= (int)$campaign['failed']; ?>">
                                        Email <?= (int)$campaign['sent']; ?>/<?= (int)$campaign['total']; ?> · antre <?= (int)$campaign['pending']; ?> · gagal <?= (int)$campaign['failed']; ?>
                                    </span>
                                <?php } ?>
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit voucher" href="/editvoucher/<?= $v['id']; ?>" class="text-dark"><i class="material-icons">edit</i></a>
                                <div data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete voucher" onclick="triggerToast('Hapus voucher <?= $v['nama']; ?>?','/deletevoucher/<?= $v['id']; ?>')" style="cursor: pointer;" class="d-flex justify-content-center align-items-center"><i class="material-icons">delete_forever</i></div>
                            </div>
                        </div>
                    </div>
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
<?= $this->endSection(); ?>
