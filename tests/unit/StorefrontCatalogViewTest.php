<?php

use CodeIgniter\Test\CIUnitTestCase;

final class StorefrontCatalogViewTest extends CIUnitTestCase
{
    public function testProductCardsPreservePricesVariantsAndLinks(): void
    {
        $directory = WRITEPATH . 'cache/catalog-review';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $html = '';
        foreach (['home', 'all', 'product', 'wishlist', 'artikel'] as $page) {
            $source = file_get_contents(APPPATH . 'Views/pages/' . $page . '.php');
            preg_match('~<a class="card1 store-product-card".*?</a>~s', $source, $match);
            $this->assertNotEmpty($match, $page);
            // Render the actual card fragment, not a rewritten copy of its business logic.
            $fragment = $directory . '/fragment-' . $page . '.php';
            file_put_contents($fragment, $match[0]);
            foreach ([0, 20] as $discount) {
                $p = ['path' => 'lemari-test', 'nama' => 'Lemari Minimalis Tiga Pintu dengan Laci Penyimpanan Serbaguna', 'diskon' => $discount,
                    'harga' => 1250000, 'varian' => '["Putih","Cokelat"]', 'rate' => 5, 'gambar' => file_get_contents(FCPATH . 'img/logokategori/Lemari_Dewasa.webp'),
                    'wishlist_varian' => 'Putih Kombinasi Kayu Natural', 'terjual_custom' => 1234, 'terjual' => 20];
                $variants = ['Putih', 'Cokelat', 'Abu-abu', 'Kayu Natural Kombinasi Putih'];
                ob_start();
                try { include $fragment; $card = ob_get_contents(); } finally { ob_end_clean(); }
                $this->assertStringContainsString('href="/product/lemari-test"', $card);
                $this->assertStringContainsString($p['nama'], $card);
                $dom = new DOMDocument();
                @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $card);
                $xp = new DOMXPath($dom);
                $this->assertSame(in_array($page, ['home', 'all', 'product'], true) ? 1 : 0, $xp->query('//div[@class="product-card-media"]//span[@class="terjual-badge"]')->length);
                $this->assertSame(1, $xp->query('//div[@class="product-card-heading"]//h5')->length);
                $this->assertSame(1, $xp->query('//div[@class="product-card-pricing"]//p[contains(@class,"harga")]')->length);
                $this->assertSame($discount ? 1 : 0, $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," product-card-price-meta ")]//p[@class="diskon-bwh"]')->length);
                $this->assertStringNotContainsString('card-saving-voucher', $card);
                $this->assertStringNotContainsString('TERLARIS', $card);

                $text = preg_replace('/\s+/', ' ', strip_tags($card));
                $this->assertStringContainsString($discount ? '1.000.000' : '1.250.000', $text);
                if ($discount) $this->assertStringContainsString('1.250.000', $text);
                if (in_array($page, ['home', 'all', 'artikel'], true)) foreach ($variants as $variant) $this->assertStringContainsString($variant, $card);
                if ($page === 'wishlist') $this->assertStringContainsString('Varian dipilih:</b> Putih Kombinasi Kayu Natural', $card);
                if (in_array($page, ['home', 'all', 'product'], true)) $this->assertStringContainsString('1,2rb+ Terjual', $text);
                $html .= '<section class="container my-5"><h5 class="jdl-section mb-1">' . $page . ' / ' . ($discount ? 'Diskon' : 'Harga normal') . '</h5><div class="card-group1 no-scroll">' . str_repeat($card, 3) . '</div></section>';
            }
        }
        if (getenv('CATALOG_ARTIFACTS') === '1') {
            $url = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
            $html = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $url . '/img/', $html);
            $styles = '';
            foreach (['bootstrap.min.css', 'style.css', 'commerce-responsive.css', 'home-carousel.css', 'storefront-catalog.css', 'buyer-navigation.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
            file_put_contents($directory . '/catalog.html', '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '</head><body>' . $html . '</body></html>');
        }
    }
}
