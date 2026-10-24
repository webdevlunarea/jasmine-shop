<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\BarangModel;

final class HomeProductSectionsTest extends CIUnitTestCase
{
    public function testExistingLatestAndPopularQueriesStaySeparateAndExcludeInactiveProducts(): void
    {
        // Disposable in-memory database only; never use the default connection.
        $db = \Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
        try {
            $db->query('CREATE TABLE barang (id INTEGER PRIMARY KEY, active TEXT, tracking_pop INTEGER, path TEXT, nama TEXT, harga INTEGER, stok TEXT, diskon INTEGER, varian TEXT, terjual INTEGER, terjual_custom INTEGER)');
            for ($id=1;$id<=13;$id++) $db->table('barang')->insert(['id'=>$id,'active'=>$id===13?'0':'1','tracking_pop'=>1000-$id,'path'=>'produk-' . $id]);
            $db->table('barang')->insert(['id'=>14,'active'=>'1','tracking_pop'=>900,'path'=>'produk-12']);
            $model = new BarangModel($db);
            $this->assertSame([14,11,10,9,8,7,6,5,4,3],array_map('intval',array_column($model->getBarangBaru(),'id')));
            $this->assertSame(range(1,10),array_map('intval',array_column($model->getBarangPopuler(),'id')));
        } finally { $db->close(); }
        $controller = file_get_contents(APPPATH . 'Controllers/Pages.php');
        $this->assertStringContainsString('$produkTerbaru = $this->barangModel->getBarangBaru();',$controller);
        $this->assertStringContainsString("'produkTerbaru' => $" . 'produkTerbaru',$controller);
        $home = file_get_contents(APPPATH . 'Views/pages/home.php');
        $this->assertStringContainsString("'sectionProducts' => $" . 'produkPopuler',$home);
        $this->assertStringContainsString("'sectionProducts' => $" . 'produkTerbaru',$home);
    }

    public function testCollectionsShareCardsAndRetainRealPricesVariantsAndLinks(): void
    {
        $image = file_get_contents(FCPATH . 'img/logokategori/Lemari_Dewasa.webp');
        $products = [];
        for ($id=1;$id<=10;$id++) $products[] = ['id'=>$id,'path'=>'lemari-' . $id,'nama'=>$id%2?'Lemari penyimpanan keluarga dengan nama cukup panjang':'Rak minimalis','gambar'=>$image,'varian'=>$id%3?'["Putih","Natural","Cokelat"]':'[]','diskon'=>$id%2?20:0,'harga'=>1250000,'rate'=>5,'terjual'=>12];
        $html = '';
        foreach (['popular'=>'Produk Terpopuler','latest'=>'Produk Terbaru'] as $id=>$title) $html .= view('partials/home_product_section',['sectionId'=>'home-' . $id . '-products','sectionTitle'=>$title,'sectionProducts'=>$products],['saveData'=>false]);
        $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); $xp = new DOMXPath($dom);
        $this->assertSame(2,$xp->query('//section/h2|//section/div/h2')->length);
        $this->assertSame(20,$xp->query('//a[contains(@class,"store-product-card")]')->length);
        $this->assertSame(20,$xp->query('//img[@loading="lazy" and @decoding="async"]')->length);
        $this->assertSame(2,$xp->query('//a[@href="/all"]')->length);
        foreach (['1.000.000','1.250.000','12 Terjual','Putih','Natural'] as $text) $this->assertStringContainsString($text,html_entity_decode($html));
        $this->assertStringNotContainsString('Produk Terbaru',view('partials/home_product_section',['sectionId'=>'empty','sectionTitle'=>'Produk Terbaru','sectionProducts'=>[]],['saveData'=>false]));
        if (getenv('HOME_PRODUCTS_ARTIFACTS') !== '1') return;
        $dir = WRITEPATH . 'cache/home-products-review'; if (!is_dir($dir)) mkdir($dir,0775,true);
        $url = 'file:///' . str_replace('\\','/',rtrim(FCPATH,'\\/')); $styles = '';
        foreach (['bootstrap.min.css','style.css','commerce-responsive.css','storefront-catalog.css','buyer-navigation.css','buyer-footer.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        session()->set(['isLogin'=>false,'role'=>null,'email'=>null]);
        $nav = view('layout/navbar',['title'=>'Beranda'],['saveData'=>false]);
        $promo = view('partials/buyer_promo',['desktopText'=>'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'],['saveData'=>false]);
        $footer = view('layout/buyer_footer',['footerVariant'=>'full'],['saveData'=>false]);
        $content = preg_replace('~src="https?://[^\"]*/img/~','src="' . $url . '/img/',$promo . $nav . '<div class="buyer-page-content"><main class="konten"><h1 class="visually-hidden">Lunarea Furniture</h1>' . $html . '</main></div>' . $footer);
        foreach (['default'=>\App\Models\KonstantaModel::defaultThemeWarna(),'sand'=>['primary'=>'#2F2D20','primaryHover'=>'#232217','soft'=>'#F7F2ED','soft2'=>'#DFCBB9','background'=>'#FEFEFF']] as $name=>$theme) {
            $tokens=':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--hijau:' . $theme['primary'] . ';}';
            file_put_contents($dir . '/' . $name . '.html','<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '</style><script defer src="' . $url . '/js/buyer-navigation.js"></script></head><body class="buyer-layout">' . $content . '</body></html>');
        }
    }
}
