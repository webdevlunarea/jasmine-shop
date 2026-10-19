<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AdminWorkspaceViewTest extends CIUnitTestCase
{
    protected function tearDown(): void { session()->remove(['isLogin', 'role', 'email']); parent::tearDown(); }

    private function render(string $file, array $data): string
    {
        $renderer = new class {
            public function extend($name): string { return ''; }
            public function section($name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render($file, $data): string { extract($data, EXTR_SKIP); ob_start(); try { include APPPATH . 'Views/' . $file . '.php'; return ob_get_contents(); } finally { ob_end_clean(); } }
        };
        return $renderer->render($file, $data);
    }

    private function fixture(): array
    {
        $orders=[];
        foreach (['Proses', 'Menunggu Pembayaran Rekening', 'Dikirim', 'Selesai', 'Kadaluarsa'] as $index=>$status) {
            $orders[]=['id_midtrans'=>'L0000022'.($index+7), 'nama_pen'=>$index===0 ? 'Aditya <pelanggan>' : 'Pembeli '.($index+1), 'email_cus'=>'pembeli'.($index+1).'@example.test',
                'hp_pen'=>'081234567890', 'alamat_pen'=>'Jalan Contoh No. 12, Ngaliyan, Semarang', 'note'=>$index===0 ? 'Mohon hubungi sebelum pengiriman. <catatan>' : '', 'resi'=>$index<2 ? 'Menunggu pengiriman' : 'TEST227', 'kurir'=>'JNE', 'status'=>$status, 'bukti_bayar'=>$index===1 ? '/imgbuktibayar/L00000228' : false,
                'data_mid'=>['gross_amount'=>1920000, 'payment_type'=>'bank_transfer', 'transaction_time'=>'2026-10-16 12:34:56', '_app_payment_environment'=>'production'],
                'items'=>[['name'=>'Meja kerja minimalis / Putih <produk>', 'quantity'=>2, 'value'=>900000]],
                'shipment'=>['shipping_method_code'=>'rajaongkir_domestic', 'shipping_status'=>'Disiapkan', 'shipping_cost'=>120000, 'courier_name'=>'JNE', 'tracking_number'=>'', 'shipping_pricing'=>json_encode(['original_cost'=>150000,'subsidy_amount'=>30000])]];
        }
        return ['title'=>'List Customer','status'=>'all','page'=>1,'perPage'=>20,'totalFiltered'=>47,'totalAllOrders'=>47,'semuaTransaksiCus'=>$orders,'transaksiCus'=>$orders,'transaksiJson'=>json_encode($orders),'wsUrl'=>''];
    }

    public function testOrderViewPreservesReviewTotalsDocumentsAndSafeMarkup(): void
    {
        $html=$this->render('pages/listCustomer',$this->fixture());
        $dom=new DOMDocument(); @$dom->loadHTML('<?xml encoding="UTF-8" ?>'.$html); $xp=new DOMXPath($dom);
        $this->assertSame(5,$xp->query('//article[@data-order-index]')->length);
        $this->assertSame(5,$xp->query('//button[@data-order-toggle and @aria-expanded="false" and @aria-controls]')->length);
        $this->assertSame(5,$xp->query('//*[@class="list-customer-detail" and @hidden]')->length);
        $this->assertSame(1,$xp->query('//form[@action="/syncorder/L00000227" and @method="post"]//input[@name="review_confirmed" and @required]')->length);
        $this->assertSame(1,$xp->query('//form[@action="/syncorder/L00000227"]//input[@name="'.csrf_token().'"]')->length);
        $this->assertSame(1,$xp->query('//a[@href="/invoice/L00000227"]')->length);
        $this->assertSame(1,$xp->query('//a[@href="/pdf/L00000227"]')->length);
        $this->assertStringContainsString('Rp 1.920.000',$html);
        $this->assertStringContainsString('Rp 120.000',$html);
        $this->assertStringContainsString('Rp 30.000',$html);
        $this->assertStringContainsString('&lt;pelanggan&gt;',$html);
        $this->assertStringContainsString('&lt;produk&gt;',$html);
        $this->assertStringContainsString('Belum tersedia',$html);
        $this->assertStringContainsString('WIB',$html);
        $this->assertStringContainsString('Cari di halaman ini',$html);
        $this->assertSame(2,$xp->query('//*[contains(concat(" ",@class," ")," admin-order-modal ") and @tabindex="-1"]')->length);
        $this->assertSame(1,$xp->query('//button[@data-complete-index]')->length);
        $this->assertSame(2,$xp->query('//button[@data-shipping-index]')->length);
        $this->assertStringNotContainsString('onclick="bukaList',$html);
        $config=json_decode($xp->query('//script[@id="admin-orders-data"]')->item(0)->textContent,true);
        $this->assertSame('Aditya <pelanggan>',$config['orders'][0]['nama_pen']);
        $this->assertSame(csrf_hash(),$config['csrfHash']);
    }

    public function testEmptyAndFinalPaginationAreConsistent(): void
    {
        $data=$this->fixture(); $data['transaksiCus']=[]; $data['totalFiltered']=0;
        $html=$this->render('pages/listCustomer',$data);
        $this->assertStringContainsString('Belum ada pesanan pada filter ini',$html);
        $this->assertStringNotContainsString('class="page-link"',$html);
        $data=$this->fixture(); $data['page']=3;
        $html=$this->render('pages/listCustomer',$data);
        $dom=new DOMDocument(); @$dom->loadHTML($html); $xp=new DOMXPath($dom);
        $this->assertSame(0,$xp->query('//a[@aria-label="Halaman berikutnya"]')->length);
        $this->assertSame(1,$xp->query('//a[@href="/listcustomer/3/all" and @aria-current="page"]')->length);
    }

    public function testAdminMenuGroupsLinksAndAccessibleDrawerArePreserved(): void
    {
        session()->set(['isLogin'=>true,'role'=>'1']);
        $old=$_SERVER['REQUEST_URI']??null; $_SERVER['REQUEST_URI']='/listcustomer/2/Proses';
        try { $html=$this->render('layout/navbar',['title'=>'List Customer']); }
        finally { if ($old===null) unset($_SERVER['REQUEST_URI']); else $_SERVER['REQUEST_URI']=$old; }
        $dom=new DOMDocument(); @$dom->loadHTML($html); $xp=new DOMXPath($dom);
        $this->assertSame(17,$xp->query('//nav[@aria-label="Navigasi admin"]/a')->length);
        $this->assertSame(4,$xp->query('//nav[@aria-label="Navigasi admin"]/p')->length);
        $this->assertSame(1,$xp->query('//a[@href="/listcustomer" and @aria-current="page"]')->length);
        $this->assertSame(1,$xp->query('//button[@aria-controls="admin-navigation" and @aria-expanded="false"]')->length);
        foreach (['/shippingadmin','/customeradmin','/notificationtestadmin','/listproduct','/lunamappingadmin','/stokadmin/all/1','/invoiceadmin','/listvoucher','/settingwarna','/keluar'] as $path) $this->assertSame(1,$xp->query('//a[@href="'.$path.'"]')->length);
    }

    public function testExportSyntheticPreviewWhenRequested(): void
    {
        if (getenv('LUNAREA_UI_PREVIEW')!=='1') { $this->assertTrue(true); return; }
        session()->set(['isLogin'=>true,'role'=>'1']); $_SERVER['REQUEST_URI']='/listcustomer';
        $data=$this->fixture();
        $nav=$this->render('layout/navbar',['title'=>'List Customer']); $orders=$this->render('pages/listCustomer',$data);
        $css=file_get_contents(FCPATH.'css/style.css')."\n".file_get_contents(FCPATH.'css/admin-workspace.css');
        $css.=':root{--theme-primary:#2F2D20;--theme-primary-hover:#242218;--theme-sidebar:#2F2D20;--theme-background:#f8f8f5;--theme-accent:#C7A78A;--theme-soft:#f4f0e8;--hijau:#2F2D20;}.admin-body{--admin-primary:var(--theme-primary);--admin-bg:var(--theme-background);--admin-primary-soft:var(--theme-soft);}';
        $html='<!doctype html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"><style>'.$css.'</style></head><body class="admin-body">'.$nav.$orders.'<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script><script>'.file_get_contents(FCPATH.'js/admin-workspace.js').'</script><script>'.file_get_contents(FCPATH.'js/admin-orders.js').'</script></body></html>';
        $html=preg_replace('~<script defer src="/js/admin-orders.js[^>]*></script>~','',$html);
        file_put_contents(sys_get_temp_dir().'/lunarea-admin-preview.html',$html);
        $expanded=str_replace('id="order-detail-0" hidden','id="order-detail-0"',$html);
        $expanded=str_replace('aria-expanded="false" aria-controls="order-detail-0"','aria-expanded="true" aria-controls="order-detail-0"',$expanded);
        file_put_contents(sys_get_temp_dir().'/lunarea-admin-expanded.html',$expanded);
        $this->assertStringContainsString('example.test',$html);
    }
}
