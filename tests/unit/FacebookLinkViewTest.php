<?php

use CodeIgniter\Test\CIUnitTestCase;

final class FacebookLinkViewTest extends CIUnitTestCase
{
    /** @dataProvider states */
    public function testAccountLinkStatesAndCsrfForm(string $state, int $formCount): void
    {
        $html = view('partials/account_facebook', ['facebookLinkState' => $state], ['saveData' => false]);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xp = new DOMXPath($dom);
        $this->assertSame($formCount, $xp->query('//form')->length);
        if ($state === 'available') {
            $form = $xp->query('//form')->item(0);
            $this->assertSame('post', $form->getAttribute('method'));
            $this->assertSame(site_url('auth/facebook/link'), $form->getAttribute('action'));
            $this->assertSame(1, $xp->query('//input[@type="hidden" and @name="' . csrf_token() . '"]')->length);
            $this->assertSame(1, $xp->query('//button[@type="submit" and @aria-describedby="facebook-link-help"]')->length);
            $this->assertStringContainsString('Tautkan Facebook', $html);
        }
        if ($state === 'linked') $this->assertStringContainsString('Facebook sudah tertaut', $html);
        if ($state === 'disabled') $this->assertSame(0, $xp->query('//section')->length);
    }
    public static function states(): array { return [['available', 1], ['linked', 0], ['unavailable', 0], ['disabled', 0]]; }

    public function testLinkFormIsSeparateFromProfileForm(): void
    {
        $source = file_get_contents(APPPATH . 'Views/pages/account.php');
        $this->assertStringContainsString("view('partials/account_facebook'", $source);
        $this->assertLessThan(strpos($source, '<div class="row g-3 account-main-grid">'), strpos($source, "view('partials/account_facebook'"));
    }
}
