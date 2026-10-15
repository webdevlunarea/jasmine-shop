<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
.btn-use-point {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    color: black;
    width: 100%;
    border: 1px solid var(--hijau);
    margin-block: 0.5em;
}

.btn-use-point:hover {
    border: 1px solid var(--hijaumuda);
    color: var(--hijau);
}

.btn-use-point.use {
    margin: 0;
    border: none;
}

.tombol-pilih-kurir i {
    position: relative;
    transform: rotate(0deg);
    transition: 0.3s;
}

.tombol-pilih-kurir.show i {
    transform: rotate(90deg);
    transition: 0.3s;
}

.tmb-input-redeem {
    background-color: var(--hijau);
    color: white;
    cursor: pointer;
}

.tmb-input-redeem:hover {
    background-color: var(--hijaumuda);
    color: var(--hijau);
}
</style>
<div id="modal-redeem" role="dialog" aria-modal="true" aria-labelledby="checkout-redeem-title" class="d-none justify-content-center align-items-center"
    style="z-index: 12; background-color: rgba(0, 0, 0, 0.5); position:fixed; left: 0; top: 0; height: 100svh; width: 100vw;">
    <div style="background-color: white;" class="p-4 rounded-3">
        <h2 id="checkout-redeem-title" class="checkout-section-title">Klaim voucher</h2>
        <p class="m-0 text-secondary mb-1">Masukkan kode voucher Anda</p>
        <form action="/voucher/redeem/checkout" method="post">
            <label class="visually-hidden" for="checkout-voucher-code">Kode voucher</label>
            <input id="checkout-voucher-code" type="text" name="code" class="form-control mb-2 w-100" required>
            <div class="d-flex justify-content-end gap-1">
                <button onclick="closeRedeem()" type="button" class="btn btn-outline-dark">Batal</button>
                <button type="submit" class="btn btn-primary1">Ok</button>
            </div>
        </form>
    </div>
