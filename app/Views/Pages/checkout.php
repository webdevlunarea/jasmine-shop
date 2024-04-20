<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div id="container-pilih-kurir">
    <div class="container-pilih-kurir">
        <h5>Pilih Kurir</h5>
        <ul class="nav nav-tabs mb-2" id="tabs">
            <!-- <li class="nav-item">
                <a class="nav-link active">Active</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
            </li> -->
        </ul>
        <div id="pilih-kurir" class="d-flex flex-column gap-2">
            <!-- <div class="tombol-pilih-kurir">
                <div class="d-flex align-items-center gap-4">
                    <div class="parent-img">
                        <img src="img/kurir/jnt.png" />
                    </div>
                    <p class="mb-0">J&T Same Day<br>Estimasi Pengiriman 1-4 Hari<br>Rp 120.000</p>
                </div>
            </div>
            <div class="tombol-pilih-kurir">
                <div class="d-flex align-items-center gap-4">
                    <div class="parent-img">
                        <img src="img/kurir/jne.png" />
                    </div>
                    <p class="mb-0">JNE Same Day<br>Estimasi Pengiriman 1-4 Hari<br>Rp 120.000</p>
                </div>
            </div>
            <div class="tombol-pilih-kurir">
                <div class="d-flex align-items-center gap-4">
                    <div class="parent-img">
                        <img src="img/kurir/wahana.png" />
                    </div>
                    <p class="mb-0">Wahana Same Day<br>Estimasi Pengiriman 1-4 Hari<br>Rp 120.000</p>
                </div>
            </div>
            <div class="tombol-pilih-kurir">
                <div class="d-flex align-items-center gap-4">
                    <div class="parent-img">
                        <img src="img/kurir/dakota.png" class="dakota" />
                    </div>
                    <p class="mb-0">Dakota Same Day<br>Estimasi Pengiriman 1-4 Hari<br>Rp 120.000</p>
                </div>
            </div> -->
        </div>
    </div>
