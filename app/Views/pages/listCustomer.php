<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="container-edit-resi" style="z-index: 100;">
    <div class="bg-white p-4 rounded" style="box-shadow: 0 0 10px rgba(0,0,0,0.4);">
        <div class="d-flex justify-content-between align-items-start">
            <h3>Edit resi</h3>
            <div class="d-flex gap-2">
                <a class="btn d-flex" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Simpan" onclick="acteditresi()"><i class="material-icons" style="font-size:large">check</i></a>
                <a class="btn d-flex" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Batal" onclick="closeeditresi()"><i class="material-icons" style="font-size:large">close</i></a>
            </div>
        </div>
        <div class="mb-1">
            <label class="form-label">Kurir</label>
            <input type="text" class="form-control" id="floatingInput1" placeholder="KURIR Jenis-Layanan" name="kurir" value="Lunarea - Darat">
        </div>
        <div class="m-0">
            <label class="form-label">Resi</label>
            <input type="text" class="form-control" id="floatingInput" placeholder="XXXXXXXXXXXX" name="resi">
        </div>
    </div>
</div>
<div id="container-bukti-bayar" style="z-index: 100; position:fixed; top: 0; left: 0; width: 100%; height:100svh;" class="d-none p-3 justify-content-center align-items-center">
    <div style="background-color: white; box-shadow: 0 0 10px rgba(0,0,0,0.4);" class="px-4 py-3 rounded">
        <h3 class="m-0">Bukti Pembayaran</h3>
        <p class="isi-bukti-bayar mb-2">Id Pesanan : </p>
        <div class="d-flex justify-content-center">
            <img class="isi-bukti-bayar rounded" src="" alt="" style="max-height: 70svh; max-width: 70vw;">
        </div>
        <div class="d-flex gap-1 mt-2 justify-content-center align-items-center">
            <form class="isi-bukti-bayar" action="" method="post">
                <button type="submit" class="btn btn-primary1">Konfirmasi Pesanan</button>
            </form>
            <form class="isi-bukti-bayar" action="" method="post">
                <button type="submit" class="btn btn-danger">Gagalkan Pesanan</button>
            </form>
            <button type="button" onclick="closeBuktiBayar()" class="btn btn-outline-dark">Tutup</button>
        </div>
    </div>
