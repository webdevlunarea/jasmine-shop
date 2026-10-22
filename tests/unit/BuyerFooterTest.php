<?php

use App\Libraries\BuyerFooter;
use CodeIgniter\Test\CIUnitTestCase;

final class BuyerFooterTest extends CIUnitTestCase
{
    /** @dataProvider pageCases */
    public function testFooterMappingUsesWholeRouteSegments(string $path, string $base, string $variant, bool $payment): void
    {
        $this->assertSame(['variant' => $variant, 'payment' => $payment], BuyerFooter::resolve($path, $base));
    }

    public static function pageCases(): array
    {
        $cases = [];
        foreach (['/', '/all', '/all/lemari', '/product/lemari', '/article', '/article/judul', '/about', '/contact', '/faq', '/kebijakan-privasi', '/syarat-dan-ketentuan', '/accounting', '/cartoon', '/checkout-other'] as $path) $cases[] = [$path, '', 'full', false];
        foreach (['/account', '/account/addresses/manage', '/cart', '/transaction', '/wishlist', '/point/history', '/voucher', '/retur/order/test', '/lacak-pengiriman'] as $path) $cases[] = [$path, '', 'compact', false];
        foreach (['/checkout', '/actionpaycore', '/order/test', '/payorder/test', '/invoice/test'] as $path) $cases[] = [$path, '', 'minimal', true];
        foreach (['/login', '/signup', '/masuk', '/daftar', '/verify/url/test', '/auth/google/callback', '/lupa-sandi', '/reset-sandi'] as $path) $cases[] = [$path, '', 'minimal', false];
        $cases[] = ['/checkout/?voucher=1', '', 'minimal', true];
        $cases[] = ['/jasmine-shop/public/index.php/point/history', '/jasmine-shop/public/', 'compact', false];
        $cases[] = ['/index.php/signup', '', 'minimal', false];
        return $cases;
    }

    public function testCompactAndMinimalKeepHelpAndPolicyWithoutMarketing(): void
    {
        foreach (['compact', 'minimal'] as $variant) {
            $html = view('layout/buyer_footer', ['footerVariant' => $variant, 'footerPayment' => true], ['saveData' => false]);
            $this->assertStringContainsString('buyer-footer--' . $variant, $html);
            $this->assertStringContainsString('/kebijakan-privasi', $html);
            $this->assertStringContainsString('/syarat-dan-ketentuan', $html);
            $this->assertStringContainsString('phone=' . LUNAREA_CS_WHATSAPP_E164, $html);
            $this->assertStringNotContainsString('Our Brand', $html);
            $this->assertStringNotContainsString('shopee.co.id', $html);
            $this->assertStringNotContainsString('Metode pembayaran', $html);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xp = new DOMXPath($dom);
            $this->assertSame(1, $xp->query('//footer')->length);
            // Phone numbers stay in hrefs, not in customer-facing text.
            $this->assertStringNotContainsString(LUNAREA_CS_WHATSAPP_E164, strip_tags($html));
            $this->assertStringNotContainsString(LUNAREA_RETURN_WHATSAPP_E164, strip_tags($html));
            if ($variant === 'compact') {
                $this->assertStringContainsString('/lacak-pengiriman', $html);
                $this->assertStringContainsString('Retur / Komplain', $html);
            } else {
                $this->assertStringContainsString('Bantuan pembayaran', $html);
                $this->assertSame(2, $xp->query('//a[starts-with(@href,"/") and @target="_blank" and @rel="noopener noreferrer"]')->length);
            }
        }
        $auth = view('layout/buyer_footer', ['footerVariant' => 'minimal', 'footerPayment' => false], ['saveData' => false]);
        $this->assertStringNotContainsString('Periksa rincian pesanan', $auth);
        $full = view('layout/buyer_footer', ['footerVariant' => 'full'], ['saveData' => false]);
        $this->assertStringContainsString('Metode pembayaran', $full);
        $this->assertStringContainsString('Brand kami', $full);
        $this->assertStringNotContainsString('Kenali brand kami', $full);
        $fallback = view('layout/buyer_footer', ['footerVariant' => '<invalid>'], ['saveData' => false]);
        $this->assertSame(preg_replace('/<!--.*?-->/s', '', $full), preg_replace('/<!--.*?-->/s', '', $fallback));
    }

