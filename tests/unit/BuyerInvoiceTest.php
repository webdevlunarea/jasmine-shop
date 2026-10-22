<?php

use App\Services\Orders\InvoiceDocument;
use App\Services\Orders\InvoicePdf;
use CodeIgniter\Test\CIUnitTestCase;

final class BuyerInvoiceTest extends CIUnitTestCase
{
    private function order(): array
    {
        return ['id_midtrans'=>'L00000227', 'email_cus'=>'buyer@example.test', 'nama_pen'=>'Pembeli & Keluarga',
            'alamat_pen'=>'Jl. Contoh 12, Ngaliyan, Semarang', 'status'=>'Proses', 'kurir'=>'JNE - REG - Rp 125.000', 'resi'=>'RESI123',
            'items'=>json_encode([['name'=>'Lemari Putih (Natural)', 'value'=>1000000, 'quantity'=>2]]),
            'data_mid'=>json_encode(['transaction_time'=>'2026-10-15 12:30:00','payment_type'=>'bank_transfer','va_numbers'=>[['bank'=>'bca']], 'gross_amount'=>2075000]),
            'diskonVoucher'=>50000, 'pakai_poin'=>10000, 'cashback'=>20000];
    }

    public function testOnlyAuthenticatedOwnerOrAdminCanReadInvoice(): void
    {
        $order = $this->order();
        $this->assertTrue(InvoiceDocument::canRead($order, 'buyer@example.test', '0', true));
        $this->assertTrue(InvoiceDocument::canRead($order, 'admin@example.test', '1', true));
        $this->assertFalse(InvoiceDocument::canRead($order, 'other@example.test', '0', true));
        $this->assertFalse(InvoiceDocument::canRead($order, '', '0', true));
        $this->assertFalse(InvoiceDocument::canRead($order, 'buyer@example.test', '0', false));
        $this->assertFalse(InvoiceDocument::canRead(null, 'admin@example.test', '1', true));
    }

    public function testTotalsUseOrderSnapshotsAndReconcileWithoutInventingAdminFees(): void
    {
        $order = $this->order();
        $doc = InvoiceDocument::build($order, ['shipping_cost'=>125000,'courier_name'=>'JNE','service_name'=>'REG']);
        $this->assertSame(2075000, $doc['total']);
        $this->assertSame($doc['total'], array_sum(array_column($doc['rows'], 'amount')));
        $this->assertSame(10000, end($doc['rows'])['amount']);
        $this->assertNotContains('Biaya Admin', array_column($doc['rows'], 'label'));
        $this->assertSame('15 Okt 2026, 12:30 WIB', $doc['date']);
        $this->assertSame('Invoice-Lunarea-L00000227.pdf', $doc['filename']);
        $this->assertSame('Lunas', $doc['badge']);
        $this->assertSame('Transfer bank · BCA', $doc['method']);
        $this->assertSame(20000, $doc['cashback']);
        $this->assertSame(InvoiceDocument::build($order), InvoiceDocument::build($order));
        $this->assertSame($this->order(), $order);
    }

    public function testLegacyMissingAndPendingDataDoNotInventFreeShippingOrPaidStatus(): void
    {
        $doc = InvoiceDocument::build(['items'=>'bad json','data_mid'=>'{}','status'=>'Menunggu Pembayaran Rekening','kurir'=>'kosong','resi'=>'Menunggu pengiriman']);
        $this->assertNull($doc['total']);
        $this->assertFalse($doc['paid']);
        $this->assertSame('Belum dibayar', $doc['badge']);
        $this->assertSame('Belum tersedia', $doc['tracking']);
        $this->assertNotContains('Biaya pengiriman', array_column($doc['rows'], 'label'));
        $html = view('pages/invoice', ['invoice'=>$doc,'pdf'=>false], ['saveData'=>false]);
        $this->assertStringContainsString('bukan bukti pembayaran lunas', $html);
        $this->assertStringContainsString('Belum tercatat', $html);
        $this->assertStringNotContainsString('1970', $html);
    }

    public function testGlassProtectionSnapshotIsNamedAndReconcilesInvoiceTotal(): void
    {
        $order = $this->order();
        $payment = json_decode($order['data_mid'], true);
        $payment['_app_glass_service'] = ['selected'=>true, 'amount'=>50000, 'unit_price'=>25000];
        $payment['gross_amount'] = 2125000;
        $order['data_mid'] = json_encode($payment);

        $doc = InvoiceDocument::build($order, ['shipping_cost'=>125000,'courier_name'=>'JNE','service_name'=>'REG']);
        $labels = array_column($doc['rows'], 'label');
        $this->assertContains('Proteksi kaca', $labels);
        $this->assertSame(50000, $doc['rows'][array_search('Proteksi kaca', $labels, true)]['amount']);
        $this->assertSame($doc['total'], array_sum(array_column($doc['rows'], 'amount')));

        $payment['_app_glass_service']['selected'] = 'false';
        $order['data_mid'] = json_encode($payment);
        $withoutGlass = InvoiceDocument::build($order, ['shipping_cost'=>125000]);
        $this->assertNotContains('Proteksi kaca', array_column($withoutGlass['rows'], 'label'));
    }