</div>
<div class="konten">
    <div class="container">
        <div class="d-flex mb-2 justify-content-between">
            <h3 class="">List Customer</h3>
            <div class="form-floating">
                <select class="form-select" aria-label="Default select example" onchange="pilihStatus(event)">
                    <option <?= $status == 'all' ? 'selected' : ''; ?> value="all">Semua</option>
                    <option <?= $status == 'Menunggu-Pembayaran' ? 'selected' : ''; ?> value="Menunggu-Pembayaran">Menunggu Pembayaran</option>
                    <option <?= $status == 'Menunggu-Pembayaran-Rekening' ? 'selected' : ''; ?> value="Menunggu-Pembayaran Rekening">Menunggu Pembayaran Rekening</option>
                    <option <?= $status == 'Kadaluarsa' ? 'selected' : ''; ?> value="Kadaluarsa">Kadaluarsa</option>
                    <option <?= $status == 'Proses' ? 'selected' : ''; ?> value="Proses">Proses</option>
                    <option <?= $status == 'Dikirim' ? 'selected' : ''; ?> value="Dikirim">Dikirim</option>
                    <option <?= $status == 'Dibatalkan' ? 'selected' : ''; ?> value="Dibatalkan">Dibatalkan</option>
                    <option <?= $status == 'Ditolak' ? 'selected' : ''; ?> value="Ditolak">Ditolak</option>
                    <option <?= $status == 'Selesai' ? 'selected' : ''; ?> value="Selesai">Selesai</option>
                </select>
                <label for="floatingInput">Status</label>
            </div>
        </div>
        <style>
            .btn-reload {
                color: black;
                text-decoration: underline;
            }

            .btn-reload:hover {
                color: white;
            }
        </style>
        <p id="btn-reload" class="d-none bg-warning px-2 py-1">Terdeteksi terjadi perubahan data! <a href="/listcustomer" class="btn-reload">Reload sekarang!</a></p>
        <div class="mb-2 show-flex-ke-hide" style="padding-inline: 2em;">
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
        </div>
        <div class="container-list-customer mb-2">
            <?php foreach ($transaksiCus as $t_ind => $t) { ?>
                <div class="list-customer <?= ($t['status'] == 'Menunggu Pembayaran Rekening' && $t['bukti_bayar'] != '') ? 'need-confirm' : ''; ?>" onclick="bukaList('<?= $t_ind; ?>')">
                    <div>
                        <div class="d-flex align-items-start">
                            <div style="flex: 4;">
                                <p class="mb-0 fw-bold nama"><?= $t['nama_pen']; ?></p>
                                <p class="mb-0"><?= $t['email_cus']; ?></p>
                                <a href="/order/<?= $t['id_midtrans']; ?>" class="hide-ke-show-flex gap-1 align-items-center mb-1 text-secondary">
                                    <p class="m-0" style="font-size: 12px; text-wrap:nowrap;">ID Pesanan : <?= $t['id_midtrans']; ?></p>
                                    <i class="material-icons" style="font-size: 10px;">open_in_new</i>
                                </a>
                                <p class="mb-2 fw-bold badge rounded-pill hide-ke-show-block <?php
                                                                                                switch ($t['status']) {
                                                                                                    case 'Menunggu Pembayaran':
                                                                                                        echo "text-bg-primary";
                                                                                                        break;
                                                                                                    case 'Menunggu Pembayaran Rekening':
                                                                                                        echo $t['bukti_bayar'] ? 'text-bg-warning' : 'text-bg-primary';
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
                                                                                                    case 'Dibatalkan':
                                                                                                        echo "text-bg-danger";
                                                                                                        break;
                                                                                                    case 'Gagal':
                                                                                                        echo "text-bg-danger";
                                                                                                        break;
                                                                                                    default:
                                                                                                        echo "text-bg-dark";
                                                                                                        break;
                                                                                                }
                                                                                                ?>"><?= $t['status'] == 'Menunggu Pembayaran Rekening' ? ($t['bukti_bayar'] ? 'Butuh konfirmasi' : 'Menunggu Pembayaran') : $t['status']; ?></p>
                                <div class="hide-ke-show-flex gap-1 mt-1" style="width: fit-content;">
                                    <a class="btn btn-success hide-ke-show-flex btn-sm <?= $t['status'] == 'Dikirim' ? '' : 'disabled'; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Pesanan Diterima" onclick="triggerToast('Pesanan dengan ID <?= $t['id_midtrans']; ?> telah diterima?', '/orderdone/<?= $t['id_midtrans']; ?>')"><i class="material-icons" style="font-size:large">check</i></a>
                                    <a class="btn btn-success hide-ke-show-flex btn-sm <?php switch ($t['status']) {
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
                                    <a class="btn btn-success hide-ke-show-flex btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Invoice" href="/invoice/<?= $t['id_midtrans']; ?>"><i class="material-icons" style="font-size:large">description</i></a>
                                    <a class="btn btn-success hide-ke-show-flex btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Form Gudang" href="/pdf/<?= $t['id_midtrans']; ?>"><i class="material-icons" style="font-size:large">file_download</i></a>
                                    <?php if ($t['status'] == 'Menunggu Pembayaran Rekening' || $t['bukti_bayar']) { ?>
                                        <a class="btn btn-success hide-ke-show-flex btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Bukti Bayar" onclick="openBuktiBayar('<?= $t['id_midtrans']; ?>', <?= $t['status'] == 'Menunggu Pembayaran Rekening' ? ($t['bukti_bayar'] != '' ? 'true' : 'false') : 'false'; ?>)"><i class="material-icons" style="font-size:large">attach_money</i></a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div style="flex: 3;" class="d-flex justify-content-center align-items-start">
                                <p class="mb-0 fw-bold badge rounded-pill show-ke-hide <?php
                                                                                        switch ($t['status']) {
                                                                                            case 'Menunggu Pembayaran':
                                                                                                echo "text-bg-primary";
                                                                                                break;
                                                                                            case 'Menunggu Pembayaran Rekening':
                                                                                                echo $t['bukti_bayar'] ? 'text-bg-warning' : 'text-bg-primary';
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
                                                                                            case 'Dibatalkan':
                                                                                                echo "text-bg-danger";
                                                                                                break;
                                                                                            case 'Gagal':
                                                                                                echo "text-bg-danger";
                                                                                                break;
                                                                                            default:
                                                                                                echo "text-bg-dark";
                                                                                                break;
                                                                                        }
                                                                                        ?>"><?= $t['status'] == 'Menunggu Pembayaran Rekening' ? ($t['bukti_bayar'] ? 'Butuh Konfirmasi' : 'Menunggu Pembayaran') : $t['status']; ?></p>
                            </div>
                            <a href="/order/<?= $t['id_midtrans']; ?>" style="flex: 3;" class="show-flex-ke-hide justify-content-center align-items-center gap-1 btn-teks-aja">
                                <p class="mb-0 fw-bold"><?= $t['id_midtrans']; ?></p>
                                <i class="material-icons" style="font-size: 13px;">open_in_new</i>
                            </a>
                            <div style="flex: 3;" class="d-flex justify-content-center align-items-start">
                                <p class="mb-0 fw-bold"><?= date("d/m/Y H:i:s", strtotime($t['data_mid']['transaction_time'])); ?></p>
                            </div>
                        </div>
                        <div style="gap: 3px; width: fit-content; border-top: 1px solid var(--hijau); padding-top: 10px;" class="w-100 show-flex-ke-hide justify-content-center">
                            <a class="btn btn-success show-flex-ke-hide <?= $t['status'] == 'Dikirim' ? '' : 'disabled'; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Pesanan Diterima" onclick="triggerToast('Pesanan dengan ID <?= $t['id_midtrans']; ?> telah diterima?', '/orderdone/<?= $t['id_midtrans']; ?>')"><i class="material-icons" style="font-size:large">check</i></a>
                            <a class="btn btn-success show-flex-ke-hide <?php switch ($t['status']) {
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
                            <a class="btn btn-success show-flex-ke-hide" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Invoice" href="/invoice/<?= $t['id_midtrans']; ?>"><i class="material-icons" style="font-size:large">description</i></a>
                            <a class="btn btn-success show-flex-ke-hide" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Form Gudang" href="/pdf/<?= $t['id_midtrans']; ?>"><i class="material-icons" style="font-size:large">file_download</i></a>
                            <?php if ($t['status'] == 'Menunggu Pembayaran Rekening' || $t['bukti_bayar']) { ?>
                                <a class="btn btn-success show-flex-ke-hide" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Bukti Bayar" onclick="openBuktiBayar('<?= $t['id_midtrans']; ?>', <?= $t['status'] == 'Menunggu Pembayaran Rekening' ? ($t['bukti_bayar'] != '' ? 'true' : 'false') : 'false'; ?>)"><i class="material-icons" style="font-size:large">attach_money</i></a>
                            <?php } ?>
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
                                foreach ($t['items'] as $barang) {
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
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp
                                        <?= number_format($totalHargaBarang, 0, ",", "."); ?></p>
                                </div>
                                <div class="d-flex w-100 align-items-start">
                                    <p style="flex: 3;" class="mb-0">Biaya Ongkir dan Admin</p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp
                                        <?= number_format(($t['data_mid']['gross_amount'] - $totalHargaBarang), 0, ",", "."); ?>
                                    </p>
                                </div>
                                <div class="d-flex w-100 align-items-start">
                                    <p style="flex: 3;" class="mb-0">Total Keseluruhan</p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp
                                        <?= number_format($t['data_mid']['gross_amount'], 0, ",", "."); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($t['status'] == "Menunggu Pembayaran") { ?>
                                <p class="mb-0 fw-bold">Metode Pembayaran</p>
                                <p class="mb-0">
                                    <?php
                                    switch ($t['data_mid']['payment_type']) {
                                        case 'credit_card':
                                            echo "Credit Card<br>" . strtoupper($t['data_mid']['bank']) . " " . ucfirst($t['data_mid']['card_type']);
                                            break;
                                        case 'echannel':
                                            switch ($t['data_mid']['biller_code']) {
                                                case '70012':
                                                    echo "Mandiri Bill<br>" . "Biller Code: " . $t['data_mid']['biller_code'] . "<br>Bill Key: " . $t['data_mid']['bill_key'];
                                                    break;
                                                default:
                                                    echo "EChannel<br>" . "Biller Code: " . $t['data_mid']['biller_code'] . "<br>Bill Key: " . $t['data_mid']['bill_key'];
                                                    break;
                                            }
                                            break;
                                        case 'bank_transfer':
                                            if (isset($t['data_mid']['va_numbers']))
                                                echo strtoupper($t['data_mid']['va_numbers'][0]['bank']) . " " . $t['data_mid']['va_numbers'][0]['va_number'];
                                            else if (isset($t['data_mid']['permata_va_number']))
                                                echo "Bank Permata VA<br>" . $t['data_mid']['permata_va_number'];
                                            else if (isset($t['data_mid']['bca_va_number']))
                                                echo "BCA VA<br>" . $t['data_mid']['bca_va_number'];
                                            break;
                                        case 'gopay':
                                            echo 'Qris<br><a href="/qris/' . $t['data_mid']['order_id'] . '-' . $t['data_mid']['gross_amount'] . '" style="color: #1db954; cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
                                            break;
                                        case 'qris':
                                            echo 'Qris<br><a href="/qris/' . $t['data_mid']['order_id'] . '-' . $t['data_mid']['gross_amount'] . '" style="color: #1db954; cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
                                            break;
                                        default:
                                            echo $t['data_mid']['payment_type'];
                                            break;
                                    }
                                    ?></p>
                            <?php } ?>
                            <p class="fw-bold mb-0 resi">Nomor resi : <?= $t['resi']; ?> (<?= $t['kurir']; ?>)
                            </p>
                        </div>
                        <div class="w-100 d-flex flex-column align-items-start pd-2" style="max-width: 400px;">
                            <p class="fw-bold mb-0">Informasi Penerima</p>
                            <p class="mb-0"><?= $t['nama_pen']; ?></p>
                            <p class="mb-0"><?= $t['alamat_pen']; ?></p>
                            <p class="mb-0"><?= $t['hp_pen']; ?></p>
                            <?php if ($t['note'] != '') { ?>
                                <p class="mb-0 fw-bold">Note : <?= $t['note']; ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if (count($semuaTransaksiCus) > count($transaksiCus)) { ?>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <?php if ((int)$page > 1) { ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="/listcustomer/<?= ((int)$page - 1); ?>/<?= $status; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php }
                    $hitungGrupMax = ceil(count($semuaTransaksiCus) / 20);
                    for ($x = 1; $x <= $hitungGrupMax; $x++) {
                    ?>
                        <li class="page-item"><a class="page-link text-dark" href="/listcustomer/<?= $x; ?>/<?= $status; ?>"><?= $x; ?></a></li>
                    <?php } ?>
                    <?php if ((int)$page < $hitungGrupMax) { ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="/listcustomer/<?= ((int)$page + 1); ?>/<?= $status; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        <?php } ?>
    </div>
</div>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    const containerEditResiElm = document.getElementById("container-edit-resi")
    const resiInputElm = document.querySelector('input[name="resi"]')
    const kurirInputElm = document.querySelector('input[name="kurir"]')
    const arrBadgeElm = document.querySelectorAll(".badge");
    const arrResiElm = document.querySelectorAll(".resi");
    const transaksiJson = JSON.parse(<?= json_encode($transaksiJson); ?>)
    const listCustomerDetailElm = document.querySelectorAll(".list-customer-detail");
    const containerBuktiBayar = document.getElementById('container-bukti-bayar');
    const isiBuktiBayarElm = document.querySelectorAll('.isi-bukti-bayar')
    console.log(transaksiJson)
    var idMidSelected;
    var indexItemSelected;

    // <p class="isi-bukti-bayar mb-2">Id Pesanan : L15535320</p>
    // <div class="d-flex justify-content-center">
    //     <img class="isi-bukti-bayar rounded" src="/imgbuktibayar/L15535320" alt="" style="max-height: 70svh; max-width: 70vw;">
    // </div>
    // <div class="d-flex gap-1 mt-2 justify-content-center align-items-center">
    //     <form class="isi-bukti-bayar" action="/payorder/L15535320/confirm/accept" method="post">
    //         <button type="submit" class="btn btn-primary1">Konfirmasi Pesanan</button>
    //     </form>
    //     <form class="isi-bukti-bayar" action="/payorder/L15535320/confirm/cancel" method="post">
    //         <button type="submit" class="btn btn-danger">Gagalkan Pesanan</button>
    //     </form>
    //     <button type="button" onclick="closeBuktiBayar()" class="btn btn-outline-dark">Tutup</button>
    // </div>
    function openBuktiBayar(idMid, blmConfirm) {
        console.log(blmConfirm)
        isiBuktiBayarElm[0].innerHTML = `Id Pesanan : ${idMid}` //id pesanan
        isiBuktiBayarElm[1].src = `/imgbuktibayar/${idMid}` //gambar
        if (blmConfirm) {
            isiBuktiBayarElm[2].action = `/payorder/${idMid}/confirm/accept` //accept
            isiBuktiBayarElm[3].action = `/payorder/${idMid}/confirm/cancel` //cancel
            isiBuktiBayarElm[2].classList.remove('d-none')
            isiBuktiBayarElm[3].classList.remove('d-none')
        } else {
            isiBuktiBayarElm[2].action = `` //accept
            isiBuktiBayarElm[3].action = `` //cancel
            isiBuktiBayarElm[2].classList.add('d-none')
            isiBuktiBayarElm[3].classList.add('d-none')
        }
        containerBuktiBayar.classList.remove('d-none')
        containerBuktiBayar.classList.add('d-flex')
    }

    function closeBuktiBayar() {
        isiBuktiBayarElm[0].innerHTML = `Id Pesanan:` //id pesanan
        isiBuktiBayarElm[1].src = `` //gambar
        isiBuktiBayarElm[2].action = `` //accept
        isiBuktiBayarElm[3].action = `` //cancel
        containerBuktiBayar.classList.add('d-none')
        containerBuktiBayar.classList.remove('d-flex')
    }

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
        kurirInputElm.value = ''
    }

    function acteditresi() {
        const bodynya = {
            resi: resiInputElm.value,
            kurir: kurirInputElm.value,
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

    function pilihStatus(e) {
        console.log(e.target.value)
        window.location.href = '/listcustomer/' + '<?= $page; ?>' + '/' + e.target.value
    }
</script>
<script>
    const btnReloadElm = document.getElementById('btn-reload');
    const socket = new WebSocket('<?= $wsUrl; ?>');
    socket.onopen = () => {
        console.log(`Socket berhasil terkoneksi <?= $wsUrl; ?>`)
    }
    socket.onmessage = (event) => {
        const datanya = JSON.parse(event.data);
        console.log(datanya)
        if (datanya.jenis == 'order') {
            btnReloadElm.classList.remove('d-none');
        }
    }
</script>
<?= $this->endSection(); ?>