<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerCheckoutViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        session()->remove(['isLogin', 'role', 'email', 'checkout_address_owner', 'checkout_address_id']);
        parent::tearDown();
    }

    /** @dataProvider checkoutStates */
    public function testCheckoutKeepsControlsAndRendersClearSections(string $state): void
    {
        $guest = $state === 'guest';
        $glassAvailable = $state !== 'no-glass';
        $email = $guest ? 'tamu' : 'buyer@example.test';
        session()->set(['isLogin' => true, 'role' => 0, 'email' => $email]);
        $address = ['prov_id' => '33', 'prov' => 'Jawa Tengah', 'kab_id' => '3374', 'kab' => 'Semarang', 'kec_id' => '337401', 'kec' => 'Ngaliyan', 'desa' => 'Ngaliyan', 'kodepos' => '50181', 'add' => 'Jalan Contoh Nomor 12', 'alamat' => 'Jalan Contoh Nomor 12, Ngaliyan, Semarang'];
        $voucher = ['id' => 2, 'nama' => 'Hemat belanja', 'keterangan' => 'Potongan belanja pilihan', 'max_potongan' => 50000];
        $data = [
            'msg' => '', 'user' => ['nama' => 'Pelanggan Contoh', 'email' => $email, 'nohp' => '081234567890', 'alamat' => $address],
            'provinsi' => [['id' => '33', 'label' => 'Jawa Tengah']], 'kabupaten' => [['id' => '3374', 'label' => 'Semarang']],
            'kecamatan' => [['id' => '337401', 'label' => 'Ngaliyan']], 'desa' => [['label' => 'Ngaliyan', 'kodepos' => '50181']],
            'produk' => [['nama' => 'Lemari pakaian minimalis dua pintu dengan laci tambahan', 'harga' => 1000000, 'diskon' => 10]],
            'keranjang' => [['varian' => 'Putih natural']], 'jumlah' => [2], 'subtotal' => 1800000, 'total' => 1800000,
            'diskonVoucher' => in_array($state, ['member', 'all-discounts'], true) ? 50000 : 0, 'potonganPreorder' => $state === 'all-discounts' ? 100000 : 0,
            'gantiKaca' => $glassAvailable, 'totalkaca' => $glassAvailable ? 25000 : 0, 'poin' => $guest ? 0 : 20000, 'usepoin' => $state === 'all-discounts', 'biayaAdmin' => 4000,
            'voucher' => $guest || $state === 'no-voucher' ? [] : [$voucher], 'voucherSelected' => $state === 'member' ? $voucher : null,
            'activeVoucher' => $state === 'member' ? 2 : 0, 'shippingWeightGrams' => 40000,
            'shippingWeightDetails' => [['name' => 'Lemari pakaian minimalis dua pintu', 'variant' => 'Putih natural', 'quantity' => 2, 'dimension' => '120 x 45 x 180', 'weight_kg' => 20, 'total_weight_kg' => 40]],
            'addressBookAvailable' => !$guest,
            'addressBook' => !$guest ? [['id' => 1, 'label' => 'Rumah', 'recipient_name' => 'Pelanggan Contoh', 'is_default' => true]] : [],
        ];
        $renderer = new class {
            private array $data;
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function include(string $name): string { return view($name, $this->data, ['saveData' => false]); }
            public function render(array $data): string {
                $this->data = $data;
                extract($data, EXTR_SKIP);
                ob_start();
                try { include APPPATH . 'Views/pages/checkoutcorecc.php'; return ob_get_contents(); }
                finally { ob_end_clean(); }
            }
        };
        $html = $renderer->render($data);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xp = new DOMXPath($dom);
        $this->assertSame(1, $xp->query('//main[@aria-labelledby="checkout-title"]')->length);
        $this->assertSame(1, $xp->query('//h1')->length);
        $this->assertSame(4, $xp->query('//main//section[contains(@class,"checkout-section")]')->length);
        $this->assertSame(1, $xp->query('//aside[@aria-labelledby="checkout-summary-title"]')->length);
        $this->assertSame(1, $xp->query('//section[contains(@class,"checkout-delivery")]//*[@id="shipping-options"]')->length);
        $this->assertSame(0, $xp->query('//aside//*[@id="shipping-options"]')->length);
        $this->assertSame(1, $xp->query('//form[@id="form-checkout" and @action="/actionpaycore" and @method="post"]')->length);
        foreach (['shipping_quote_token', 'ganti-kaca', 'nama', 'nohp', 'alamat_add', 'alamat', 'provinsi', 'kota', 'kecamatan', 'kodepos', 'note'] as $name) {
            $this->assertSame(1, $xp->query('//*[@name="' . $name . '"]')->length, $name);
        }
        $this->assertSame($glassAvailable ? 'true' : 'false', $xp->query('//form[@id="form-checkout"]//input[@name="ganti-kaca"]')->item(0)->getAttribute('value'));
        foreach (['checkout-name', 'checkout-phone', 'checkout-street', 'checkout-province', 'checkout-city', 'checkout-district', 'checkout-village', 'checkout-note'] as $id) {
            $this->assertSame(1, $xp->query('//*[@id="' . $id . '"]')->length);
            $this->assertSame(1, $xp->query('//label[@for="' . $id . '"]')->length);
        }
        $this->assertSame(1, $xp->query('//button[@id="btn-checkout" and @disabled]')->length);
        $this->assertSame(1, $xp->query('//button[@type="submit" and contains(@class,"w-100")]')->length);
        $this->assertSame(1, $xp->query('//*[@id="modal-metode-bayar" and @role="dialog"]')->length);
        $this->assertStringContainsString('40,00 kg untuk estimasi ongkir', $html);
        $this->assertStringContainsString('checkout-shipping-audit', $html);
        foreach (['payment-shipping-cost', 'payment-base-total', 'biaya-admin', 'biaya-total', 'payment-submit'] as $id) {
            $this->assertSame(1, $xp->query('//*[@id="' . $id . '"]')->length);
        }
        $this->assertSame(1, $xp->query('//button[@id="payment-submit" and @disabled]')->length);
        $this->assertStringContainsString('Subtotal produk', $html);
        if ($state === 'all-discounts') {
            foreach (['Potongan voucher', 'Potongan preorder', 'Luna Poin digunakan', 'Proteksi kaca'] as $text) $this->assertStringContainsString($text, $html);
        }

        if (getenv('BUYER_CHECKOUT_ARTIFACTS') !== '1') return;
        $dir = WRITEPATH . 'cache/buyer-checkout-review';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $url = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
        $styles = '';
        foreach (['bootstrap.min.css', 'style.css', 'commerce-responsive.css', 'buyer-navigation.css', 'buyer-footer.css', 'buyer-checkout.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        $nav = view('layout/navbar', ['title' => 'Check Out'], ['saveData' => false]);
        $promo = view('partials/buyer_promo', ['desktopText' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'], ['saveData' => false]);
        $footer = view('layout/buyer_footer', ['footerVariant' => 'minimal'], ['saveData' => false]);
        $content = str_replace('src="/img/', 'src="' . $url . '/img/', $promo . $nav . '<div class="buyer-page-content">' . $html . '</div>' . $footer);
        foreach (['default' => \App\Models\KonstantaModel::defaultThemeWarna(), 'sand' => ['primary' => '#2F2D20', 'primaryHover' => '#232217', 'soft' => '#F7F2ED', 'soft2' => '#DFCBB9', 'background' => '#FEFEFF']] as $name => $theme) {
            $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--hijau:' . $theme['primary'] . ';}';
            file_put_contents($dir . '/' . $state . '-' . $name . '.html', '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '</style><script defer src="' . $url . '/js/buyer-navigation.js"></script><script defer src="' . $url . '/js/buyer-checkout.js"></script></head><body class="buyer-layout">' . $content . '</body></html>');
        }
    }

    public static function checkoutStates(): array { return [['member'], ['guest'], ['no-voucher'], ['all-discounts'], ['no-glass']]; }
}
