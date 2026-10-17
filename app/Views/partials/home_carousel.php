<?php $banner = $banner ?? []; ?>
        <div id="carouselExampleIndicators" class="carousel slide home-hero-carousel" data-bs-ride="carousel" data-bs-touch="true">
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
            <script>
                (() => {
                    const carousel = document.getElementById('carouselExampleIndicators');
                    if (!carousel || carousel.dataset.desktopDragReady === 'true') return;
                    carousel.dataset.desktopDragReady = 'true';

                    let startX = 0;
                    let startY = 0;
                    let mouseDown = false;
                    let suppressClick = false;

                    carousel.addEventListener('mousedown', (event) => {
                        if (event.button !== 0) return;
                        startX = event.clientX;
                        startY = event.clientY;
                        mouseDown = true;
                        carousel.classList.add('is-dragging');
                    });

                    document.addEventListener('mouseup', (event) => {
                        if (!mouseDown) return;
                        mouseDown = false;
                        carousel.classList.remove('is-dragging');
                        const deltaX = event.clientX - startX;
                        const deltaY = event.clientY - startY;
                        if (Math.abs(deltaX) < 45 || Math.abs(deltaX) < Math.abs(deltaY) * 1.2) return;

                        suppressClick = true;
                        const Carousel = window.bootstrap && window.bootstrap.Carousel;
                        if (Carousel) {
                            const instance = Carousel.getOrCreateInstance(carousel);
                            deltaX < 0 ? instance.next() : instance.prev();
                        }
                        window.setTimeout(() => { suppressClick = false; }, 350);
                    });

                    carousel.addEventListener('dragstart', (event) => event.preventDefault());
                    carousel.addEventListener('click', (event) => {
                        if (!suppressClick) return;
                        event.preventDefault();
                        event.stopPropagation();
                        suppressClick = false;
                    }, true);
                })();
            </script>
        </div>
