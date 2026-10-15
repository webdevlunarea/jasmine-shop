<?php

use CodeIgniter\Test\CIUnitTestCase;

final class HomeCarouselViewTest extends CIUnitTestCase
{
    /** @dataProvider bannerCases */
    public function testSlidesKeepLinksAltTextAndOneActiveItem(string $name, array $banners, int $count): void
    {
        $html = view('partials/home_carousel', ['banner' => $banners], ['saveData' => false]);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($dom);
        $this->assertSame($count, $xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," carousel-item ")]')->length);
        $this->assertSame(1, $xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," carousel-item ") and contains(concat(" ",normalize-space(@class)," ")," active ")]')->length);
        $this->assertSame($count, $xpath->query('//button[@data-bs-slide-to]')->length);
        $this->assertSame($count, $xpath->query('//div[@class="home-hero-media"]')->length);
        $this->assertSame($count - 1, $xpath->query('//img[@loading="lazy"]')->length);
        $this->assertStringNotContainsString('--home-hero-art', $html);
        if ($banners) {
            $this->assertStringContainsString('href="/all?sort=harga&amp;promo=1"', $html);
            $this->assertStringContainsString('alt="Promo &lt;Lemari&gt;"', $html);
        }

        if (getenv('HOME_CAROUSEL_ARTIFACTS') === '1') $this->writeFixture($name, $html);
    }

    public static function bannerCases(): array
    {
        return [
            'fallback' => ['fallback', [], 5],
            'dynamic' => ['dynamic', [
                ['id' => 301, 'link' => '/all?sort=harga&promo=1', 'alt' => 'Promo <Lemari>', 'judul' => 'Promo', 'updated_at' => '2026-10-15'],
                ['id' => 302, 'link' => '', 'alt' => '', 'judul' => 'Kursi', 'updated_at' => '2026-10-15'],
                ['id' => 303, 'link' => '/all', 'alt' => 'Meja', 'judul' => 'Meja', 'updated_at' => '2026-10-15'],
            ], 3],
        ];
    }

    private function writeFixture(string $name, string $html): void
    {
        $directory = WRITEPATH . 'cache/home-carousel-review';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        $publicUrl = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
        $html = preg_replace_callback('~src="/imgbanner/(\d+)\?[^\"]*"~', static function ($match) use ($publicUrl) {
            return 'src="' . $publicUrl . '/img/benner/' . ((int)$match[1] - 300) . '.webp"';
        }, $html);
        $html = str_replace('src="/img/', 'src="' . $publicUrl . '/img/', $html);
        $styles = '';
        foreach (['bootstrap.min.css', 'style.css', 'buyer-navigation.css', 'home-carousel.css'] as $file) {
            $styles .= '<link rel="stylesheet" href="' . $publicUrl . '/css/' . $file . '">';
        }
        $nav = view('layout/navbar', ['title' => 'Beranda'], ['saveData' => false]);
        $nav = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $publicUrl . '/img/', $nav);
        $promo = view('partials/buyer_promo', ['desktopText' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'], ['saveData' => false]);
        file_put_contents($directory . '/' . $name . '.html', '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . $styles . '<script defer src="' . $publicUrl . '/js/bootstrap.bundle.min.js"></script><script defer src="' . $publicUrl . '/js/buyer-navigation.js"></script>'
            . '<style>.material-icons{font-size:0!important}.material-icons::after{content:"●";font-size:20px}</style></head><body>'
            . $promo . $nav . '<main class="konten pb-0"><div class="container home-hero-container">' . $html
            . '</div><div class="container my-3"><h5>Kategori</h5><div style="height:800px"></div></div></main></body></html>');
    }
}
