<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AdminModulesViewTest extends CIUnitTestCase
{
    protected function tearDown(): void { session()->remove(['isLogin','role','email']); parent::tearDown(); }

    private function render(string $page, array $data): string
    {
        $renderer=new class {
            private array $data=[];
            public function extend($name): string { return ''; }
            public function section($name): string { return ''; }
            public function endSection(): string { return ''; }
            public function include($name): string { return view($name,$this->data,['saveData'=>false]); }
            public function render($page,$data): string {
                $this->data=$data; extract($data,EXTR_SKIP); ob_start();
                try { include APPPATH.'Views/pages/'.$page.'.php'; return ob_get_contents(); } finally { ob_end_clean(); }
            }
        };
        return $renderer->render($page,$data);
    }

    private function data(string $page): array
    {
        $data=['title'=>'Administrasi','msg'=>'','error'=>'','cari'=>'','filterBarang'=>'','find'=>null,'page'=>1,'pag'=>1,'url'=>'all/1','voucherJson'=>'[]','stokJson'=>'[]','produkAllJson'=>'[]','idProduk'=>'all','countAllStok'=>0,'emailtambah'=>false,'stockManagedByLuna'=>true,
            'produk'=>[],'semuaProduk'=>[],'products'=>[],'previewProducts'=>[],'produkOpsi'=>[],'ratingList'=>[],'stok'=>[],'stokVarian'=>[],'redeem'=>[],'voucher'=>[],'banner'=>[],'form'=>[],'seluruhInvoice'=>[],'artikel'=>[],
            'tinymce'=>'fixture-only','tinyMCE'=>'fixture-only','waktu'=>'2026-10-16 12:34:56','gambar'=>[],'varian'=>'Putih','variantImageMap'=>[],
            'settings'=>['enabled'=>true,'mode'=>'manual','kicker'=>'Promo pilihan','title'=>'Flash Sale Lunarea','subtitle'=>'Produk pilihan untuk ruangan Anda.','end_time'=>'23:59','limit'=>7,'product_ids'=>[],'button_text'=>'Lihat semua'],
            'internal'=>['is_active'=>true],'rajaongkir'=>['is_active'=>true],'rajaSettings'=>['couriers'=>['jne','tiki','sentral'],'origin_id'=>'64987','origin_label'=>'Ngaliyan, Semarang','origin_provinsi'=>'Jawa Tengah','origin_kecamatan'=>'Ngaliyan','origin_kelurahan'=>'Ngaliyan','origin_postal_code'=>'50181'],
            'coverages'=>[['id'=>1,'is_active'=>true,'kecamatan_label'=>'Ngaliyan','kabupaten_label'=>'Semarang','provinsi_label'=>'Jawa Tengah'],['id'=>2,'is_active'=>false,'kecamatan_label'=>'Mijen','kabupaten_label'=>'Semarang','provinsi_label'=>'Jawa Tengah']],
            'provinsi'=>[['id'=>33,'label'=>'Jawa Tengah']],'subsidyStorageReady'=>true,'categories'=>['meja'=>'Meja','kursi'=>'Kursi','lemari'=>'Lemari'],'images'=>[],
            'theme'=>\App\Models\KonstantaModel::defaultThemeWarna(),'topPromo'=>['desktop'=>'Harga khusus untuk pembelian pertama','mobile'=>'Pilihan furniture untuk Anda'],
            'range'=>7,'fromDate'=>'2026-10-16','toDate'=>'2026-10-22','summary'=>['pageviews'=>1280,'visitors'=>840,'avg_duration'=>93,'today'=>182],'dailyTraffic'=>[],'topPages'=>[],'recentVisits'=>[],'broadcast'=>[],'emailBroadcast'=>[],
            'ready'=>true,'errors'=>[],'logs'=>[],'filters'=>['from'=>null,'to'=>null,'actor'=>'','route'=>'','method'=>''],'queryString'=>'','pages'=>1,'total'=>0,'returns'=>[],'status'=>''];
        if (in_array($page,['stokAdmin','editProduct'],true)) $data['produk']=['id'=>'FIXTURE','nama'=>'Lunarea - Meja kerja putih','varian'=>'["Putih","Natural"]','jml_varian'=>2,'harga'=>900000,'diskon'=>10,'stok'=>'20,10','berat'=>15,'dimensi'=>'120X60X75','kategori'=>'Meja','subkategori'=>'Meja kerja','deskripsi'=>'Deskripsi fixture','deskripsi_nonhtml'=>'Deskripsi fixture','pencarian'=>'meja'];
        if ($page==='stokAdmin') $data['produk']['varian']=['Putih','Natural'];
        if ($page==='editVoucher') $data['voucher']=['id'=>1,'nama'=>'Promo fixture','nominal'=>10,'satuan'=>'persen','jenis'=>'diskon','durasi'=>30,'durasi_poin'=>30,'jadwal'=>'','all_user'=>1,'auto_claimed'=>0,'code'=>[],'isi_email'=>'','isi_email_input'=>'','kuota'=>100,'max_potongan'=>100000,'keterangan'=>'','syarat_ketentuan'=>'','tidak_gabung_voucher_baru'=>0];
        if ($page==='editArtikel') $data['artikel']=['id'=>1,'judul'=>'Artikel fixture','penulis'=>'Admin','isi'=>'<p>Isi fixture</p>','kategori'=>'meja,edukasi'];
        if ($page==='editBanner') $data['banner']=['id'=>1,'judul'=>'Banner fixture','alt'=>'Ruang keluarga','link'=>'/all','updated_at'=>'2026-10-16 12:00:00','urutan'=>1,'active'=>1];
        return $data;
    }

    public static function pages(): array
    {
        return array_map(static fn($page)=>[$page],['shippingAdmin','listInvoiceAdmin','addInvoice','listProduct','flashSaleAdmin','categoryImagesAdmin','addArtikel1','editArtikel','listBanner','addBanner','editBanner','listVoucher','addVoucher','editVoucher','manageRatingTerjual','stokAdmin','listRedeem','trafficAdmin','settingWarna','editProduct','addProduct','listForm','artikelAll','adminActivityLogs','adminReturnQueue']);
    }

    /** @dataProvider pages */
    public function testModuleRendersWithoutWarnings(string $page): void
    {
        session()->set(['isLogin'=>true,'role'=>'1']);
        $html=$this->render($page,$this->data($page));
        $this->assertStringContainsString('admin-module-page',$html);
        $this->assertNotEmpty($html);
        if (getenv('LUNAREA_MODULE_PREVIEW')==='1') $this->preview($page,$html);
    }

    private function contract(string $source): array
    {
        $source=preg_replace_callback('~<\?(?:php|=).*?\?>~s',static fn($m)=>'PHP_'.substr(hash('sha256',$m[0]),0,12),$source);
        preg_match_all('~<(form|input|select|textarea)\b([^>]*)>~i',$source,$tags,PREG_SET_ORDER);
        $result=['forms'=>[],'fields'=>[]];
        foreach ($tags as $tag) {
            preg_match_all('~([\w-]+)\s*=\s*(?:"([^"]*)"|\x27([^\x27]*)\x27)~',$tag[2],$matches,PREG_SET_ORDER);
            $attrs=[]; foreach ($matches as $attr) $attrs[$attr[1]]=($attr[2]??'')!==''?$attr[2]:($attr[3]??'');
            $form=strtolower($tag[1])==='form';
            if (!$form && empty($attrs['name'])) continue;
            $row=[]; foreach ($form?['action','method','enctype']:['name','type','value','min','max','step','maxlength','multiple'] as $key) $row[$key]=$attrs[$key]??'';
            if (!$form && $row['name']==='cari') { $row['type']=''; $row['value']=''; }
            $result[$form?'forms':'fields'][]=$row;
        }
        return $result;
    }

    public function testExistingFormEndpointsAndFieldDefaultsArePreserved(): void
    {
        $manifest=json_decode(file_get_contents(TESTPATH.'fixtures/admin-form-contract.json'),true);
        foreach ($manifest as $page=>$expected) {
            $actual=$this->contract(file_get_contents(APPPATH.'Views/pages/'.$page.'.php'));
            // UI sections may move; field names/defaults and their multiplicity must stay intact.
            foreach (['forms','fields'] as $key) {
                usort($expected[$key], static fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));
                usort($actual[$key], static fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));
            }
            $this->assertSame($expected,$actual,$page.' form contract changed');
        }
    }

    public function testShippingCoverageAndSubsidyAreStillIndependentPostForms(): void
    {
        $html=$this->render('shippingAdmin',$this->data('shippingAdmin'));
        $dom=new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html); $xp=new DOMXPath($dom);
        $this->assertSame(2,$xp->query('//form[@action="/shippingadmin" and @method="post"]')->length);
        $this->assertSame(1,$xp->query('//form[@action="/shippingadmin/subsidy" and @method="post"]')->length);
        $this->assertSame(1,$xp->query('//form[@action="/shippingadmin/coverage/1/deactivate" and @method="post"]')->length);
        foreach (['shipping-methods','shipping-coverage','shipping-subsidy'] as $id) $this->assertSame(1,$xp->query('//*[@id="'.$id.'"]')->length);
        $this->assertSame(1,$xp->query('//select[@id="ship-kec" and @name="kecamatan_id" and @required and @disabled]')->length);
        $this->assertSame(1,$xp->query('//input[@name="rajaongkir_origin_id" and @value="64987"]')->length);
        $this->assertSame(1,$xp->query('//input[@name="subsidy_percentage" and @max="100"]')->length);
        foreach (['shipping-origin-id','shipping-origin-label','shipping-origin-province','shipping-origin-district','shipping-origin-postal','shipping-origin-village','shipping-couriers','ship-prov','ship-kab','ship-kec'] as $id) {
            $this->assertSame(1,$xp->query('//label[@for="'.$id.'"]')->length);
            $this->assertSame(1,$xp->query('//*[self::input or self::select][@id="'.$id.'"]')->length);
            $this->assertSame(0,$xp->query('//label[@for="'.$id.'"]//input[@type="checkbox"]')->length);
        }
    }

    public function testSharedHeadingEscapesTextAndUsesSafeExternalLinks(): void
    {
        $html=view('partials/admin_page_heading',['adminHeadingTitle'=>'<Title>','adminHeadingDescription'=>'<script>test</script>','adminHeadingActions'=>[['href'=>'/','label'=>'<Preview>','external'=>true]]],['saveData'=>false]);
        $this->assertStringContainsString('&lt;Title&gt;',$html);
        $this->assertStringContainsString('rel="noopener noreferrer"',$html);
        $this->assertStringNotContainsString('<script>',$html);
    }

    private function preview(string $page,string $content): void
    {
        $root=':root{--theme-primary:#2F2D20;--theme-primary-hover:#242218;--theme-sidebar:#2F2D20;--theme-background:#f8f8f5;--theme-accent:#C7A78A;--theme-soft:#f4f0e8;--theme-soft-2:#e8e0d5;--hijau:#2F2D20;}.admin-body{--admin-primary:var(--theme-primary);--admin-bg:var(--theme-background);--admin-primary-soft:var(--theme-soft);}';
        $css=file_get_contents(FCPATH.'css/style.css').file_get_contents(FCPATH.'css/admin-workspace.css').file_get_contents(FCPATH.'css/admin-modules.css').file_get_contents(FCPATH.'css/admin-media.css').$root;
        $nav=view('layout/navbar',['title'=>'Administrasi'],['saveData'=>false]);
        $html='<!doctype html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"><style>'.$css.'</style></head><body class="admin-body">'.$nav.$content.'<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script><script>'.file_get_contents(FCPATH.'js/admin-workspace.js').'</script><script>'.file_get_contents(FCPATH.'js/admin-media.js').'</script></body></html>';
        // External editor SDKs are excluded from synthetic styling previews, not from application views.
        $html=preg_replace('~<script[^>]+src="https://cdn\.tiny\.cloud[^>]*>.*?</script>~s','',$html);
        file_put_contents(sys_get_temp_dir().'/lunarea-module-'.$page.'.html',$html);
    }
}
