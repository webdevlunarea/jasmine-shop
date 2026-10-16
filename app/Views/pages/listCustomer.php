<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="container-edit-resi" style="z-index: 100;">
    <div class="bg-white p-4 rounded" style="box-shadow: 0 0 10px rgba(0,0,0,0.4);">
        <div class="d-flex justify-content-between align-items-start">
            <h3 id="shipping-editor-title">Pengiriman Pesanan</h3>
            <div class="d-flex gap-2">
                <a class="btn d-flex" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Simpan" onclick="acteditresi()"><i class="material-icons" style="font-size:large">check</i></a>
                <a class="btn d-flex" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Batal" onclick="closeeditresi()"><i class="material-icons" style="font-size:large">close</i></a>
            </div>
        </div>
        <p id="shipping-save-feedback" class="small" role="status"></p>
        <div id="internal-shipping-fields" class="mb-2" hidden>
            <label class="form-label" for="internal-shipping-status">Tahap pengiriman</label>
            <select id="internal-shipping-status" class="form-select">
                <option value="Disiapkan">Disiapkan</option>
                <option value="Dalam Pengiriman">Dalam Pengiriman</option>
                <option value="Gagal Kirim">Gagal Kirim</option>
                <option value="Terkirim">Terkirim</option>
            </select>
            <small class="text-secondary">Simpan resi pada tahap Disiapkan. Pilih Dalam Pengiriman hanya saat barang benar-benar dikirim.</small>
        </div>
        <div class="mb-1" id="external-shipping-courier">
            <label class="form-label">Kurir</label>
            <input type="text" class="form-control" id="floatingInput1" placeholder="KURIR Jenis-Layanan" name="kurir" value="Lunarea - Darat">
        </div>
        <div class="m-0" id="external-shipping-awb">
            <label class="form-label" id="awb-label" for="floatingInput">Nomor resi (wajib)</label>
            <input type="text" class="form-control" id="floatingInput" placeholder="XXXXXXXXXXXX" name="resi" maxlength="120" required>
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
                <?= csrf_field(); ?>
                <button type="submit" class="btn btn-primary1">Konfirmasi Pesanan</button>
            </form>
            <form class="isi-bukti-bayar" action="" method="post">
                <?= csrf_field(); ?>
                <button type="submit" class="btn btn-danger">Gagalkan Pesanan</button>
            </form>
            <button type="button" onclick="closeBuktiBayar()" class="btn btn-outline-dark">Tutup</button>
        </div>
    </div>
