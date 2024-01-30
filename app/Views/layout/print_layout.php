<div class="print">
    <div class="p-5">
        <img src="img/Logo Jasmine.png" width="150mm" />
        <h4 class="fw-bold text-center">FORMULIR PEMESANAN BARANG</h4>
        <div style="float: right" class="mb-3">
            <table>
                <tbody>
                    <tr>
                        <td class="pe-1">Tanggal Order</td>
                        <td id="print-tanggal-order">: _____________</td>
                    </tr>
                    <tr>
                        <td>No. Orderan</td>
                        <td id="print-no-order">: _____________</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <table class="table table-bordered border-dark">
                <thead id="print-tabel-barang">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Kode Barang</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                    </tr>
                    <!-- <tr>
                        <td>1</td>
                        <td>Barang1</td>
                        <td>MR101</td>
                        <td>3</td>
                        <td>Rp 3.000.000</td>
                        <td>Rp 3.000.000</td>
                    </tr> -->
                </thead>
            </table>
        </div>
        <div class="mb-3">
            <p class="fw-bold mb-0">Informasi Pemesanan</p>
            <table>
                <tbody>
                    <tr>
                        <td class="pe-1">Nama Lengkap</td>
                        <td id="print-nama-pemesan">: _____________</td>
                    </tr>
                    <tr>
                        <td>No. Hp</td>
                        <td id="print-no-pemesan">: _____________</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mb-3">
            <table class="table table-bordered border-dark">
                <thead>
                    <tr>
                        <th colspan="2">Informasi Pembayaran</th>
                    </tr>
                    <tr>
                        <td><input type="radio" name="print-info-bayar" value="transfer" class="print-info-bayar" /></td>
                        <td class="text-start">Transfer</td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="print-info-bayar" value="kredit" class="print-info-bayar" /></td>
                        <td class="text-start">
                            Kartu Kredit
                            <p style="
                                            font-size: small;
                                            margin: 0;
                                        ">
                                (Jika pemilik kartu ini tidak hadir,
                                wajib melampirkan formular otorisasi
                                yang telah diisi secara lengkap)
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="print-info-bayar" value="ewallet" class="print-info-bayar" /></td>
                        <td class="text-start">Dompet Digital (Gopay, Dana, ShopeePay)</td>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="mb-3">
            <p class="fw-bold mb-0">Produk diatas akan dikirim ke</p>
            <table>
                <tbody>
                    <tr>
                        <td>Nama</td>
                        <td id="print-nama-penerima">: _____________</td>
                    </tr>
                    <tr>
                        <td class="pe-1">Alamat</td>
                        <td id="print-alamat-penerima">: _____________</td>
                    </tr>
                    <tr>
                        <td>Kota</td>
                        <td id="print-kota">
                            : _____________Kode Pos :_____________ Telp.
                            ___________
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex">
            <div class="border flex-grow-1 border-dark d-flex justify-content-center align-items-end" style="height: 150px">
                <p class="mb-0">Staf</p>
            </div>
            <div class="border flex-grow-1 border-dark d-flex justify-content-center align-items-end" style="height: 150px">
                <p class="mb-0">Pemesanan</p>
            </div>
        </div>
    </div>
</div>