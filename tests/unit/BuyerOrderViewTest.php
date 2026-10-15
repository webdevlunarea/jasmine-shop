<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerOrderViewTest extends CIUnitTestCase
{
    protected function tearDown(): void { session()->remove(['isLogin', 'role', 'email']); parent::tearDown(); }

    /** @dataProvider orderStates */
    public function testOrderJourneyPreservesAmountsActionsAndStatus(string $state, string $page, string $status, string $payment, ?array $shipment, bool $proof): void
    {
        session()->set(['isLogin' => true, 'role' => 0, 'email' => 'buyer@example.test']);
        $data = [
            'pemesanan' => ['id_midtrans' => 'ORDER-FIXTURE-12345678901234567890', 'status' => $status, 'bukti_bayar' => $proof ? 'fixture.jpg' : '', 'resi' => 'TRACKING-FIXTURE-12345678901234567890', 'alamat_pen' => 'Jalan Contoh Nomor 12 <alamat>, Ngaliyan, Semarang'],
            'dataMid' => ['payment_type' => $payment, 'gross_amount' => 1954000, 'order_id' => 'ORDER-FIXTURE-12345678901234567890', 'expiry_time' => '2099-12-31 23:59:59'],
            'bank' => 'bca', 'va_number' => $payment === 'qris' ? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j0ioAAAAASUVORK5CYII=' : '123456789012345678901234',
            'biller_code' => '', 'waktuExpire' => '31 Des 2099 23:59:59', 'waktu' => '01:00:00', 'kurir' => $status === 'Proses' ? 'kosong' : 'JNE',
            'shipment' => $shipment, 'wsUrl' => 'ws://fixture.invalid',
            'items' => [['name' => str_repeat('Lemari putih ', 5) . '<produk>', 'quantity' => 2, 'value' => 900000], ['name' => 'Ongkir', 'quantity' => 1, 'value' => 125000], ['name' => 'Voucher', 'quantity' => 1, 'value' => -50000], ['name' => 'Proteksi kaca', 'quantity' => 1, 'value' => 25000], ['name' => 'Biaya Admin', 'quantity' => 1, 'value' => 4000]],
            'caraPembayaran' => [['nama' => 'Mobile banking', 'isi' => 'Buka aplikasi bank.<br>Periksa nomor dan nominal pembayaran.']],
        ];
        $renderer = new class {
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render(string $page, array $data): string {
                extract($data, EXTR_SKIP); ob_start();
                try { include APPPATH . 'Views/pages/' . $page . '.php'; return ob_get_contents(); } finally { ob_end_clean(); }
            }
        };
        $html = $renderer->render($page, $data);
        $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); $xp = new DOMXPath($dom);
        $this->assertSame(1, $xp->query('//main[@aria-labelledby="order-title"]')->length);
        $this->assertSame(1, $xp->query('//h1')->length);
        $this->assertStringContainsString('&lt;produk&gt;', $html);
        $this->assertStringContainsString('1.954.000', $html);
        $this->assertStringContainsString('-50.000', $html);
        $this->assertSame(2, $xp->query('//*[contains(concat(" ",@class," ")," order-detail-section ")]')->length);
        if ($page === 'orderProgress') {
            $this->assertSame(1, $xp->query('//li[@aria-current="step"]')->length);
            $this->assertStringContainsString('/cancelorder/ORDER-FIXTURE-', $html);
            if ($payment === 'rekening' && !$proof) {
                $this->assertSame(1, $xp->query('//form[@method="post" and @enctype="multipart/form-data"]')->length);
                $this->assertSame(1, $xp->query('//input[@name="buktiBayar" and @type="file"]')->length);
                $this->assertSame(1, $xp->query('//button[@id="btn-submit-bayar" and @disabled]')->length);
            }
            if ($payment === 'qris') {
                $this->assertSame(1, $xp->query('//*[@id="modal-qr" and contains(@class,"d-none")]')->length);
                $this->assertSame(1, $xp->query('//button[@aria-label="Tutup kode QR"]')->length);
            }
            if ($proof) $this->assertStringContainsString('Pembayaran sedang diverifikasi', $html);
        } elseif ($page === 'orderShipping') {
            $this->assertStringContainsString('&lt;alamat&gt;', $html);
            $this->assertStringContainsString('/lacak-pengiriman?keyword=', $html);
            if ($status === 'Selesai') {
                $this->assertStringContainsString('Pesanan selesai', $html);
                $this->assertSame(1, $xp->query('//li[@aria-current="step"]/span[text()="4"]')->length);
            }
        } else {
            $this->assertSame(0, $xp->query('//ol[contains(@class,"order-steps")]')->length);
            $this->assertStringNotContainsString('Cara pembayaran', $html);
            $this->assertStringNotContainsString('/payorder/', $html);
        }
        if (getenv('BUYER_ORDER_ARTIFACTS') !== '1') return;
        $dir = WRITEPATH . 'cache/buyer-order-review'; if (!is_dir($dir)) mkdir($dir,0775,true);
        $url = 'file:///' . str_replace('\\','/',rtrim(FCPATH,'\\/'));
        $styles = ''; foreach (['bootstrap.min.css','style.css','commerce-responsive.css','buyer-navigation.css','buyer-footer.css','buyer-order.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        $nav = view('layout/navbar',['title'=>'Peroses Pembayaran'],['saveData'=>false]);
        $promo = view('partials/buyer_promo',['desktopText'=>'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'],['saveData'=>false]);
        $footer = view('layout/buyer_footer',['footerVariant'=>'minimal'],['saveData'=>false]);
        $content = preg_replace('~src="https?://[^\"]*/img/~','src="' . $url . '/img/',$promo . $nav . '<div class="buyer-page-content">' . $html . '</div>' . $footer);
        foreach (['default'=>\App\Models\KonstantaModel::defaultThemeWarna(),'sand'=>['primary'=>'#2F2D20','primaryHover'=>'#232217','soft'=>'#F7F2ED','soft2'=>'#DFCBB9','background'=>'#FEFEFF']] as $name=>$theme) {
            $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--hijau:' . $theme['primary'] . ';}';
            file_put_contents($dir . '/' . $state . '-' . $name . '.html','<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '</style><script defer src="' . $url . '/js/bootstrap.bundle.min.js"></script><script defer src="' . $url . '/js/buyer-navigation.js"></script><script defer src="' . $url . '/js/buyer-order.js"></script></head><body class="buyer-layout">' . $content . '</body></html>');
        }
    }
    public static function orderStates(): array
    {
        $external = ['shipping_method_code'=>'external','courier_name'=>'JNE','service_name'=>'REG','shipping_cost'=>125000,'tracking_number'=>'TRACKING-FIXTURE-12345678901234567890','shipping_status'=>'Dikirim'];
        $internal = ['shipping_method_code'=>'internal_delivery','shipping_cost'=>0,'tracking_number'=>'','shipping_status'=>'Dalam Pengiriman'];
        return [
            ['pending','orderProgress','Menunggu Pembayaran','bank_transfer',null,false],
            ['manual','orderProgress','Menunggu Pembayaran Rekening','rekening',null,false],
            ['verification','orderProgress','Menunggu Pembayaran Rekening','rekening',null,true],
            ['qris','orderProgress','Menunggu Pembayaran','qris',null,false],
            ['processing','orderShipping','Proses','bank_transfer',null,false],
            ['external','orderShipping','Dikirim','bank_transfer',$external,false],
            ['internal','orderShipping','Dikirim','rekening',$internal,false],
            ['completed','orderShipping','Selesai','bank_transfer',$external,false],
            ['expired','orderExpire','Kadaluarsa','bank_transfer',null,false],
            ['cancelled','orderExpire','Dibatalkan','rekening',null,false],
        ];
    }
    public function testCompletedOrderUsesExistingDetailRendererWithoutChangingStatus(): void
    {
        $source = file_get_contents(APPPATH . 'Controllers/Pages.php');
        $this->assertMatchesRegularExpression("/case 'Selesai':\\s*case 'Dikirim':/", $source);
    }
}
