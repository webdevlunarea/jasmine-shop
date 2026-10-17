<?php

use App\Controllers\FacebookLoginTrait;
use App\Services\Auth\FacebookOAuth;
use CodeIgniter\Test\CIUnitTestCase;

final class FacebookCallbackTest extends CIUnitTestCase
{
    protected function setUp(): void { parent::setUp(); session()->destroy(); }
    protected function tearDown(): void { session()->destroy(); parent::tearDown(); }

    private function controller(array $query = [], bool $ready = true)
    {
        return new class($query, $ready) {
            use FacebookLoginTrait;
            public $request;
            public array $calls = [];
            public bool $ready;
            public bool $failProfile = false;
            public bool $collision = false;
            public string $role = '0';
            public function __construct(array $query, bool $ready) {
                $this->ready = $ready;
                $this->request = new class($query) {
                    private array $query;
                    public function __construct(array $query) { $this->query = $query; }
                    public function getGet($key) { return $this->query[$key] ?? null; }
                    public function getMethod() { return 'POST'; }
                };
            }
            protected function facebookOAuth(): FacebookOAuth {
                return new class($this) extends FacebookOAuth {
                    private $owner;
                    public function __construct($owner) { $this->owner = $owner; }
                    public function ready(): bool { return $this->owner->ready; }
                    public function authorizationUrl(string $state): string { return 'https://www.facebook.com/dialog/oauth?state=' . $state; }
                    public function profile(string $code): array {
                        $this->owner->calls[] = 'profile';
                        if ($this->owner->failProfile) throw new \RuntimeException('provider failed');
                        return ['id' => '987'];
                    }
                };
            }
            protected function facebookCustomer(array $profile): array {
                $this->calls[] = 'customer';
                if ($this->collision) throw new \DomainException('Email sudah terdaftar.');
                return ['email' => 'buyer@example.test', 'active' => '1', 'role' => $this->role];
            }
            private function loginUserSession(array $user): bool { $this->calls[] = 'session'; session()->set(['email' => $user['email'], 'role' => $user['role'], 'isLogin' => true]); return true; }
            protected function facebookLinkCustomer(array $profile, string $email): array {
                $this->calls[] = 'link:' . $email;
                return ['email' => $email, 'active' => '1', 'role' => '0'];
            }
            private function claimMissingAutoVouchersForEmail(string $email): void { $this->calls[] = 'claim'; }
            private function prepareLoginVoucherNotification(string $email): void { $this->calls[] = 'voucher'; }
        };
    }

    private function pending(int $age = 0): void
    {
        session()->set('facebook_oauth', ['state' => 'fixture-state', 'created' => time() - $age, 'redirect' => '/checkout?from=cart']);
    }

    public function testStartStoresRandomStateAndSanitizesTarget(): void
    {
        $c = $this->controller(['redirect' => '//evil.test']);
        $this->assertStringStartsWith('https://www.facebook.com/', $c->facebookLogin()->getHeaderLine('Location'));
        $pending = session()->get('facebook_oauth');
        $this->assertSame('', $pending['redirect']);
        $this->assertSame(64, strlen($pending['state']));
        $first = $pending['state'];
        $c->facebookLogin();
        $this->assertNotSame($first, session()->get('facebook_oauth')['state']);
    }

    public function testDisabledProviderReturnsToLoginWithoutState(): void
    {
        $c = $this->controller(['redirect' => '/checkout'], false);
        $this->assertStringEndsWith('/login?redirect=%2Fcheckout', $c->facebookLogin()->getHeaderLine('Location'));
        $this->assertNull(session()->get('facebook_oauth'));
    }

    /** @dataProvider invalidCallbacks */
    public function testInvalidCallbacksNeverReachProvider(array $query, int $age): void
    {
        $this->pending($age);
        $c = $this->controller($query);
        $this->assertStringContainsString('/login?', $c->facebookCallback()->getHeaderLine('Location'));
        $this->assertSame([], $c->calls);
        $this->assertNull(session()->get('facebook_oauth'));
        $this->assertFalse((bool)session('isLogin'));
    }
    public static function invalidCallbacks(): array {
        return [[[], 0], [['state' => 'wrong', 'code' => 'code'], 0], [['state' => ['bad'], 'code' => 'code'], 0],
            [['state' => 'fixture-state', 'code' => ['bad']], 0], [['state' => 'fixture-state', 'code' => 'code'], 601],
            [['state' => 'fixture-state', 'code' => 'code'], -10], [['state' => 'fixture-state', 'error' => 'access_denied'], 0]];
    }

    public function testSuccessfulCallbackPreservesCheckoutAndConsumesState(): void
    {
        $this->pending();
        $c = $this->controller(['state' => 'fixture-state', 'code' => 'code']);
        session()->set('usepoin', 'old');
        $response = $c->facebookCallback();
        $this->assertSame('no-store, private', $response->getHeaderLine('Cache-Control'));
        $this->assertSame('no-referrer', $response->getHeaderLine('Referrer-Policy'));
        $this->assertStringEndsWith('/hapuslocalstorage/' . base64_encode('/checkout?from=cart'), $response->getHeaderLine('Location'));
        $this->assertSame(['profile', 'customer', 'session', 'claim', 'voucher'], $c->calls);
        $this->assertSame('buyer@example.test', session('email'));
        $this->assertTrue(session('isLogin'));
        $this->assertNull(session('usepoin'));
        $this->assertNull(session('facebook_oauth'));
        session()->remove(['email', 'isLogin']);
        $c->calls = [];
        $c->facebookCallback();
        $this->assertSame([], $c->calls);
    }

