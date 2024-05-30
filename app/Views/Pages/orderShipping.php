<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="d-flex justify-content-center align-items-center gap-2 my-5">
            <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">access_time</i></div>
            <div style="color: var(--hijau)">--------</div>
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div style="background-color: var(--hijau); padding: 1em; border-radius: 2em;"><i class="material-icons text-light">local_shipping</i></div>
                <p class="m-0 text-center fw-bold" style="line-height: 20px; color: var(--hijau)">Diproses</p>
            </div>
            <div style="color: gray">--------</div>
            <div style="background-color: gray; padding: 1em; border-radius: 2em;"><i class="material-icons text-light">done</i></div>
        </div>
        <div class="baris-ke-kolom">
            <div class="limapuluh-ke-seratus">
                <div class="d-flex justify-content-between mb-3">
                    <div class="flex-grow-1">
                        <p class="m-0">Nomor Virtual Account</p>
                        <h5>BCA 0009277816423</h5>
                    </div>
                    <div class="flex-grow-1">
                        <p class="m-0">Nominal</p>
                        <h5>Rp 300.000</h5>
                    </div>
                </div>
                <div class="mb-3">
                    <p class="m-0">Status</p>
                    <h5 style="color: var(--hijau);">Telah dibayar</h5>
                </div>
                <span class="garis mb-3"></span>
                <p class="mb-2 fw-bold text-center">Produk yang Anda pesan</p>
                <div>
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>
                                    <p class="mb-0">Lemari Hias LH 3310 (Sonoma)</p>
                                </td>
                                <td>
                                    <p class="mb-0">2</p>
                                </td>
                                <td class="text-end">
                                    <p class="mb-0">Rp 350.000</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="mb-0">Lemari Hias LH 3310 (Sonoma)</p>
                                </td>
                                <td>
                                    <p class="mb-0">2</p>
                                </td>
                                <td class="text-end">
                                    <p class="mb-0">Rp 350.000</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="limapuluh-ke-seratus">
                <p class="fw-bold mb-2">Pengiriman</p>
                <div class="flex-grow-1 mb-4">
                    <div class="d-flex justify-content-between">
                        <div class="flex-grow-1">
                            <img src="/img/kurir/logoIndahSVG.webp" class="mb-1" style="height:40px"></img>
                            <h5 class="m-0">Indah Cargo</h5>
                            <p class="m-0">Ekonomi</p>
                        </div>
                        <div class="flex-grow-1">
                            <p class="m-0">Resi</p>
                            <h5 class="mb-1">SRG982478123</h5>
                            <a href="https://indahonline.com/tracking/cek-resi" class="btn btn-primary1">Tracking</a>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="m-0">Alamat</p>
                    <h5>Jl. Manahan IV No.11 Jonggrangan, Klaten Utara, Klaten</h5>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>