</div>
<div class="konten">
    <div class="container">
        <div class="baris-ke-kolom gap-5 mt-3">
            <div id="form-checkout" class="w-100">
                <div class="pb-3 border-bottom">
                    <h5 class="mb-0">Informasi Pemesan</h5>
                    <?php if ($user['email'] != 'tamu') { ?>
                        <p class="mb-0"><?= $user['nama']; ?> </p>
                        <p class="mb-0"><?= $user['nohp']; ?></p>
                    <?php } else { ?>
                        <div class="form-floating mb-1">
                            <input type="email" class="form-control" placeholder="Email" name="emailPem" required value="<?= session()->getFlashdata('emailPem') ? session()->getFlashdata('emailPem') : ''; ?>">
                            <label for="floatingInput">Email</label>
                        </div>
                        <div class="form-floating mb-1">
                            <input type="text" class="form-control" placeholder="Nama" name="namaPem" required value="<?= session()->getFlashdata('namaPem') ? session()->getFlashdata('namaPem') : ''; ?>">
                            <label for="floatingInput">Nama Lengkap</label>
                        </div>
                        <div class="form-floating mb-1">
                            <input type="number" class="form-control" placeholder="Nomor Handphone" name="nohpPem" required value="<?= session()->getFlashdata('nohpPem') ? session()->getFlashdata('nohpPem') : ''; ?>">
                            <label for="floatingInput">No. HP</label>
                        </div>
                    <?php } ?>
                </div>
                <div class="py-3">
                    <h5 class="mb-2">Informasi Penerima</h5>
                    <div class="form-floating mb-1">
                        <input type="text" class="form-control" placeholder="Email" name="nama" required value="<?= $user['nama']; ?>">
                        <label for="floatingInput">Nama Lengkap</label>
                    </div>
                    <!-- <div class="form-floating mb-1">
                    <input type="email" class="form-control" placeholder="Email" name="email" required value="<?= $user['email']; ?>">
                    <label for="floatingInput">Email</label>
                </div> -->
                    <div class="form-floating mb-1">
                        <input type="number" class="form-control" placeholder="Nomor Handphone" name="nohp" required value="<?= $user['nohp']; ?>">
                        <label for="floatingInput">No. HP</label>
                    </div>
                    <!-- <div class="form-floating mb-1">
                    <input type="text" class="form-control" placeholder="Alamat Lengkap" name="alamat" required value="">
                    <label for="floatingPassword">Alamat Lengkap</label>
                </div> -->
                    <div class="form-alamat d-flex mb-1 gap-1 <?= $user['alamat'] ? 'd-none' : ''; ?>">
                        <div class="form-floating w-50">
                            <select class="form-select" aria-label="Default select example" name="provinsi">
                                <option value="-1">-- Pilih provinsi --</option>
                                <?php foreach ($provinsi as $p) { ?>
                                    <option value="<?= $p['province_id']; ?>-<?= $p['province']; ?>" <?= $user['alamat'] ? ($p['province_id'] == $user['alamat']['prov_id'] ? 'selected' : '') : ''; ?>><?= $p['province']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <label for="floatingProvinsi">Provinsi</label>
                        </div>
                        <div class="form-floating w-50">
                            <select class="form-select" aria-label="Default select example" name="kota">
                                <option value="-1">-- Pilih kota --</option>
                                <?= $user['alamat'] ? '<option value="' . $user['alamat']['kab_id'] . '-' . $user['alamat']['kab'] . '" selected>' . $user['alamat']['kab'] . '</option>' : ''; ?>
                            </select>
                            <label for="floatingProvinsi">Kabupaten/Kota</label>
                        </div>
                    </div>
                    <div class="form-alamat d-flex mb-1 gap-1 <?= $user['alamat'] ? 'd-none' : ''; ?>">
                        <div class="form-floating w-50">
                            <select class="form-select" aria-label="Default select example" name="kecamatan">
                                <option selected value="-1">-- Pilih kecamatan --</option>
                                <?= $user['alamat'] ? '<option value="' . $user['alamat']['kec_id'] . '-' . $user['alamat']['kec'] . '" selected>' . $user['alamat']['kec'] . '</option>' : ''; ?>
                            </select>
                            <label for="floatingProvinsi">Kecamatan</label>
                        </div>
                        <div class="form-floating w-50">
                            <select class="form-select" aria-label="Default select example" name="kodepos">
                                <option value="-1">-- Pilih Desa --</option>
                                <?= $user['alamat'] ? '<option value="' . $user['alamat']['desa'] . '-' . $user['alamat']['kodepos'] . '" selected>' . $user['alamat']['desa'] . '</option>' : ''; ?>
                            </select>
                            <label for="floatingProvinsi">Desa/Kelurahan</label>
                        </div>
                    </div>
                    <div class="form-alamat form-floating mb-1 <?= $user['alamat'] ? 'd-none' : ''; ?>">
                        <input type="text" class="form-control" placeholder="Email" name="alamat_add" required value="<?= $user['alamat'] ? $user['alamat']['add'] : ''; ?>">
                        <label for="floatingInput">Jalan, Nomor Rumah, RT-RW</label>
                        <div class="invalid-feedback">Tidak boleh mengandung karakter /</div>
                    </div>
                    <div class="form-alamat <?= $user['alamat'] ? '' : 'd-none'; ?>">
                        <fieldset disabled>
                            <div class="form-floating mb-1">
                                <textarea type="text" class="form-control" placeholder="Email" name="alamat" required style="height: fit-content;"><?= $user['alamat'] ? $user['alamat']['alamat'] : ''; ?></textarea>
                                <label for="floatingInput">Alamat Lengkap</label>
                            </div>
                        </fieldset>
                    </div>
                    <!-- <div class="form-floating mb-1 d-none">
                    <select class="form-select" aria-label="Default select example" name="area">
                        <option value="-1">-- Pilih area --</option>
                    </select>
                    <label for="floatingProvinsi">Area</label>
                </div> -->
                    <!-- <button class="btn btn-primary1" onclick="handleEditAlamat()">Simpan</button> -->
                    <a onclick="handleEditAlamat(event)" style="color: var(--hijau); cursor:pointer;" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover fw-bold"><?= $user['alamat'] ? 'Edit' : 'Simpan'; ?> Alamat</a>
                </div>
                <div class="tombol-pilih-kurir <?= $user['alamat'] ? '' : 'd-none'; ?>" id="btn-pilih-kurir" onclick="pilihKurirRO()">
                    <p class="mb-0">Pilih Kurir</p>
                    <span><i class="material-icons">chevron_right</i></span>
                </div>
                <!-- <div class="form-floating mb-1 d-none">
                    <select class="form-select" aria-label="Default select example" name="ekspedisi">
                        <option value="pos">Pos Indonesia</option>
                        <option value="tiki">TIKI</option>
                        <option value="jne" selected>JNE</option>
                    </select>
                    <label for="floatingProvinsi">Ekspedisi</label>
                </div> -->
                <div class="form-floating mb-1 d-none">
                    <select class="form-select" aria-label="Default select example" name="paket1">
                        <option value="-1">-- Pilih paket --</option>
                    </select>
                    <label for="floatingProvinsi">Paket</label>
                    <input id="set-paket" name="paket" type="text" value="0" />
                </div>
                <p class="text-secondary mt-2"><i>*Pemesanan dari tanggal 6 - 18 April 2024 akan mulai diproses pada tanggal 19 April 2024</i></p>
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
                    <p class="my-2">Pengiriman:</p>
                    <p class="my-2"><b id="total-pengiriman">-</b></p>
                </div>
                <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                    <p class="my-2">Total:</p>
                    <p class="my-2"><b id="total-semua">Rp
                            <?= number_format($total, 0, ",", "."); ?></b>
                    </p>
                </div>
                <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                    <p class="my-2">Berat:</p>
                    <p class="my-2"><b><?= $beratAkhir; ?> kg</b>
                    </p>
                </div>
                <div class="mt-2">
                    <p>
                        <input type="checkbox" id="syarat" style="display: inline;" required>
                        <label for="syarat" style="display: inline;">Saya telah membaca dan menyetujui segala <a href="/syarat-dan-ketentuan" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Syarat & Ketentuan</a> serta <a href="/kebijakan-privasi" style="color: var(--hijau);" class="link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Kebijakan Privasi</a> yang berlaku</label>
                    </p>
                </div>
                <?php if ($user['alamat']) { ?>
                    <button id="btn-checkout" class="btn btn-primary1 disabled" type="button">Bayar</button>
                <?php } ?>
            </div>
        </div>
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
    const costElm = document.getElementById("total-pengiriman");
    const totalElm = document.getElementById("total-semua");
    const containerPilihKurir = document.getElementById("container-pilih-kurir");
    const pilihKurirElm = document.getElementById("pilih-kurir");
    const tabsElm = document.getElementById("tabs");
    const btnPilihKurirElm = document.getElementById("btn-pilih-kurir");
    const inputPaketElm = document.getElementById("set-paket");
    const subtotal = "<?= $subtotal; ?>";
    let email = "<?= session()->get('email') ?>";
    let nama = "<?= session()->get('nama') ?>";
    let nohp = "<?= session()->get('nohp') ?>";
    const alamat = JSON.parse(<?= json_encode($alamatJson); ?>);
    const beratTotal = Number("<?= $beratAkhir; ?>");
    const dimensiSemua = "<?= $dimensiSemua; ?>".split("-");
    let hasilApiKurir = [];
    let hasilApiKurirRO = JSON.parse(<?= json_encode($paketJson); ?>);
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
    const formCheckoutElm = document.getElementById('form-checkout')
    const formCheckoutNama = document.querySelector('#form-checkout input[name="nama"]')
    const formCheckoutAlamat = document.querySelector('#form-checkout input[name="alamat"]')
    const formCheckoutEmail = document.querySelector('#form-checkout input[name="email"]')
    const formCheckoutNoHp = document.querySelector('#form-checkout input[name="nohp"]')
    const formCheckoutPaket = document.querySelector('#form-checkout input[name="paket"]')
    const formCheckout = document.querySelectorAll('#form-checkout .form-control')

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    if (btnCheckoutElm) {
        btnCheckoutElm.addEventListener("click", () => {
            if (email == 'tamu') {
                inputEmailPemElm.classList.remove("is-invalid")
                inputNamaPemElm.classList.remove("is-invalid")
                inputNohpPemElm.classList.remove("is-invalid")
            }
            inputNamaElm.classList.remove("is-invalid")
            inputNohpElm.classList.remove("is-invalid")
            if (email == 'tamu') {
                if (inputEmailPemElm.value == '') return inputEmailPemElm.classList.add("is-invalid")
                if (inputNamaPemElm.value == '') return inputNamaPemElm.classList.add("is-invalid")
                if (inputNohpPemElm.value == '') return inputNohpPemElm.classList.add("is-invalid")
                nama = inputNamaPemElm.value;
                nohp = inputNohpPemElm.value;
            }
            if (inputNamaElm.value == '') return inputNamaElm.classList.add("is-invalid")
            if (inputNohpElm.value == '') return inputNohpElm.classList.add("is-invalid")
            if (formCheckoutPaket.value.length > 0 && !isEditAlamat) {
                btnPilihKurirElm.style.border = "1px solid rgb(214, 214, 214)";
                btnCheckoutElm.innerHTML = "Loading"
                const data = {
                    nama: nama,
                    alamat: JSON.stringify(alamat),
                    email: email == 'tamu' ? inputEmailPemElm.value : email,
                    nohp: nohp,
                    paket: formCheckoutPaket.value,
                    namaPen: inputNamaElm.value,
                    nohpPen: inputNohpElm.value,
                    produk: JSON.stringify(produk),
                    keranjang: JSON.stringify(keranjang)
                }
                // const dataPen = {
                //     nama: inputNamaElm.value,
                //     nohp: inputNohpElm.value,
                // }
                // console.log(data, dataPen)
                async function getTokenMditrans() {
                    var formBody = [];
                    for (var property in data) {
                        var encodedKey = encodeURIComponent(property);
                        var encodedValue = encodeURIComponent(data[property]);
                        formBody.push(encodedKey + "=" + encodedValue);
                    }
                    formBody = formBody.join("&");

                    const response = await fetch("actioncheckout", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(data),
                    });
                    const snaptoken = await response.json();
                    console.log(snaptoken);
                    btnCheckoutElm.innerHTML = "Pesan"
                    /*window.snap.pay(snaptoken.snapToken, {
                        onSuccess: function(result) {
                            // alert("payment success!");
                            addTransaction(result, data, dataPen, (atob(formCheckoutPaket.value)).split("@")[1]);
                        },
                        onPending: function(result) {
                            // alert("wating your payment!");
                            //addTransaction(result, data, dataPen, (atob(formCheckoutPaket.value)).split("@")[1]);
                        },
                        onError: function(result) {
                            alert("Pembayaran Gagal");
                        },
                        onClose: function(result) {
                            console.log(result)
                        }
                    });*/
                    window.snap.pay(snaptoken.snapToken);
                }
                getTokenMditrans()
            } else {
                if (Number(formCheckoutPaket.value) <= 0) btnPilihKurirElm.style.border = "1px solid var(--merah)";
            }
        })
    }

    function handleEditAlamat(e) {
        e.target.innerHTML = 'Loading';
        if (isEditAlamat) {
            const stringDataLain = inputEmailPemElm ? `${inputEmailPemElm.value}&${inputNamaPemElm.value}&${inputNohpPemElm.value}&${inputNamaElm.value}&${inputNohpElm.value}` : '0'
            // inputNamaElm.classList.remove("is-invalid")
            // inputNohpElm.classList.remove("is-invalid")
            inputAlamatAddElm.classList.remove("is-invalid")
            provElm.classList.remove("is-invalid")
            kotaElm.classList.remove("is-invalid")
            kecElm.classList.remove("is-invalid")
            kodeElm.classList.remove("is-invalid")
            // if (inputNamaElm.value == '') return inputNamaElm.classList.add("is-invalid")
            // if (inputNohpElm.value == '') return inputNohpElm.classList.add("is-invalid")
            if (Number(provElm.value) < 0) return provElm.classList.add("is-invalid")
            if (Number(kotaElm.value) < 0) return kotaElm.classList.add("is-invalid")
            if (Number(kecElm.value) < 0) return kecElm.classList.add("is-invalid")
            if (Number(kodeElm.value) < 0) return kodeElm.classList.add("is-invalid")
            if (inputAlamatAddElm.value == '') return inputAlamatAddElm.classList.add("is-invalid");
            if (inputAlamatAddElm.value.includes("/")) return inputAlamatAddElm.classList.add("is-invalid");
            window.location.href = `updatealamat/${provElm.value}&${kotaElm.value}&${kecElm.value}&${kodeElm.value}&${inputAlamatAddElm.value}&${inputAlamatElm.value}&${email}/${stringDataLain}`
        } else {
            formAlamatElm.forEach((form, ind) => {
                if (ind < formAlamatElm.length - 1)
                    form.classList.remove("d-none")
                else form.classList.add("d-none")
            })
            btnCheckoutElm.classList.add("d-none")
            btnCheckoutElm.classList.add("disabled")
            btnPilihKurirElm.classList.add("d-none")
            isEditAlamat = true;
            e.target.innerHTML = 'Simpan Alamat';
        }
    }

    // async function addTransaction(dataMid, dataCus, dataPen, kurir) {
    //     let status;
    //     switch (dataMid.transaction_status) {
    //         case 'settlement':
    //             status = "Proses";
    //             break;
    //         case 'capture':
    //             status = "Proses";
    //             break;
    //         case 'pending':
    //             status = "Menunggu Pembayaran";
    //             break;
    //         case 'expire':
    //             status = "Kadaluarsa";
    //             break;
    //         case 'deny':
    //             status = "Ditolak";
    //             break;
    //         case 'failure':
    //             status = "Gagal";
    //             break;
    //         case 'refund':
    //             status = "Refund";
    //             break;
    //         case 'partial_refund':
    //             status = "Partial Refund";
    //             break;
    //         default:
    //             status = "No Status";
    //             break;
    //     }
    //     const dataYgdikirim = {
    //         namaCus: dataCus.nama,
    //         hpCus: dataCus.nohp,
    //         emailCus: dataCus.email,
    //         namaPen: dataPen.nama,
    //         hpPen: dataPen.nohp,
    //         alamatPen: alamat,
    //         resi: "Menunggu pengiriman " + kurir,
    //         idMid: dataMid.order_id,
    //         items: produk,
    //         status: status,
    //         kurir: kurir,
    //         dataMid: dataMid,
    //     }

    //     const response = await fetch("addtransaction", {
    //         method: "POST",
    //         headers: {
    //             "Content-Type": "application/json",
    //         },
    //         body: JSON.stringify(dataYgdikirim),
    //     });
    //     const result = await response.json();
    //     const transaksi = result.transaksi;
    //     if (result.success) return window.location.href = "/afteraddtransaction/" + transaksi;
    //     else console.log(result);
    // }

    async function getKota(idprov) {
        const response = await fetch("getkota/" + idprov);
        const kota = await response.json();
        const hasil = kota.rajaongkir.results;
        // console.log(hasil)
        kotaElm.innerHTML = '<option value="-1">-- Pilih kota --</option>';
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
        kecElm.innerHTML = '<option value="-1">-- Pilih kecamatan --</option>';
        kodeElm.innerHTML = '<option value="-1">-- Pilih Desa --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = element.subdistrict_id + "-" + element.subdistrict_name.split("/")[0]
            optElm.innerHTML = element.subdistrict_name
            kecElm.appendChild(optElm);
        });
    }
    async function getKode(kec) {
        const response = await fetch("https://api.jasminefurniture.co.id/getkode/" + kec);
        const kode = await response.json();
        const hasil = kode.data;
        // console.log(hasil)
        kodeElm.innerHTML = '<option value="-1">-- Pilih Desa --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = titleCase(element.DesaKelurahan).split("/")[0] + "-" + element.KodePos
            optElm.innerHTML = titleCase(element.DesaKelurahan)
            kodeElm.appendChild(optElm);
        });
    }

    function resetUIBtnPilihKurir() {
        inputPaketElm.value = "0";
        // <p class="mb-0">Pilih Kurir</p>
        //             <span><i class="material-icons">chevron_right</i></span>
        const pElm = document.createElement("p");
        pElm.classList.add("mb-0");
        pElm.innerHTML = "Pilih Kurir";
        const spanElm = document.createElement("span");
        spanElm.innerHTML = '<i class="material-icons">chevron_right</i>';
        btnPilihKurirElm.innerHTML = ""
        btnPilihKurirElm.appendChild(pElm)
        btnPilihKurirElm.appendChild(spanElm)
    }

    function generateAlamat(isi, posisi) {
        //posisi : 1.jln 2.desa 3.kec 4.kab 5.prov 6.kodepos
        isiAlamat[posisi - 1] = isi;
        const stringAlamat = `${isiAlamat[0]} ${isiAlamat[1]}, ${isiAlamat[2]}, ${isiAlamat[3]}, ${isiAlamat[4]} ${isiAlamat[5]}`
        inputAlamatElm.value = stringAlamat
    }

    provElm.addEventListener("change", (e) => {
        generateAlamat(e.target.value.split("-")[1], 5);
        kotaElm.innerHTML = '<option value="-1">Loading..</option>'
        kecElm.innerHTML = '<option value="-1">-- Pilih kecamatan --</option>';
        kodeElm.innerHTML = '<option value="-1">-- Pilih Desa --</option>';
        costElm.innerHTML = '-'
        hasilApiKurir = [];
        hasilApiKurirRO = [];
        resetUIBtnPilihKurir();
        const valuenya = e.target.value.split("-");
        const idprov = Number(valuenya[0]);
        if (idprov > 0) {
            prov_kab_kec_desa[0] = e.target.value
            getKota(idprov)
        }
    })
    kotaElm.addEventListener("change", (e) => {
        generateAlamat(e.target.value.split("-")[1], 4);
        kecElm.innerHTML = '<option value="-1">Loading..</option>'
        kodeElm.innerHTML = '<option value="-1">-- Pilih Desa --</option>';
        costElm.innerHTML = '-'
        hasilApiKurir = [];
        hasilApiKurirRO = [];
        resetUIBtnPilihKurir();
        const value = e.target.value.split("-")
        const idkota = Number(value[0])
        if (idkota > 0) {
            prov_kab_kec_desa[1] = e.target.value;
            getKec(idkota)
        }
    })
    kecElm.addEventListener("change", (e) => {
        generateAlamat(e.target.value.split("-")[1], 3);
        kodeElm.innerHTML = '<option value="-1">Loading..</option>'
        costElm.innerHTML = '-'
        hasilApiKurir = [];
        hasilApiKurirRO = [];
        resetUIBtnPilihKurir();
        const value = e.target.value.split("-")
        const idkec = Number(value[0])
        if (idkec > 0) {
            prov_kab_kec_desa[2] = e.target.value;
            getKode(value[1])
        }
    })
    kodeElm.addEventListener("change", (e) => {
        generateAlamat(e.target.value.split("-")[0], 2);
        generateAlamat(e.target.value.split("-")[1], 6);
        // kodeElm.innerHTML = '<option value="-1">Loading..</option>'
        costElm.innerHTML = '-'
        hasilApiKurir = [];
        hasilApiKurirRO = [];
        resetUIBtnPilihKurir();
        const value = e.target.value.split("-")
        const kodepos = Number(value[1])
        if (kodepos > 0) {
            prov_kab_kec_desa[3] = e.target.value;
            // const pElm = document.createElement("p");
            // pElm.classList.add("mb-0");
            // pElm.innerHTML = "Loading..";
            // btnPilihKurirElm.innerHTML = ""
            // btnPilihKurirElm.appendChild(pElm)
        }
    })

    inputAlamatAddElm.addEventListener('change', (e) => {
        generateAlamat(e.target.value, 1);
    })

    function pilihKurirRO() {
        if (hasilApiKurirRO.length > 0) {
            const hasilApi = hasilApiKurirRO
            tabsElm.innerHTML = "";
            hasilApi.forEach((element, ind) => {
                const liElm = document.createElement("li");
                liElm.classList.add("nav-item");
                const aElm = document.createElement("a")
                aElm.classList.add("nav-link");
                aElm.classList.add("text-dark");
                if (ind == 0) {
                    aElm.classList.add("active");
                    tampilkanPilihanKurirRO(element.code, element.costs, ind);
                }
                aElm.innerHTML = element.code.toUpperCase();
                liElm.appendChild(aElm);
                liElm.addEventListener("click", (e) => {
                    removeActiveTabs();
                    e.target.classList.add("active")
                    tampilkanPilihanKurirRO(element.code, element.costs, ind)
                })
                tabsElm.appendChild(liElm);
            })
            containerPilihKurir.style.display = "flex";
        }
    }

    function removeActiveTabs() {
        const seluruhTabsElm = document.querySelectorAll('a.nav-link');
        console.log(seluruhTabsElm);
        seluruhTabsElm.forEach((elmT) => elmT.classList.remove("active"));
    }

    function tampilkanPilihanKurirRO(kurir, costs, ind_kurir) {
        pilihKurirElm.innerHTML = ""
        costs.forEach((elm, ind_service) => {
            const costnya = elm.cost[0] ? elm.cost[0] : elm.cost;
            const tmbPilihElm = document.createElement("div");
            tmbPilihElm.classList.add("tombol-pilih-kurir");
            const tmbPilihElmChild = document.createElement("div")
            tmbPilihElmChild.setAttribute("class", "d-flex align-items-center gap-4");
            const parentImgElm = document.createElement("div");
            parentImgElm.classList.add("parent-img");
            const keteranganElm = document.createElement("p");
            keteranganElm.classList.add("mb-0");
            if (costnya.etd) {
                keteranganElm.innerHTML =
                    `${kurir.toUpperCase()} ${elm.description}<br>Estimasi Pengiriman ${costnya.etd} Hari<br>Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
            } else {
                keteranganElm.innerHTML =
                    `${kurir.toUpperCase()} ${elm.description}<br>Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
            }
            const imgElm = document.createElement("img");
            imgElm.src = `img/kurir/${kurir}.webp`;
            parentImgElm.appendChild(imgElm)
            tmbPilihElmChild.appendChild(parentImgElm)
            tmbPilihElmChild.appendChild(keteranganElm)
            tmbPilihElm.appendChild(tmbPilihElmChild);
            pilihKurirElm.appendChild(tmbPilihElm);
            tmbPilihElm.addEventListener("click", () => {
                btnPilihKurirElm.innerHTML = "";
                const divElmPK = document.createElement("div");
                divElmPK.setAttribute("class", "d-flex align-items-center gap-3");
                const imgElmPK = document.createElement("img")
                imgElmPK.src = `img/kurir/${kurir}.webp`;
                const keteranganElmPK = document.createElement("p");
                keteranganElmPK.classList.add("mb-0");
                if (costnya.etd) {
                    keteranganElmPK.innerHTML =
                        `${kurir.toUpperCase()} ${elm.description}<br>Estimasi Pengiriman ${costnya.etd} Hari<br>Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
                } else {
                    keteranganElmPK.innerHTML =
                        `${kurir.toUpperCase()} ${elm.description}<br>Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
                }
                const iconElmPK = document.createElement("span");
                iconElmPK.innerHTML = '<i class="material-icons">chevron_right</i>';
                divElmPK.appendChild(imgElmPK)
                divElmPK.appendChild(keteranganElmPK)
                btnPilihKurirElm.appendChild(divElmPK)
                btnPilihKurirElm.appendChild(iconElmPK);

                costElm.innerHTML = `Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`;
                totalElm.innerHTML =
                    `Rp ${(5000 + Number(costnya.value) + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
                console.log(costnya.value, subtotal)
                // inputPaketElm.value = btoa(`${costnya.value}`);
                inputPaketElm.value = btoa(`${costnya.value}@${kurir.toUpperCase()} - ${elm.description}`);
                containerPilihKurir.style.display = "none";
                if (checkboxElm.checked) btnCheckoutElm.classList.remove("disabled")
            })
        })
    }

    checkboxElm.addEventListener("change", (e) => {
        if (inputPaketElm.value != '0') {
            if (e.target.checked) btnCheckoutElm.classList.remove('disabled')
            else btnCheckoutElm.classList.add('disabled')
        }
    })
</script>
<?= $this->endSection(); ?>