<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ThemeSandViewTest extends CIUnitTestCase
{
    public function testSandPresetUsesRequestedPaletteWithoutReplacingCurrentTheme(): void
    {
        $theme = \App\Models\KonstantaModel::defaultThemeWarna();
        $renderer = new class {
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render(array $theme): string
            {
                $msg = '';
                $topPromo = [];
                ob_start();
                try {
                    include APPPATH . 'Views/pages/settingWarna.php';
                    return ob_get_contents();
                } finally {
                    ob_end_clean();
                }
            }
        };
        $html = $renderer->render($theme);
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($document);
        $buttons = $xpath->query('//button[@class="preset-btn"][.//p[text()="Lunarea Sand"]]');
        $this->assertSame(1, $buttons->length);
        $colors = json_decode($buttons->item(0)->getAttribute('data-colors'), true);
        $this->assertSame('#2F2D20', $colors['primary']);
        $this->assertSame('#C7A78A', $colors['accent']);
        $this->assertSame('#FEFEFF', $colors['background']);
        $this->assertSame('#2F2D20', $colors['sidebar']);
        $this->assertEqualsCanonicalizing(array_keys($theme), array_keys($colors));
        foreach ($colors as $color) $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $color);
        $this->assertSame($theme['primary'], $xpath->query('//input[@id="primary"]')->item(0)->getAttribute('value'));
        $this->assertStringContainsString('Aizome Sakura', $html);
        $this->assertStringContainsString('Lunarea Hijau', $html);

        if (getenv('BUYER_NAV_ARTIFACTS') === '1') {
            $directory = WRITEPATH . 'cache/buyer-navigation-review';
            if (!is_dir($directory)) mkdir($directory, 0775, true);
            $publicUrl = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
            file_put_contents($directory . '/theme-sand.html', '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
                . '<link rel="stylesheet" href="' . $publicUrl . '/css/bootstrap.min.css"><link rel="stylesheet" href="' . $publicUrl . '/css/style.css">'
                . '</head><body class="admin-body">' . $html . '</body></html>');
        }
    }
}
