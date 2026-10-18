<?php

use CodeIgniter\Test\CIUnitTestCase;

final class UsernameViewTest extends CIUnitTestCase
{
    /** @dataProvider settings */
    public function testAccountFormAndUnavailableState(bool $available, string $username): void
    {
        $html = view('partials/account_username', ['usernameSettings' => ['available' => $available, 'username' => $username, 'can_change' => true]], ['saveData' => false]);
        $dom = new DOMDocument(); @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html); $xp = new DOMXPath($dom);
        $this->assertSame($available ? 1 : 0, $xp->query('//form')->length);
        if (!$available) return;
        $this->assertSame(site_url('account/username'), $xp->query('//form')->item(0)->getAttribute('action'));
        $this->assertSame('post', $xp->query('//form')->item(0)->getAttribute('method'));
        $this->assertSame(1, $xp->query('//input[@name="' . csrf_token() . '"]')->length);
        $input = $xp->query('//input[@name="username"]')->item(0);
        $this->assertSame($username, $input->getAttribute('value'));
        $this->assertSame('30', $input->getAttribute('maxlength'));
        $this->assertSame('username', $input->getAttribute('autocomplete'));
        $this->assertSame(1, $xp->query('//label[@for="account-username-input"]')->length);
        $this->assertStringContainsString($username === '' ? 'Simpan username' : 'Ubah username', $html);
    }
    public static function settings(): array { return [[false, ''], [true, ''], [true, 'pembeli_01']]; }

    public function testLoginAcceptsUsernameAndAccountFormKeepsCsrf(): void
    {
        $login = file_get_contents(APPPATH . 'Views/pages/login.php');
        $this->assertStringContainsString('Email atau username', $login);
        $this->assertStringContainsString('id="login-email" type="text"', $login);
        $this->assertStringContainsString('autocomplete="username"', $login);
        $account = file_get_contents(APPPATH . 'Views/pages/account.php');
        $this->assertStringContainsString("view('partials/account_username'", $account);
        $this->assertStringContainsString('<?= csrf_field(); ?>', $account);
    }
}
