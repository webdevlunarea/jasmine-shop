<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten d-flex align-items-center">
    <div class="container">
        <div class="justify-content-center">
            <div class="text-center">
                <h3>Pembayaran Berhasil</h3>
                <p class="my-2">Terima kasih telah melakukan pembayaran.</p>
                <div class="mb-3">
                    <a href="/order/<?= $id_pesanan; ?>" class="btn btn-primary1 me-3 mb-2">
                        <p id="counter" class="d-inline m-0">5 |</p> Pergi ke halaman detail pesanan
                    </a>
                    <a href="https://wa.me/628112938160?text=Halo%20,%20saya%20mengalami%20masalah%20dengan%20pembayaran%20saya.%20Bisakah%20Anda%20bantu%20saya?" class="btn btn-dark mb-2">Butuh Bantuan?</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const idPesanan = "<?= $id_pesanan; ?>"
    let counter = 5;
    const counterElm = document.getElementById('counter');
    setInterval(() => {
        counterElm.innerHTML = counter + " |";
        counter--;
        if (counter <= 0) window.location.href = '/order/' + idPesanan
    }, 1000);
</script>
<?= $this->endSection(); ?>