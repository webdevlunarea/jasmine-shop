<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<div class="konten">
    <div class="container">
        <h1>Detail Pembayaran</h1>
        <div class="baris-ke-kolom gap-5 mt-3">
            <div id="form-checkout" class="limapuluh-ke-seratus">
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
                <div class="form-floating mb-1">
                    <select class="form-select" aria-label="Default select example" name="provinsi">
                        <option selected value="-1">-- Pilih provinsi --</option>
                        <?php foreach ($provinsi as $p) { ?>
                            <option value="<?= $p['province_id']; ?>"><?= $p['province']; ?></option>
                        <?php } ?>
                    </select>
                    <label for="floatingProvinsi">Provinsi</label>
                </div>
                <div class="form-floating mb-1">
                    <select class="form-select" aria-label="Default select example" name="kota">
                        <option value="-1">-- Pilih kota --</option>
                    </select>
                    <label for="floatingProvinsi">Kota</label>
                </div>
                <div class="form-floating mb-1">
                    <select class="form-select" aria-label="Default select example" name="ekspedisi">
                        <option value="pos">Pos Indonesia</option>
                        <option value="tiki">TIKI</option>
                        <option value="jne" selected>JNE</option>
                    </select>
                    <label for="floatingProvinsi">Ekspedisi</label>
                </div>
                <div class="form-floating mb-1">
                    <select class="form-select" aria-label="Default select example" name="paket">
                        <option value="-1">-- Pilih paket --</option>
                    </select>
                    <label for="floatingProvinsi">Paket</label>
                </div>
            </div>
            <div class="limapuluh-ke-seratus">
                <div>
                    <table class="table table-borderless">
                        <tbody>
                            <?php foreach ($produk as $index => $p) { ?>
                                <tr>
                                    <td><?= $p['nama']; ?></td>
                                    <td><?= $jumlah[$index]; ?></td>
                                    <td class="text-end">Rp
                                        <?php
                                        if ($p['diskon']) {
                                            $persen = (100 - $p['diskon']) / 100;
                                            $hasil = $persen * $p['harga'];
                                            echo number_format($hasil, 0, ",", ".");
                                        } else {
                                            $hasil = $p['harga'];
                                            echo number_format($p['harga'], 0, ",", ".");
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between border-bottom border-top mt-3" style="gap: 10em;">
                    <p class="my-2">Subtotal:</p>
                    <p class="my-2"><b>Rp <?= number_format(session()->get('subtotal'), 0, ",", "."); ?></b></p>
                </div>
                <div class="d-flex justify-content-between border-bottom" style="gap: 10em;">
                    <p class="my-2">Pengiriman:</p>
                    <p class="my-2"><b id="total-pengiriman">-</b></p>
                </div>
                <div class="d-flex justify-content-between" style="gap: 10em;">
                    <p class="my-2">Total:</p>
                    <p class="my-2"><b id="total-semua">Rp <?= number_format(session()->get('subtotal'), 0, ",", "."); ?></b>
                    </p>
                </div>
                <button id="btn-checkout" class="btn btn-danger" type="button" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Pastikan seluruh detail pembayaran telah diisi">Pesan</button>
            </div>
        </div>
    </div>
</div>
<script>
    const provElm = document.querySelector('select[name="provinsi"]');
    const kotaElm = document.querySelector('select[name="kota"]');
    const ekspedisiElm = document.querySelector('select[name="ekspedisi"]');
    const paketElm = document.querySelector('select[name="paket"]');
    const costElm = document.getElementById("total-pengiriman");
    const totalElm = document.getElementById("total-semua");
    const subtotal = <?= session()->get('subtotal'); ?>;

    async function getKota(idprov) {
        const response = await fetch("getkota/" + idprov);
        const kota = await response.json();
        const hasil = kota.rajaongkir.results;
        kotaElm.innerHTML = '<option value="-1">-- Pilih kota --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = element.city_id
            optElm.innerHTML = element.city_name
            kotaElm.appendChild(optElm);
        });
    }
    async function getPaket(asal, tujuan, kurir) {
        const response = await fetch("getpaket/" + asal + "/" + tujuan + "/" + kurir);
        const paket = await response.json();
        const hasil = paket.rajaongkir.results[0].costs;
        paketElm.innerHTML = '<option value="-1">-- Pilih paket --</option>';
        hasil.forEach(element => {
            const optElm = document.createElement("option");
            optElm.value = element.cost[0].value
            optElm.innerHTML = `${element.description} | Rp ${element.cost[0].value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
            paketElm.appendChild(optElm);
        });
    }
    provElm.addEventListener("change", (e) => {
        kotaElm.innerHTML = '<option value="-1">Loading..</option>'
        paketElm.innerHTML = '<option value="-1">-- Pilih paket --</option>';
        costElm.innerHTML = '-'
        const idprov = Number(e.target.value)
        if (idprov > 0)
            getKota(idprov)
    })
    kotaElm.addEventListener("change", (e) => {
        paketElm.innerHTML = '<option value="-1">Loading..</option>'
        costElm.innerHTML = '-'
        const idkota = Number(e.target.value)
        const ekspedisi = ekspedisiElm.value;
        if (idkota > 0)
            getPaket("399", idkota, ekspedisi) //399 adalah id kota semarang
    })
    ekspedisiElm.addEventListener("change", (e) => {
        paketElm.innerHTML = '<option value="-1">Loading..</option>'
        costElm.innerHTML = '-'
        const idkota = kotaElm.value;
        const ekspedisi = e.target.value;
        if (idkota > 0)
            getPaket("399", idkota, ekspedisi) //399 adalah id kota semarang
    })
    paketElm.addEventListener("change", (e) => {
        costElm.innerHTML = `Rp ${e.target.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`;
        totalElm.innerHTML = `Rp ${(Number(e.target.value) + Number(subtotal)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")}`
    })
</script>
<?= $this->endSection(); ?>