    public function testViewEscapesCustomerContentAndPdfHasNoExternalDependencies(): void
    {
        $order = $this->order();
        $order['nama_pen'] = '<script>alert(1)</script>';
        $order['alamat_pen'] = '<img src="http://169.254.169.254/secret">';
        $doc = InvoiceDocument::build($order);
        $html = view('pages/invoice', ['invoice'=>$doc,'pdf'=>true], ['saveData'=>false]);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('https://', $html);
        $source = file_get_contents(APPPATH . 'Controllers/Pages.php');
        $start = strpos($source, 'public function invoice($id_mid)');
        $end = strpos($source, 'private function fixIsiArtikel()', $start);
        $method = substr($source, $start, $end-$start);
        $this->assertStringContainsString('attachment; filename=', $method);
        $this->assertStringContainsString('private, no-store', $method);
        $this->assertStringContainsString('InvoiceDocument::canRead(', $method);
        $this->assertStringNotContainsString('5000', $method);
    }

    public function testPdfIsRealA4AndSupportsMultiplePages(): void
    {
        $order = $this->order();
        $shipment = ['shipping_cost'=>125000,'courier_name'=>'JNE','service_name'=>'REG'];
        $doc = InvoiceDocument::build($order, $shipment);
        $renderer = new InvoicePdf();
        $pdf = $renderer->render($doc);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertMatchesRegularExpression('~/MediaBox\s*\[\s*0(?:\.\d+)?\s+0(?:\.\d+)?\s+595\.~', $pdf);
        $this->assertStringNotContainsString('/JavaScript', $pdf);
        $order['items'] = json_encode(array_fill(0, 85, ['name'=>str_repeat('Lemari Putih Natural ',4), 'value'=>1000000, 'quantity'=>2]));
        $payment=json_decode($order['data_mid'],true); $payment['gross_amount']=170075000; $order['data_mid']=json_encode($payment);
        $long = $renderer->render(InvoiceDocument::build($order, $shipment));
        preg_match_all('~/Type\s*/Page\b~', $long, $pages);
        $this->assertGreaterThan(1, count($pages[0]));
        if (getenv('INVOICE_ARTIFACTS') === '1') {
            $dir = WRITEPATH . 'cache/invoice-review';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            file_put_contents($dir . '/invoice.pdf', $pdf);
            file_put_contents($dir . '/invoice-long.pdf', $long);
            file_put_contents($dir . '/preview.html', view('pages/invoice', ['invoice'=>$doc,'pdf'=>false], ['saveData'=>false]));
        }
    }

    public function testControllerDownloadsPdfButRejectsOtherBuyersBeforeReadingShipping(): void
    {
        $make = static function (?array $order, bool $preview = false) {
            return new class($order, $preview) extends \App\Controllers\Pages {
                public int $shipmentReads = 0;
                public function __construct(?array $order, bool $preview) {
                    $this->pemesananModel = new class($order) {
                        private ?array $order;
                        public function __construct(?array $order) { $this->order=$order; }
                        public function getPemesanan($id) { return $this->order; }
                    };
                    $this->request = new class($preview) {
                        private bool $preview;
                        public function __construct(bool $preview) { $this->preview=$preview; }
                        public function getGet($key) { return $this->preview ? '1' : null; }
                    };
                    $this->response = \Config\Services::response(null, false);
                }
                protected function invoiceShipment(string $orderId): ?array { $this->shipmentReads++; return null; }
            };
        };
        try {
            session()->set(['email'=>'other@example.test','role'=>'0','isLogin'=>true]);
            $controller=$make($this->order()); $result=$controller->invoice('L00000227');
            $this->assertSame(404,$result->getStatusCode());
            $this->assertSame(0,$controller->shipmentReads);
            $this->assertStringNotContainsString('Pembeli', $result->getBody());
            session()->set('email','buyer@example.test');
            $result=$make($this->order())->invoice('L00000227');
            $this->assertSame(200,$result->getStatusCode());
            $this->assertStringContainsString('application/pdf',$result->getHeaderLine('Content-Type'));
            $this->assertSame('attachment; filename="Invoice-Lunarea-L00000227.pdf"',$result->getHeaderLine('Content-Disposition'));
            $this->assertSame('private, no-store, max-age=0',$result->getHeaderLine('Cache-Control'));
            $this->assertStringStartsWith('%PDF-',$result->getBody());
            $preview=$make($this->order(),true)->invoice('L00000227');
            $this->assertStringContainsString('text/html',$preview->getHeaderLine('Content-Type'));
            $this->assertStringContainsString('Unduh PDF',$preview->getBody());
            $this->assertSame(404,$make(null)->invoice('missing')->getStatusCode());
        } finally { session()->remove(['email','role','isLogin']); }
    }
}
