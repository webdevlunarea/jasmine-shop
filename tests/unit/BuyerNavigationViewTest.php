<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerNavigationViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        session()->remove(['isLogin', 'role', 'email']);
        parent::tearDown();
    }

    /** @dataProvider navigationStates */
    public function testNavbarSeparatesBuyerAndAdminNavigation(string $state, bool $loggedIn, ?int $role, ?string $email): void
    {
        session()->set(['isLogin' => $loggedIn, 'role' => $role, 'email' => $email]);
        $html = view('layout/navbar', ['title' => 'Beranda'], ['saveData' => false]);

        if ($role === 1) {
            $this->assertStringContainsString('admin-sidebar', $html);
            $this->assertStringNotContainsString('buyer-account-toggle', $html);
            $this->assertStringNotContainsString('buyer-mobile-header', $html);
        } else {
            $this->assertStringContainsString('buyer-mobile-header', $html);
            $this->assertStringContainsString('mobile-navbar-spacer', $html);
            $this->assertStringNotContainsString('id="akun-icon"', $html);
            $this->assertSame(3, substr_count($html, '<svg class="buyer-nav-icon"'));
            if ($loggedIn && $email !== 'tamu') {
                $this->assertSame(1, substr_count($html, 'id="buyer-account-toggle"'));
                $this->assertStringContainsString('aria-controls="buyer-account-menu"', $html);
                $this->assertStringContainsString('aria-labelledby="buyer-account-title" hidden', $html);
                $this->assertStringContainsString('href="/lacak-pengiriman"', $html);
                $dom = new DOMDocument();
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
                $xpath = new DOMXPath($dom);
                $this->assertSame(0, $xpath->query('//*[@id="buyer-account-menu"]/ancestor::nav')->length,
                    'Dialog must be outside the filtered/fixed navbar containing block.');
            } else {
                $this->assertStringNotContainsString('id="buyer-account-menu"', $html);
            }
        }

        // Optional browser fixtures: real PHP rendering, synthetic session, no database or remote API.
        if (getenv('BUYER_NAV_ARTIFACTS') === '1') {
            $this->writeBrowserFixture($state, $html);
        }
    }

    public static function navigationStates(): array
    {
        return [
            'member' => ['member', true, 0, 'buyer@example.test'],
            'guest' => ['guest', true, 0, 'tamu'],
            'anonymous' => ['anonymous', false, null, null],
            'admin' => ['admin', true, 1, 'admin@example.test'],
        ];
    }

    public function testAccountTabsHaveCurrentPageSemantics(): void
    {
        $html = view('partials/account_nav', ['activeAccountMenu' => 'voucher'], ['saveData' => false]);
        $this->assertStringContainsString('<nav class="account-mobile-tabs"', $html);
        $this->assertSame(2, substr_count($html, 'aria-current="page"'));
        $this->assertStringContainsString('href="/voucher" aria-current="page"', $html);
    }

    public function testPromoBannerEscapesConfiguredTextAndUsesMobileFallback(): void
    {
        $html = view('partials/buyer_promo', ['desktopText' => 'Promo <script>test</script>'], ['saveData' => false]);
        $this->assertSame(2, substr_count($html, '&lt;script&gt;test&lt;/script&gt;'));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('buyer-promo-banner--mobile', $html);
    }

    private function writeBrowserFixture(string $state, string $html): void
    {
        $directory = WRITEPATH . 'cache/buyer-navigation-review';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $publicUrl = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
        $html = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $publicUrl . '/img/', $html);
        $styles = '';
        foreach (['bootstrap.min.css', 'style.css', 'commerce-responsive.css', 'buyer-navigation.css'] as $file) {
            $styles .= '<link rel="stylesheet" href="' . $publicUrl . '/css/' . $file . '">';
        }
        $tabs = view('partials/account_nav', ['activeAccountMenu' => 'voucher'], ['saveData' => false]);
        $promo = $state === 'admin' ? '' : view('partials/buyer_promo', [
            'desktopText' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%',
            'mobileText' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%',
        ], ['saveData' => false]);
        $page = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . $styles . '<style>:root{--hijau:#207e44;--hijaumuda:#edfaf1}.material-icons{font-size:0!important}.material-icons::after{content:"●";font-size:20px}</style>'
            . '<script defer src="' . $publicUrl . '/js/buyer-navigation.js"></script></head><body>' . $promo . $html
            . '<main class="konten account-shell"><div class="container"><div class="account-layout">' . $tabs
            . '<section class="account-content-card"><h1>Voucher saya</h1><p>Konten pembeli untuk pengujian navigasi.</p><div style="height:1200px"></div></section></div></div></main></body></html>';
        file_put_contents($directory . '/' . $state . '.html', $page);
        if ($state === 'member') {
            file_put_contents($directory . '/member-long-promo.html', str_replace(
                'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%',
                str_repeat('Harga khusus pembelian pertama dan gratis ongkir hingga 100%. ', 4), $page));
            foreach (['home', 'all'] as $name) {
                $source = file_get_contents(APPPATH . 'Views/pages/' . $name . '.php');
                $start = strpos($source, 'const kategoriDropdownWraps =');
                $end = strpos($source, $name === 'home'
                    ? "document.querySelectorAll('[data-flash-countdown]')" : 'let bukaMeta = false;', $start);
                $script = "const containeKategoriScrollElm = document.querySelector('.container-kategori-scroll');\n"
                    . substr($source, $start, $end - $start);
                file_put_contents($directory . '/categories-' . $name . '.js', $script);
                $category = '<div class="container-kategori-scroll"><div class="container-kategori">'
                    . '<div class="kategori-wrap has-dropdown" style="position:fixed;right:16px;bottom:100px;width:96px;height:72px">'
                    . '<a class="kategori" href="#test">Lemari</a><button type="button" class="kategori-arrow" aria-expanded="false" aria-label="Buka kategori">v</button>'
                    . '<div class="kategori-dropdown-menu">' . str_repeat('<a href="#test">Lemari Dewasa</a>', 12)
                    . '</div></div></div></div>';
                $scriptUrl = 'file:///' . str_replace('\\', '/', $directory . '/categories-' . $name . '.js');
                file_put_contents($directory . '/categories-' . $name . '.html', str_replace('</body>',
                    $category . '<script src="' . $scriptUrl . '"></script></body>', $page));
            }
        }
    }
}
