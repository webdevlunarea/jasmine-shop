<?php

use App\Services\PublicCatalogImage;
use CodeIgniter\Test\CIUnitTestCase;

final class PublicCatalogImageTest extends CIUnitTestCase
{
    public function testImageUrlDoesNotRequireTheImageBlobInListingRows(): void
    {
        $product = ['id' => 27, 'gambar' => 'first image'];
        $url = PublicCatalogImage::productUrl($product);
        $this->assertSame($url, PublicCatalogImage::productUrl($product));
        $this->assertSame('/imgpic/27', $url);
        $product['gambar'] = 'new image';
        $this->assertSame($url, PublicCatalogImage::productUrl($product));
        $this->assertSame('/img/logo%20icon.png', PublicCatalogImage::productUrl([]));
    }

    public function testMatchingContentVersionGetsLongImmutableCache(): void
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9l0AAAAASUVORK5CYII=');
        $version = substr(hash('sha256', $bytes), 0, 32);
        $response = PublicCatalogImage::respond(\Config\Services::response(null, false), $bytes, '', $version);
        $this->assertSame('public, max-age=31536000, immutable', $response->getHeaderLine('Cache-Control'));
    }

    public function testPublicImageReturnsActualMimeBytesAndBoundedCache(): void
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9l0AAAAASUVORK5CYII=');
        $response = PublicCatalogImage::respond(\Config\Services::response(null, false), $bytes);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/png', $response->getHeaderLine('Content-Type'));
        $this->assertSame($bytes, $response->getBody());
        $this->assertSame('public, max-age=300, must-revalidate', $response->getHeaderLine('Cache-Control'));
        $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        $etag = $response->getHeaderLine('ETag');
        foreach ([$etag, 'W/' . $etag, '"other", W/' . $etag, '*'] as $condition) {
            $cached = PublicCatalogImage::respond(\Config\Services::response(null, false), $bytes, $condition);
            $this->assertSame(304, $cached->getStatusCode());
            $this->assertSame('', $cached->getBody());
        }
        $fresh = PublicCatalogImage::respond(\Config\Services::response(null, false), $bytes, '"outdated"');
        $this->assertSame(200, $fresh->getStatusCode());
    }

    public function testMissingOrInvalidPicturesAreNotCached(): void
    {
        foreach ([null, '', '<svg onload="alert(1)"></svg>', 'invalid'] as $bytes) {
            $response = PublicCatalogImage::respond(\Config\Services::response(null, false), $bytes);
            $this->assertSame(404, $response->getStatusCode());
            $this->assertSame('no-store', $response->getHeaderLine('Cache-Control'));
            $this->assertSame('', $response->getBody());
        }
    }

    public function testHomeCardsNoLongerEmbedPictureBytesInHtml(): void
    {
        $image = file_get_contents(FCPATH . 'img/logokategori/Lemari_Dewasa.webp');
        $products = array_fill(0, 10, ['id'=>27, 'path'=>'lemari', 'nama'=>'Lemari', 'gambar'=>$image, 'varian'=>'[]', 'diskon'=>0, 'harga'=>1250000, 'rate'=>5, 'terjual'=>12]);
        $html = view('partials/home_product_section', ['sectionProducts'=>$products, 'sectionId'=>'test', 'sectionTitle'=>'Produk'], ['saveData'=>false]);
        $this->assertStringNotContainsString('data:image/', $html);
        $this->assertSame(10, substr_count(html_entity_decode($html, ENT_QUOTES | ENT_HTML5), '/imgpic/27'));
        $this->assertLessThan(strlen(base64_encode($image)) * 10, strlen($html));
        $this->assertSame(10, substr_count($html, 'loading="lazy" decoding="async"'));
    }

    public function testOnlyPublicImageHandlersUseCachingService(): void
    {
        $source = file_get_contents(APPPATH . 'Controllers/GambarController.php');
        $this->assertSame(3, substr_count($source, 'PublicCatalogImage::respond('));
        foreach (['tampilGambarBarang', 'tampilFotoProduk', 'tampilGambarBanner'] as $method) {
            preg_match('/public function ' . $method . '\([^)]*\).*?\n    }/s', $source, $match);
            $this->assertNotEmpty($match);
            $this->assertStringContainsString('PublicCatalogImage::respond(', $match[0]);
        }
        foreach (['tampilGambarUser', 'tampilGambarBuktiBayar'] as $method) {
            preg_match('/public function ' . $method . '\([^)]*\).*?\n    }/s', $source, $match);
            $this->assertNotEmpty($match);
            $this->assertStringNotContainsString('PublicCatalogImage', $match[0]);
        }
        $template = file_get_contents(APPPATH . 'Views/layout/template.php');
        $this->assertStringNotContainsString('/css/style.css?v=<?= time(); ?>', $template);
        $html = view('partials/home_carousel', ['banner'=>[]], ['saveData'=>false]);
        $this->assertSame(1, substr_count($html, 'fetchpriority="high"'));
        $this->assertSame(4, substr_count($html, 'loading="lazy"'));
    }
}
