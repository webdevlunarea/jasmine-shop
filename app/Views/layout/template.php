<?php
$title = (string) ($title ?? '');
$themeWarna = \App\Models\KonstantaModel::defaultThemeWarna();
try {
    $themeModel = new \App\Models\KonstantaModel();
    $themeWarna = $themeModel->getThemeWarna();
} catch (\Throwable $e) {
    $themeWarna = \App\Models\KonstantaModel::defaultThemeWarna();
}

$themeCss = [];
foreach ($themeWarna as $key => $value) {
    $themeCss[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$primaryHex = ltrim($themeWarna['primary'], '#');
$themePrimaryRgb = '36, 59, 107';
if (strlen($primaryHex) === 6 && ctype_xdigit($primaryHex)) {
    $themePrimaryRgb = hexdec(substr($primaryHex, 0, 2)) . ', ' . hexdec(substr($primaryHex, 2, 2)) . ', ' . hexdec(substr($primaryHex, 4, 2));
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-K134RFDV6Z"></script>
    <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-K134RFDV6Z');
    </script>
    <!-- Google Tag Manager -->
    <script>
    (function(w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-WMVCNHX5');
    </script>
    <!-- End Google Tag Manager -->
    <meta charset="UTF-8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Lunarea Furniture">
    <?php
    $seoTitle = trim((string) ($title ?? ''));
    $seoTitle = $seoTitle !== '' ? $seoTitle . ' | Lunarea Furniture' : 'Lunarea Furniture | Furnitur Rumah untuk Ruang yang Lebih Nyaman';
    $seoDescription = trim(strip_tags((string) ($metaDeskripsi ?? '')));
    if ($seoDescription === '') {
        $seoDescription = 'Lunarea Furniture menghadirkan pilihan lemari, meja, rak, dan kursi untuk rumah Anda. Temukan furnitur fungsional, pilihan produk, serta penawaran terbaru di toko resmi Lunarea.';
    }
    $seoCanonical = site_url(trim((string) service('uri')->getPath(), '/'));
    $seoPath = trim((string) service('uri')->getPath(), '/');
    $seoIsHome = $seoPath === '';
    $seoNoindexPaths = ['login', 'signup', 'cart', 'checkout', 'account', 'wishlist', 'voucher', 'point', 'transaction', 'order', 'invoice'];
    $seoRobots = 'index, follow';
    if ((string) session()->get('role') === '1' || in_array($seoPath, $seoNoindexPaths, true)
        || preg_match('~^(find|search|invoice|transaction|order)(/|$)~i', $seoPath)) {
        $seoRobots = 'noindex, follow';
    }
    if (isset($metaRobots) && in_array($metaRobots, ['noindex, follow', 'noindex, nofollow'], true)) {
        $seoRobots = $metaRobots;
    }
    $seoProductData = null;
    if (isset($produk['nama'], $produk['id'], $produk['harga'])) {
        $seoPriceRaw = trim((string) $produk['harga']);
        $seoPrice = preg_replace('/[^0-9]/', '', $seoPriceRaw);
        $seoName = trim(strip_tags((string) $produk['nama']));
        $seoDescriptionProduct = trim(strip_tags((string) ($produk['deskripsi_nonhtml'] ?? $produk['deskripsi'] ?? '')));
        if ($seoName !== '' && $seoPrice !== '' && (int) $seoPrice > 0) {
            $seoProductData = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $seoName,
                'url' => $seoCanonical,
                'sku' => (string) $produk['id'],
                'image' => [base_url('imgpic/' . rawurlencode((string) $produk['id']))],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $seoCanonical,
                    'priceCurrency' => 'IDR',
                    'price' => (int) $seoPrice,
                    'itemCondition' => 'https://schema.org/NewCondition',
                ],
            ];
            if ($seoDescriptionProduct !== '') $seoProductData['description'] = $seoDescriptionProduct;
            $stockRaw = trim((string) ($produk['stok'] ?? ''));
            $stockValues = preg_match('/^\d+(?:\s*[,|]\s*\d+)*$/', $stockRaw)
                ? array_map('intval', preg_split('/[,|]/', $stockRaw) ?: [])
                : [];
            if (count($stockValues) > 0) {
                $seoProductData['offers']['availability'] = max($stockValues) > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock';
            }
        }
    }
    ?>
    <meta name="description" content="<?= esc($seoDescription, 'attr'); ?>">
    <meta name="keywords"
        content="<?= esc((string) ($metaKeyword ?? ('lunarea furniture,toko furniture,' . strtolower($title) . ' lunarea')), 'attr'); ?>">
    <meta name="author" content="Lunarea Furniture">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($seoProductData !== null) { ?>
    <script type="application/ld+json"><?= json_encode($seoProductData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
    <?php } ?>
    <meta data-rh="true" property="og:title" content="<?= esc($seoTitle, 'attr'); ?>">
    <meta data-rh="true" property="og:site_name" content="Lunarea Furniture">
    <meta data-rh="true" property="og:url" content="<?= esc($seoCanonical, 'attr'); ?>">
    <meta data-rh="true" property="og:description" content="<?= esc($seoDescription, 'attr'); ?>">
    <meta data-rh="true" property="og:image" content="https://lunareafurniture.com/logo icon.png">
    <?php if ($seoProductData !== null) { ?>
    <meta property="product:price:currency" content="IDR">
    <meta property="product:price:amount" content="<?= esc((string) $seoProductData['offers']['price'], 'attr'); ?>">
    <?php } ?>
    <meta name="robots" content="<?= esc($seoRobots, 'attr'); ?>">
    <link rel="canonical" href="<?= esc($seoCanonical, 'attr'); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($seoTitle, 'attr'); ?>">
    <meta name="twitter:description" content="<?= esc($seoDescription, 'attr'); ?>">
    <meta name="twitter:image" content="https://lunareafurniture.com/logo icon.png">
    <?php if ($seoIsHome) { ?>
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => 'https://lunareafurniture.com/#website',
                'url' => 'https://lunareafurniture.com/',
                'name' => 'Lunarea Furniture',
                'alternateName' => ['Lunarea', 'Lunarea Furniture Indonesia'],
                'inLanguage' => 'id-ID',
                'publisher' => ['@id' => 'https://lunareafurniture.com/#organization'],
            ],
            [
                '@type' => 'Organization',
                '@id' => 'https://lunareafurniture.com/#organization',
                'name' => 'Lunarea Furniture',
                'url' => 'https://lunareafurniture.com/',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
    </script>
    <?php } ?>
    <title><?= esc($seoTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <?php if ($title == 'Check Out') { ?>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-aGWfdxs2btRH4xSd">
    </script>
    <?php if (\App\Services\Payments\PaymentSafety::isTestEmail((string)$user['email'], $emailUji)) { ?>
    <script id="midtrans-script" src="https://api.midtrans.com/v2/assets/js/midtrans-new-3ds.min.js"
        data-environment="sandbox" data-client-key="SB-Mid-client-aGWfdxs2btRH4xSd" type="text/javascript"></script>
    <?php } else { ?>
    <script id="midtrans-script" src="https://api.midtrans.com/v2/assets/js/midtrans-new-3ds.min.js"
        data-environment="production" data-client-key="Mid-client-9sUvUz3XTM_xqGOp" type="text/javascript"></script>
    <?php } ?>
    <?php } ?>

    <script defer src="https://kit.fontawesome.com/917733e7d4.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/css/style.css?v=<?= filemtime(FCPATH . 'css/style.css'); ?>">
    <link rel="stylesheet" href="/css/commerce-responsive.css?v=<?= filemtime(FCPATH . 'css/commerce-responsive.css'); ?>">
    <link rel="stylesheet" href="/css/home-carousel.css?v=<?= filemtime(FCPATH . 'css/home-carousel.css'); ?>">
    <link rel="stylesheet" href="/css/image-skeletons.css?v=<?= filemtime(FCPATH . 'css/image-skeletons.css'); ?>">
    <link rel="stylesheet" href="/css/storefront-catalog.css?v=<?= filemtime(FCPATH . 'css/storefront-catalog.css'); ?>">
    <link rel="stylesheet" href="/css/public-articles.css?v=<?= filemtime(FCPATH . 'css/public-articles.css'); ?>">
    <link rel="stylesheet" href="/css/buyer-navigation.css?v=<?= filemtime(FCPATH . 'css/buyer-navigation.css'); ?>">
    <link rel="stylesheet" href="/css/buyer-footer.css?v=<?= filemtime(FCPATH . 'css/buyer-footer.css'); ?>">
    <?php if (in_array($title, ['Masuk', 'Daftar', 'Lupa sandi', 'Konfirmasi email pemulihan', 'Reset sandi'], true)) { ?>
    <link rel="stylesheet" href="/css/buyer-auth.css?v=<?= filemtime(FCPATH . 'css/buyer-auth.css'); ?>">
    <?php } ?>
    <?php if (in_array($title, ['Kontak', 'Kontak Kami'], true)) { ?>
    <link rel="stylesheet" href="/css/buyer-contact.css?v=<?= filemtime(FCPATH . 'css/buyer-contact.css'); ?>">
    <?php } ?>
    <?php if ($title === 'Tentang') { ?>
    <link rel="stylesheet" href="/css/buyer-about.css?v=<?= filemtime(FCPATH . 'css/buyer-about.css'); ?>">
    <?php } ?>
    <?php if (in_array($title, ['FAQ', 'Syarat dan Ketentuan', 'Kebijakan Privasi', 'Lacak Pengiriman'], true)) { ?>
    <link rel="stylesheet" href="/css/buyer-help.css?v=<?= filemtime(FCPATH . 'css/buyer-help.css'); ?>">
    <?php } ?>
    <?php if (isset($varian, $variantImageMap, $produk['id'])) { ?>
    <link rel="stylesheet" href="/css/buyer-product.css?v=<?= filemtime(FCPATH . 'css/buyer-product.css'); ?>">
    <?php } ?>
    <link rel="stylesheet" href="/css/buyer-account.css?v=<?= filemtime(FCPATH . 'css/buyer-account.css'); ?>">
    <link rel="stylesheet" href="/css/carousel-theme.css?v=<?= filemtime(FCPATH . 'css/carousel-theme.css'); ?>">
    <?php if ((string)session()->get('role') === '1') { ?>
    <link rel="stylesheet" href="/css/admin-workspace.css?v=<?= filemtime(FCPATH . 'css/admin-workspace.css'); ?>">
    <link rel="stylesheet" href="/css/admin-modules.css?v=<?= filemtime(FCPATH . 'css/admin-modules.css'); ?>">
    <link rel="stylesheet" href="/css/admin-media.css?v=<?= filemtime(FCPATH . 'css/admin-media.css'); ?>">
    <script defer src="/js/admin-media.js?v=<?= filemtime(FCPATH . 'js/admin-media.js'); ?>"></script>
    <script defer src="/js/admin-workspace.js?v=<?= filemtime(FCPATH . 'js/admin-workspace.js'); ?>"></script>
    <?php } ?>
    <?php if (in_array($title, ['Peroses Pembayaran', 'Pembayaran Sukes'], true)) { ?>
    <link rel="stylesheet" href="/css/buyer-order.css?v=<?= filemtime(FCPATH . 'css/buyer-order.css'); ?>">
    <script defer src="/js/buyer-order.js?v=<?= filemtime(FCPATH . 'js/buyer-order.js'); ?>"></script>
    <?php } ?>
    <?php if ($title === 'Check Out') { ?>
    <link rel="stylesheet" href="/css/buyer-checkout.css?v=<?= filemtime(FCPATH . 'css/buyer-checkout.css'); ?>">
    <script defer src="/js/buyer-checkout.js?v=<?= filemtime(FCPATH . 'js/buyer-checkout.js'); ?>"></script>
    <?php } ?>
    <script defer src="/js/buyer-navigation.js?v=<?= filemtime(FCPATH . 'js/buyer-navigation.js'); ?>"></script>
    <style id="global-theme-tone">
    :root,
    * {
        --hijau: <?= $themeCss['primary']; ?>;
        --hijaumuda: <?= $themeCss['soft']; ?>;
        --hijaumuda1: <?= $themeCss['soft1']; ?>;
        --hijaumuda2: <?= $themeCss['soft2']; ?>;
        --theme-primary: <?= $themeCss['primary']; ?>;
        --theme-primary-hover: <?= $themeCss['primaryHover']; ?>;
        --theme-soft: <?= $themeCss['soft']; ?>;
        --theme-soft-1: <?= $themeCss['soft1']; ?>;
        --theme-soft-2: <?= $themeCss['soft2']; ?>;
        --theme-accent: <?= $themeCss['accent']; ?>;
        --theme-background: <?= $themeCss['background']; ?>;
        --theme-sidebar: <?= $themeCss['sidebar']; ?>;
        --theme-primary-rgb: <?= $themePrimaryRgb; ?>;
        --bs-success: var(--theme-primary);
        --bs-success-rgb: var(--theme-primary-rgb);
        --bs-success-bg-subtle: var(--theme-soft);
        --bs-success-border-subtle: var(--theme-soft-2);
        --bs-success-text-emphasis: var(--theme-primary-hover);
    }

    .btn-teks-aja:hover {
        color: var(--theme-primary-hover);
    }

    .admin-body {
        --admin-bg: var(--theme-background);
        --admin-primary: var(--theme-primary);
        --admin-primary-soft: var(--theme-soft);
    }

    .admin-sidebar {
        background: var(--theme-sidebar);
    }

    .btn-success {
        --bs-btn-color: #fff;
        --bs-btn-bg: var(--theme-primary);
        --bs-btn-border-color: var(--theme-primary);
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: var(--theme-primary-hover);
        --bs-btn-hover-border-color: var(--theme-primary-hover);
        --bs-btn-focus-shadow-rgb: var(--theme-primary-rgb);
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: var(--theme-primary-hover);
        --bs-btn-active-border-color: var(--theme-primary-hover);
        --bs-btn-disabled-color: #fff;
        --bs-btn-disabled-bg: var(--theme-primary);
        --bs-btn-disabled-border-color: var(--theme-primary);
    }

    .btn-outline-success {
        --bs-btn-color: var(--theme-primary);
        --bs-btn-border-color: var(--theme-primary);
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: var(--theme-primary);
        --bs-btn-hover-border-color: var(--theme-primary);
        --bs-btn-focus-shadow-rgb: var(--theme-primary-rgb);
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: var(--theme-primary);
        --bs-btn-active-border-color: var(--theme-primary);
        --bs-btn-disabled-color: var(--theme-primary);
        --bs-btn-disabled-border-color: var(--theme-primary);
    }

    .bg-success,
    .text-bg-success {
        background-color: rgba(var(--theme-primary-rgb), var(--bs-bg-opacity, 1)) !important;
    }

    .border-success {
        border-color: rgba(var(--theme-primary-rgb), var(--bs-border-opacity, 1)) !important;
    }

    .text-success {
        color: rgba(var(--theme-primary-rgb), var(--bs-text-opacity, 1)) !important;
    }
    </style>
    <link rel="icon" href="<?= base_url('img/logo icon.png'); ?>">
</head>

<?php
$isAdminLayout = session()->get('role') == '1';
$isAuthPage = !$isAdminLayout && in_array($title, ['Masuk', 'Daftar', 'Lupa sandi', 'Konfirmasi email pemulihan', 'Reset sandi'], true);
$promoPath = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$showTopPromo = !$isAdminLayout && !$isAuthPage && (
    $promoPath === '/' ||
    str_starts_with($promoPath, '/all') ||
    str_starts_with($promoPath, '/page/') ||
    str_starts_with($promoPath, '/find/') ||
    str_starts_with($promoPath, '/product/')
);
?>

<body class="<?= $isAdminLayout ? 'admin-body' : ($isAuthPage ? 'buyer-layout buyer-auth-body' : 'buyer-layout'); ?>">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WMVCNHX5" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="toast confirm-dialog start-50 translate-middle" role="dialog" aria-modal="true" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message" aria-live="polite">
        <div class="toast-body confirm-dialog__body">
            <div class="confirm-dialog__icon">
                <i class="material-icons">help_outline</i>
            </div>
            <div class="confirm-dialog__content">
                <p class="confirm-dialog__eyebrow">Konfirmasi</p>
                <h5 id="confirm-dialog-title">Apakah Anda yakin?</h5>
                <p class="confirm-dialog__message" id="confirm-dialog-message">Tindakan ini akan diproses setelah Anda mengonfirmasi.</p>
            </div>
            <div class="confirm-dialog__actions">
                <button type="button" class="btn btn-light confirm-dialog__cancel" onclick="hapusToast()">Batal</button>
                <form action="" method="post">
                    <?= csrf_field(); ?>
                    <button type="submit" class="btn btn-primary1 confirm-dialog__ok">Lanjutkan</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    <?php if ($showTopPromo) {
        $topPromoTexts = model(\App\Models\KonstantaModel::class)->getTopPromoTexts();
        $topPromoDesktop = $topPromoTexts['desktop'] ?? 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%';
        $topPromoMobile = $topPromoTexts['mobile'] ?? $topPromoDesktop;
    ?>
        <?= view('partials/buyer_promo', ['desktopText' => $topPromoDesktop, 'mobileText' => $topPromoMobile], ['saveData' => false]); ?>
    <?php } ?>

    <?= $this->include('layout/navbar'); ?>

    <!-- tombol form dan wa -->
    <?php if (!$isAdminLayout && !$isAuthPage) { ?>
    <div
        class="container-melayang d-flex gap-2 align-items-end <?= isset($geser_container_melayang) ? 'geser' : ''; ?>">
        <div id="container-greeting-card" class="d-none">
            <div style="background-color: white; border-radius: 1em; box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);"
                class="p-4">
                <div class="d-flex align-items-start justify-content-between">
                    <p class="m-0">Hi, Teman Luna!👋</p>
                    <p class="m-0" style="cursor: pointer;" onclick="closeGreetingCard()">x</p>
                </div>
                <p class="m-0">Kalau kamu butuh bantuan,<br>hubungi CS Lunarea via WhatsApp ya!</p>
            </div>
            <div style="height: 30px;"></div>
        </div>
        <div class="d-flex flex-column gap-2">
            <a class="btn-circle" href="/form"><i class="material-icons">insert_comment</i></a>
            <a class="btn-circle hijau" id="btn-wa"
                href="https://api.whatsapp.com/send?phone=<?= LUNAREA_CS_WHATSAPP_E164; ?>&text=Hallo%20CS%20*Lunarea*%2C%20saya%20ingin%20membeli%20furniture....."
                aria-label="Chat Customer Service Lunarea">
                <i class="material-icons text-light">phone</i>
            </a>
        </div>
    </div>
    <?php } ?>

    <?php if (!$isAdminLayout) { ?>
    <div class="buyer-page-content">
    <?php } ?>
    <?= $this->renderSection('content'); ?>
    <?php if (isset($geser_container_melayang)) { ?>
    <div class="hide-ke-show-block" style="height: 53px; width: 100%"></div>
    <?php } ?>
    <?php if (!$isAdminLayout) { ?>
    </div>
        <?php $buyerFooter = \App\Libraries\BuyerFooter::resolve(service('uri')->getPath(), (string) parse_url(base_url(), PHP_URL_PATH)); ?>
        <?= view('layout/buyer_footer', ['footerVariant' => $buyerFooter['variant'], 'footerPayment' => $buyerFooter['payment']], ['saveData' => false]); ?>
    <?php } ?>
    <?php if (session()->get('role') != '1') { ?>
    <script>
    // console.log('qdqwd');
    // document.onkeydown = function(e) {
    //     if (e.key == "F12") {
    //         // alert('Akses diblokir');
    //         e.preventDefault();
    //     }
    //     // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
    //     if (e.ctrlKey && e.shiftKey && (e.key == "I" || e.key == "J")) {
    //         // alert('Akses diblokir');
    //         e.preventDefault();
    //     }
    //     if (e.ctrlKey && e.key == "u") {
    //         // alert('Akses diblokir');
    //         e.preventDefault();
    //     }
    // };
    // document.addEventListener('contextmenu', function(event) {
    //     // alert('Akses diblokir');
    //     event.preventDefault();
    // });
    </script>
    <?php } ?>
    <script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
    <script>
    <?php if (!$isAdminLayout) { ?>
    document.addEventListener("DOMContentLoaded", function() {
        const startTime = Date.now();
        let trackingSent = false;

        function sendVisitorTracking() {
            if (trackingSent) return;
            trackingSent = true;

            const duration = Math.max(0, (Date.now() - startTime) / 1000);
            const payload = JSON.stringify({
                durasi: duration,
                path: window.location.pathname
            });

            if (navigator.sendBeacon) {
                const blob = new Blob([payload], {
                    type: 'application/json'
                });
                navigator.sendBeacon('/addtracking', blob);
                return;
            }

            fetch('/addtracking', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: payload,
                keepalive: true
            }).catch(function() {});
        }

        window.addEventListener('pagehide', sendVisitorTracking);
    });
    <?php } ?>

    const toastElm = document.querySelector(".toast")
    const toastTeksElm = document.querySelector(".confirm-dialog__message")
    const toastTitleElm = document.querySelector(".confirm-dialog__content h5")
    const toastOkElm = document.querySelector(".confirm-dialog form")
    const toastOkBtnElm = document.querySelector(".confirm-dialog__ok")
    const toastCancelBtnElm = document.querySelector(".confirm-dialog__cancel")
    let confirmReturnFocus = null;

    function triggerToast(text, linkAction, onConfirm) {
        const hasCallback = typeof onConfirm === 'function';
        toastOkElm.onsubmit = hasCallback ? function(event) {
            event.preventDefault();
            hapusToast();
            onConfirm();
        } : null;
        toastElm.classList.add("show")
        toastTitleElm.textContent = 'Konfirmasi tindakan'
        toastTeksElm.textContent = text
        toastOkElm.action = linkAction || ''
        if (!linkAction && !hasCallback) {
            toastOkElm.classList.add('d-none');
            toastOkElm.setAttribute('disabled', '');
            toastTitleElm.textContent = 'Informasi'
            toastCancelBtnElm.textContent = 'Mengerti';
        } else {
            toastOkElm.classList.remove('d-none');
            toastOkElm.removeAttribute('disabled');
            toastOkBtnElm.textContent = 'Ya, lanjutkan';
            toastCancelBtnElm.textContent = 'Periksa kembali';
        }
        if (hasCallback) window.requestAnimationFrame(() => toastOkBtnElm.focus());
    }

    function hapusToast() {
        toastElm.classList.remove("show")
        toastOkElm.onsubmit = null;
        if (confirmReturnFocus && typeof confirmReturnFocus.focus === 'function') confirmReturnFocus.focus();
        confirmReturnFocus = null;
    }

    // Konfirmasi terarah untuk pengiriman formulir pelanggan yang mengubah data.
    // Form tanpa data-confirm-submit tetap menggunakan perilaku existing.
    document.addEventListener('submit', function(event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-submit')) return;

        if (form.dataset.confirmApproved === '1') {
            delete form.dataset.confirmApproved;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
        const message = form.dataset.confirmMessage || 'Pastikan informasi yang Anda masukkan sudah benar sebelum melanjutkan.';
        confirmReturnFocus = submitter || document.activeElement;
        triggerToast(message, '', function() {
            form.dataset.confirmApproved = '1';
            if (typeof form.requestSubmit === 'function') {
                submitter ? form.requestSubmit(submitter) : form.requestSubmit();
            } else {
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    }, true);

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && toastElm.classList.contains('show')) {
            event.preventDefault();
            hapusToast();
        }
    });

    function bukaDropdown(id) {
        const elementDropdown = document.getElementById(id);
        if (elementDropdown.style.height == "100%") {
            elementDropdown.style.height = "0";
        } else {
            elementDropdown.style.height = "100%";
        }
    }

    const searchBox = document.querySelectorAll(".search-box");

    searchBox.forEach((elm) => {
        elm.addEventListener('submit', (e) => {
            e.preventDefault()
            const query = elm.querySelector('.search-input').value.trim();
            if (!query) return;
            const isinya = query.replace(/\s+/g, '-');
            window.location.href = "/find/" + encodeURIComponent(isinya);
        })
    })

    const greetingCardElm = document.getElementById('container-greeting-card');
    if (!window.sessionStorage.getItem('greeting-close')) {
        if (greetingCardElm) {
            greetingCardElm.classList.remove('d-none')
        }
    }

    function closeGreetingCard() {
        if (greetingCardElm) {
            greetingCardElm.classList.add('d-none')
        }
        window.sessionStorage.setItem('greeting-close', true);
    }

    // Tampilkan validasi formulir native dalam Bahasa Indonesia.
    document.addEventListener('invalid', function(event) {
        const field = event.target;
        if (!field || typeof field.setCustomValidity !== 'function') return;

        if (field.validity.valueMissing) {
            field.setCustomValidity('Bagian ini wajib diisi.');
        } else if (field.validity.typeMismatch && field.type === 'email') {
            field.setCustomValidity('Masukkan alamat email dengan format yang benar.');
        } else if (field.validity.typeMismatch && field.type === 'url') {
            field.setCustomValidity('Masukkan alamat tautan dengan format yang benar.');
        } else if (field.validity.patternMismatch) {
            field.setCustomValidity('Periksa kembali format yang dimasukkan.');
        } else if (field.validity.tooShort) {
            field.setCustomValidity('Isian terlalu pendek.');
        } else if (field.validity.tooLong) {
            field.setCustomValidity('Isian terlalu panjang.');
        } else if (field.validity.rangeUnderflow || field.validity.rangeOverflow) {
            field.setCustomValidity('Nilai yang dimasukkan berada di luar rentang yang diizinkan.');
        } else if (field.validity.badInput) {
            field.setCustomValidity('Masukkan nilai dengan format yang benar.');
        }
    }, true);

    document.addEventListener('input', function(event) {
        if (event.target && typeof event.target.setCustomValidity === 'function') {
            event.target.setCustomValidity('');
        }
    }, true);
    </script>
    <script defer src="/js/image-skeletons.js?v=<?= filemtime(FCPATH . 'js/image-skeletons.js'); ?>"></script>
</body>

</html>