</div>
<main class="konten checkout-page-shell" aria-labelledby="checkout-title">
    <div class="container">
        <header class="checkout-page-heading">
            <a href="/cart" class="checkout-back-link"><span aria-hidden="true">&larr;</span> Kembali ke keranjang</a>
            <h1 id="checkout-title">Selesaikan pesanan</h1>
            <p>Lengkapi alamat dan pilih pengiriman sebelum melanjutkan pembayaran.</p>
        </header>
        <form class="w-100" id="form-checkout" action="/actionpaycore" method="post">
            <input type="hidden" name="shipping_quote_token" id="shipping_quote_token" value="">
            <div id="modal-metode-bayar" role="dialog" aria-modal="true" aria-labelledby="checkout-payment-title" class="d-none p-2 justify-content-center align-items-center"
                style="z-index: 12; background-color: rgba(0, 0, 0, 0.5); position:fixed; left: 0; top: 0; height: 100svh; width: 100vw;">
                <div style="background-color: white; max-width: 400px;" class="p-4 rounded-3">
                    <div class="h-100" style="overflow-y: auto; max-height: 80svh;">
                        <div class="d-flex justify-content-between">
                            <h2 id="checkout-payment-title" class="checkout-section-title m-0">Metode pembayaran</h2>
                            <button type="button" class="checkout-dialog-close" aria-label="Tutup metode pembayaran" onclick="closeMetode()"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <hr>
                        <p>Virtual Account</p>
                        <div class="alert d-none" role="alert" id="alert-cc"></div>
                        <div class="container-pembayaran mb-1">
                            <input type="radio" name="pembayaran" id="pembayaran1" value="bca" checked>
                            <label for="pembayaran1" class="item-logo-pembayaran"><img src="/img/pembayaran/bca.webp"
                                    alt=""></label>
                            <input type="radio" name="pembayaran" id="pembayaran2" value="bni">
                            <label for="pembayaran2" class="item-logo-pembayaran"><img src="/img/pembayaran/bni.webp"
                                    alt=""></label>
                            <input type="radio" name="pembayaran" id="pembayaran3" value="bri">
                            <label for="pembayaran3" class="item-logo-pembayaran"><img src="/img/pembayaran/bri.webp"
                                    alt=""></label>
                            <input type="radio" name="pembayaran" id="pembayaran4" value="mandiri">
                            <label for="pembayaran4" class="item-logo-pembayaran"><img
                                    src="/img/pembayaran/mandiri.webp" alt=""></label>
                            <input type="radio" name="pembayaran" id="pembayaran5" value="permata">
                            <label for="pembayaran5" class="item-logo-pembayaran"><img
                                    src="/img/pembayaran/permatabank.webp" alt=""></label>
                            <input type="radio" name="pembayaran" id="pembayaran6" value="cimb">
                            <label for="pembayaran6" class="item-logo-pembayaran"><img src="/img/pembayaran/cimb.webp"
                                    alt=""></label>
                            <input type="radio" name="pembayaran" id="pembayaran7" value="qris">
                            <label for="pembayaran7" class="item-logo-pembayaran"><img src="/img/pembayaran/qris.webp"
                                    alt=""></label>
                            <input type="radio" name="pembayaran" id="pembayaran8" value="gopay">
                            <label for="pembayaran8" class="item-logo-pembayaran"><img src="/img/pembayaran/gopay.webp"
                                    alt=""></label>
                            <!-- <input type="radio" name="pembayaran" id="pembayaran9" value="shopeepay">
                            <label for="pembayaran9" class="item-logo-pembayaran"><img src="/img/pembayaran/shopeepay.webp" alt=""></label> -->

                            <input type="radio" name="pembayaran" id="pembayaran10" value="card">
                            <label for="pembayaran10" class="item-logo-pembayaran"><img
                                    src="/img/pembayaran/mastercard.webp" alt=""></label>
                        </div>

                        <div class="pt-3 border-top d-none" id="container-form-cc">
                            <h5 class="mb-2">Informasi Kredit Card</h5>
                            <div class="form-floating mb-1">
                                <input type="number" class="form-control" placeholder="ccNumber" name="ccNumber">
                                <label for="floatingInput">Card Number</label>
                            </div>
                            <div class="form-floating mb-1">
                                <input type="number" class="form-control" placeholder="ccCvv" name="ccCvv">
                                <label for="floatingInput">Card CVV</label>
                            </div>
                            <div class="d-flex gap-1 w-100">
                                <div class="form-floating mb-1">
                                    <select class="form-control" name="ccMonth">
                                        <option value="01">Januari</option>
                                        <option value="02">Februari</option>
                                        <option value="03">Maret</option>
                                        <option value="04">April</option>
                                        <option value="05">Mei</option>
                                        <option value="06">Juni</option>
                                        <option value="07">Juli</option>
                                        <option value="08">Agustus</option>
                                        <option value="09">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">Nopember</option>
                                        <option value="12">Desember</option>
                                    </select>
                                    <label for="floatingInput">Expire Month</label>
                                </div>
                                <div class="form-floating mb-1">
                                    <input type="number" class="form-control" placeholder="ccCvv" name="ccYear">
                                    <label for="floatingInput">Expire Year</label>
                                </div>
                            </div>
                            <input type="text" name="tokencc" class="d-none">
                            <button type="button" class="btn btn-primary1" onclick="verifKartu(event)">Verifikasi
                                Kartu</button>
                        </div>
                        <hr>
                        <p>Rekening Bank</p>
                        <div class="container-pembayaran">
                            <input type="radio" name="pembayaran" id="pembayaran12" value="rekening">
                            <label for="pembayaran12" class="item-logo-pembayaran"><img src="/img/pembayaran/bri.webp"
                                    alt=""></label>
                        </div>
                        <hr>
                        <table class="mb-3">
                            <tbody>
                                <tr>
                                    <td>Total Pembelian</td>
                                    <td class="fw-bold ps-4 text-right">: Rp
                                        <?= number_format($total - $diskonVoucher - $potonganPreorder - ($usepoin ? $poin : 0) + ($gantiKaca ? $totalkaca : 0), 0, ",", "."); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Biaya Admin</td>
                                    <td id="biaya-admin" class="fw-bold ps-4 text-right">: Rp
                                        <?= number_format($biayaAdmin, 0, ",", "."); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <hr class="my-1">
                                    </td>
                                </tr>
                                <tr>
                                    <td>Total Bayar</td>
                                    <td id="biaya-total" class="fw-bold ps-4 text-right" style="color: var(--hijau);">:
                                        Rp
                                        <?= number_format($total - $diskonVoucher - $potonganPreorder - ($usepoin ? $poin : 0) + $biayaAdmin + ($gantiKaca ? $totalkaca : 0), 0, ",", "."); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <script>
                        const biayaAdminElm = document.getElementById('biaya-admin');
                        const biayaTotalElm = document.getElementById('biaya-total');
                        const alertCcElm = document.getElementById('alert-cc');
                        const radioPembayaranElm = document.querySelectorAll('input[name="pembayaran"]');
                        radioPembayaranElm.forEach(elm => {
                            elm.addEventListener('change', (e) => {
                                alertCcElm.classList.add('d-none')
                                const pembayaranSelected = e.target.value
                                const containerFormCCElm = document.getElementById('container-form-cc')
                                if (pembayaranSelected == 'card') {
                                    containerFormCCElm.classList.remove('d-none')
                                } else {
                                    containerFormCCElm.classList.add('d-none')
                                }

                                (async () => {
                                    const bodyFetchBiayaaAdmin = {
                                        bank: pembayaranSelected,
                                        nominal: <?= $total - $diskonVoucher - $potonganPreorder - ($usepoin ? $poin : 0) + ($gantiKaca ? $totalkaca : 0); ?> + Number(window.selectedShippingCost || 0)
                                    }

                                    try {
                                        const fetchBiayaAdmin = await fetch('/getadminfee', {
                                            method: 'post',
                                            headers: {
                                                'Content-type': 'application/json'
                                            },
                                            body: JSON.stringify(bodyFetchBiayaaAdmin)
                                        })
                                        if (!fetchBiayaAdmin.ok) throw new Error(fetchBiayaAdmin.statusText)

                                        const fetchBiayaAdminJson = await fetchBiayaAdmin.json()
                                        const biayaAdmin = Number(fetchBiayaAdminJson.biayaAdmin ?? 0)
                                        biayaAdminElm.innerHTML =
                                            `: Rp ${biayaAdmin.toLocaleString("id-ID")}`
                                        biayaTotalElm.innerHTML =
                                            `: Rp ${(biayaAdmin + bodyFetchBiayaaAdmin.nominal).toLocaleString("id-ID")}`
                                    } catch (error) {
                                        console.error('fetch biaya admin error:', error)
                                        alertCcElm.classList.remove('d-none')
                                        alertCcElm.classList.remove('alert-success')
                                        alertCcElm.classList.add('alert-danger')
                                        alertCcElm.innerHTML = 'Gagal menghitung biaya admin. Silakan coba lagi.'
                                    }
                                })()
                            })
                        })

                        const tokenCCElm = document.querySelector('input[name="tokencc"]');
                        const ccNumber = document.querySelector('input[name="ccNumber"]');
                        const ccCvv = document.querySelector('input[name="ccCvv"]');
                        const ccMonth = document.querySelector('select[name="ccMonth"]');
                        const ccYear = document.querySelector('input[name="ccYear"]');

                        function verifKartu(e) {
                            var card = {
                                card_number: ccNumber.value,
                                card_cvv: ccCvv.value,
                                card_exp_month: ccMonth.value,
                                card_exp_year: ccYear.value,
                            }
                            var options = {
                                onSuccess: function(response) {
                                    alertCcElm.classList.remove('d-none')
                                    alertCcElm.classList.add('alert-success')
                                    alertCcElm.classList.remove('alert-danger')
                                    alertCcElm.innerHTML = response.status_message

                                    e.target.innerHTML = 'Ubah'
                                    e.target.setAttribute('onclick', "reloadPage(event)")

                                    ccNumber.setAttribute('disabled', true);
                                    ccCvv.setAttribute('disabled', true);
                                    ccMonth.setAttribute('disabled', true);
                                    ccYear.setAttribute('disabled', true);
                                    tokenCCElm.value = response.token_id;
                                    console.log(response)
                                },
                                onFailure: function(response) {
                                    alertCcElm.classList.remove('d-none')
                                    alertCcElm.classList.remove('alert-success')
                                    alertCcElm.classList.add('alert-danger')
                                    alertCcElm.innerHTML = response.status_message

                                    e.target.innerHTML = 'Ubah'
                                    e.target.setAttribute('onclick', "reloadPage(event)")

                                    ccNumber.setAttribute('disabled', true);
                                    ccCvv.setAttribute('disabled', true);
                                    ccMonth.setAttribute('disabled', true);
                                    ccYear.setAttribute('disabled', true);
                                    console.log(response)
                                }
                            }

                            MidtransNew3ds.getCardToken(card, options);
                        }

                        function reloadPage(e) {
                            alertCcElm.classList.add('d-none')

                            e.target.innerHTML = 'Verifikasi Kartu'
                            e.target.setAttribute('onclick', "verifKartu(event)")

                            ccNumber.removeAttribute('disabled');
                            ccCvv.removeAttribute('disabled');
                            ccMonth.removeAttribute('disabled');
                            ccYear.removeAttribute('disabled');
                            tokenCCElm.value = '';
                        }
                        </script>
                        <button type="submit" class="btn btn-primary1 w-100">Lanjut bayar</button>
                    </div>
                </div>
            </div>
            <div class="checkout-layout mt-3">
                <div class="checkout-recipient-column">
                    <?php if ($msg) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $msg; ?>
                    </div>
                    <?php } ?>
                    <section class="checkout-section checkout-customer">
                        <h2 class="checkout-section-title">Informasi pemesan</h2>
                        <?php if ($user['email'] != 'tamu') { ?>
                        <p class="mb-0">Email : <?= $user['email']; ?></p>
                        <?php } else { ?>
                        <div class="form-floating mb-1">
                            <input type="email" class="form-control" placeholder="Email" id="checkout-email" name="emailPem" required
                                value="<?= session()->getFlashdata('emailPem') ? session()->getFlashdata('emailPem') : ''; ?>">
                            <label for="checkout-email">Email</label>
                        </div>
                        <?php } ?>
                    </section>
                    <section class="checkout-section checkout-address">
                        <h2 class="checkout-section-title">Alamat penerima</h2>
                        <?= $this->include('pages/partials/checkoutAddressBook'); ?>
                        <div class="form-floating mb-1">
                            <input type="text" class="form-control" placeholder="Email" id="checkout-name" name="nama" required
                                value="<?= esc($user['nama']); ?>">
                            <label for="checkout-name">Nama Lengkap</label>
                        </div>
                        <div class="form-floating mb-1">
                            <input type="number" class="form-control" placeholder="Nomor Handphone" id="checkout-phone" name="nohp" required
                                value="<?= esc($user['nohp']); ?>">
                            <label for="checkout-phone">No. HP</label>
                        </div>
                        <div class="form-alamat d-flex mb-1 gap-1">
                            <div class="form-floating w-50">
                                <select class="form-select" name="provinsi" id="checkout-province">
                                    <option value="">-- Pilih provinsi --</option>
                                    <?php foreach ($provinsi as $p) { ?>
                                    <option value="<?= $p['id']; ?>-<?= $p['label']; ?>" <?= (!empty($user['alamat'])
                                                && isset($user['alamat']['prov_id'])
                                                && isset($p['id'])
                                                && (string)$p['id'] === (string)$user['alamat']['prov_id'])
                                                ? 'selected' : ''; ?>>
                                        <?= $p['label']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <label for="checkout-province">Provinsi</label>
                            </div>
                            <div class="form-floating w-50">
                                <select class="form-select" name="kota" id="checkout-city">
                                    <option value="">-- Pilih kota --</option>
                                    <?php foreach ($kabupaten as $p) {
                                        $id   = $p['city_id']  ?? $p['id']    ?? '';
                                        $name = $p['city_name']?? $p['label'] ?? '';
                                        $name = explode('/', (string)$name)[0];
                                        $isSelected = !empty($user['alamat']['kab_id']) && (string)$id === (string)$user['alamat']['kab_id'];
                                        ?>
                                    <option value="<?= $id; ?>-<?= $name; ?>" <?= $isSelected ? 'selected' : ''; ?>>
                                        <?= $p['city_name'] ?? $p['label'] ?? $name; ?>
                                    </option>
                                    <?php } ?>

                                </select>
                                <label for="checkout-city">Kabupaten/Kota</label>
                            </div>
                        </div>
                        <div class="form-alamat d-flex mb-1 gap-1">
                            <div class="form-floating w-50">
                                <select class="form-select" name="kecamatan" id="checkout-district">
                                    <option selected value="">-- Pilih kecamatan --</option>
                                    <?php foreach ($kecamatan as $p) {
                                        $id   = $p['subdistrict_id']   ?? $p['id']    ?? '';
                                        $name = $p['subdistrict_name'] ?? $p['label'] ?? '';
                                        $name = explode('/', (string)$name)[0];
                                        $isSelected = !empty($user['alamat']['kec_id']) && (string)$id === (string)$user['alamat']['kec_id'];
                                        ?>
                                    <option value="<?= $id; ?>-<?= $name; ?>" <?= $isSelected ? 'selected' : ''; ?>>
                                        <?= $p['subdistrict_name'] ?? $p['label'] ?? $name; ?>
                                    </option>
                                    <?php } ?>

                                </select>
                                <label for="checkout-district">Kecamatan</label>
                            </div>
                            <div class="form-floating w-50">
                                <select class="form-select" name="kodepos" id="checkout-village">
                                    <option value="">-- Pilih Desa --</option>
                                    <?php foreach ($desa as $p) {
                                    $name = $p['DesaKelurahan'] ?? $p['label'] ?? '';
                                    $name = ucwords(strtolower(explode('/', (string)$name)[0]));
                                    $pos  = $p['KodePos'] ?? $p['kodepos'] ?? '';
                                    $isSelected = !empty($user['alamat']['desa']) && ($name === $user['alamat']['desa']);
                                    ?>
                                    <option value="<?= $name; ?>-<?= $pos; ?>" <?= $isSelected ? 'selected' : ''; ?>>
                                        <?= $p['DesaKelurahan'] ?? $p['label'] ?? $name; ?>
                                    </option>
                                    <?php } ?>

                                </select>
                                <label for="checkout-village">Desa/Kelurahan</label>
                            </div>
                        </div>
                        <div class="form-alamat form-floating mb-1">
                            <input type="text" class="form-control" placeholder="Email" id="checkout-street" name="alamat_add" required
                                value="<?= esc($user['alamat'] ? $user['alamat']['add'] : ''); ?>">
                            <label for="checkout-street">Jalan, Nomor Rumah, RT-RW</label>
                            <div class="invalid-feedback">Tidak boleh mengandung karakter /</div>
                        </div>
                        <div class="form-alamat d-none">
                            <fieldset>
                                <div class="form-floating mb-1">
                                    <textarea type="text" class="form-control" placeholder="Email" name="alamat"
                                        required
                                        style="height: fit-content;" id="checkout-full-address"><?= esc($user['alamat'] ? $user['alamat']['alamat'] : ''); ?></textarea>
                                    <label for="checkout-full-address">Alamat Lengkap</label>
                                </div>
                            </fieldset>
                        </div>
                        <p id="peringatan-lokasi" class="my-2 text-secondary" style="display: none;">*Untuk alamat
                            pengiriman di luar pulau Jawa, Madura, Bali, dimohon untuk menghubungi <a
                                href="https://wa.me/<?= LUNAREA_CS_WHATSAPP_E164; ?>" style="color: var(--hijau);"
                                class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Customer
                                Service kami</a> setelah Anda melakukan pemesanan</p>
                    </section>
                    <section class="checkout-section checkout-note">
                        <h2 class="checkout-section-title">Catatan pesanan <span>(opsional)</span></h2>
                        <div class="form-floating mb-1">
                            <input type="text" class="form-control" id="checkout-note" placeholder="Pesan untuk toko" name="note">
                            <label for="checkout-note">Pesan untuk toko</label>
                        </div>
                    </section>
                    <section class="checkout-section checkout-delivery" aria-label="Pilihan pengiriman">
                    <div class="checkout-shipping-choices">
                        <div class="d-flex justify-content-between align-items-start gap-3 shipping-request-header">
                            <div>
                                <h2 class="checkout-section-title">Metode pengiriman</h2>
                                <p class="m-0 text-secondary" role="status" aria-live="polite" id="shipping-helper" style="font-size: small;">Lengkapi alamat untuk melihat pilihan pengiriman.</p>
                            </div>
                            <button class="btn btn-sm btn-outline-success" type="button" onclick="loadShippingQuotes()">Cek Ongkir</button>
                        </div>
                        <div id="shipping-options" class="shipping-options-list mt-2"></div>
                    </div>
                    <details class="checkout-shipping-audit my-3">
                        <summary class="shipping-audit-toggle">
                            <span>
                                <svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3v18M4 21h16M3 7h18M6 7l-4 8h8L6 7Zm12 0-4 8h8l-4-8Z"/></svg>
                                <b>Detail berat untuk ongkir</b>
                            </span>
                            <small><?= number_format(((int)($shippingWeightGrams ?? 1000)) / 1000, 2, ',', '.'); ?> kg untuk estimasi ongkir</small>
                        </summary>
                        <div class="shipping-audit-body">
                            <p class="text-secondary small mb-2">Estimasi ongkir menggunakan total berat produk sesuai jumlah pesanan. Periksa rincian berat dan dimensi produk di bawah ini.</p>
                            <?php foreach (($shippingWeightDetails ?? []) as $weightItem) { ?>
                                <div class="shipping-weight-row">
                                    <div>
                                        <strong><?= esc($weightItem['name']); ?></strong>
                                        <small><?= esc($weightItem['variant']); ?> · Qty <?= (int)$weightItem['quantity']; ?> · Dimensi <?= esc($weightItem['dimension']); ?> cm</small>
                                    </div>
                                    <span><?= number_format((float)$weightItem['weight_kg'], 2, ',', '.'); ?> kg x <?= (int)$weightItem['quantity']; ?> = <b><?= number_format((float)$weightItem['total_weight_kg'], 2, ',', '.'); ?> kg</b></span>
                                </div>
                            <?php } ?>
                        </div>
                    </details>
                    </section>
                </div>

                <aside class="checkout-summary" aria-labelledby="checkout-summary-title">
                    <h2 class="checkout-section-title" id="checkout-summary-title">Ringkasan pesanan</h2>
                    <?php if (session()->get('email') != 'tamu') { ?>
                    <button type="button" onclick="openRedeem()" class="py-2 px-3 mb-1 d-flex gap-1 tmb-input-redeem">
                        <svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 6h18v4a2 2 0 0 0 0 4v4H3v-4a2 2 0 0 0 0-4V6Zm12 0v2m0 3v2m0 3v2"/></svg>
                        <span>Klaim kode voucher</span>
                    </button>
                    <?php if (count($voucher) > 0) { ?>
                    <div id="tombol-pilih-kurir"
                        class="tombol-pilih-kurir mb-1<?= $voucherSelected ? ' active' : ''; ?>"
                        onclick="pilihVoucher()">
                        <?php if ($voucherSelected) { ?>
                        <div>
                            <h5 class="m-0"><?= $voucherSelected['nama']; ?></h5>
                            <p class="m-0" style="font-size: small;"><?= $voucherSelected['keterangan'] ?></p>
                            <?php if (!empty($voucherSelected['max_potongan'])) { ?>
                            <p class="m-0 text-secondary" style="font-size: small;">Maks. potongan Rp <?= number_format((int)$voucherSelected['max_potongan'], 0, ",", "."); ?></p>
                            <?php } ?>
                            <?php if (!empty($voucherSelected['tidak_gabung_voucher_baru'])) { ?>
                            <p class="m-0 text-secondary" style="font-size: small;">Tidak digabung dengan voucher pengguna baru</p>
                            <?php } ?>
                        </div>
                        <svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m9 5 7 7-7 7"/></svg>
                        <?php } else { ?>
                        <p class="m-0 fw-bold">Pakai Voucher</p>
                        <svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m9 5 7 7-7 7"/></svg>
                        <?php } ?>
                    </div>
                    <div class="container-pilih-voucher mb-2">
                        <?php foreach ($voucher as $v) { ?>
                        <a class="item-voucher <?= $activeVoucher == $v['id'] ? 'active' : ''; ?>"
                            href="/<?= $activeVoucher == $v['id'] ? 'cancelvoucher' : 'usevoucher'; ?>/<?= $v['id']; ?>">
                            <div>
                                <h5 class="m-0"><?= $v['nama']; ?></h5>
                                <p class="m-0" style="font-size: small;"><?= $v['keterangan'] ?></p>
                                <?php if (!empty($v['max_potongan'])) { ?>
                                <p class="m-0 text-secondary" style="font-size: small;">Maks. potongan Rp <?= number_format((int)$v['max_potongan'], 0, ",", "."); ?></p>
                                <?php } ?>
                                <?php if (!empty($v['tidak_gabung_voucher_baru'])) { ?>
                                <p class="m-0 text-secondary" style="font-size: small;">Tidak digabung dengan voucher pengguna baru</p>
                                <?php } ?>
                            </div>
                            <?php if ($activeVoucher == $v['id']) { ?>
                            <div><i class="material-icons">close</i></div>
                            <?php } ?>
                        </a>
                        <?php } ?>
                    </div>
                    <script>
                    const containerPilihVoucherElm = document.querySelector('.container-pilih-voucher');
                    let bukaContainerPilihVoucher = false;
                    const tombolPilihKurirElm = document.getElementById('tombol-pilih-kurir');

                    function pilihVoucher() {
                        if (bukaContainerPilihVoucher) {
                            containerPilihVoucherElm.classList.remove('buka');
                            bukaContainerPilihVoucher = false
                            tombolPilihKurirElm.classList.remove('show');
                        } else {
                            containerPilihVoucherElm.classList.add('buka');
                            bukaContainerPilihVoucher = true
                            tombolPilihKurirElm.classList.add('show');
                        }
                    }
                    </script>
                    <?php } else { ?>
                    <div id="tombol-pilih-kurir" class="tombol-pilih-kurir mb-2" onclick="pilihVoucher()">
                        <p class="m-0 fw-bold">Pakai Voucher</p>
                        <svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m9 5 7 7-7 7"/></svg>
                    </div>
                    <script>
                    let bukaContainerPilihVoucher1 = false;
                    const tombolPilihKurirElm1 = document.getElementById('tombol-pilih-kurir');

                    function pilihVoucher() {
                        if (bukaContainerPilihVoucher1) {
                            bukaContainerPilihVoucher1 = false
                            tombolPilihKurirElm1.classList.remove('show');
                        } else {
                            bukaContainerPilihVoucher1 = true
                            tombolPilihKurirElm1.classList.add('show');
                        }
                    }
                    </script>
                    <?php }
                    } else { ?>
                    <a href="/hapuslocalstorage/<?= base64_encode('/keluar/regist'); ?>"
                        class="py-2 px-3 mb-1 d-flex gap-1 tmb-input-redeem">
                        <svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 6h18v4a2 2 0 0 0 0 4v4H3v-4a2 2 0 0 0 0-4V6Zm12 0v2m0 3v2m0 3v2"/></svg>
                        <p class="m-0">Daftar member untuk menggunakan voucher</p>
                    </a>
                    <?php } ?>
                    <div class="checkout-cart-preview">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="checkout-cart-title">Produk pesanan</h3>
                            <style>
                            .go-cart i {
                                font-size: 21px;
                                color: var(--hijau);
                            }

                            .go-cart:hover i {
                                color: #1a1a1a;
                            }

                            @media (max-width: 600px) {
                                .go-cart i {
                                    font-size: 17px;
                                }
                            }
                            </style>
                            <a href="/cart" style="text-decoration: none;" class="go-cart" aria-label="Ubah isi keranjang"><svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m15 5 4 4M4 20l4-1L20 7a2 2 0 0 0-4-4L4 15v5Z"/></svg></a>
                        </div>
                        <hr class="mb-1 mt-1">
                        <table class="table table-borderless m-0 checkout-products-table">
                            <tbody>
                                <?php foreach ($produk as $index => $p) { ?>
                                <tr>
                                    <td>
                                        <p class="mb-0 checkout-item-name"><?= esc($p['nama']); ?></p>
                                        <p class="m-0 text-secondary checkout-item-variant"><?= esc($keranjang[$index]['varian']); ?>
                                        </p>
                                    </td>
                                    <td>
                                        <p class="mb-0 checkout-item-quantity">&times;<?= $jumlah[$index]; ?></p>
                                    </td>
                                    <td class="text-end">
                                        <p class="mb-0 checkout-product-price">Rp
                                            <?php
                                                if ($p['diskon']) {
                                                    $persen = (100 - $p['diskon']) / 100;
                                                    $hasil = $persen * $p['harga'];
                                                    echo number_format($hasil, 0, ",", ".");
                                                } else {
                                                    $hasil = $p['harga'];
                                                    echo number_format($p['harga'], 0, ",", ".");
                                                }
                                                ?></p>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-top mt-3 checkout-total-row">
                        <p class="my-2">Subtotal:</p>
                        <p class="my-2"><b>Rp <?= number_format($subtotal, 0, ",", "."); ?></b></p>
                    </div>
                    <?php if ($diskonVoucher > 0) { ?>
                    <div class="d-flex justify-content-between border-bottom checkout-total-row">
                        <p class="my-2">Diskon Voucher:</p>
                        <p class="my-2"><b>- Rp <?= number_format($diskonVoucher, 0, ",", "."); ?></b></p>
                    </div>
                    <?php } ?>
                    <?php if ($totalkaca > 0) { ?>
                    <a href="/gantikaca/<?= $gantiKaca ? 'false' : 'true' ?>"
                        class="d-flex justify-content-between border-bottom checkout-total-row"
                        style="color: <?= $gantiKaca ? 'var(--hijau)' : 'gray'; ?>;">
                        <div class="d-flex gap-2 align-items-center">
                            <svg class="checkout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 4h16v16H4V4Z"/><?php if ($gantiKaca) { ?><path d="m7 12 3 3 7-7"/><?php } ?></svg>
                            <p class="my-2">Proteksi Kaca:</p>
                        </div>
                        <p class="my-2"><b>Rp <?= number_format($totalkaca, 0, ",", "."); ?></b></p>
                    </a>
                    <?php } ?>
                    <!-- <div class="d-flex justify-content-between border-bottom checkout-total-row">
                        <p class="my-2">Biaya Admin:</p>
                        <p class="my-2"><b>Rp 5.000</b></p>
                    </div> -->
                    <?php if ($potonganPreorder > 0) { ?>
                    <div class="d-flex justify-content-between border-bottom checkout-total-row">
                        <p class="my-2">Potongan Preorder:</p>
                        <p class="my-2"><b id="total-semua">- Rp
                                <?= number_format($potonganPreorder, 0, ",", "."); ?></b>
                        </p>
                    </div>
                    <?php } ?>
                    <?php if ($poin > 0) { ?>
                    <div class="d-flex justify-content-between border-bottom checkout-total-row">
                        <a href="/point/<?= $usepoin ? 'cancel' : 'use'; ?>"
                            class="btn-use-point <?= $usepoin ? 'use' : ''; ?>">
                            <?php if ($usepoin) { ?>
                            <div class="d-flex gap-2 align-items-center">
                                <i class="material-icons" style="font-size: small;">close</i>
                                <p class="my-2">Luna point:</p>
                            </div>
                            <p class="my-2"><b>- Rp
                                    <?= number_format($poin, 0, ",", "."); ?></b>
                            </p>
                            <?php } else { ?>
                            <p class="my-2 text-center w-100">Pakai Luna Point?</p>
                            <?php } ?>
                        </a>
                    </div>
                    <?php } ?>
                    <div class="d-flex justify-content-between border-bottom checkout-total-row">
                        <p class="my-2">Ongkir:</p>
                        <p class="my-2"><b id="shipping-cost-label">Rp 0</b></p>
                    </div>
                    <div class="d-flex justify-content-between checkout-total-row checkout-final-total">
                        <p class="my-2">Total pesanan</p>
                        <p class="my-2" style="color: var(--hijau);"><b id="checkout-grand-total">Rp
                                <?= number_format($total - $diskonVoucher - $potonganPreorder - ($usepoin ? $poin : 0) + ($gantiKaca ? $totalkaca : 0), 0, ",", "."); ?></b>
                        </p>
                    </div>
                    <div class="checkout-consent">
                        <p>
                            <input type="checkbox" id="checkbox-syarat" style="display: inline;" class="me-2" required>
                            <label for="checkbox-syarat" style="display: inline;">Saya telah membaca dan menyetujui
                                segala <a href="/syarat-dan-ketentuan" style="color: var(--hijau);"
                                    class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Syarat
                                    & Ketentuan</a> serta <a href="/kebijakan-privasi" style="color: var(--hijau);"
                                    class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Kebijakan
                                    Privasi</a> yang berlaku</label>
                        </p>
                    </div>
                    <button id="btn-checkout" class="btn btn-primary1 disabled" type="button" disabled
                        onclick="openMetode()">Pilih pembayaran</button>
                    <p class="checkout-submit-note">Lengkapi data, pilih pengiriman, dan setujui ketentuan untuk melanjutkan.</p>
                </aside>
            </div>
        </form>
    </div>
</main>
<script>
const modalRedeemElm = document.getElementById('modal-redeem');

function openRedeem() {
    modalRedeemElm.classList.remove('d-none')
    modalRedeemElm.classList.add('d-flex')
}

function closeRedeem() {
    modalRedeemElm.classList.add('d-none')
    modalRedeemElm.classList.remove('d-flex')
}
</script>
<script>
const modalMetodeBayarElm = document.getElementById('modal-metode-bayar');

function openMetode() {
    const tokenInput = document.getElementById('shipping_quote_token');
    const helper = document.getElementById('shipping-helper');
    if (!tokenInput || !tokenInput.value) {
        if (helper) helper.innerHTML = 'Pilih metode pengiriman terlebih dahulu sebelum melanjutkan pembayaran.';
        const shippingBox = document.getElementById('shipping-options');
        if (shippingBox) shippingBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    modalMetodeBayarElm.classList.remove('d-none')
    modalMetodeBayarElm.classList.add('d-flex')
}

function closeMetode() {
    modalMetodeBayarElm.classList.add('d-none')
    modalMetodeBayarElm.classList.remove('d-flex')
}
</script>
<script>
const checkboxElm = document.getElementById('checkbox-syarat');
const formAlamatElm = document.querySelectorAll(".form-alamat")
const inputNamaPemElm = document.querySelector('input[name="namaPem"]');
const inputNohpPemElm = document.querySelector('input[name="nohpPem"]');
const inputEmailPemElm = document.querySelector('input[name="emailPem"]');
const inputNamaElm = document.querySelector('input[name="nama"]');
const inputNohpElm = document.querySelector('input[name="nohp"]');
const inputAlamatAddElm = document.querySelector('input[name="alamat_add"]');
const inputAlamatElm = document.querySelector('textarea[name="alamat"]');
const inputNoteElm = document.querySelector('input[name="note"]');
const provElm = document.querySelector('select[name="provinsi"]');
const kotaElm = document.querySelector('select[name="kota"]');
const kecElm = document.querySelector('select[name="kecamatan"]');
const kodeElm = document.querySelector('select[name="kodepos"]');
// const costElm = document.getElementById("total-pengiriman");
const totalElm = document.getElementById("checkout-grand-total") || document.getElementById("total-semua");
const subtotal = Number("<?= $subtotal; ?>");
const checkoutBaseTotal = Number("<?= $total - $diskonVoucher - $potonganPreorder - ($usepoin ? $poin : 0) + ($gantiKaca ? $totalkaca : 0); ?>");
window.selectedShippingCost = 0;
let email = "<?= session()->get('email') ?>";
let nama = "<?= session()->get('nama') ?>";
let nohp = "<?= session()->get('nohp') ?>";
function formatRupiah(value) {
    return `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
}

function updateCheckoutShippingTotal(cost) {
    window.selectedShippingCost = Number(cost || 0);
    const shippingCostLabel = document.getElementById('shipping-cost-label');
    if (shippingCostLabel) shippingCostLabel.innerHTML = formatRupiah(window.selectedShippingCost);
    if (totalElm) totalElm.innerHTML = formatRupiah(checkoutBaseTotal + window.selectedShippingCost);
    refreshCheckoutButtonState();

    const selectedPayment = document.querySelector('input[name="pembayaran"]:checked');
    if (selectedPayment) {
        selectedPayment.dispatchEvent(new Event('change', {
            bubbles: true
        }));
    }
}

function resetShippingSelection() {
    const tokenInput = document.getElementById('shipping_quote_token');
    const helper = document.getElementById('shipping-helper');
    const container = document.getElementById('shipping-options');
    if (tokenInput) tokenInput.value = '';
    if (container) container.innerHTML = '';
    if (helper) helper.innerHTML = 'Alamat berubah. Klik cek ongkir untuk memperbarui pilihan pengiriman.';
    updateCheckoutShippingTotal(0);
    refreshCheckoutButtonState();
}

function selectedAddressPayload() {
    const splitValue = (value) => {
        const parts = String(value || '').split('-');
        return { id: parts[0] || '', label: parts.slice(1).join('-') || '' };
    };
    const prov = splitValue(provElm.value);
    const kab = splitValue(kotaElm.value);
    const kec = splitValue(kecElm.value);
    const kel = splitValue(kodeElm.value);
    return {
        provinsi_id: prov.id,
        kabupaten_id: kab.id,
        kecamatan_id: kec.id,
        kelurahan_id: kel.id,
        kodepos: kel.label
    };
}

async function loadShippingQuotes() {
    const helper = document.getElementById('shipping-helper');
    const container = document.getElementById('shipping-options');
    const tokenInput = document.getElementById('shipping_quote_token');
    if (!container || !helper || !tokenInput) return;

    tokenInput.value = '';
    updateCheckoutShippingTotal(0);
    container.innerHTML = '';

    const payload = selectedAddressPayload();
    if (!payload.provinsi_id || !payload.kabupaten_id || !payload.kecamatan_id) {
        helper.innerHTML = 'Alamat belum lengkap. Pilih provinsi, kota, kecamatan, dan desa terlebih dahulu.';
        return;
    }

    helper.innerHTML = 'Mengambil pilihan pengiriman...';
    try {
        const res = await fetch('/shipping/quote', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.message || 'Gagal mengambil ongkir');

        const quotes = json.data?.quotes || [];
        if (quotes.length === 0) {
            helper.innerHTML = json.data?.message || 'Ongkir perlu dikonfirmasi admin.';
            return;
        }

        helper.innerHTML = 'Pilih salah satu metode pengiriman. Pilihan termurah tampil di atas.';
        container.innerHTML = quotes.map((q, idx) => {
            const escapeShippingText = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
            const courierCode = String(q.courier_code || '').toLowerCase();
            const courierLabels = { jne: 'JNE', tiki: 'TIKI', jnt: 'J&T', jntcargo: 'J&T Cargo', sicepat: 'SiCepat', pos: 'POS', sentral: 'Sentral Cargo', internal: 'Jasa Kirim Toko' };
            const label = escapeShippingText(courierLabels[courierCode] || (courierCode ? courierCode.toUpperCase() : (q.courier_name || 'Pengiriman')));
            const service = q.service_code || q.service_name;
            const desc = service ? `<small class="text-secondary d-block">${escapeShippingText(service)}</small>` : '';
            const etd = q.etd ? `<small class="text-secondary d-block">Estimasi: ${escapeShippingText(q.etd)}</small>` : '';
            const badge = idx === 0 ? '<em>Termurah</em>' : '';
            return `<label class="shipping-option-card">
                <span class="shipping-option-main"><input type="radio" name="shipping_option_radio" value="${q.token}" data-cost="${q.cost}" ${idx === 0 ? 'checked' : ''}> <span><b>${label}</b>${desc}${etd}</span></span>
                <span class="shipping-option-price">${badge}${formatRupiah(q.cost)}</span>
            </label>`;
        }).join('');

        const radios = container.querySelectorAll('input[name="shipping_option_radio"]');
        radios.forEach((radio) => {
            radio.addEventListener('change', function() {
                tokenInput.value = this.value;
                updateCheckoutShippingTotal(Number(this.dataset.cost || 0));
            });
        });
        if (radios[0]) {
            tokenInput.value = radios[0].value;
            updateCheckoutShippingTotal(Number(radios[0].dataset.cost || 0));
        }
    } catch (error) {
        console.error(error);
        helper.innerHTML = 'Pengiriman belum bisa dihitung otomatis. Silakan coba lagi atau hubungi admin.';
        refreshCheckoutButtonState();
    }
}
let prov_kab_kec_desa = <?= json_encode([
    !empty($user['alamat']) ? $user['alamat']['prov_id'] . '-' . $user['alamat']['prov'] : '',
    !empty($user['alamat']) ? $user['alamat']['kab_id'] . '-' . $user['alamat']['kab'] : '',
    !empty($user['alamat']) ? $user['alamat']['kec_id'] . '-' . $user['alamat']['kec'] : '',
    !empty($user['alamat']) ? $user['alamat']['desa'] . '-' . $user['alamat']['kodepos'] : '',
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
let isiAlamat = <?= json_encode(array_map(static fn($key) => $user['alamat'][$key] ?? '', ['add', 'desa', 'kec', 'kab', 'prov', 'kodepos']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
let isEditAlamat = <?= $user['alamat'] ? 'false' : 'true'; ?>;

function titleCase(str) {
    var splitStr = str.toLowerCase().split(' ');
    for (var i = 0; i < splitStr.length; i++) {
        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
    }
    return splitStr.join(' ');
}

const btnCheckoutElm = document.getElementById('btn-checkout')

function isShippingSelected() {
    const tokenInput = document.getElementById('shipping_quote_token');
    return !!(tokenInput && tokenInput.value);
}

function isBasicCheckoutFormValid() {
    if (email == 'tamu') {
        return inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '';
    }

    return inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '';
}

function refreshCheckoutButtonState() {
    if (!btnCheckoutElm) return;
    const isReady = isBasicCheckoutFormValid() && isShippingSelected();
    btnCheckoutElm.classList.toggle('disabled', !isReady);
    btnCheckoutElm.disabled = !isReady;
    btnCheckoutElm.title = isReady ? '' : 'Lengkapi data dan pilih pengiriman terlebih dahulu.';
}

document.querySelectorAll('#form-checkout input, #form-checkout select, #form-checkout textarea').forEach((field) => {
    field.addEventListener('input', () => setTimeout(refreshCheckoutButtonState, 0));
    field.addEventListener('change', () => setTimeout(refreshCheckoutButtonState, 0));
});
refreshCheckoutButtonState();

async function getKota(idprov) {
    kotaElm.innerHTML = '<option value="">Loading kota…</option>';
    try {
        const res = await fetch(`/getkota/${idprov}`);
        if (!res.ok) throw new Error(res.statusText);
        const payload = await res.json();
        // console.log("getKota payload:", payload);
        const list =
            Array.isArray(payload) ? payload :
            Array.isArray(payload.label) ? payload.label :
            Array.isArray(payload.results) ? payload.results :
            Array.isArray(payload.data?.results) ? payload.data.results : [];
        kotaElm.innerHTML = '<option value="">-- Pilih kota --</option>';
        if (list.length === 0) {
            kotaElm.innerHTML = '<option value="">(Tidak ada kota)</option>';
            return;
        }
        list.forEach(item => {
            const id = item.city_id ?? item.id;
            const nama = (item.city_name?.split("/")[0] ?? item.label).trim();
            const type = item.type === 'Kota' ? ' Kota' : '';
            const opt = document.createElement("option");
            opt.value = `${id}-${nama}`;
            opt.textContent = `${nama}${type}`;
            kotaElm.appendChild(opt);
        });

    } catch (err) {
        console.error("getKota error:", err);
        kotaElm.innerHTML = '<option value="">(Gagal memuat kota)</option>';
    }
}

async function getKec(idkota) {
    kecElm.innerHTML = '<option value="">Loading kecamatan…</option>';
    kodeElm.innerHTML = '<option value="">-- Pilih Desa --</option>';
    try {
        const res = await fetch(`/getkec/${idkota}`);
        if (!res.ok) throw new Error(res.statusText);
        const payload = await res.json();
        // console.log("getKec payload:", payload);
        const list =
            Array.isArray(payload) ? payload :
            Array.isArray(payload.rajaongkir?.results) ? payload.rajaongkir.results :
            Array.isArray(payload.results) ? payload.results :
            Array.isArray(payload.data?.results) ? payload.data.results : [];
        kecElm.innerHTML = '<option value="">-- Pilih kecamatan --</option>';
        if (list.length === 0) {
            kecElm.innerHTML = '<option value="">(Tidak ada kecamatan)</option>';
            return;
        }
        list.forEach(el => {
            const id = el.id;
            const nama = (el.subdistrict_name ?? el.label).split("/")[0].trim();
            const opt = document.createElement("option");
            opt.value = `${id}-${nama}`;
            opt.textContent = nama;
            kecElm.appendChild(opt);
        });

    } catch (err) {
        console.error("getKec error:", err);
        kecElm.innerHTML = '<option value="">(Gagal memuat kecamatan)</option>';
    }
}

async function getKode(kecId) {
    kodeElm.innerHTML = '<option value="">Loading desa…</option>';
    try {
        const res = await fetch(`/getkode/${kecId}`);
        if (!res.ok) throw new Error(res.statusText);
        const payload = await res.json();
        // console.log("getKode payload:", payload);
        const list =
            Array.isArray(payload) ? payload :
            Array.isArray(payload.results) ? payload.results :
            Array.isArray(payload.data) ? payload.data : [];
        kodeElm.innerHTML = '<option value="">-- Pilih Desa --</option>';
        if (list.length === 0) {
            kodeElm.innerHTML = '<option value="">(Tidak ada desa)</option>';
            return;
        }
        list.forEach(el => {
            const rawName = el.DesaKelurahan ?? el.label;
            const nama = titleCase(rawName).split("/")[0].trim();
            const pos = el.KodePos ?? el.kodepos;
            const opt = document.createElement("option");
            opt.value = `${nama}-${pos}`;
            opt.textContent = nama;
            kodeElm.appendChild(opt);
        });

    } catch (err) {
        console.error("getKode error:", err);
        kodeElm.innerHTML = '<option value="">(Gagal memuat desa)</option>';
    }
}


function generateAlamat(isi, posisi) {
    //posisi : 1.jln 2.desa 3.kec 4.kab 5.prov 6.kodepos
    isiAlamat[posisi - 1] = isi;
    const stringAlamat =
        `${isiAlamat[0]} ${isiAlamat[1]}, ${isiAlamat[2]}, ${isiAlamat[3]}, ${isiAlamat[4]} ${isiAlamat[5]}`
    inputAlamatElm.value = stringAlamat
}

provElm.addEventListener("change", (e) => {
    resetShippingSelection();
    generateAlamat(e.target.value.split("-")[1], 5);
    kotaElm.innerHTML = '<option value="">Loading..</option>'
    kecElm.innerHTML = '<option value="">-- Pilih kecamatan --</option>';
    kodeElm.innerHTML = '<option value="">-- Pilih Desa --</option>';
    // costElm.innerHTML = '-'
    // hasilApiKurir = [];
    // hasilApiKurirRO = [];
    // resetUIBtnPilihKurir();
    const valuenya = e.target.value.split("-");
    const idprov = Number(valuenya[0]);
    if (idprov > 0) {
        prov_kab_kec_desa[0] = e.target.value
        getKota(idprov)
    }

    const provJawaMaduraBali = ["Banten", "Jawa Barat", "DKI Jakarta", "Jawa Tengah", "DI Yogyakarta",
        "Jawa Timur", "Bali"
    ];
    if (!provJawaMaduraBali.includes(valuenya[1])) {
        document.getElementById("peringatan-lokasi").style.display = "block";
        triggerToast(
            'Untuk alamat pengiriman di luar pulau Jawa, Madura, Bali, dimohon untuk menghubungi <a href="https://api.whatsapp.com/send?phone=<?= LUNAREA_CS_WHATSAPP_E164; ?>&text=Hai%20CS%20*Lunarea*%2C%20saya%20mau%20membeli%20furniture....." style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Customer Service kami</a> setelah Anda melakukan pemesanan'
        );
    } else {
        document.getElementById("peringatan-lokasi").style.display = "none";
    }
    btnCheckoutElm.classList.add('disabled');
})
kotaElm.addEventListener("change", (e) => {
    resetShippingSelection();
    generateAlamat(e.target.value.split("-")[1], 4);
    kecElm.innerHTML = '<option value="">Loading..</option>'
    kodeElm.innerHTML = '<option value="">-- Pilih Desa --</option>';
    // costElm.innerHTML = '-'
    // hasilApiKurir = [];
    // hasilApiKurirRO = [];
    // resetUIBtnPilihKurir();
    const value = e.target.value.split("-")
    const idkota = Number(value[0])
    if (idkota > 0) {
        prov_kab_kec_desa[1] = e.target.value;
        getKec(idkota)
    }

    btnCheckoutElm.classList.add('disabled');
})
kecElm.addEventListener("change", (e) => {
    resetShippingSelection();
    const namaKec = e.target.value.split("-")[1];
    generateAlamat(namaKec, 3);
    kodeElm.innerHTML = '<option value="">Loading..</option>';
    const [idkec] = e.target.value.split("-");
    if (Number(idkec) > 0) {
        prov_kab_kec_desa[2] = e.target.value;
        getKode(idkec);
    }

    btnCheckoutElm.classList.add('disabled');
});
kodeElm.addEventListener("change", (e) => {
    resetShippingSelection();
    generateAlamat(e.target.value.split("-")[0], 2);
    generateAlamat(e.target.value.split("-")[1], 6);
    // kodeElm.innerHTML = '<option value="-1">Loading..</option>'
    // costElm.innerHTML = '-'
    // hasilApiKurir = [];
    // hasilApiKurirRO = [];
    // resetUIBtnPilihKurir();
    const value = e.target.value.split("-")
    const kodepos = Number(value[1])
    if (kodepos > 0) {
        prov_kab_kec_desa[3] = e.target.value;
    }

    if (email == 'tamu') {
        if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm
            .checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value !=
            '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
        else btnCheckoutElm.classList.add('disabled');
    } else {
        if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value !=
            '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value !=
            '') btnCheckoutElm.classList.remove('disabled');
        else btnCheckoutElm.classList.add('disabled');
    }
})

inputAlamatAddElm.addEventListener('input', (e) => {
    generateAlamat(e.target.value, 1);

    if (email == 'tamu') {
        if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm
            .checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value !=
            '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
        else btnCheckoutElm.classList.add('disabled');
    } else {
        if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value !=
            '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value !=
            '') btnCheckoutElm.classList.remove('disabled');
        else btnCheckoutElm.classList.add('disabled');
    }
})
</script>
<?php if (session()->get('email') == 'tamu') { ?>
<script>
checkboxElm.addEventListener("change", (e) => {
    if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm
        .checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' &&
        inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
    else btnCheckoutElm.classList.add('disabled');
})
// inputNamaPemElm.addEventListener("input", (e) => {
//     if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
//     else btnCheckoutElm.classList.add('disabled');
// })
// inputNohpPemElm.addEventListener("input", (e) => {
//     if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
//     else btnCheckoutElm.classList.add('disabled');
// })
inputEmailPemElm.addEventListener("input", (e) => {
    if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm
        .checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' &&
        inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
    else btnCheckoutElm.classList.add('disabled');
})
inputNamaElm.addEventListener("input", (e) => {
    if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm
        .checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' &&
        inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
    else btnCheckoutElm.classList.add('disabled');
})
inputNohpElm.addEventListener("input", (e) => {
    if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm
        .checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' &&
        inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
    else btnCheckoutElm.classList.add('disabled');
})
</script>
<?php } else { ?>
<script>
checkboxElm.addEventListener("change", (e) => {
    if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' &&
        kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '')
        btnCheckoutElm.classList.remove('disabled');
    else btnCheckoutElm.classList.add('disabled');
})
inputNamaElm.addEventListener("input", (e) => {
    if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' &&
        kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '')
        btnCheckoutElm.classList.remove('disabled');
    else btnCheckoutElm.classList.add('disabled');
})
inputNohpElm.addEventListener("input", (e) => {
    if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' &&
        kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '')
        btnCheckoutElm.classList.remove('disabled');
    else btnCheckoutElm.classList.add('disabled');
})
</script>
<?php } ?>
<style>
    .checkout-shipping-audit {
        border: 1px solid rgba(32, 126, 68, .14);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(237, 250, 241, .9), #fff);
        overflow: hidden;
    }

    .shipping-audit-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        cursor: pointer;
        list-style: none;
    }

    .shipping-audit-toggle::-webkit-details-marker {
        display: none;
    }

    .shipping-audit-toggle span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #20303d;
    }

    .shipping-audit-toggle .material-icons {
        color: var(--hijau);
        font-size: 20px;
    }

    .shipping-audit-toggle small {
        color: var(--hijau);
        font-weight: 800;
        white-space: nowrap;
    }

    .shipping-audit-body {
        border-top: 1px solid rgba(32, 126, 68, .12);
        padding: 12px 14px;
        display: grid;
        gap: 8px;
    }

    .shipping-weight-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .06);
    }

    .shipping-weight-row strong,
    .shipping-weight-row small {
        display: block;
    }

    .shipping-weight-row small {
        color: #66737f;
        margin-top: 2px;
    }

    .shipping-weight-row span {
        text-align: right;
        white-space: nowrap;
        color: #20303d;
    }

    .shipping-options-list {
        display: grid;
        gap: 8px;
        max-height: 260px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .shipping-option-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(32, 126, 68, .16);
        border-radius: 14px;
        background: #fff;
        cursor: pointer;
        transition: .18s ease;
    }

    .shipping-option-card:hover,
    .shipping-option-card:has(input:checked) {
        border-color: var(--hijau);
        box-shadow: 0 8px 20px rgba(32, 126, 68, .08);
        background: rgba(237, 250, 241, .8);
    }

    .shipping-option-main {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        min-width: 0;
    }

    .shipping-option-main b {
        display: block;
        line-height: 1.2;
    }

    .shipping-option-main small {
        font-size: 11px;
        line-height: 1.25;
    }

    .shipping-option-price {
        display: grid;
        justify-items: end;
        gap: 2px;
        color: var(--hijau);
        font-weight: 900;
        white-space: nowrap;
    }

    .shipping-option-price em {
        font-style: normal;
        font-size: 10px;
        color: #fff;
        background: var(--hijau);
        border-radius: 999px;
        padding: 2px 7px;
    }

    #btn-checkout:disabled {
        opacity: .55;
        cursor: not-allowed;
        pointer-events: auto;
    }

    @media (max-width: 575.98px) {
        .shipping-audit-toggle,
        .shipping-weight-row,
        .shipping-option-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .shipping-audit-toggle small,
        .shipping-weight-row span,
        .shipping-option-price {
            text-align: left;
            justify-items: start;
            white-space: normal;
        }

        .shipping-options-list {
            max-height: 320px;
        }
    }
</style>
<?= $this->endSection(); ?>