    public function testProviderErrorAndEmailCollisionNeverCreateSession(): void
    {
        foreach (['failProfile', 'collision'] as $failure) {
            $this->pending();
            $c = $this->controller(['state' => 'fixture-state', 'code' => 'code']);
            $c->$failure = true;
            $this->assertStringContainsString('/login?', $c->facebookCallback()->getHeaderLine('Location'));
            $this->assertNotContains('session', $c->calls);
            $this->assertFalse((bool)session('isLogin'));
        }
    }

    public function testAdminIsNotLoggedInThroughFacebook(): void
    {
        $this->pending();
        $c = $this->controller(['state' => 'fixture-state', 'code' => 'code']);
        $c->role = '1';
        $c->facebookCallback();
        $this->assertNotContains('session', $c->calls);
        $this->assertFalse((bool)session('isLogin'));
    }

    public function testSuccessfulCallbackRespectsXamppSubdirectory(): void
    {
        $app = config(\Config\App::class);
        $original = $app->baseURL;
        $app->baseURL = 'http://localhost/jasmine-shop/public/';
        try {
            $this->pending();
            $c = $this->controller(['state' => 'fixture-state', 'code' => 'code']);
            $response = $c->facebookCallback();
            $this->assertStringEndsWith('/jasmine-shop/public/hapuslocalstorage/' . base64_encode('/jasmine-shop/public/checkout?from=cart'), $response->getHeaderLine('Location'));
        } finally {
            $app->baseURL = $original;
        }
    }

    private function signedIn(): void
    {
        session()->set(['isLogin' => true, 'email' => 'buyer@example.test', 'role' => '0', 'active' => '1', 'keranjang' => [['id' => 123]], 'poin' => [['nominal' => 50]]]);
    }

    private function pendingLink(): void
    {
        $this->signedIn();
        session()->set('facebook_link_nonce', 'fixture-binding');
        session()->set('facebook_oauth', ['mode' => 'link', 'state' => 'fixture-state', 'created' => time(),
            'email' => 'buyer@example.test', 'link_nonce' => 'fixture-binding', 'redirect' => '/account']);
    }

    public function testLinkStartBindsIntentToCurrentAccountAndSession(): void
    {
        $this->signedIn();
        $c = $this->controller();
        $response = $c->facebookLink();
        $this->assertStringStartsWith('https://www.facebook.com/', $response->getHeaderLine('Location'));
        $pending = session('facebook_oauth');
        $this->assertSame('link', $pending['mode']);
        $this->assertSame('buyer@example.test', $pending['email']);
        $this->assertSame(session('facebook_link_nonce'), $pending['link_nonce']);
        $this->assertSame(64, strlen($pending['state']));
    }

    public function testGuestCannotStartLinking(): void
    {
        $c = $this->controller();
        $this->assertStringEndsWith('/login', $c->facebookLink()->getHeaderLine('Location'));
        $this->assertNull(session('facebook_oauth'));
    }

    public function testLinkCallbackPreservesCurrentSessionAndDoesNotCreateAnotherAccount(): void
    {
        $this->pendingLink();
        $c = $this->controller(['state' => 'fixture-state', 'code' => 'code']);
        $response = $c->facebookCallback();
        $this->assertStringEndsWith('/account', $response->getHeaderLine('Location'));
        $this->assertSame(['profile', 'link:buyer@example.test'], $c->calls);
        $this->assertSame('buyer@example.test', session('email'));
        $this->assertSame([['id' => 123]], session('keranjang'));
        $this->assertSame([['nominal' => 50]], session('poin'));
        $this->assertNull(session('facebook_link_nonce'));
        $this->assertNull(session('facebook_oauth'));
        $c->calls = [];
        $c->facebookCallback();
        $this->assertSame([], $c->calls);
    }

    /** @dataProvider changedLinkSessions */
    public function testChangedLinkSessionNeverCallsProviderOrDatabase(string $field, $value): void
    {
        $this->pendingLink();
        session()->set($field, $value);
        $c = $this->controller(['state' => 'fixture-state', 'code' => 'code']);
        $c->facebookCallback();
        $this->assertSame([], $c->calls);
        $this->assertNull(session('facebook_oauth'));
    }
    public static function changedLinkSessions(): array
    {
        return [['email', 'other@example.test'], ['isLogin', false], ['active', '0'], ['role', '1'], ['facebook_link_nonce', 'different-session']];
    }

    /** @dataProvider invalidLinkCallbacks */
    public function testCancelledExpiredOrInvalidLinkDoesNotChangeAccount(array $query, int $age): void
    {
        $this->pendingLink();
        $pending = session('facebook_oauth'); $pending['created'] = time() - $age; session()->set('facebook_oauth', $pending);
        $c = $this->controller($query);
        $this->assertStringEndsWith('/account', $c->facebookCallback()->getHeaderLine('Location'));
        $this->assertSame([], $c->calls);
        $this->assertSame('buyer@example.test', session('email'));
        $this->assertNull(session('facebook_oauth'));
    }
    public static function invalidLinkCallbacks(): array
    {
        return [[['state' => 'fixture-state', 'error' => 'access_denied'], 0],
            [['state' => 'wrong', 'code' => 'code'], 0], [['state' => 'fixture-state', 'code' => 'code'], 601]];
    }

    public function testLogoutInvalidatesPendingLinkFlow(): void
    {
        $this->pendingLink();
        $controller = new class extends \App\Controllers\Pages { public function __construct() {} };
        $controller->logout();
        $this->assertNull(session('facebook_oauth'));
        $this->assertNull(session('facebook_link_nonce'));
        $this->assertFalse((bool)session('isLogin'));
    }
}
