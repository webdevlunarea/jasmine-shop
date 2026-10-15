<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerContactViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        session()->remove(['isLogin', 'role', 'email']);
        parent::tearDown();
    }

    /** @dataProvider contactCases */
    public function testContactPagesKeepRoutesFieldsAndTheme(string $page, bool $filled): void
    {
        $renderer = new class {
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render(string $page, array $data): string {
                extract($data, EXTR_SKIP);
                ob_start();
                try { include APPPATH . 'Views/pages/' . $page . '.php'; return ob_get_contents(); }
                finally { ob_end_clean(); }
            }
        };
        $data = ['val' => ['msg' => $filled ? 'Pesan <contoh> untuk pelanggan' : ''], 'artikel' => $filled ? [
            ['judul' => 'Tips memilih furnitur <contoh>', 'path' => 'tips-furnitur', 'excerpt' => 'Panduan <contoh> memilih furnitur untuk kebutuhan rumah Anda.'],
            ['judul' => str_repeat('Ruang nyaman ', 8), 'path' => 'ruang-nyaman', 'excerpt' => str_repeat('Pilihan furnitur rumah. ', 20)],
            ['judul' => 'Merawat lemari', 'path' => 'rawat-lemari', 'excerpt' => 'Kenali cara merawat lemari.'],
        ] : []];
        $html = $renderer->render($page, $data);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xp = new DOMXPath($dom);
        $this->assertSame(1, $xp->query('//main[@aria-labelledby="contact-title"]')->length);
        $this->assertSame(1, $xp->query('//h1')->length);
        $this->assertSame(0, $xp->query('//*[@style]')->length);
        $this->assertSame(1, $xp->query('//nav[@aria-label="Breadcrumb"]')->length);
        if ($page === 'contact') {
            $this->assertSame(3, $xp->query('//a[contains(@class,"contact-channel")]')->length);
            $this->assertSame($filled ? 3 : 0, $xp->query('//article')->length);
            foreach (['/form', '/faq', '/faq?a=5#flush-collapse5', '/faq?a=6#flush-collapse6', 'mailto:cs@lunareafurniture.com'] as $url) {
                $this->assertSame(1, $xp->query('//a[@href="' . $url . '"]')->length);
            }
            $this->assertStringContainsString('phone=' . LUNAREA_CS_WHATSAPP_E164, $html);
            $this->assertStringContainsString('phone=' . LUNAREA_RETURN_WHATSAPP_E164, $html);
            $this->assertStringNotContainsString(LUNAREA_CS_WHATSAPP_E164, strip_tags($html));
            $this->assertStringNotContainsString(LUNAREA_RETURN_WHATSAPP_E164, strip_tags($html));
        } else {
            $this->assertSame(1, $xp->query('//form[@action="/actionform" and @method="post"]')->length);
            foreach (['nama', 'nohp', 'alamat', 'pesan'] as $name) $this->assertSame(1, $xp->query('//*[@name="' . $name . '"]')->length);
            $this->assertSame(2, $xp->query('//form//*[@required]')->length);
            $this->assertSame(1, $xp->query('//input[@name="nohp" and @type="tel" and @autocomplete="tel"]')->length);
            $this->assertSame(1, $xp->query('//button[@id="btn-kirim" and @type="submit" and @disabled]')->length);
            foreach (['contact-name', 'contact-phone', 'contact-address', 'contact-message'] as $id) $this->assertSame(1, $xp->query('//label[@for="' . $id . '"]')->length);
            $this->assertSame(1, $xp->query('//input[@name="' . csrf_token() . '"]')->length);
            $this->assertSame($filled ? 1 : 0, $xp->query('//*[@role="status"]')->length);
        }
        if ($filled) $this->assertStringContainsString('&lt;contoh&gt;', $html);
        $this->assertFileExists(FCPATH . 'css/buyer-contact.css');
        if (getenv('BUYER_CONTACT_ARTIFACTS') !== '1') return;
        $dir = WRITEPATH . 'cache/buyer-contact-review';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $url = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
        $styles = '';
        foreach (['bootstrap.min.css', 'style.css', 'commerce-responsive.css', 'storefront-catalog.css', 'buyer-navigation.css', 'buyer-footer.css', 'buyer-contact.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        session()->set(['isLogin' => false, 'role' => null, 'email' => null]);
        $nav = view('layout/navbar', ['title' => $page === 'contact' ? 'Kontak' : 'Kontak Kami'], ['saveData' => false]);
        $promo = view('partials/buyer_promo', ['desktopText' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'], ['saveData' => false]);
        $footer = view('layout/buyer_footer', ['footerVariant' => 'full'], ['saveData' => false]);
        $content = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $url . '/img/', $promo . $nav . '<div class="buyer-page-content">' . $html . '</div>' . $footer);
        $content = str_replace('src="/img/', 'src="' . $url . '/img/', $content);
        foreach (['default' => \App\Models\KonstantaModel::defaultThemeWarna(), 'sand' => ['primary' => '#2F2D20', 'primaryHover' => '#232217', 'soft' => '#F7F2ED', 'soft2' => '#DFCBB9', 'background' => '#FEFEFF']] as $name => $theme) {
            $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--hijau:' . $theme['primary'] . ';}';
            file_put_contents($dir . '/' . $page . '-' . ($filled ? 'filled' : 'empty') . '-' . $name . '.html', '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '</style><script defer src="' . $url . '/js/buyer-navigation.js"></script></head><body class="buyer-layout">' . $content . '</body></html>');
        }
    }
    public static function contactCases(): array { return [['contact', false], ['contact', true], ['form', false], ['form', true]]; }
}
