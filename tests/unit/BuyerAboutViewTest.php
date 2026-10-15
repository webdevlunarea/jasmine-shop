<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerAboutViewTest extends CIUnitTestCase
{
    public function testEditorialPageKeepsBrandInformationAndExistingActions(): void
    {
        $renderer = new class {
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render(): string {
                ob_start();
                try { include APPPATH . 'Views/pages/about.php'; return ob_get_contents(); } finally { ob_end_clean(); }
            }
        };
        $html = $renderer->render();
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xp = new DOMXPath($dom);
        $this->assertSame(1, $xp->query('//main')->length);
        $this->assertSame(1, $xp->query('//h1')->length);
        $this->assertSame(1, $xp->query('//figure/img[@width="1000" and @height="1000"]')->length);
        $this->assertSame(3, $xp->query('//dl/div')->length);
        $this->assertSame(3, $xp->query('//article/h3')->length);
        $this->assertSame(1, $xp->query('//a[@href="/all"]')->length);
        $this->assertSame(1, $xp->query('//a[@href="/form"]')->length);
        $this->assertSame(0, $xp->query('//script|//*[@style]|//*[@class="about-gallery"]')->length);
        foreach (['1989', '30+', '100+', 'produsen sekaligus distributor', 'meja, kursi, lemari, dan rak', 'material pilihan', 'ratusan distributor', 'marketplace', 'Kualitas terjaga', 'Desain fungsional', 'Mudah dijangkau', 'always your choice, always your furniture'] as $text) $this->assertStringContainsString($text, strip_tags($html));
        $this->assertStringContainsString("if ($" . "title === 'Tentang')", file_get_contents(APPPATH . 'Views/layout/template.php'));
        if (getenv('BUYER_ABOUT_ARTIFACTS') !== '1') return;
        $dir = WRITEPATH . 'cache/buyer-about-review';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $url = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
        $styles = '';
        foreach (['bootstrap.min.css', 'style.css', 'commerce-responsive.css', 'storefront-catalog.css', 'buyer-navigation.css', 'buyer-footer.css', 'buyer-about.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        session()->set(['isLogin' => false, 'role' => null, 'email' => null]);
        $nav = view('layout/navbar', ['title' => 'Tentang'], ['saveData' => false]);
        $promo = view('partials/buyer_promo', ['desktopText' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%'], ['saveData' => false]);
        $footer = view('layout/buyer_footer', ['footerVariant' => 'full'], ['saveData' => false]);
        $content = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $url . '/img/', $promo . $nav . '<div class="buyer-page-content">' . $html . '</div>' . $footer);
        $content = str_replace('src="/img/', 'src="' . $url . '/img/', $content);
        foreach (['default' => \App\Models\KonstantaModel::defaultThemeWarna(), 'sand' => ['primary' => '#2F2D20', 'primaryHover' => '#232217', 'soft' => '#F7F2ED', 'soft2' => '#DFCBB9', 'background' => '#FEFEFF']] as $name => $theme) {
            $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--hijau:' . $theme['primary'] . ';}';
            file_put_contents($dir . '/' . $name . '.html', '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '</style><script defer src="' . $url . '/js/buyer-navigation.js"></script></head><body class="buyer-layout">' . $content . '</body></html>');
        }
    }
}
