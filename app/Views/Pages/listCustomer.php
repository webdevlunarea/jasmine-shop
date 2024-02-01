<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="container-edit-resi">
    <div class="bg-white p-4 rounded" style="box-shadow: 0 0 10px rgba(0,0,0,0.4);">
        <div class="d-flex justify-content-between align-items-start">
            <h3>Edit resi</h3>
            <div class="d-flex gap-2">
                <a class="btn d-flex" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Simpan" onclick="acteditresi()"><i class="material-icons" style="font-size:large">check</i></a>
                <a class="btn d-flex" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Batal" onclick="closeeditresi()"><i class="material-icons" style="font-size:large">close</i></a>
            </div>
        </div>
        <div class="form-floating">
            <input type="text" class="form-control" id="floatingInput" placeholder="Nomor resi" name="resi">
            <label for="floatingInput">Resi</label>
        </div>
    </div>
</div>
<div class="konten">
    <div class="container">
        <h3 class="my-3">List Customer</h3>
        <div class="d-flex mb-2" style="padding-inline: 2em;">
            <div style="flex: 4;">
                <p class="mb-0 fw-bold text-black-50">Basic Info</p>
            </div>
            <div style="flex: 3;">
                <p class="mb-0 fw-bold text-center text-black-50">Status</p>
            </div>
            <div style="flex: 3;">
                <p class="mb-0 fw-bold text-center text-black-50">Kode</p>
            </div>
            <div style="flex: 3;">
                <p class="mb-0 fw-bold text-center text-black-50">Waktu</p>
            </div>
            <div style="width: 100px;">
            </div>
        </div>
        <div class="container-list-customer">
            <?php foreach ($transaksiCus as $t_ind => $t) { ?>
                <div class="list-customer" onclick="bukaList('<?= $t_ind; ?>')">
                    <div class="d-flex">
                        <div style="flex: 4;">
                            <p class="mb-0 fw-bold nama"><?= $t['nama_cus']; ?></p>
                            <p class="mb-0"><?= $t['email_cus']; ?></p>
                        </div>
                        <div style="flex: 3;">
                            <p class="mb-0 fw-bold badge rounded-pill <?php
                                                                        switch ($t['status']) {
                                                                            case 'Menunggu Pembayaran':
                                                                                echo "text-bg-primary";
                                                                                break;
                                                                            case 'Proses':
                                                                                echo "text-bg-warning";
                                                                                break;
                                                                            case 'Dikirim':
                                                                                echo "text-bg-info";
                                                                                break;
                                                                            case 'Selesai':
                                                                                echo "text-bg-success";
                                                                                break;
                                                                            default:
                                                                                echo "text-bg-dark";
                                                                                break;
                                                                        }
                                                                        ?>"><?= $t['status']; ?></p>
                        </div>
                        <div style="flex: 3;">
                            <p class="mb-0 fw-bold"><?= $t['id_midtrans']; ?></p>
                        </div>
                        <div style="flex: 3;">
                            <p class="mb-0 fw-bold"><?= json_decode($t['data_mid'], true)['transaction_time']; ?></p>
                        </div>
                        <div style="gap: 3px; width: 100px;">
                            <a class="btn btn-success d-flex <?php switch ($t['status']) {
                                                                    case 'Proses':
                                                                        echo "";
                                                                        break;
                                                                    case 'Dikirim':
                                                                        echo "";
                                                                        break;
                                                                    default:
                                                                        echo "disabled";
                                                                        break;
                                                                } ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit resi" onclick="editresi('<?= $t['id_midtrans']; ?>', '<?= $t_ind; ?>')"><i class="material-icons" style="font-size:large">mode_edit</i></a>
                            <a class="btn btn-success d-flex" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download" href="/pdf/<?= $t['id_midtrans']; ?>"><i class="material-icons" style="font-size:large">file_download</i></a>
                        </div>
                    </div>
                    <div class="list-customer-detail baris-ke-kolom justify-content-between align-items-start mt-2 d-none">
                        <div class="w-100 pd-2">
                            <p class="fw-bold mb-0">Item Pesanan</p>
                            <div class="d-flex flex-column w-100 mb-2">
                                <div class="d-flex w-100 align-items-start">
                                    <p style="flex: 2; color: var(--hijau)" class="mb-0">Nama</p>
                                    <p style="flex: 1; color: var(--hijau)" class="mb-0 text-center">Jumlah</p>
                                    <p style="flex: 1; color: var(--hijau)" class="mb-0 text-end">Harga satuan</p>
                                </div>
                                <?php
                                $totalHargaBarang = 0;
                                foreach (json_decode($t['items'], true) as $barang) {
                                    $totalHargaBarang += $barang['value'] * $barang['quantity'];
                                ?>
                                    <div class="d-flex w-100 align-items-start">
                                        <p style="flex: 2" class="mb-0"><?= $barang['name']; ?></p>
                                        <p style="flex: 1" class="mb-0 text-center"><?= $barang['quantity']; ?></p>
                                        <p style="flex: 1" class="mb-0 text-end">Rp
                                            <?= number_format($barang['value'], 0, ",", "."); ?></p>
                                    </div>
                                <?php } ?>
                                <div class="d-flex w-100 align-items-start border-top border-secondary mt-1 pt-1">
                                    <p style="flex: 3;" class="mb-0">Total</p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp <?= number_format($totalHargaBarang, 0, ",", "."); ?></p>
                                </div>
                                <div class="d-flex w-100 align-items-start">
                                    <p style="flex: 3;" class="mb-0">Biaya Ongkir dan Admin</p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp <?= number_format((json_decode($t['data_mid'], true)['gross_amount'] - $totalHargaBarang), 0, ",", "."); ?></p>
                                </div>
                                <div class="d-flex w-100 align-items-start">
                                    <p style="flex: 3;" class="mb-0">Total Keseluruhan</p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp <?= number_format(json_decode($t['data_mid'], true)['gross_amount'], 0, ",", "."); ?></p>
                                </div>
                            </div>
                            <?php if ($t['status'] == "Menunggu Pembayaran") { ?>
                                <p class="mb-0"><b><?= ucfirst(str_replace('_', ' ', json_decode($t['data_mid'], true)['payment_type'])); ?></b></p>
                                <p class="mb-0"><?= json_decode($t['data_mid'], true)['payment_type'] == "bank_transfer" ? strtoupper(json_decode($t['data_mid'], true)['va_numbers'][0]['bank']) . " " . json_decode($t['data_mid'], true)['va_numbers'][0]['va_number'] : "" ?></p>
                                <p class="mb-0"><?= json_decode($t['data_mid'], true)['payment_type'] == "echannel" ? "Biller Code: " . json_decode($t['data_mid'], true)['biller_code'] . "<br>Bill Key: " . json_decode($t['data_mid'], true)['bill_key'] : "" ?></p>
                            <?php } ?>
                            <p class="fw-bold mb-0 resi">Nomor resi : <?= $t['resi']; ?> (<?= strtoupper($t['kurir']); ?>)</p>
                        </div>
                        <div class="w-100 d-flex flex-column align-items-start pd-2" style="max-width: 400px;">
                            <p class="fw-bold mb-0">Alamat dan Nomor Hp Customer</p>
                            <p class="mb-0"><?= $t['alamat_cus']; ?></p>
                            <p class="mb-0"><?= $t['hp_cus']; ?></p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    const containerEditResiElm = document.getElementById("container-edit-resi")
    const resiInputElm = document.querySelector('input[name="resi"]')
    const arrBadgeElm = document.querySelectorAll(".badge");
    const arrResiElm = document.querySelectorAll(".resi");
    const transaksiJson = JSON.parse('<?= $transaksiJson; ?>')
    const listCustomerDetailElm = document.querySelectorAll(".list-customer-detail");
    console.log(transaksiJson)
    console.log(pInfoBayar)
    let idMidSelected;
    let indexItemSelected;

    function bukaList(ind) {
        listCustomerDetailElm.forEach(element => {
            element.classList.add("d-none")
        });
        listCustomerDetailElm[ind].classList.remove('d-none');
    }

    function editresi(id_mid, index_item) {
        containerEditResiElm.style.display = 'flex'
        console.log(id_mid)
        idMidSelected = id_mid
        indexItemSelected = index_item
        resiInputElm.value = ''
    }

    function acteditresi() {
        console.log(resiInputElm.value)
        const bodynya = {
            resi: resiInputElm.value,
            idMid: idMidSelected,
            data: transaksiJson[indexItemSelected]
        }
        async function fetchEditResi() {
            const res = await fetch("editresi", {
                method: 'post',
                headers: {
                    "Content-type": "application/json"
                },
                body: JSON.stringify(bodynya)
            });
            const resJson = await res.json()
            if (resJson.success) {
                arrBadgeElm[indexItemSelected].innerHTML = resJson.status;
                arrBadgeElm[indexItemSelected].classList.remove("text-bg-warning");
                arrBadgeElm[indexItemSelected].classList.add("text-bg-info");
                arrResiElm[indexItemSelected].innerHTML = "Nomor resi : " + resJson.resi;
            }
        }
        fetchEditResi()
        closeeditresi()
    }

    function closeeditresi() {
        containerEditResiElm.style.display = 'none'
    }
</script>
<?= $this->endSection(); ?>