<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Tentang Kami</h1>
                <?php if($token) { ?>
                <p><?= $token ?></p>
                <button onclick="tampilkanMidtrans('<?= $token ?>')">Tampikan midtrans</button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>