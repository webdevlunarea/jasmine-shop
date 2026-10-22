<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerProductViewTest extends CIUnitTestCase
{
    /** @dataProvider states */
    public function testProductDetailKeepsPurchaseVariantAndReviewContracts(string $state): void
    {
        $loggedIn=$state!=='anonymous';$active=$state!=='inactive';
        session()->set(['isLogin'=>$loggedIn,'role'=>$loggedIn?'0':null,'active'=>$active?'1':'0','email'=>'buyer@example.test']);
        $image=file_get_contents(FCPATH.'img/logokategori/Lemari_Dewasa.webp');
        $varian=['Putih','Natural kayu','Abu-abu'];if($state==='long')$varian[1]=str_repeat('Natural kombinasi kayu ',5);
        $stock=$state==='out-of-stock'?'0,0,0':'5,0,3';
        $product=['id'=>12,'path'=>'lemari-contoh','nama'=>'Lemari keluarga minimalis dengan nama produk yang cukup panjang dan pilihan warna natural','harga'=>1250000,'diskon'=>20,'stok'=>$stock,'jml_varian'=>1,'subkategori'=>'lemari-dewasa','dimensi'=>$state==='missing-dimensions'?'120 x 45':'120 x 45 x 180','terjual'=>12,'terjual_custom'=>0,'shopee'=>'https://example.test/shopee/produk-12','tokped'=>'https://example.test/tokopedia/produk-12','tiktok'=>'https://example.test/tiktok/produk-12','youtube'=>'https://example.test/video/produk-12','deskripsi'=>'<p>Spesifikasi produk sesuai informasi toko.</p><table style="width:800px"><tr><td>Berat</td><td>24 kg</td></tr><tr><td>Dimensi</td><td>120 × 45 × 180 cm</td></tr></table>'];
        $related=$product+['gambar'=>$image,'varian'=>'["Putih","Natural"]','rate'=>5];
        $list=$state==='no-reviews'?[]:[['id'=>1,'nama_sensor'=>'P*** C***','rating'=>5,'created_at'=>'2026-10-15 12:00:00','updated_at'=>'2026-10-15 12:00:00','komentar'=>'Produk sesuai pesanan dan pilihan warna.']];
        $data=['produk'=>$product,'gambar'=>['id'=>12,'gambar1'=>$image,'gambar2'=>$image,'gambar3'=>$image,'gambar4'=>$image],'varian'=>$varian,'dimensi'=>[120,45,180],'produksekategori'=>[$related],'msg'=>'','stok'=>$stock,'ratingList'=>$list,'ratingStats'=>['avg'=>$list?4.5:0,'count'=>count($list)],'bolehRating'=>in_array($state,['member','long'],true),'userRating'=>null,'variantImageMap'=>[$varian[0]=>0,$varian[1]=>2,$varian[2]=>1],'wishlistKeys'=>['12|putih']];
        $renderer=new class {
            public function extend(string $name):string{return '';}
            public function section(string $name):string{return '';}
            public function endSection():string{return '';}
            public function render(array $data):string{extract($data,EXTR_SKIP);ob_start();try{include APPPATH.'Views/pages/product.php';return ob_get_contents();}finally{ob_end_clean();}}
        };
        $html=$renderer->render($data);$dom=new DOMDocument();@$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);$xp=new DOMXPath($dom);
        $this->assertSame(1,$xp->query('//main[contains(@class,"buyer-product-page")]')->length);
        $this->assertSame(1,$xp->query('//h1[@class="product-detail-title"]')->length);
        $this->assertSame(3,$xp->query('//*[@id="varian-group"]/input[@type="radio"]')->length);
        $this->assertSame(4,$xp->query('//*[@role="button" and @tabindex="0"]')->length);
        $this->assertSame(1,$xp->query('//*[@id="stok" and @role="status"]')->length);
        foreach(['1.000.000','1.250.000','24 kg','120 × 45 × 180 cm','Spesifikasi produk sesuai informasi toko.'] as $text)$this->assertStringContainsString($text,$html);
        $this->assertStringContainsString($product['shopee'],$html);
        $this->assertSame(1,$xp->query('//*[@id="flush-collapse2"]')->length);
        $this->assertSame(1,$xp->query('//*[@id="flush-collapse"]')->length);
        $this->assertSame($state==='missing-dimensions'?0:1,$xp->query('//*[@data-product-size-fit]')->length);
        if($loggedIn&&$active){
            $this->assertSame(2,$xp->query('//form[contains(@class,"product-wishlist-form")]')->length);
            $this->assertSame(2,$xp->query('//input[@name="varian" and @value="Putih"]')->length);
            $this->assertSame(4,$xp->query('//button[contains(@class,"btn-beli-product")]')->length);
            $this->assertSame($state==='out-of-stock'?4:0,$xp->query('//button[contains(@class,"btn-beli-product") and @disabled]')->length);
        }elseif(!$loggedIn){$this->assertStringContainsString('/login?redirect=',$html);$this->assertSame(0,$xp->query('//form[contains(@class,"product-wishlist-form")]')->length);}
        $this->assertSame($data['bolehRating']?1:0,$xp->query('//form[@action="/addrating/12"]')->length);
        if(getenv('BUYER_PRODUCT_ARTIFACTS')!=='1')return;
        $dir=WRITEPATH.'cache/buyer-product-review';if(!is_dir($dir))mkdir($dir,0775,true);$url='file:///'.str_replace('\\','/',rtrim(FCPATH,'\\/'));
        $styles='';foreach(['bootstrap.min.css','style.css','commerce-responsive.css','storefront-catalog.css','buyer-navigation.css','buyer-footer.css','buyer-product.css'] as $css)$styles.='<link rel="stylesheet" href="'.$url.'/css/'.$css.'">';
        $nav=view('layout/navbar',['title'=>$product['nama']],['saveData'=>false]);$promo=view('partials/buyer_promo',['desktopText'=>'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'],['saveData'=>false]);$footer=view('layout/buyer_footer',['footerVariant'=>'full'],['saveData'=>false]);
        $content=preg_replace('~src="https?://[^\"]*/img/~','src="'.$url.'/img/',$promo.$nav.'<div class="buyer-page-content">'.$html.'<div class="hide-ke-show-block" style="height:53px"></div></div>'.$footer);$content=str_replace('src="/img/','src="'.$url.'/img/',$content);
        foreach(['default'=>\App\Models\KonstantaModel::defaultThemeWarna(),'sand'=>['primary'=>'#2F2D20','primaryHover'=>'#232217','background'=>'#FEFEFF','soft'=>'#F7F2ED','soft2'=>'#DFCBB9']] as $name=>$t){$tokens=':root,*{--theme-primary:'.$t['primary'].';--theme-primary-hover:'.$t['primaryHover'].';--theme-background:'.$t['background'].';--theme-soft:'.$t['soft'].';--theme-soft-2:'.$t['soft2'].';--hijau:'.$t['primary'].';}';file_put_contents($dir.'/'.$state.'-'.$name.'.html','<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'.$styles.'<style>'.$tokens.'</style><script defer src="'.$url.'/js/bootstrap.bundle.min.js"></script><script defer src="'.$url.'/js/buyer-navigation.js"></script></head><body class="buyer-layout">'.$content.'</body></html>');}
    }
    public static function states():array{return [['member'],['anonymous'],['inactive'],['out-of-stock'],['no-reviews'],['long'],['missing-dimensions']];}
}
