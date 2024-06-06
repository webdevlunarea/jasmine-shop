<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <form class="w-100" id="form-checkout">
            <div class="baris-ke-kolom gap-5 mt-3">
                <div class="w-100">
                    <div class="pb-3 border-bottom">
                        <h5 class="mb-0">Informasi Pemesan</h5>
                        <?php if ($user['email'] != 'tamu') { ?>
                            <p class="mb-0">Email : <?= $user['email']; ?></p>
                        <?php } else { ?>
                            <div class="form-floating mb-1">
                                <input type="email" class="form-control" placeholder="Email" name="emailPem" required value="<?= session()->getFlashdata('emailPem') ? session()->getFlashdata('emailPem') : ''; ?>">
                                <label for="floatingInput">Email</label>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="py-3 border-bottom">
                        <h5 class="mb-2">Informasi Penerima</h5>
                        <div class="form-floating mb-1">
                            <input type="text" class="form-control" placeholder="Email" name="nama" required value="<?= $user['nama']; ?>">
                            <label for="floatingInput">Nama Lengkap</label>
                        </div>
                        <div class="form-floating mb-1">
                            <input type="number" class="form-control" placeholder="Nomor Handphone" name="nohp" required value="<?= $user['nohp']; ?>">
                            <label for="floatingInput">No. HP</label>
                        </div>
                        <div class="form-alamat d-flex mb-1 gap-1">
                            <div class="form-floating w-50">
                                <select class="form-select" aria-label="Default select example" name="provinsi">
                                    <option value="">-- Pilih provinsi --</option>
                                    <?php foreach ($provinsi as $p) { ?>
                                        <option value="<?= $p['province_id']; ?>-<?= $p['province']; ?>" <?= $user['alamat'] ? ($p['province_id'] == $user['alamat']['prov_id'] ? 'selected' : '') : ''; ?>><?= $p['province']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="floatingProvinsi">Provinsi</label>
                            </div>
                            <div class="form-floating w-50">
                                <select class="form-select" aria-label="Default select example" name="kota">
                                    <option value="">-- Pilih kota --</option>
                                    <?php foreach ($kabupaten as $p) { ?>
                                        <option value="<?= $p['city_id']; ?>-<?= explode("/", $p['city_name'])[0]; ?>" <?= $user['alamat'] ? ($p['city_id'] == $user['alamat']['kab_id'] ? 'selected' : '') : ''; ?>><?= $p['city_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="floatingProvinsi">Kabupaten/Kota</label>
                            </div>
                        </div>
                        <div class="form-alamat d-flex mb-1 gap-1">
                            <div class="form-floating w-50">
                                <select class="form-select" aria-label="Default select example" name="kecamatan">
                                    <option selected value="">-- Pilih kecamatan --</option>
                                    <?php foreach ($kecamatan as $p) { ?>
                                        <option value="<?= $p['subdistrict_id']; ?>-<?= explode("/", $p['subdistrict_name'])[0]; ?>" <?= $user['alamat'] ? ($p['subdistrict_id'] == $user['alamat']['kec_id'] ? 'selected' : '') : ''; ?>><?= $p['subdistrict_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="floatingProvinsi">Kecamatan</label>
                            </div>
                            <div class="form-floating w-50">
                                <select class="form-select" aria-label="Default select example" name="kodepos">
                                    <option value="">-- Pilih Desa --</option>
                                    <?php foreach ($desa as $p) { ?>
                                        <option value="<?= explode("/", ucwords(strtolower($p['DesaKelurahan'])))[0]; ?>-<?= $p['KodePos']; ?>" <?= $user['alamat'] ? (explode("/", ucwords(strtolower($p['DesaKelurahan'])))[0] == $user['alamat']['desa'] ? 'selected' : '') : ''; ?>><?= ucwords(strtolower($p['DesaKelurahan'])); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="floatingProvinsi">Desa/Kelurahan</label>
                            </div>
                        </div>
                        <div class="form-alamat form-floating mb-1">
                            <input type="text" class="form-control" placeholder="Email" name="alamat_add" required value="<?= $user['alamat'] ? $user['alamat']['add'] : ''; ?>">
                            <label for="floatingInput">Jalan, Nomor Rumah, RT-RW</label>
                            <div class="invalid-feedback">Tidak boleh mengandung karakter /</div>
                        </div>
                        <div class="form-alamat d-none">
                            <fieldset>
                                <div class="form-floating mb-1">
                                    <textarea type="text" class="form-control" placeholder="Email" name="alamat" required style="height: fit-content;"><?= $user['alamat'] ? $user['alamat']['alamat'] : ''; ?></textarea>
                                    <label for="floatingInput">Alamat Lengkap</label>
                                </div>
                            </fieldset>
                        </div>
                        <p id="peringatan-lokasi" class="my-2 text-secondary" style="display: none;">*Untuk alamat pengiriman di luar pulau Jawa, Madura, Bali, dimohon untuk menghubungi <a href="https://wa.me/+628112938160" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Customer Service kami</a> setelah Anda melakukan pemesanan</p>
                    </div>
                    <!--<div class="pt-3">-->
                    <!--    <h5 class="mb-2">Metode Pembayaran</h5>-->
                    <!--    <div class="container-pembayaran mb-1">-->
                    <!--        <input type="radio" checked name="pembayaran" id="pembayaran1" value="bca">-->
                    <!--        <label for="pembayaran1" class="item-logo-pembayaran"><img src="/img/pembayaran/bca.webp" alt=""></label>-->
                    <!--        <input type="radio" name="pembayaran" id="pembayaran2" value="bni">-->
                    <!--        <label for="pembayaran2" class="item-logo-pembayaran"><img src="/img/pembayaran/bni.webp" alt=""></label>-->
                    <!--        <input type="radio" name="pembayaran" id="pembayaran3" value="bri">-->
                    <!--        <label for="pembayaran3" class="item-logo-pembayaran"><img src="/img/pembayaran/bri.webp" alt=""></label>-->
                    <!--        <input type="radio" name="pembayaran" id="pembayaran4" value="mandiri">-->
                    <!--        <label for="pembayaran4" class="item-logo-pembayaran"><img src="/img/pembayaran/mandiri.webp" alt=""></label>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>

                <div style="width: 100%; max-width: 400px;">
                    <div>
                        <table class="table table-borderless">
                            <tbody>
                                <?php foreach ($produk as $index => $p) { ?>
                                    <tr>
                                        <td>
                                            <p class="mb-0"><?= $p['nama'] . " (" . $keranjang[$index]['varian'] . ")"; ?></p>
                                        </td>
                                        <td>
                                            <p class="mb-0"><?= $jumlah[$index]; ?></p>
                                        </td>
                                        <td class="text-end">
                                            <p class="mb-0">Rp
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
                    <div class="d-flex justify-content-between border-bottom border-top mt-3" style="gap: 10em;">
                        <p class="my-2">Subtotal:</p>
                        <p class="my-2"><b>Rp <?= number_format($subtotal, 0, ",", "."); ?></b></p>
                    </div>
                    <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                        <p class="my-2">Biaya Admin:</p>
                        <p class="my-2"><b>Rp 5.000</b></p>
                    </div>
                    <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                        <p class="my-2">Total:</p>
                        <p class="my-2"><b id="total-semua">Rp
                                <?= number_format($total, 0, ",", "."); ?></b>
                        </p>
                    </div>
                    <div class="mt-2">
                        <p>
                            <input type="checkbox" id="syarat" style="display: inline;" required>
                            <label for="syarat" style="display: inline;">Saya telah membaca dan menyetujui segala <a href="/syarat-dan-ketentuan" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Syarat & Ketentuan</a> serta <a href="/kebijakan-privasi" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Kebijakan Privasi</a> yang berlaku</label>
                        </p>
                    </div>
                    <button id="btn-checkout" class="btn btn-primary1 disabled" type="submit">Bayar</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    const checkboxElm = document.querySelector('input[type="checkbox"]');
    const formAlamatElm = document.querySelectorAll(".form-alamat")
    const inputNamaPemElm = document.querySelector('input[name="namaPem"]');
    const inputNohpPemElm = document.querySelector('input[name="nohpPem"]');
    const inputEmailPemElm = document.querySelector('input[name="emailPem"]');
    const inputNamaElm = document.querySelector('input[name="nama"]');
    const inputNohpElm = document.querySelector('input[name="nohp"]');
    const inputAlamatAddElm = document.querySelector('input[name="alamat_add"]');
    const inputAlamatElm = document.querySelector('textarea[name="alamat"]');
    const provElm = document.querySelector('select[name="provinsi"]');
    const kotaElm = document.querySelector('select[name="kota"]');
    const kecElm = document.querySelector('select[name="kecamatan"]');
    const kodeElm = document.querySelector('select[name="kodepos"]');
    // const costElm = document.getElementById("total-pengiriman");
    const totalElm = document.getElementById("total-semua");
    const subtotal = "<?= $subtotal; ?>";
    let email = "<?= session()->get('email') ?>";
    let nama = "<?= session()->get('nama') ?>";
    let nohp = "<?= session()->get('nohp') ?>";
    const beratTotal = Number("<?= $beratAkhir; ?>");
    const dimensiSemua = "<?= $dimensiSemua; ?>".split("-");
    let prov_kab_kec_desa = ["<?= $user['alamat'] ? $user['alamat']['prov_id'] . "-" . $user['alamat']['prov'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['kab_id'] . "-" . $user['alamat']['kab'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['kec_id'] . "-" . $user['alamat']['kec'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['desa'] . "-" . $user['alamat']['kodepos'] : ''; ?>"];
    const produk = JSON.parse(<?= json_encode($produkJson); ?>);
    const keranjang = JSON.parse(<?= json_encode($keranjangJson); ?>);
    let isiAlamat = ["<?= $user['alamat'] ? $user['alamat']['add'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['desa'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['kec'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['kab'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['prov'] : ''; ?>", "<?= $user['alamat'] ? $user['alamat']['kodepos'] : ''; ?>"]
    let isEditAlamat = <?= $user['alamat'] ? 'false' : 'true'; ?>;

    function titleCase(str) {
        var splitStr = str.toLowerCase().split(' ');
        for (var i = 0; i < splitStr.length; i++) {
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
        }
        return splitStr.join(' ');
    }

    const btnCheckoutElm = document.getElementById('btn-checkout')
    const formCheckoutElm = document.getElementById("form-checkout")
    formCheckoutElm.addEventListener('submit', (e) => {
        e.preventDefault();
        btnCheckoutElm.innerHTML = "Loading"
        const data = {
            email: email == 'tamu' ? inputEmailPemElm.value : email,
            nama: inputNamaElm.value,
            nohp: inputNohpElm.value,
            provinsi: provElm.value,
            kota: kotaElm.value,
            kecamatan: kecElm.value,
            kodepos: kodeElm.value,
            alamat: inputAlamatElm.value,
            alamat_add: inputAlamatAddElm.value,
            keranjang: JSON.stringify(keranjang)
        }
        async function getTokenMditrans() {
            const response = await fetch("actionpaysnap", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });
            const snaptoken = await response.json();
            console.log(snaptoken);
            btnCheckoutElm.innerHTML = "Bayar"
            window.snap.pay(snaptoken.token);
        }
        getTokenMditrans()
    })

    async function getKota(idprov) {
        const response = await fetch("getkota/" + idprov);
        const kota = await response.json();
        const hasil = kota.rajaongkir.results;
        // console.log(hasil)
        kotaElm.innerHTML = '<option value="">-- Pilih kota --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = element.city_id + "-" + element.city_name.split("/")[0]
            optElm.innerHTML = element.type == 'Kota' ? `${element.city_name} Kota` : element.city_name
            kotaElm.appendChild(optElm);
        });
    }
    async function getKec(idkota) {
        const response = await fetch("getkec/" + idkota);
        const kecamatan = await response.json();
        const hasil = kecamatan.rajaongkir.results;
        // console.log(hasil)
        kecElm.innerHTML = '<option value="">-- Pilih kecamatan --</option>';
        kodeElm.innerHTML = '<option value="">-- Pilih Desa --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = element.subdistrict_id + "-" + element.subdistrict_name.split("/")[0]
            optElm.innerHTML = element.subdistrict_name
            kecElm.appendChild(optElm);
        });
    }
    async function getKode(kec) {
        const response = await fetch("getkode/" + kec);
        const kode = await response.json();
        console.log(kode);
        const hasil = kode;
        // console.log(hasil)
        kodeElm.innerHTML = '<option value="">-- Pilih Desa --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = titleCase(element.DesaKelurahan).split("/")[0] + "-" + element.KodePos
            optElm.innerHTML = titleCase(element.DesaKelurahan)
            kodeElm.appendChild(optElm);
        });
    }

    function generateAlamat(isi, posisi) {
        //posisi : 1.jln 2.desa 3.kec 4.kab 5.prov 6.kodepos
        isiAlamat[posisi - 1] = isi;
        const stringAlamat = `${isiAlamat[0]} ${isiAlamat[1]}, ${isiAlamat[2]}, ${isiAlamat[3]}, ${isiAlamat[4]} ${isiAlamat[5]}`
        inputAlamatElm.value = stringAlamat
    }

    provElm.addEventListener("change", (e) => {
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

        const provJawaMaduraBali = ["Banten", "Jawa Barat", "DKI Jakarta", "Jawa Tengah", "DI Yogyakarta", "Jawa Timur", "Bali"];
        if (!provJawaMaduraBali.includes(valuenya[1]))
            document.getElementById("peringatan-lokasi").style.display = "block";
        else
            document.getElementById("peringatan-lokasi").style.display = "none";

        btnCheckoutElm.classList.add('disabled');
    })
    kotaElm.addEventListener("change", (e) => {
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
        generateAlamat(e.target.value.split("-")[1], 3);
        kodeElm.innerHTML = '<option value="">Loading..</option>'
        // costElm.innerHTML = '-'
        // hasilApiKurir = [];
        // hasilApiKurirRO = [];
        // resetUIBtnPilihKurir();
        const value = e.target.value.split("-")
        const idkec = Number(value[0])
        if (idkec > 0) {
            prov_kab_kec_desa[2] = e.target.value;
            getKode(value[1])
        }

        btnCheckoutElm.classList.add('disabled');
    })
    kodeElm.addEventListener("change", (e) => {
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
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        } else {
            if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        }
    })

    inputAlamatAddElm.addEventListener('input', (e) => {
        generateAlamat(e.target.value, 1);

        if (email == 'tamu') {
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        } else {
            if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        }
    })
</script>
<?php if (session()->get('email') == 'tamu') { ?>
    <script>
        checkboxElm.addEventListener("change", (e) => {
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
        inputNamaPemElm.addEventListener("input", (e) => {
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
        inputNohpPemElm.addEventListener("input", (e) => {
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
        inputEmailPemElm.addEventListener("input", (e) => {
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
        inputNamaElm.addEventListener("input", (e) => {
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
        inputNohpElm.addEventListener("input", (e) => {
            if (inputEmailPemElm.value != '' && inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
    </script>
<?php } else { ?>
    <script>
        checkboxElm.addEventListener("change", (e) => {
            if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
        inputNamaElm.addEventListener("input", (e) => {
            if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
        inputNohpElm.addEventListener("input", (e) => {
            if (inputNamaElm.value != '' && inputNohpElm.value != '' && checkboxElm.checked && provElm.value != '' && kotaElm.value != '' && kecElm.value != '' && kodeElm.value != '' && inputAlamatAddElm.value != '') btnCheckoutElm.classList.remove('disabled');
            else btnCheckoutElm.classList.add('disabled');
        })
    </script>
<?php } ?>
<?= $this->endSection(); ?>