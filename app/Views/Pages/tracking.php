<?= $this->extend("layout/template"); ?>
<?= $this->section("content"); ?>
<div class="konten">
    <div class="container">
        <p class="fw-bold">Tracking paket Anda</p>
        <div class="container-list-tracking">

            <?php 
            if(isset($hasilnya[0]['tanggal'])){

            foreach ($hasilnya as $ind_list => $list) { ?>
            <div class="list-tracking <?= $ind_list == 0 ? '' : 'done' ?>">
                <div style="width:40px;">
                    <?= explode(" ",$list['tanggal'])[1] ?>
                </div>
                <div class="d-flex flex-column align-items-center gap-1 h-100">
                    <span class="line-tracking"></span>
                    <span class="point-tracking"></span>
                    <span class="line-tracking"></span>
                </div>
                <div class="py-1 flex-grow-1">
                    <div class="container-keterangan-tracking">
                        <div style="width:80px;">
                            <p class="mb-0 text-black-50"><?= explode("/",explode(" ",$list['tanggal'])[0])[0] ?>
                                <?= $bulan[explode("/",explode(" ",$list['tanggal'])[0])[1] - 1] ?></p>
                            <p class="mb-0 text-black-50"><?= explode("/",explode(" ",$list['tanggal'])[0])[2] ?></p>
                        </div>
                        <div>
                            <p class="mb-0"><?= $list['keterangan'] ?></p>
                            <p class="kota-tracking"><?= $list['posisi'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php }}
            else {

             foreach ($hasilnya as $ind_list => $list) { ?>
            <div class="list-tracking <?= $ind_list == 0 ? '' : 'done' ?>">
                <div style="width:40px;">
                    <?= $list['manifest_time'] ?>
                </div>
                <div class="d-flex flex-column align-items-center gap-1 h-100">
                    <span class="line-tracking"></span>
                    <span class="point-tracking"></span>
                    <span class="line-tracking"></span>
                </div>
                <div class="py-1 flex-grow-1">
                    <div class="container-keterangan-tracking">
                        <div style="width:80px;">
                            <p class="mb-0 text-black-50"><?= explode("-",$list['manifest_date'])[2] ?>
                                <?= $bulan[(int)explode("-",$list['manifest_date'])[1]] ?></p>
                            <p class="mb-0 text-black-50"><?= explode("-",$list['manifest_date'])[0] ?></p>
                        </div>
                        <div>
                            <p class="mb-0"><?= $list['manifest_description'] ?></p>
                            <p class="kota-tracking"><?= $list['city_name'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php }} ?>

        </div>
    </div>

</div>


<?= $this->endSection(); ?>