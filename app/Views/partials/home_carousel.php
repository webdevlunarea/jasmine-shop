<?php $banner = $banner ?? []; ?>
        <div id="carouselExampleIndicators" class="carousel slide home-hero-carousel" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php if (!empty($banner)) { ?>
                    <?php foreach ($banner as $ind_b => $b) { ?>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?= $ind_b; ?>" class="<?= $ind_b == 0 ? 'active' : ''; ?>"
                            <?= $ind_b == 0 ? 'aria-current="true"' : ''; ?> aria-label="Slide <?= $ind_b + 1; ?>"></button>
                    <?php } ?>
                <?php } else { ?>
                    <?php for ($i = 0; $i < 5; $i++) { ?>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?= $i; ?>" class="<?= $i == 0 ? 'active' : ''; ?>"
                            <?= $i == 0 ? 'aria-current="true"' : ''; ?> aria-label="Slide <?= $i + 1; ?>"></button>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="carousel-inner" style="border-radius: 0.5em;">
                <?php if (!empty($banner)) { ?>
                    <?php foreach ($banner as $ind_b => $b) { ?>
                        <div class="carousel-item <?= $ind_b == 0 ? 'active' : ''; ?>">
                            <div class="home-hero-media image-skeleton">
                            <?php if ($b['link']) { ?>
                                <a href="<?= esc($b['link']); ?>">
                            <?php } ?>
                            <img data-skeleton-image <?= $ind_b > 0 ? 'loading="lazy"' : 'fetchpriority="high"'; ?> src="/imgbanner/<?= $b['id']; ?>?v=<?= strtotime($b['updated_at'] ?? '') ?: $b['id']; ?>" class="home-hero-image" decoding="async"
                                alt="<?= esc($b['alt'] ?: $b['judul']); ?>">
                            <?php if ($b['link']) { ?>
                                </a>
                            <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                        <div class="carousel-item <?= $i == 1 ? 'active' : ''; ?>">
                            <div class="home-hero-media image-skeleton">
                            <img data-skeleton-image <?= $i > 1 ? 'loading="lazy"' : 'fetchpriority="high"'; ?> src="/img/benner/<?= $i; ?>.webp?v=<?= filemtime(FCPATH . 'img/benner/' . $i . '.webp'); ?>" class="home-hero-image" decoding="async"
                                alt="Lunarea Furniture Banner <?= $i; ?>">
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Slide sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Slide berikutnya</span>
            </button>
        </div>
