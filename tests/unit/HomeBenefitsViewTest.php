<?php

use CodeIgniter\Test\CIUnitTestCase;

final class HomeBenefitsViewTest extends CIUnitTestCase
{
    public function testBenefitsKeepAllInformationAndUseThemeableIcons(): void
    {
        $html = view('partials/home_benefits', [], ['saveData' => false]);
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($document);
        $this->assertSame(3, $xpath->query('//li[@class="home-benefit"]')->length);
        $this->assertSame(3, $xpath->query('//*[local-name()="svg" and @stroke="currentColor" and @aria-hidden="true"]')->length);
        $this->assertSame(0, $xpath->query('//img|//*[@style]')->length);
        foreach (['Gratis ongkir 100%', 'Pengiriman di pulau Jawa, Madura, dan Bali tanpa minimum pembelian', 'Penawaran Spesial Diskon 5%', 'Khusus pelanggan Lunarea untuk pembelian pertama', 'Pengiriman ke seluruh Indonesia', 'Dengan jasa ekspedisi terpercaya yang mengantarkan ke alamat tujuan'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString("include('partials/home_benefits')", file_get_contents(APPPATH . 'Views/pages/home.php'));
        if (getenv('HOME_BENEFITS_ARTIFACTS') === '1') {
            $directory = WRITEPATH . 'cache/home-benefits-review';
            if (!is_dir($directory)) mkdir($directory, 0775, true);
            $url = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
            $styles = '';
            foreach (['bootstrap.min.css', 'style.css', 'storefront-catalog.css', 'buyer-navigation.css', 'buyer-footer.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
            $footer = view('layout/buyer_footer', ['footerVariant' => 'full'], ['saveData' => false]);
            $footer = str_replace('src="/img/', 'src="' . $url . '/img/', $footer);
            foreach (['default' => \App\Models\KonstantaModel::defaultThemeWarna(), 'sand' => ['primary' => '#2F2D20', 'primaryHover' => '#232217', 'soft' => '#F7F2ED', 'soft2' => '#DFCBB9', 'background' => '#FEFEFF']] as $name => $theme) {
                $variables = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';--theme-background:' . $theme['background'] . ';}';
                file_put_contents($directory . '/' . $name . '.html', '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $variables . '</style></head><body class="buyer-layout"><div class="buyer-page-content"><main>' . $html . '</main></div>' . $footer . '</body></html>');
            }
        }
    }
}
