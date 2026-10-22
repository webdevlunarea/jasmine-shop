<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$formatNumber = function ($value) {
    return number_format((float)$value, 0, ',', '.');
};

$formatDuration = function ($seconds) {
    $seconds = (int)round((float)$seconds);
    if ($seconds < 60) {
        return $seconds . 's';
    }

    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    return $minutes . 'm ' . $remainingSeconds . 's';
};

$maxPageviews = 1;
foreach ($dailyTraffic as $day) {
    if ((int)$day['pageviews'] > $maxPageviews) {
        $maxPageviews = (int)$day['pageviews'];
    }
}
?>
<div class="konten admin-module-page admin-module-traffic-admin">
    <div class="container">
        <div class="admin-module-heading d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="jdl-section mb-2">Traffic Pengunjung</h5>
                <h1 class="mb-0">Statistik Traffic</h1>
            </div>
            <div class="traffic-range">
                <?php foreach ([7, 30, 90] as $rangeOption) { ?>
                    <a href="<?= site_url('/trafficadmin/' . $rangeOption); ?>" class="<?= $range == $rangeOption ? 'active' : ''; ?>">
                        <?= $rangeOption; ?> hari
                    </a>
                <?php } ?>
            </div>
        </div>

        <form method="get" action="<?= site_url('/trafficadmin'); ?>" class="traffic-filter mb-4">
            <div class="traffic-filter__dates">
                <label class="traffic-filter__field">
                    <span>Dari tanggal</span>
                    <input type="date" name="from" value="<?= esc($fromDate); ?>" max="<?= esc($toDate); ?>" required>
                </label>
                <span class="traffic-filter__separator" aria-hidden="true">—</span>
                <label class="traffic-filter__field">
                    <span>Sampai tanggal</span>
                    <input type="date" name="to" value="<?= esc($toDate); ?>" max="<?= esc(date('Y-m-d', strtotime('+7 hours'))); ?>" required>
                </label>
            </div>
            <div class="traffic-filter__actions">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a class="btn btn-outline-secondary" href="<?= esc(site_url('/trafficadmin/export?from=' . rawurlencode($fromDate) . '&to=' . rawurlencode($toDate))); ?>">Unduh Excel</a>
            </div>
        </form>

        <?php if ($error) { ?>
            <div class="alert alert-warning"><?= esc($error); ?></div>
        <?php } ?>

        <div class="traffic-summary-grid mb-4">
            <div class="traffic-card">
                <p>Pageviews</p>
                <h3><?= $formatNumber($summary['pageviews']); ?></h3>
            </div>
            <div class="traffic-card">
                <p>Pengunjung Unik</p>
                <h3><?= $formatNumber($summary['visitors']); ?></h3>
            </div>
            <div class="traffic-card">
                <p>Durasi Rata-rata</p>
                <h3><?= $formatDuration($summary['avg_duration']); ?></h3>
            </div>
            <div class="traffic-card">
                <p>Pageviews pada tanggal akhir</p>
                <h3><?= $formatNumber($summary['today']); ?></h3>
            </div>
        </div>

        <div class="traffic-section mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Grafik Harian</h3>
                <span class="text-muted"><?= esc(date('d M Y', strtotime($fromDate))); ?> – <?= esc(date('d M Y', strtotime($toDate))); ?></span>
            </div>
            <?php if (count($dailyTraffic) > 0) { ?>
                <div class="traffic-chart">
                    <?php foreach ($dailyTraffic as $day) {
                        $height = max(8, round(((int)$day['pageviews'] / $maxPageviews) * 100));
                    ?>
                        <div class="traffic-bar-item">
                            <div class="traffic-bar-track">
                                <span style="height: <?= $height; ?>%;"></span>
                            </div>
                            <p><?= date('d M', strtotime($day['tanggal'])); ?></p>
                            <strong><?= $formatNumber($day['pageviews']); ?></strong>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="traffic-empty">Belum ada data traffic untuk periode ini.</div>
            <?php } ?>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="traffic-section h-100">
                    <h3 class="mb-3">Top Halaman</h3>
                    <?php if (count($topPages) > 0) { ?>
                        <div class="table-responsive">
                            <table class="table traffic-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Path</th>
                                        <th class="text-end">Views</th>
                                        <th class="text-end">Visitor</th>
                                        <th class="text-end">Durasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topPages as $page) { ?>
                                        <tr>
                                            <td><a href="<?= esc($page['path']); ?>" target="_blank"><?= esc($page['path']); ?></a></td>
                                            <td class="text-end"><?= $formatNumber($page['pageviews']); ?></td>
                                            <td class="text-end"><?= $formatNumber($page['visitors']); ?></td>
                                            <td class="text-end"><?= $formatDuration($page['avg_duration']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="traffic-empty">Belum ada halaman yang tercatat.</div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="traffic-section h-100">
                    <h3 class="mb-3">Kunjungan Terbaru</h3>
                    <?php if (count($recentVisits) > 0) { ?>
                        <div class="traffic-recent-list">
                            <?php foreach ($recentVisits as $visit) { ?>
                                <div class="traffic-recent-item">
                                    <div>
                                        <p class="mb-1"><?= esc($visit['path']); ?></p>
                                        <span><?= esc($visit['ip']); ?> - <?= date('d M H:i', strtotime($visit['waktu'])); ?></span>
                                    </div>
                                    <strong><?= $formatDuration($visit['durasi']); ?></strong>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="traffic-empty">Belum ada kunjungan terbaru.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
