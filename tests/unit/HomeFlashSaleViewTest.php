<?php

use CodeIgniter\Test\CIUnitTestCase;

final class HomeFlashSaleViewTest extends CIUnitTestCase
{
    /** @dataProvider cases */
    public function testFlashSaleRetainsAdminSettingsPriceAndProductOrder(string $state, int $count): void
    {
        $products = []; $image = file_get_contents(FCPATH . 'img/logokategori/Lemari_Dewasa.webp');
        for ($i=0;$i<$count;$i++) $products[] = ['id'=>50-$i,'path'=>'produk-' . (50-$i), 'nama'=>$i%2?'Rak minimalis':'Lemari penyimpanan dengan nama produk sangat panjang untuk keluarga dan ruang tamu', 'diskon'=>20, 'harga'=>1250000, 'stok'=>['0','2,1','12,4'][$i%3], 'gambar'=>$image];
        $settings = ['kicker'=>'Pilihan <Lunarea>', 'title'=>$state==='long'?str_repeat('Flash Sale koleksi pilihan ',5):'Flash Sale Lunarea', 'subtitle'=>'Harga spesial produk pilihan & promo toko.'];
        $end = $state==='expired'?0:time()+3723;
        $html = view('partials/home_flash_sale', ['flashSaleProducts'=>$products,'flashSaleSettings'=>$settings,'flashSaleEnd'=>$end], ['saveData'=>false]);
        if ($count===0) { $this->assertStringNotContainsString('flash-sale-section',$html); return; }
        $dom=new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xp=new DOMXPath($dom);
        $this->assertSame($count,$xp->query('//a[@class="flash-sale-card"]')->length);
        $this->assertSame($count,$xp->query('//h3[@title]')->length);
        $this->assertSame(1,$xp->query('//*[@id="flash-sale-title"]')->length);
        $this->assertStringContainsString(esc($settings['title']),$html);
        $this->assertStringContainsString('Pilihan &lt;Lunarea&gt;',$html);
        $this->assertStringContainsString('Rp 1.000.000',$html);
        $this->assertStringContainsString('Rp 1.250.000',$html);
        $this->assertSame(1,$xp->query('//*[@role="timer" and @aria-live="off" and @data-end="'.$end.'"]')->length);
        $this->assertSame(1,$xp->query('//a[@href="/all"]')->length);
        $this->assertStringNotContainsString('flash-sale-stock-bar',$html);
        $this->assertStringContainsString('Stok habis',$html);
        if ($count>1) $this->assertStringContainsString('Stok terbatas',$html);
        if ($count>2) $this->assertStringContainsString('Stok tersedia',$html);
        $hrefs=[];foreach($xp->query('//a[@class="flash-sale-card"]') as $a)$hrefs[]=$a->getAttribute('href');
        $this->assertSame(array_map(static fn($p)=>'/product/'.$p['path'],$products),$hrefs);
        if (getenv('HOME_FLASH_ARTIFACTS')!=='1') return;
        $dir=WRITEPATH.'cache/home-flash-review';if(!is_dir($dir))mkdir($dir,0775,true);
        $source=file_get_contents(APPPATH.'Views/pages/home.php');$start=strpos($source,"document.querySelectorAll('[data-flash-countdown]')");$endScript=strpos($source,'</script>',$start);
        file_put_contents($dir.'/flash-sale-ui.js',substr($source,$start,$endScript-$start));
        $url='file:///'.str_replace('\\','/',rtrim(FCPATH,'\\/'));$styles='';foreach(['bootstrap.min.css','style.css','commerce-responsive.css','storefront-catalog.css','buyer-navigation.css','buyer-footer.css'] as $css)$styles.='<link rel="stylesheet" href="'.$url.'/css/'.$css.'">';
        session()->set(['isLogin'=>false,'role'=>null,'email'=>null]);
        $nav=view('layout/navbar',['title'=>'Beranda'],['saveData'=>false]);$promo=view('partials/buyer_promo',['desktopText'=>'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'],['saveData'=>false]);$footer=view('layout/buyer_footer',['footerVariant'=>'full'],['saveData'=>false]);
        $content=preg_replace('~src="https?://[^\"]*/img/~','src="'.$url.'/img/',$promo.$nav.'<div class="buyer-page-content"><main class="konten"><h1 class="visually-hidden">Lunarea Furniture</h1>'.$html.'</main></div>'.$footer);$content=str_replace('src="/img/','src="'.$url.'/img/',$content);
        foreach(['default'=>\App\Models\KonstantaModel::defaultThemeWarna(),'sand'=>['primary'=>'#2F2D20','primaryHover'=>'#232217','background'=>'#FEFEFF','soft'=>'#F7F2ED','soft2'=>'#DFCBB9']] as $name=>$t){
            $tokens=':root,*{--theme-primary:'.$t['primary'].';--theme-primary-hover:'.$t['primaryHover'].';--theme-background:'.$t['background'].';--theme-soft:'.$t['soft'].';--theme-soft-2:'.$t['soft2'].';--hijau:'.$t['primary'].';}';
            file_put_contents($dir.'/'.$state.'-'.$name.'.html','<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'.$styles.'<style>'.$tokens.'</style><script defer src="'.$url.'/js/buyer-navigation.js"></script><script defer src="flash-sale-ui.js"></script></head><body class="buyer-layout">'.$content.'</body></html>');
        }
    }
    public static function cases():array{return [['empty',0],['single',1],['normal',7],['long',12],['expired',2]];}
}
