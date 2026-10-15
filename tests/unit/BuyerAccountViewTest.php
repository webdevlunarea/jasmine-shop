<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerAccountViewTest extends CIUnitTestCase
{
    /** @dataProvider memberPages */
    public function testConnectedCustomerPagesKeepNavigationAndActions(string $page, bool $filled): void
    {
        session()->set(['role' => '0', 'email' => 'pembeli@example.test', 'isLogin' => true, 'active' => true]);
        $data = $this->sampleData($filled);
        $renderer = new class {
            private string $menu;
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function include(string $name): string { return view($name, ['activeAccountMenu' => $this->menu], ['saveData' => false]); }
            public function render(string $page, array $data): string {
                $this->menu = $page === 'pointHistory' ? 'point' : $page;
                extract($data, EXTR_SKIP);
                ob_start();
                try { include APPPATH . 'Views/pages/' . $page . '.php'; return ob_get_contents(); } finally { ob_end_clean(); }
            }
        };
        $html = $renderer->render($page, $data);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xp = new DOMXPath($dom);
        $this->assertSame(1, $xp->query('//h1')->length, $page);
        $this->assertSame(1, $xp->query('//main[contains(@class,"account-content-card")]')->length, $page);
        $this->assertSame(2, $xp->query('//a[@aria-current="page"]')->length, $page);
        foreach (['/account', '/wishlist', '/cart', '/voucher', '/point', '/transaction'] as $url) $this->assertGreaterThanOrEqual(2, $xp->query('//a[@href="' . $url . '"]')->length, $page . $url);
        if ($page === 'account') {
            $this->assertStringContainsString('enctype="multipart/form-data"', $html);
            $this->assertSame(1, $xp->query('//form[@action="/account"]//input[@name="foto"]')->length);
            $this->assertStringNotContainsString('account-signout-link', $html);
            $this->assertStringContainsString('data-buyer-logout', $html);
            $this->assertStringNotContainsString('>Menu akun</h5>', $html);
        }
        if ($page === 'cart') {
            $this->assertStringContainsString('href="/checkout"', $html);
            if ($filled) {
                foreach (['/delcart/0', '/redcart/0', '/addcart/'] as $action) $this->assertStringContainsString($action, $html);
                $this->assertStringContainsString('2.000.000', $html);
            }
        }
        if ($page === 'wishlist') {
            $this->assertStringContainsString('action="/wishlisttocart"', $html);
            $this->assertSame($filled ? 0 : 1, $xp->query('//form[@action="/wishlisttocart"]//button[@disabled]')->length);
            if ($filled) $this->assertStringContainsString('Varian dipilih:', $html);
        }
        if ($page === 'voucher') {
            $this->assertSame(1, $xp->query('//*[@role="dialog" and @aria-modal="true"]')->length);
            $this->assertStringContainsString('action="/voucher/redeem"', $html);
            if ($filled) $this->assertStringContainsString('action="/voucher/claim"', $html);
        }
        if ($page === 'point') {
            $this->assertStringContainsString('href="/point/history"', $html);
            $this->assertStringContainsString('1 poin = Rp1', $html);
        }
        if ($page === 'pointHistory' && $filled) {
            $this->assertStringContainsString('+2.000 pts', $html);
            $this->assertStringContainsString('-500 pts', $html);
        }
        if ($page === 'transaction' && $filled) {
            $this->assertStringContainsString('data-bs-target="#collapse0"', $html);
            $this->assertStringContainsString('/order/ORDER-TEST-12345678901234567890', $html);
        }
        if (getenv('BUYER_ACCOUNT_ARTIFACTS') === '1') $this->fixture($page, $filled, $html);
    }

    public static function memberPages(): array
    {
        $cases = [];
        foreach (['account','wishlist','cart','voucher','point','pointHistory','transaction'] as $page) foreach ([false,true] as $filled) $cases[] = [$page,$filled];
        return $cases;
    }

    private function sampleData(bool $filled): array
    {
        $image = file_get_contents(FCPATH . 'img/logokategori/Lemari_Dewasa.webp');
        $product = ['id' => 12, 'path' => 'lemari-test', 'nama' => 'Lemari penyimpanan keluarga dengan nama produk yang panjang dan varian putih natural', 'harga' => 1250000, 'diskon' => 20, 'gambar' => $image, 'varian' => '["Putih","Natural"]', 'wishlist_varian' => 'Putih Natural', 'rate' => 5];
        $order = ['id_midtrans' => 'ORDER-TEST-12345678901234567890', 'status' => 'Menunggu Pembayaran', 'kurir' => 'JNE', 'resi' => '', 'nama_pen' => 'Pembeli Contoh', 'alamat_pen' => str_repeat('Alamat penerima sangat panjang di Semarang. ', 3), 'hp_pen' => '081234567890', 'bukti_bayar' => '',
            'items' => json_encode([['name' => $product['nama'], 'quantity' => 2, 'value' => 1000000, 'price' => 1000000]]),
            'data_mid' => json_encode(['gross_amount' => 2025000, 'transaction_time' => '2026-10-15 10:00:00', 'payment_type' => 'bank_transfer', 'va_numbers' => [['bank' => 'bca', 'va_number' => '1234567890']]])];
        $coupon = ['id' => 1, 'index' => 0, 'jenis' => 'diskon', 'nama' => 'Voucher belanja furnitur untuk member Lunarea', 'keterangan' => 'Berlaku sesuai syarat dan ketentuan voucher saat checkout.', 'kadaluarsa' => '2026-12-31', 'code' => []];
        $codeCoupon = $coupon; $codeCoupon['id'] = 2; $codeCoupon['code'] = [['code' => 'CONTOH']];
        return [
            'msg' => '', 'nama' => 'Pembeli dengan nama cukup panjang', 'nohp' => '081234567890', 'tgl_lahir' => '1990-01-01', 'foto' => 'data:image/webp;base64,' . base64_encode($image), 'is_google_account' => true,
            'profileCompletion' => 85, 'activeOrders' => $filled ? 1 : 0, 'voucherCount' => $filled ? 2 : 0, 'poinTotal' => 2000, 'wishlistCount' => $filled ? 1 : 0,
            'pendingPayment' => $filled ? $order : null, 'latestOrders' => $filled ? [$order] : [], 'latestReturn' => null, 'kurang_dari' => false, 'batas_tgl_lahir' => '',
            'keranjang' => $filled ? [['varian' => 'Putih Natural', 'index_gambar' => 0]] : [], 'produk' => $filled ? [$product] : [], 'jumlah' => [2], 'gambar' => [$image], 'berat' => 24, 'indStokHabis' => [], 'adaPesananPending' => null,
            'wishlist' => $filled ? [12] : [], 'voucher' => $filled ? [$coupon, $codeCoupon] : [], 'voucherClaimed' => $filled ? [$coupon] : [],
            'poin' => $filled ? 2000 : 0, 'tier' => ['label' => 'bronze', 'data' => [['nominal' => 500000]]], 'tierMeta' => ['label' => 'Bronze', 'min' => 0, 'next' => 1000000], 'pointSummary' => ['next_expiry' => '2026-12-31', 'expired' => []],
            'bonus' => ['bronze' => [['nominal' => '5%', 'nama' => 'Benefit member', 'keterangan' => 'Mengikuti syarat tier dan promo yang berlaku.']]],
            'bulan' => ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'], 'history' => $filled ? [['label'=>'bonus belanja','keterangan'=>'Poin dari pesanan Anda.','tanggal'=>'2026-10-15','nominal'=>2000],['label'=>'pembelian','keterangan'=>'Poin dipakai saat checkout.','tanggal'=>'2026-10-15','nominal'=>-500]] : [],
            'transaksi' => $filled ? [$order] : [], 'shipmentMap' => [], 'returMap' => [],
        ];
    }

    private function fixture(string $page, bool $filled, string $html): void
    {
        $dir = WRITEPATH . 'cache/buyer-account-review'; if (!is_dir($dir)) mkdir($dir,0775,true);
        $url = 'file:///' . str_replace('\\','/',rtrim(FCPATH,'\\/'));
        $styles = ''; foreach (['bootstrap.min.css','style.css','commerce-responsive.css','storefront-catalog.css','buyer-navigation.css','buyer-footer.css','buyer-account.css'] as $file) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $file . '">';
        $nav = view('layout/navbar',['title'=>'Akun'],['saveData'=>false]);
        $promo = view('partials/buyer_promo',['desktopText'=>'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'],['saveData'=>false]);
        $footer = view('layout/buyer_footer',['footerVariant'=>'compact'],['saveData'=>false]);
        $content = $promo . $nav . '<div class="buyer-page-content">' . $html . '</div>' . $footer;
        $content = preg_replace('~src="https?://[^\"]*/img/~','src="' . $url . '/img/',$content); $content = str_replace('src="/img/','src="' . $url . '/img/',$content);
        $theme = \App\Models\KonstantaModel::defaultThemeWarna();
        $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--hijau:' . $theme['primary'] . ';}';
        file_put_contents($dir . '/' . $page . ($filled ? '-filled' : '-empty') . '.html','<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '.material-icons{font-size:0!important}.material-icons::after{content:"●";font-size:18px}</style><script defer src="' . $url . '/js/bootstrap.bundle.min.js"></script><script defer src="' . $url . '/js/buyer-navigation.js"></script></head><body class="buyer-layout">' . $content . '</body></html>');
    }
}
