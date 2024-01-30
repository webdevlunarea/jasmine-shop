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
        <h1>Detail Pembayaran</h1>
        <div class="baris-ke-kolom gap-5 mt-3">
            <div id="form-checkout" class="w-100">
                <div class="form-floating mb-1">
                    <input type="text" class="form-control" placeholder="Email" name="nama" required>
                    <label for="floatingInput">Nama Lengkap</label>
                </div>
                <div class="form-floating mb-1">
                    <input type="email" class="form-control" placeholder="Email" name="email" required value="<?= $user['email']; ?>">
                    <label for="floatingInput">Email</label>
                </div>
                <div class="form-floating mb-1">
                    <input type="text" class="form-control" placeholder="Alamat Lengkap" name="alamat" required value="<?= $user['alamat']; ?>">
                    <label for="floatingPassword">Alamat Lengkap</label>
                </div>
                <div class="form-floating mb-1">
                    <input type="number" class="form-control" placeholder="Nomor Handphone" name="phone" required>
                    <label for="floatingInput">No. HP</label>
                </div>
                <div class="d-flex mb-1 gap-1">
                    <div class="form-floating w-50">
                        <select class="form-select" aria-label="Default select example" name="provinsi">
                            <option selected value="-1">-- Pilih provinsi --</option>
                            <?php foreach ($provinsi as $p) { ?>
                                <option value="<?= $p['province_id']; ?>@<?= $p['province']; ?>"><?= $p['province']; ?></option>
                            <?php } ?>
                        </select>
                        <label for="floatingProvinsi">Provinsi</label>
                    </div>
                    <div class="form-floating w-50">
                        <select class="form-select" aria-label="Default select example" name="kota">
                            <option value="-1">-- Pilih kota --</option>
                        </select>
                        <label for="floatingProvinsi">Kabupaten/Kota</label>
                    </div>
                </div>
                <div class="d-flex mb-1 gap-1">
                    <div class="form-floating w-50">
                        <select class="form-select" aria-label="Default select example" name="kecamatan">
                            <option selected value="-1">-- Pilih kecamatan --</option>
                        </select>
                        <label for="floatingProvinsi">Kecamatan</label>
                    </div>
                    <div class="form-floating w-50">
                        <select class="form-select" aria-label="Default select example" name="kodepos">
                            <option value="-1">-- Pilih kodepos --</option>
                        </select>
                        <label for="floatingProvinsi">Kode Pos</label>
                    </div>
                </div>
                <div class="form-floating mb-1 d-none">
                    <select class="form-select" aria-label="Default select example" name="area">
                        <option value="-1">-- Pilih area --</option>
                    </select>
                    <label for="floatingProvinsi">Area</label>
                </div>
                <div class="tombol-pilih-kurir" id="btn-pilih-kurir" onclick="pilihKurirRO()">
                    <p class="mb-0">Pilih Kurir</p>
                    <span><i class="material-icons">chevron_right</i></span>
                </div>
                <div class="form-floating mb-1 d-none">
                    <select class="form-select" aria-label="Default select example" name="ekspedisi">
                        <option value="pos">Pos Indonesia</option>
                        <option value="tiki">TIKI</option>
                        <option value="jne" selected>JNE</option>
                    </select>
                    <label for="floatingProvinsi">Ekspedisi</label>
                </div>
                <div class="form-floating mb-1 d-none">
                    <select class="form-select" aria-label="Default select example" name="paket1">
                        <option value="-1">-- Pilih paket --</option>
                    </select>
                    <label for="floatingProvinsi">Paket</label>
                    <input id="set-paket" name="paket" type="text" value="0" />
                </div>
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
                <div class="d-flex justify-content-between" style="gap: 10em;">
                    <p class="my-2">Berat:</p>
                    <!-- <p class="my-2"><b><?= $berat > $beratHitung ? $berat : $beratHitung; ?> kg</b> -->
                    <p class="my-2"><b><?= $berat; ?> kg</b>
                    </p>
                </div>
                <button id="btn-checkout" class="btn btn-primary1" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Pastikan seluruh detail pembayaran telah diisi">Pesan</button>
            </div>
        </div>
    </div>
