<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerHelpViewTest extends CIUnitTestCase
{
    /** @dataProvider pages */
    public function testHelpViewsPreserveContentAndTrackingContracts(string $page, string $state): void
    {
        $data = ['voucher' => [['nama' => 'Voucher contoh', 'syarat_ketentuan' => '<p>Ketentuan voucher contoh tetap tersedia.</p>']], 'keyword' => '', 'courier' => '', 'couriers' => ['jne' => 'JNE', 'tiki' => 'TIKI'], 'order' => null, 'shipment' => null, 'tracking' => [], 'error' => ''];
        if ($state !== 'initial' && $page === 'lacakPengiriman') $data['keyword'] = 'RESI-CONTOH-12345678901234567890';
        if ($state === 'error') $data['error'] = 'Periksa kembali nomor resi dan kurir yang dipilih.';
        if (in_array($state, ['success', 'internal', 'no-history'], true)) {
            $data['order'] = ['id_midtrans' => 'ORDER-CONTOH-12345678901234567890', 'nama_pen' => 'Pembeli Rahasia Lengkap', 'kurir' => 'JNE', 'resi' => $data['keyword']];
            $data['tracking'] = ['success' => true, 'provider' => 'PROVIDER-HIDDEN', 'summary' => ['status' => 'Dalam pengiriman', 'awb' => $data['keyword'], 'courier' => 'JNE', 'service' => 'REG', 'date' => '2026-10-15'], 'detail' => ['origin' => 'Semarang, Jawa Tengah', 'destination' => str_repeat('Kecamatan tujuan pengiriman ', 4)], 'history' => $state === 'no-history' ? [] : [['date' => '2026-10-15 13:00:00', 'desc' => str_repeat('Paket diproses di fasilitas pengiriman. ', 5), 'location' => 'Semarang'], ['date' => '2026-10-14 08:30:00', 'desc' => 'Paket diterima kurir.', 'location' => 'Semarang']]];
            if ($state === 'internal') $data['shipment'] = ['shipping_method_code' => 'internal_delivery', 'tracking_number' => ''];
        }
        $renderer = new class {
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render(string $page, array $data): string { extract($data, EXTR_SKIP); ob_start(); try { include APPPATH . 'Views/pages/' . $page . '.php'; return ob_get_contents(); } finally { ob_end_clean(); } }
        };
        $html = $renderer->render($page, $data);
        $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); $xp = new DOMXPath($dom);
        $this->assertSame(1, $xp->query('//main')->length);
        $this->assertSame(1, $xp->query('//h1')->length);
        $this->assertSame(4, $xp->query('//nav[@aria-label="Bantuan dan informasi"]/a')->length);
        $this->assertSame(1, $xp->query('//nav[@aria-label="Bantuan dan informasi"]/a[@aria-current="page"]')->length);
        if ($page === 'faq') {
            $this->assertSame(8, $xp->query('//article//div[contains(@class,"accordion-item")]')->length);
            $this->assertSame(8, $xp->query('//article//*[@data-bs-parent="#accordionFlushExample"]')->length);
            $this->assertStringContainsString('flush-collapse6', $html);
            $this->assertStringNotContainsString('console.log', $html);
        } elseif ($page === 'lacakPengiriman') {
            $this->assertSame(1, $xp->query('//form[@action="/lacak-pengiriman" and @method="post"]')->length);
            $this->assertSame(1, $xp->query('//label[@for="tracking-keyword"]')->length);
            $this->assertSame(1, $xp->query('//select[@name="courier"]')->length);
            $this->assertStringNotContainsString('PROVIDER-HIDDEN', $html);
            $this->assertStringNotContainsString('Pembeli Rahasia Lengkap', $html);
            if ($state === 'success') $this->assertSame(2, $xp->query('//*[contains(concat(" ",@class," ")," tracking-timeline-item ")]')->length);
            if ($state === 'error') $this->assertSame(1, $xp->query('//*[@role="status"]')->length);
            if ($state === 'internal') $this->assertStringContainsString('Jasa Kirim Toko', $html);
        } else {
            $this->assertGreaterThan(5, $xp->query('//article//p')->length);
            $this->assertGreaterThan(5, $xp->query('//article//h2')->length);
            if ($page === 'syaratdanketentuan') { $this->assertSame(1, $xp->query('//*[@id="voucher"]')->length); $this->assertStringContainsString('Ketentuan voucher contoh tetap tersedia.', $html); }
        }
        if (getenv('BUYER_HELP_ARTIFACTS') !== '1') return;
        $dir = WRITEPATH . 'cache/buyer-help-review'; if (!is_dir($dir)) mkdir($dir,0775,true);
        $url = 'file:///' . str_replace('\\','/',rtrim(FCPATH,'\\/')); $styles = '';
        foreach (['bootstrap.min.css','style.css','commerce-responsive.css','storefront-catalog.css','buyer-navigation.css','buyer-footer.css','buyer-help.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        session()->set(['isLogin' => false, 'role' => null, 'email' => null]);
        $title = $page === 'lacakPengiriman' ? 'Lacak Pengiriman' : ($page === 'faq' ? 'FAQ' : ($page === 'syaratdanketentuan' ? 'Syarat dan Ketentuan' : 'Kebijakan Privasi'));
        $nav = view('layout/navbar', ['title' => $title], ['saveData' => false]);
        $promo = view('partials/buyer_promo', ['desktopText' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'], ['saveData' => false]);
        $footer = view('layout/buyer_footer', ['footerVariant' => $page === 'lacakPengiriman' ? 'compact' : 'full'], ['saveData' => false]);
        $content = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $url . '/img/', $promo . $nav . '<div class="buyer-page-content">' . $html . '</div>' . $footer);
        $content = str_replace('src="/img/', 'src="' . $url . '/img/', $content);
        foreach (['default' => \App\Models\KonstantaModel::defaultThemeWarna(), 'sand' => ['primary'=>'#2F2D20','primaryHover'=>'#232217','soft'=>'#F7F2ED','soft2'=>'#DFCBB9','background'=>'#FEFEFF']] as $themeName => $theme) {
            $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--hijau:' . $theme['primary'] . ';}';
            file_put_contents($dir . '/' . $page . '-' . $state . '-' . $themeName . '.html','<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '</style><script defer src="' . $url . '/js/bootstrap.bundle.min.js"></script><script defer src="' . $url . '/js/buyer-navigation.js"></script></head><body class="buyer-layout">' . $content . '</body></html>');
        }
    }
    public static function pages(): array { return [['faq','initial'],['syaratdanketentuan','initial'],['kebijakanprivasi','initial'],['lacakPengiriman','initial'],['lacakPengiriman','error'],['lacakPengiriman','success'],['lacakPengiriman','internal'],['lacakPengiriman','no-history']]; }
}
