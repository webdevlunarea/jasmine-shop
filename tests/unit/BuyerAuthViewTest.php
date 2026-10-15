<?php

use CodeIgniter\Test\CIUnitTestCase;

final class BuyerAuthViewTest extends CIUnitTestCase
{
    /** @dataProvider authCases */
    public function testCleanFormsKeepAuthenticationContracts(string $page, bool $errors): void
    {
        $val = array_fill_keys(['msg', 'val_nama', 'val_email', 'val_sandi', 'val_nohp', 'isiEmail'], '');
        if ($errors) {
            $val['msg'] = 'Silakan periksa kembali data akun Anda.';
            foreach (['val_nama', 'val_email', 'val_sandi', 'val_nohp'] as $key) $val[$key] = 'Data belum sesuai. Periksa kembali isian Anda.';
        }
        $val['isiEmail'] = 'pelanggan@example.test';
        $renderer = new class {
            public function extend(string $name): string { return ''; }
            public function section(string $name): string { return ''; }
            public function endSection(): string { return ''; }
            public function render(string $page, array $val): string {
                $redirect = '/checkout?from=cart&step=address';
                ob_start();
                try { include APPPATH . 'Views/pages/' . $page . '.php'; return ob_get_contents(); } finally { ob_end_clean(); }
            }
        };
        $html = $renderer->render($page, $val);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xp = new DOMXPath($dom);
        $this->assertSame(0, $xp->query('//aside|//*[contains(@class,"auth-trust-note") or contains(@class,"auth-eyebrow") or contains(@class,"auth-breadcrumb")]')->length);
        $this->assertSame(1, $xp->query('//h1')->length);
        $this->assertSame(1, $xp->query('//form[@method="post" and @action="' . ($page === 'login' ? '/masuk' : '/daftar') . '"]')->length);
        foreach ($page === 'login' ? ['email', 'sandi'] : ['nama', 'email', 'sandi', 'nohp'] as $name) {
            $input = $xp->query('//input[@name="' . $name . '"]')->item(0);
            $this->assertNotNull($input);
            $this->assertTrue($input->hasAttribute('required'));
            $this->assertSame(1, $xp->query('//label[@for="' . $input->getAttribute('id') . '"]')->length);
        }
        $this->assertSame(1, $xp->query('//input[@type="hidden" and @name="' . csrf_token() . '"]')->length);
        $this->assertSame('/checkout?from=cart&step=address', $xp->query('//input[@name="redirect"]')->item(0)->getAttribute('value'));
        $this->assertStringContainsString('/auth/google?redirect=%2Fcheckout%3Ffrom%3Dcart%26step%3Daddress', $html);
        $this->assertSame(1, $xp->query('//button[@type="button" and @aria-pressed="false"]')->length);
        $this->assertSame(1, $xp->query('//*[local-name()="svg" and @class="auth-google-icon"]')->length);
        if ($page === 'signup') {
            $this->assertSame(1, $xp->query('//input[@id="syarat" and @type="checkbox" and @required]')->length);
            $this->assertFalse($xp->query('//input[@name="sandi"]')->item(0)->hasAttribute('value'));
            $this->assertStringContainsString('/kebijakan-privasi', $html);
            $this->assertStringContainsString('/syarat-dan-ketentuan', $html);
        }
        $this->assertSame($errors ? 1 : 0, $xp->query('//*[@role="alert"]')->length);
        if (getenv('BUYER_AUTH_ARTIFACTS') === '1') $this->fixture($page, $errors, $html);
    }

    public function testTemplateSuppressesPromotionsOnlyForAuthPages(): void
    {
        $source = file_get_contents(APPPATH . 'Views/layout/template.php');
        $this->assertStringContainsString("in_array($" . "title, ['Masuk', 'Daftar'], true)", $source);
        $this->assertSame(2, substr_count($source, 'if (!$isAdminLayout && !$isAuthPage)'));
        $this->assertStringContainsString("view('layout/buyer_footer'", $source);
    }

    public static function authCases(): array { return [['login', false], ['login', true], ['signup', false], ['signup', true]]; }

    private function fixture(string $page, bool $errors, string $html): void
    {
        $dir = WRITEPATH . 'cache/buyer-auth-review';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $url = 'file:///' . str_replace('\\', '/', rtrim(FCPATH, '\\/'));
        $styles = '';
        foreach (['bootstrap.min.css', 'style.css', 'commerce-responsive.css', 'buyer-navigation.css', 'buyer-footer.css', 'buyer-auth.css'] as $css) $styles .= '<link rel="stylesheet" href="' . $url . '/css/' . $css . '">';
        session()->set(['isLogin' => false, 'role' => null, 'email' => null]);
        $nav = view('layout/navbar', ['title' => $page === 'login' ? 'Masuk' : 'Daftar'], ['saveData' => false]);
        $footer = view('layout/buyer_footer', ['footerVariant' => 'minimal', 'footerPayment' => false], ['saveData' => false]);
        $content = preg_replace('~src="https?://[^\"]*/img/~', 'src="' . $url . '/img/', $nav . '<div class="buyer-page-content">' . $html . '</div>' . $footer);
        $content = str_replace('src="/img/', 'src="' . $url . '/img/', $content);
        $theme = \App\Models\KonstantaModel::defaultThemeWarna();
        $tokens = ':root,*{--theme-primary:' . $theme['primary'] . ';--theme-primary-hover:' . $theme['primaryHover'] . ';--theme-background:' . $theme['background'] . ';--theme-soft:' . $theme['soft'] . ';--theme-soft-2:' . $theme['soft2'] . ';}';
        file_put_contents($dir . '/' . $page . ($errors ? '-errors' : '') . '.html', '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $styles . '<style>' . $tokens . '</style><script defer src="' . $url . '/js/buyer-navigation.js"></script></head><body class="buyer-layout buyer-auth-body">' . $content . '</body></html>');
    }
}