</div>
<div class="konten">
    <div class="container">
        <?php if ($orderFeedback = session()->getFlashdata('msg')) { ?>
            <div class="alert alert-info" role="status"><?= esc($orderFeedback); ?></div>
        <?php } ?>
        <div class="customer-admin-hero mb-3">
            <div>
                <span class="customer-admin-badge"><i class="material-icons" style="font-size:16px;">receipt_long</i>Order customer</span>
                <h3 class="mb-1 fw-bold">List Customer</h3>
                <p class="mb-0 text-secondary">Pantau pesanan, pembayaran, resi, invoice, dan form gudang dari satu halaman.</p>
            </div>
            <div class="form-floating customer-filter">
                <select class="form-select" aria-label="Filter status pesanan" onchange="pilihStatus(event)">
                    <option <?= $status == 'all' ? 'selected' : ''; ?> value="all">Semua</option>
                    <option <?= $status == 'Menunggu-Pembayaran' ? 'selected' : ''; ?> value="Menunggu-Pembayaran">Menunggu Pembayaran</option>
                    <option <?= $status == 'Menunggu-Pembayaran-Rekening' ? 'selected' : ''; ?> value="Menunggu-Pembayaran-Rekening">Menunggu Pembayaran Rekening</option>
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
            .customer-admin-hero {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                align-items: center;
                border: 1px solid #e3efe8;
                border-radius: 24px;
                padding: 1.2rem;
                background: linear-gradient(135deg, #f3fff7 0%, #fff 55%, #eef8f2 100%);
                box-shadow: 0 16px 40px rgba(26, 83, 54, .08);
            }

            .customer-admin-badge {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                background: #e9f8ee;
                color: #207247;
                border: 1px solid #caead5;
                border-radius: 999px;
                padding: .35rem .7rem;
                font-size: .78rem;
                font-weight: 700;
                margin-bottom: .45rem;
            }

            .customer-filter {
                min-width: 260px;
            }

            .customer-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: .75rem;
                margin-bottom: 1rem;
            }

            .customer-stat-card {
                border: 1px solid #e5eee8;
                border-radius: 18px;
                padding: .9rem 1rem;
                background: #fff;
                box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
            }

            .customer-stat-card small {
                color: #64748b;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .03em;
            }

            .customer-stat-card h4 {
                margin: .2rem 0 0;
                font-weight: 800;
            }

            .customer-table-head {
                background: #f8faf9;
                border: 1px solid #e5eee8;
                border-radius: 16px;
                padding: .75rem 1.1rem;
            }

            .container-list-customer {
                display: flex;
                flex-direction: column;
                gap: .75rem;
            }

            .list-customer {
                border: 1px solid #e5eee8 !important;
                border-radius: 18px !important;
                background: #fff;
                box-shadow: 0 12px 30px rgba(15, 23, 42, .055);
                transition: .18s ease;
                overflow: hidden;
            }

            .list-customer:hover {
                transform: translateY(-1px);
                box-shadow: 0 18px 38px rgba(15, 23, 42, .08);
            }

            .list-customer.need-confirm {
                border-color: #f59e0b !important;
                box-shadow: 0 14px 34px rgba(245, 158, 11, .16);
            }

            .list-customer-detail {
                border-top: 1px solid #e5eee8;
                background: #fbfefc;
                padding-top: 1rem;
            }

            .customer-empty-state {
                border: 1px dashed #cbd5e1;
                border-radius: 20px;
                background: #fff;
                padding: 2rem;
                text-align: center;
                color: #64748b;
            }

            .customer-pagination-wrap {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: .55rem;
                margin-top: 1rem;
            }

            .customer-pagination-wrap .pagination {
                flex-wrap: wrap;
                gap: .35rem;
            }

            .customer-pagination-wrap .page-link {
                min-width: 40px;
                min-height: 40px;
                border-radius: 12px !important;
                border: 1px solid #dbe7df;
                color: #1f3b2e;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                box-shadow: 0 6px 14px rgba(15, 23, 42, .04);
            }

            .customer-pagination-wrap .page-item.active .page-link {
                background: var(--hijau);
                border-color: var(--hijau);
                color: #fff;
            }

            .customer-pagination-wrap .page-item.disabled .page-link {
                background: #f8fafc;
                color: #94a3b8;
                box-shadow: none;
            }

            .btn-reload {
                color: black;
                text-decoration: underline;
            }

            .btn-reload:hover {
                color: white;
            }

            @media (max-width: 767px) {
                .customer-admin-hero {
                    flex-direction: column;
                    align-items: stretch;
                    border-radius: 18px;
                    padding: 1rem;
                }

                .customer-filter {
                    min-width: 100%;
                }
            }
        </style>
        <div class="customer-stats">
            <div class="customer-stat-card">
                <small>Total filter</small>
                <h4><?= number_format($totalFiltered ?? count($semuaTransaksiCus), 0, ',', '.'); ?></h4>
            </div>
            <div class="customer-stat-card">
                <small>Ditampilkan</small>
                <h4><?= count($transaksiCus); ?></h4>
            </div>
            <div class="customer-stat-card">
                <small>Halaman</small>
                <h4><?= (int)$page; ?></h4>
            </div>
        </div>
        <p id="btn-reload" class="d-none bg-warning px-2 py-1">Terdeteksi terjadi perubahan data! <a href="/listcustomer" class="btn-reload">Reload sekarang!</a></p>
        <div class="mb-2 show-flex-ke-hide customer-table-head">
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
            <?php if (count($transaksiCus) === 0) { ?>
                <div class="customer-empty-state">
                    <i class="material-icons mb-2" style="font-size: 42px; opacity: .45;">inbox</i>
                    <h5 class="fw-bold mb-1">Tidak ada pesanan pada filter ini</h5>
                    <p class="mb-0">Coba pilih status lain atau kembali ke filter Semua.</p>
                </div>
            <?php } ?>
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
                                                                                        } ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Pengiriman" onclick="editresi('<?= $t['id_midtrans']; ?>', '<?= $t_ind; ?>')"><i class="material-icons" style="font-size:large">mode_edit</i></a>
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
                                <p class="mb-0 fw-bold"><?= !empty($t['data_mid']['transaction_time']) ? date("d/m/Y H:i:s", strtotime($t['data_mid']['transaction_time'])) : '-'; ?></p>
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
                                                                        } ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Pengiriman" onclick="editresi('<?= $t['id_midtrans']; ?>', '<?= $t_ind; ?>')"><i class="material-icons" style="font-size:large">mode_edit</i></a>
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
                                    <p style="flex: 3;" class="mb-0"><?= !empty($t['shipment']) ? 'Biaya / penyesuaian lainnya' : 'Biaya / potongan di luar rincian'; ?></p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp
                                        <?= number_format(((float)($t['data_mid']['gross_amount'] ?? 0) - $totalHargaBarang - (int)($t['shipment']['shipping_cost'] ?? 0)), 0, ",", "."); ?>
                                    </p>
                                </div>
                                <?php if (!empty($t['shipment'])) { ?>
                                <?php $shippingPricing = json_decode((string)($t['shipment']['shipping_pricing'] ?? ''), true); ?>
                                <?php if (is_array($shippingPricing) && (int)($shippingPricing['subsidy_amount'] ?? 0) > 0) { ?>
                                <div class="d-flex w-100 align-items-start small text-secondary">
                                    <p style="flex: 3;" class="mb-0">Tarif ekspedisi asli (internal)</p>
                                    <p style="flex: 1;" class="mb-0 text-end">Rp <?= number_format((int)$shippingPricing['original_cost'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="d-flex w-100 align-items-start small text-secondary">
                                    <p style="flex: 3;" class="mb-0">Subsidi ditanggung toko (internal)</p>
                                    <p style="flex: 1;" class="mb-0 text-end">Rp <?= number_format((int)$shippingPricing['subsidy_amount'], 0, ',', '.'); ?></p>
                                </div>
                                <?php } ?>
                                <div class="d-flex w-100 align-items-start">
                                    <p style="flex: 3;" class="mb-0">Ongkir</p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp <?= number_format((int)$t['shipment']['shipping_cost'], 0, ',', '.'); ?></p>
                                </div>
                                <?php } ?>
                                <div class="d-flex w-100 align-items-start">
                                    <p style="flex: 3;" class="mb-0">Total Keseluruhan</p>
                                    <p style="flex: 1;" class="mb-0 text-end fw-bold">Rp
                                        <?= number_format((float)($t['data_mid']['gross_amount'] ?? 0), 0, ",", "."); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($t['status'] == "Menunggu Pembayaran") { ?>
                                <p class="mb-0 fw-bold">Metode Pembayaran</p>
                                <p class="mb-0">
                                    <?php
                                    $paymentType = $t['data_mid']['payment_type'] ?? '';
                                    switch ($paymentType) {
                                        case 'credit_card':
                                            echo "Credit Card<br>" . strtoupper($t['data_mid']['bank'] ?? '-') . " " . ucfirst($t['data_mid']['card_type'] ?? '-');
                                            break;
                                        case 'echannel':
                                            switch ($t['data_mid']['biller_code'] ?? '') {
                                                case '70012':
                                                    echo "Mandiri Bill<br>" . "Biller Code: " . ($t['data_mid']['biller_code'] ?? '-') . "<br>Bill Key: " . ($t['data_mid']['bill_key'] ?? '-');
                                                    break;
                                                default:
                                                    echo "EChannel<br>" . "Biller Code: " . ($t['data_mid']['biller_code'] ?? '-') . "<br>Bill Key: " . ($t['data_mid']['bill_key'] ?? '-');
                                                    break;
                                            }
                                            break;
                                        case 'bank_transfer':
                                            if (!empty($t['data_mid']['va_numbers'][0]))
                                                echo strtoupper($t['data_mid']['va_numbers'][0]['bank'] ?? '-') . " " . ($t['data_mid']['va_numbers'][0]['va_number'] ?? '-');
                                            else if (isset($t['data_mid']['permata_va_number']))
                                                echo "Bank Permata VA<br>" . $t['data_mid']['permata_va_number'];
                                            else if (isset($t['data_mid']['bca_va_number']))
                                                echo "BCA VA<br>" . $t['data_mid']['bca_va_number'];
                                            else
                                                echo "Bank Transfer";
                                            break;
                                        case 'gopay':
                                            echo 'Qris<br><a href="/qris/' . ($t['data_mid']['order_id'] ?? $t['id_midtrans']) . '-' . ($t['data_mid']['gross_amount'] ?? 0) . '" style="color: var(--hijau); cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
                                            break;
                                        case 'qris':
                                            echo 'Qris<br><a href="/qris/' . ($t['data_mid']['order_id'] ?? $t['id_midtrans']) . '-' . ($t['data_mid']['gross_amount'] ?? 0) . '" style="color: var(--hijau); cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold">Lihar barcode</a>';
                                            break;
                                        default:
                                            echo $paymentType ?: '-';
                                            break;
                                    }
                                    ?></p>
                            <?php } ?>
                            <p class="fw-bold mb-0 resi"><?= esc(($t['shipment']['shipping_method_code'] ?? '') === 'internal_delivery' ? 'Resi toko: ' . $t['resi'] : 'Nomor resi: ' . $t['resi'] . ' (' . $t['kurir'] . ')'); ?></p>
                            <?php if ($t['status'] === 'Proses') { ?>
                                <form class="order-sync-form mt-2" method="post" action="/syncorder/<?= rawurlencode($t['id_midtrans']); ?>">
                                    <?= csrf_field(); ?>
                                    <p class="small text-secondary mb-2"><?= esc(\App\Services\Integrations\LunaFulfillment::label($t['data_mid'])); ?></p>
                                    <label class="small d-block mb-2"><input type="checkbox" name="review_confirmed" value="1" required> Produk, varian, alamat, dan ekspedisi sudah saya periksa.</label>
                                    <button type="submit" class="btn btn-sm btn-outline-success">Tinjau &amp; teruskan / periksa status MyLuna</button>
                                    <p class="small text-secondary mt-1 mb-0">Periksa produk, varian, alamat dan ekspedisi sebelum meneruskan. Resi diisi setelah proses pusat. Pengiriman ulang tidak membuat pesanan baru.</p>
                                </form>
                            <?php } ?>
                            <?php if (in_array($t['status'], ['Dikirim', 'Selesai'], true) || ($t['shipment']['shipping_status'] ?? '') === 'Disiapkan') { ?>
                                <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="retryShippingSync(<?= (int)$t_ind; ?>)">Sinkron ulang pengiriman ke MyLuna</button>
                            <?php } ?>
                        </div>
                        <div class="w-100 d-flex flex-column align-items-start pd-2" style="max-width: 400px;">
                            <p class="fw-bold mb-0">Informasi Penerima</p>
                            <p class="mb-0"><?= esc($t['nama_pen']); ?></p>
                            <p class="mb-0"><?= esc($t['alamat_pen']); ?></p>
                            <p class="mb-0"><?= esc($t['hp_pen']); ?></p>
                            <?php if ($t['note'] != '') { ?>
                                <p class="mb-0 fw-bold">Note : <?= esc($t['note']); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if (($totalFiltered ?? count($semuaTransaksiCus)) > ($perPage ?? 20)) { ?>
            <?php
                $currentPage = max(1, (int)$page);
                $perPageValue = max(1, (int)($perPage ?? 20));
                $totalRows = (int)($totalFiltered ?? count($semuaTransaksiCus));
                $totalPages = max(1, (int)ceil($totalRows / $perPageValue));
                $currentPage = min($currentPage, $totalPages);
                $pages = [1, $totalPages];
                for ($x = $currentPage - 2; $x <= $currentPage + 2; $x++) {
                    if ($x >= 1 && $x <= $totalPages) $pages[] = $x;
                }
                $pages = array_values(array_unique($pages));
                sort($pages);
                $lastPrintedPage = 0;
            ?>
            <nav class="customer-pagination-wrap" aria-label="Navigasi halaman order customer">
                <div class="small text-secondary">
                    Halaman <strong><?= $currentPage; ?></strong> dari <strong><?= $totalPages; ?></strong> Â· <?= number_format($totalRows, 0, ',', '.'); ?> pesanan
                </div>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?= $currentPage <= 1 ? '#' : '/listcustomer/' . ($currentPage - 1) . '/' . $status; ?>" aria-label="Halaman sebelumnya">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php foreach ($pages as $pageNumber) { ?>
                        <?php if ($lastPrintedPage && $pageNumber > $lastPrintedPage + 1) { ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php } ?>
                        <li class="page-item <?= $pageNumber === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="/listcustomer/<?= $pageNumber; ?>/<?= $status; ?>" aria-label="Halaman <?= $pageNumber; ?>"><?= $pageNumber; ?></a>
                        </li>
                        <?php $lastPrintedPage = $pageNumber; ?>
                    <?php } ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?= $currentPage >= $totalPages ? '#' : '/listcustomer/' . ($currentPage + 1) . '/' . $status; ?>" aria-label="Halaman berikutnya">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
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
    let shippingSaving = false;
    let shippingSaved = false;
    document.querySelectorAll('.order-sync-form').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.sending === 'true') { event.preventDefault(); return; }
            form.dataset.sending = 'true';
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.textContent = 'Mengirim ke MyLuna...';
        });
    });
    let shippingCsrf = <?= json_encode(csrf_hash()); ?>;

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
        idMidSelected = id_mid
        indexItemSelected = index_item
        const order = transaksiJson[index_item];
        const internal = order.shipment?.shipping_method_code === 'internal_delivery';
        document.getElementById('internal-shipping-fields').hidden = false;
        document.getElementById('external-shipping-courier').hidden = internal;
        document.getElementById('external-shipping-awb').hidden = false;
        document.getElementById('awb-label').textContent = internal ? 'Nomor resi internal toko (wajib)' : 'Nomor resi ekspedisi (wajib)';
        document.getElementById('shipping-editor-title').textContent = internal ? 'Jasa Kirim Toko' : 'Pengiriman Ekspedisi';
        document.getElementById('shipping-save-feedback').textContent = '';
        document.getElementById('internal-shipping-status').value = ['Disiapkan', 'Dalam Pengiriman', 'Gagal Kirim', 'Terkirim'].includes(order.shipment?.shipping_status) ? order.shipment.shipping_status : (order.status === 'Proses' ? 'Disiapkan' : (order.status === 'Selesai' ? 'Terkirim' : 'Dalam Pengiriman'));
        resiInputElm.value = order.shipment?.tracking_number || (String(order.resi || '').toLowerCase().includes('menunggu') ? '' : order.resi || '');
        kurirInputElm.value = order.kurir || '';
        kurirInputElm.readOnly = !!order.shipment && !internal;
    }

    async function acteditresi(retrySync = false) {
        if (shippingSaving) return;
        const feedback = document.getElementById('shipping-save-feedback');
        if (!retrySync && !resiInputElm.value.trim()) { feedback.textContent = 'Isi nomor resi pengiriman terlebih dahulu.'; resiInputElm.focus(); return; }
        const bodynya = {
            resi: resiInputElm.value,
            kurir: kurirInputElm.value,
            idMid: idMidSelected,
            retry_sync: retrySync === true,
            shipping_status: document.getElementById('internal-shipping-status').value,
            [<?= json_encode(csrf_token()); ?>]: shippingCsrf
        }
        shippingSaving = true;
        feedback.textContent = 'Menyimpan pengiriman…';
        try {
            const res = await fetch('/editresi', {
                method: 'post',
                headers: {
                    'Content-type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(bodynya)
            });
            const resJson = await res.json();
            if (resJson.csrf_hash) {
                shippingCsrf = resJson.csrf_hash;
                document.querySelectorAll('input[name="<?= esc(csrf_token(), 'attr'); ?>"]').forEach(input => { input.value = shippingCsrf; });
            }
            if (!res.ok || !resJson.success) throw new Error(resJson.message || 'Pengiriman belum tersimpan. Muat ulang lalu coba lagi.');
            feedback.textContent = resJson.message + ' Tutup dialog untuk memperbarui daftar.';
            shippingSaved = true;
        } catch (error) {
            feedback.textContent = error.message || 'Koneksi terganggu. Muat ulang dan periksa status pesanan sebelum mencoba lagi.';
        } finally {
            shippingSaving = false;
        }
    }

    function retryShippingSync(index) {
        if (shippingSaving) return;
        editresi(transaksiJson[index].id_midtrans, index);
        acteditresi(true);
    }

    function closeeditresi() {
        if (shippingSaving) return;
        if (shippingSaved) { window.location.reload(); return; }
        containerEditResiElm.style.display = 'none'
    }

    function pilihStatus(e) {
        console.log(e.target.value)
        window.location.href = '/listcustomer/1/' + e.target.value
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