    public function testFullFooterKeepsContactPaymentAndStoreLinksWithoutVisiblePhoneNumbers(): void
    {
        $wasLoggedIn = session()->get('isLogin');
        try {
            foreach ([false, true] as $loggedIn) {
                session()->set('isLogin', $loggedIn);
                $html = view('layout/buyer_footer', ['footerVariant' => 'full'], ['saveData' => false]);
                $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); $xp = new DOMXPath($dom);
                $this->assertSame(1, $xp->query('//footer[contains(@class,"buyer-footer--full")]')->length);
                foreach (['/about','/faq','/lacak-pengiriman','/contact','/kebijakan-privasi','/syarat-dan-ketentuan',$loggedIn?'/account':'/login'] as $href) $this->assertSame(1, $xp->query('//footer//a[@href="' . $href . '"]')->length);
                $this->assertSame(6, $xp->query('//nav[@aria-label="Media sosial Lunarea"]/a[@aria-label]')->length);
                $this->assertSame(8, $xp->query('//div[@class="buyer-footer__payment-logos"]/img[@alt and @loading="lazy"]')->length);
                $this->assertSame(3, $xp->query('//nav[@aria-label="Marketplace Lunarea"]/a/img[@alt]')->length);
                $this->assertSame(0, $xp->query('//footer//img[not(@alt)]')->length);
                $this->assertSame(0, $xp->query('//footer//*[@style]')->length);
                $this->assertSame(0, $xp->query('//footer//a[@target="_blank" and not(@rel="noopener noreferrer")]')->length);
                foreach ([LUNAREA_CS_WHATSAPP_E164,LUNAREA_RETURN_WHATSAPP_E164] as $phone) {
                    $this->assertStringContainsString('phone=' . $phone, $html);
                    $this->assertStringNotContainsString($phone, strip_tags($html));
                }
                $this->assertStringContainsString('08.00 - 16.00 WIB', $html);
                $this->assertStringContainsString('08.00 - 14.00 WIB', $html);
            }
        } finally { if ($wasLoggedIn === null) session()->remove('isLogin'); else session()->set('isLogin', $wasLoggedIn); }
    }

    public function testTemplateOnlyRendersBuyerFooterOutsideAdminLayout(): void
    {
        $source = file_get_contents(APPPATH . 'Views/layout/template.php');
        $this->assertMatchesRegularExpression('~if \(!\$isAdminLayout\) \{ \?>\s*</div>\s*<\?php \$buyerFooter =.*?view\(\x27layout/buyer_footer\x27.*?<\?php \} \?>~s', $source);
        $this->assertMatchesRegularExpression('~if \(!\$isAdminLayout\) \{ \?>\s*<div class="buyer-page-content">~', $source);
        if (getenv('BUYER_FOOTER_ARTIFACTS') !== '1') return;
        $directory = WRITEPATH . 'cache/buyer-footer-review';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $url = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
        $styles = '';
        foreach (['bootstrap.min.css', 'style.css', 'commerce-responsive.css', 'storefront-catalog.css', 'buyer-navigation.css', 'buyer-footer.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        $theme = \App\Models\KonstantaModel::defaultThemeWarna();
        $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';}';
        foreach (['full' => ['/', 'Beranda'], 'compact' => ['/account', 'Akun'], 'minimal' => ['/login', 'Masuk'], 'payment' => ['/checkout', 'Check Out']] as $name => [$path, $title]) {
            session()->set(['isLogin' => false, 'role' => null, 'email' => null]);
            $config = BuyerFooter::resolve($path);
            $footer = view('layout/buyer_footer', ['footerVariant' => $config['variant'], 'footerPayment' => $config['payment']], ['saveData' => false]);
            $nav = view('layout/navbar', ['title' => $title], ['saveData' => false]);
            foreach (['short' => 50, 'long' => 1200] as $length => $height) {
                $html = $nav . '<div class="buyer-page-content"><main class="konten container"><h1>' . $title . '</h1><div style="height:' . $height . 'px">Konten halaman uji</div></main></div>' . $footer;
                $html = str_replace('src="/img/', 'src="' . $url . '/img/', $html);
                $html = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $url . '/img/', $html);
                file_put_contents($directory . '/' . $name . '-' . $length . '.html', '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '.material-icons{font-size:0!important}.material-icons::after{content:"●";font-size:20px}</style><script defer src="' . $url . '/js/buyer-navigation.js"></script></head><body class="buyer-layout">' . $html . '</body></html>');
            }
        }
    }
}