</div>
<script>
    const provElm = document.querySelector('select[name="provinsi"]');
    const kotaElm = document.querySelector('select[name="kota"]');
    // const ekspedisiElm = document.querySelector('select[name="ekspedisi"]');
    // const paketElm = document.querySelector('select[name="paket"]');
    const areaElm = document.querySelector('select[name="area"]');
    const costElm = document.getElementById("total-pengiriman");
    const totalElm = document.getElementById("total-semua");
    const containerPilihKurir = document.getElementById("container-pilih-kurir");
    const pilihKurirElm = document.getElementById("pilih-kurir");
    const tabsElm = document.getElementById("tabs");
    const btnPilihKurirElm = document.getElementById("btn-pilih-kurir");
    const inputPaketElm = document.getElementById("set-paket");
    const subtotal = "<?= $subtotal; ?>";
    const email = "<?= session()->get('email') ?>";
    // const beratTotal = Number("<?= $berat > $beratHitung ? $berat : $beratHitung; ?>"); //kg
    const beratTotal = Number("<?= $berat; ?>"); // cuma sementara
    const dimensiSemua = "<?= $dimensiSemua; ?>".split("-");
    let hasilApiKurir = [];
    let hasilApiKurirRO = [];
    let prov_kab_kec_kodepos = ["", "", "", ""];
    const produk = JSON.parse(<?= json_encode($produkJson); ?>);
    console.log(produk)
    console.log(dimensiSemua)

    const btnCheckoutElm = document.getElementById('btn-checkout')
    const formCheckoutElm = document.getElementById('form-checkout')
    const formCheckoutNama = document.querySelector('#form-checkout input[name="nama"]')
    const formCheckoutAlamat = document.querySelector('#form-checkout input[name="alamat"]')
    const formCheckoutEmail = document.querySelector('#form-checkout input[name="email"]')
    const formCheckoutNoHp = document.querySelector('#form-checkout input[name="phone"]')
    const formCheckoutPaket = document.querySelector('#form-checkout input[name="paket"]')
    const formCheckout = document.querySelectorAll('#form-checkout .form-control')

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    btnCheckoutElm.addEventListener("click", () => {
        if (formCheckoutNama.value && formCheckoutAlamat.value && formCheckoutEmail.value &&
            formCheckoutNoHp.value && formCheckoutPaket.value.length > 0) {

            formCheckoutNama.classList.remove("is-invalid")
            formCheckoutAlamat.classList.remove("is-invalid")
            formCheckoutEmail.classList.remove("is-invalid")
            formCheckoutNoHp.classList.remove("is-invalid")
            btnPilihKurirElm.style.border = "1px solid rgb(214, 214, 214)";

            btnCheckoutElm.innerHTML = "Loading"
            const data = {
                nama: formCheckoutNama.value,
                alamat: formCheckoutAlamat.value,
                email: formCheckoutEmail.value,
                phone: formCheckoutNoHp.value,
                paket: btoa((formCheckoutPaket.value).split("-")[0])
                // paket: 120000
            }
            console.log(data)
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
                window.snap.pay(snaptoken.snapToken, {
                    onSuccess: function(result) {
                        alert("payment success!");
                        addTransaction(result, data, (formCheckoutPaket.value).split("-")[1]);
                    },
                    onPending: function(result) {
                        alert("wating your payment!");
                        addTransaction(result, data, (formCheckoutPaket.value).split("-")[1]);
                    },
                    onError: function(result) {
                        alert("payment failed!");
                    },
                    onClose: function() {
                        alert('you closed the popup without finishing the payment');
                    }
                });
            }
            getTokenMditrans()
        } else {
            if (!formCheckoutNama.value) formCheckoutNama.classList.add("is-invalid")
            if (!formCheckoutAlamat.value) formCheckoutAlamat.classList.add("is-invalid")
            if (!formCheckoutEmail.value) formCheckoutEmail.classList.add("is-invalid")
            if (!formCheckoutNoHp.value) formCheckoutNoHp.classList.add("is-invalid")
            if (Number(formCheckoutPaket.value) <= 0) btnPilihKurirElm.style.border = "1px solid var(--merah)";
        }
    })

    async function addTransaction(dataMid, dataCus, kurir) {
        let status;
        switch (dataMid.transaction_status) {
            case 'settlement':
                status = "Proses";
                break;
            case 'capture':
                status = "Proses";
                break;
            case 'pending':
                status = "Menunggu Pembayaran";
                break;
            case 'expire':
                status = "Kadaluarsa";
                break;
            case 'deny':
                status = "Ditolak";
                break;
            case 'failure':
                status = "Gagal";
                break;
            case 'refund':
                status = "Refund";
                break;
            case 'partial_refund':
                status = "Partial Refund";
                break;
            default:
                status = "No Status";
                break;
        }
        const dataYgdikirim = {
            namaCus: dataCus.nama,
            emailCus: dataCus.email,
            alamatCus: dataCus.alamat,
            hpCus: dataCus.phone,
            resi: "Menunggu pengiriman",
            idMid: dataMid.order_id,
            items: produk,
            status: status,
            kurir: kurir,
            dataMid: dataMid,
        }
        const response = await fetch("addtransaction", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(dataYgdikirim),
        });
        const result = await response.json();
        const transaksi = result.transaksi;
        if (result.success) return window.location.href = "/afteraddtransaction/" + transaksi;
        else console.log(result);
    }

    async function getKota(idprov) {
        const response = await fetch("getkota/" + idprov);
        const kota = await response.json();
        const hasil = kota.rajaongkir.results;
        kotaElm.innerHTML = '<option value="-1">-- Pilih kota --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = element.city_id + "-" + element.city_name
            optElm.innerHTML = element.city_name
            kotaElm.appendChild(optElm);
        });
    }
    async function getArea(kota) {
        const response = await fetch("getarea/" + kota);
        const result = await response.json();
        const hasil = result.areas;
        areaElm.innerHTML = '<option value="-1">-- Pilih area --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = element.id;
            optElm.innerHTML = element.name
            areaElm.appendChild(optElm);
        });
    }
    async function getRates(data) {
        const response = await fetch("getrates", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });
        const result = await response.json();
        if (result.success) hasilApiKurir = result.pricing;
    }
    async function getPaket(asal, tujuan, berat) { //berat gram
        const ekspedisi = ["jne", "pos", "tiki"];
        let hasil = [];
        ekspedisi.forEach(async (kurir, ind) => {
            console.log("getpaket/" + asal + "/" + tujuan + "/" + berat + "/" + kurir)
            const response = await fetch("getpaket/" + asal + "/" + tujuan + "/" + berat + "/" + kurir);
            const paket = await response.json();
            hasil.push(paket.rajaongkir.results[0])
            if (ind >= ekspedisi.length - 1) {
                const kecamatan = await fetch("http://192.168.1.36:8082/getkec/" + prov_kab_kec_kodepos[1])
                const dataYgdikirim = {
                    prov: prov_kab_kec_kodepos[0],
                    kab: prov_kab_kec_kodepos[1],
                    kec: (await kecamatan.json()).data[0].KecamatanDistrik,
                }
                const responseDakota = await fetch("http://192.168.1.36:8082/dakota", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(dataYgdikirim)
                });
                const dakota = await responseDakota.json();
                if (dakota.pesan != 'Ok') {
                    return console.log(dakota)
                }
                const costs = Object.keys(dakota.data)
                    .filter((eFilter) => {
                        if (
                            eFilter.toLowerCase() == "reguler" ||
                            eFilter.toLowerCase() == "kurir"
                        ) {
                            if (dakota.data[eFilter][0].pokok > 0) {
                                return true;
                            } else return false;
                        } else return false;
                    })
                    .map((e, ind) => {
                        let cost = dakota.data[e][0];
                        let harga = [];
                        harga.push({
                            value: (Number(berat) / 1000) > Number(cost.minkg) ?
                                Number(cost.kgnext) * (Number(berat) / 1000) : Number(cost.pokok),
                            etd: cost.LT,
                        });
                        return {
                            description: e.charAt(0).toUpperCase() + e.slice(1),
                            cost: {
                                value: (Number(berat) / 1000) > Number(cost.minkg) ?
                                    Number(cost.kgnext) * (Number(berat) / 1000) : Number(cost.pokok),
                                etd: cost.LT,
                            },
                        };
                    });
                hasil.push({
                    code: 'dakota',
                    costs: costs
                })
                console.log(hasil);
                console.log(dakota);
                hasilApiKurirRO = hasil;
                resetUIBtnPilihKurir();
            }
        })

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
    provElm.addEventListener("change", (e) => {
        kotaElm.innerHTML = '<option value="-1">Loading..</option>'
        // paketElm.innerHTML = '<option value="-1">-- Pilih paket --</option>';
        areaElm.innerHTML = '<option value="-1">-- Pilih area --</option>';
        costElm.innerHTML = '-'
        totalElm.innerHTML = `Rp ${(5000 + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
        hasilApiKurir = [];
        hasilApiKurirRO = [];
        resetUIBtnPilihKurir();
        const valuenya = e.target.value.split("@");
        const idprov = Number(valuenya[0]);
        if (idprov > 0) {
            prov_kab_kec_kodepos[0] = valuenya[1]
            getKota(idprov)
        }
    })
    kotaElm.addEventListener("change", (e) => {
        // paketElm.innerHTML = '<option value="-1">Loading..</option>'
        areaElm.innerHTML = '<option value="-1">Loading..</option>'
        costElm.innerHTML = '-'
        totalElm.innerHTML = `Rp ${(5000 + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
        hasilApiKurir = [];
        hasilApiKurirRO = [];
        resetUIBtnPilihKurir();
        const value = e.target.value.split("-")
        const idkota = Number(value[0])
        if (idkota > 0) {
            prov_kab_kec_kodepos[1] = value[1];
            const pElm = document.createElement("p");
            pElm.classList.add("mb-0");
            pElm.innerHTML = "Loading..";
            btnPilihKurirElm.innerHTML = ""
            btnPilihKurirElm.appendChild(pElm)
            getPaket("399", idkota, beratTotal * 1000) //399 adalah id kota semarang
            getArea(value[1])
        }
    })
    // ekspedisiElm.addEventListener("change", (e) => {
    //     paketElm.innerHTML = '<option value="-1">Loading..</option>'
    //     costElm.innerHTML = '-'
    //     const idkota = kotaElm.value;
    //     const ekspedisi = e.target.value;
    //     if (idkota > 0)
    //         getPaket("399", idkota, beratTotal, ekspedisi) //399 adalah id kota semarang
    // })
    // paketElm.addEventListener("change", (e) => {
    //     costElm.innerHTML = `Rp ${e.target.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`;
    //     totalElm.innerHTML =
    //         `Rp ${(5000 + Number(e.target.value) + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
    // })

    function pilihKurir() {
        if (hasilApiKurir.length > 0) {
            const hasilApi = hasilApiKurir
            // console.log(hasilApi);
            let cekTabs = []
            tabsElm.innerHTML = "";
            hasilApi.forEach((element, ind) => {
                const liElm = document.createElement("li");
                liElm.classList.add("nav-item");
                const aElm = document.createElement("a")
                aElm.classList.add("nav-link");
                aElm.classList.add("text-dark");
                if (ind == 0) {
                    aElm.classList.add("active");
                    tampilkanPilihanKurir(element.service_type, hasilApi);
                }
                aElm.innerHTML = element.service_type;
                liElm.appendChild(aElm);
                if (!cekTabs.includes(element.service_type)) {
                    tabsElm.appendChild(liElm);
                    cekTabs.push(element.service_type);
                    liElm.addEventListener("click", (e) => {
                        removeActiveTabs();
                        e.target.classList.add("active")
                        tampilkanPilihanKurir(element.service_type, hasilApi)
                    })
                }
            })
            containerPilihKurir.style.display = "flex";
        }
    }

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
        const seluruhTabsElm = document.querySelectorAll('a[class="nav-link"]');
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
            keteranganElm.innerHTML = `${kurir.toUpperCase()} ${elm.description}<br>Estimasi Pengiriman ${costnya.etd} Hari<br>Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
            const imgElm = document.createElement("img");
            imgElm.src = `img/kurir/${kurir}.png`;
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
                imgElmPK.src = `img/kurir/${kurir}.png`;
                const keteranganElmPK = document.createElement("p");
                keteranganElmPK.classList.add("mb-0");
                keteranganElmPK.innerHTML = `${kurir.toUpperCase()} ${elm.description}<br>Estimasi Pengiriman ${costnya.etd} Hari<br>Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
                const iconElmPK = document.createElement("span");
                iconElmPK.innerHTML = '<i class="material-icons">chevron_right</i>';
                divElmPK.appendChild(imgElmPK)
                divElmPK.appendChild(keteranganElmPK)
                btnPilihKurirElm.appendChild(divElmPK)
                btnPilihKurirElm.appendChild(iconElmPK);

                costElm.innerHTML = `Rp ${costnya.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`;
                totalElm.innerHTML =
                    `Rp ${(5000 + Number(costnya.value) + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
                const timeSkrg = "<?= time(); ?>";
                // inputPaketElm.value = btoa(`${costnya.value}`);
                inputPaketElm.value = `${costnya.value}-${kurir}`;
                containerPilihKurir.style.display = "none";
            })
        })
    }

    function tampilkanPilihanKurir(serviceType, hasilApi) {
        const filterHasilApi = hasilApi.filter((elm) => {
            if (elm.service_type == serviceType) return true;
            else return false;
        }).map((e) => {
            return e
        });
        console.log(filterHasilApi);
        pilihKurirElm.innerHTML = ""
        filterHasilApi.forEach((elm) => {
            const tmbPilihElm = document.createElement("div");
            tmbPilihElm.classList.add("tombol-pilih-kurir");
            const tmbPilihElmChild = document.createElement("div")
            tmbPilihElmChild.setAttribute("class", "d-flex align-items-center gap-4");
            const parentImgElm = document.createElement("div");
            parentImgElm.classList.add("parent-img");
            const keteranganElm = document.createElement("p");
            keteranganElm.classList.add("mb-0");
            keteranganElm.innerHTML = `${elm.courier_name} ${elm.description}<br>Estimasi Pengiriman ${elm.duration}<br>Rp ${elm.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
            const imgElm = document.createElement("img");
            imgElm.src = `img/kurir/${elm.courier_code}.png`;
            parentImgElm.appendChild(imgElm)
            tmbPilihElmChild.appendChild(parentImgElm)
            tmbPilihElmChild.appendChild(keteranganElm)
            tmbPilihElm.appendChild(tmbPilihElmChild);
            pilihKurirElm.appendChild(tmbPilihElm);
            tmbPilihElm.addEventListener("click", () => {
                //     <div class="d-flex align-items-center gap-3">
                //         <img src="img/kurir/jnt.png" />
                //         <p class="mb-0">J&T Same Day<br>Estimasi Pengiriman 1-4 Hari<br>Rp 120.000</p>
                //     </div>
                //     <span><i class="material-icons">chevron_right</i></span>
                btnPilihKurirElm.innerHTML = "";
                const divElmPK = document.createElement("div");
                divElmPK.setAttribute("class", "d-flex align-items-center gap-3");
                const imgElmPK = document.createElement("img")
                imgElmPK.src = `img/kurir/${elm.courier_code}.png`;
                const keteranganElmPK = document.createElement("p");
                keteranganElmPK.classList.add("mb-0");
                keteranganElmPK.innerHTML = `${elm.courier_name} ${elm.description}<br>Estimasi Pengiriman ${elm.duration}<br>Rp ${elm.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
                const iconElmPK = document.createElement("span");
                iconElmPK.innerHTML = '<i class="material-icons">chevron_right</i>';
                divElmPK.appendChild(imgElmPK)
                divElmPK.appendChild(keteranganElmPK)
                btnPilihKurirElm.appendChild(divElmPK)
                btnPilihKurirElm.appendChild(iconElmPK);

                costElm.innerHTML = `Rp ${elm.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`;
                totalElm.innerHTML =
                    `Rp ${(5000 + Number(elm.price) + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
                inputPaketElm.value = Number(elm.price);

                containerPilihKurir.style.display = "none";
            })
        })
    }

    areaElm.addEventListener("change", (e) => {
        costElm.innerHTML = '-'
        totalElm.innerHTML = `Rp ${(5000 + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
        hasilApiKurir = [];
        resetUIBtnPilihKurir();
        const id_area = e.target.value;
        const bodynya = {
            origin_area_id: "IDNP10IDNC393IDND4705IDZ50122", //id kota semarang (tpi belum fix bener ato nggk postal codenya)
            destination_area_id: id_area,
            couriers: "jne,jnt,wahana",
            items: produk
        }
        getRates(bodynya);
    })
</script>
<?= $this->endSection(); ?>