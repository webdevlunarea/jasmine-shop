<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/** Exercise the real route collection and Pages bootstrap, without contacting Meta. */
final class FacebookRoutesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private $originalFacebook;

    protected function setUp(): void
    {
        parent::setUp();
        session()->destroy();
        $this->originalFacebook = clone config(\Config\Facebook::class);
        $c = config(\Config\Facebook::class);
        $c->enabled = true; $c->appId = '123'; $c->appSecret = 'route-fixture-only';
        $c->redirectUri = 'https://example.test/auth/facebook/callback'; $c->graphVersion = 'v25.0';
    }

    protected function tearDown(): void
    {
        $c = config(\Config\Facebook::class);
        foreach (['enabled', 'appId', 'appSecret', 'redirectUri', 'graphVersion'] as $key) $c->$key = $this->originalFacebook->$key;
        session()->destroy();
        parent::tearDown();
    }

    public function testStartRouteRedirectsToFacebookWithStateAndSecurityHeaders(): void
    {
        $result = $this->get('/auth/facebook?redirect=%2Fcheckout');
        $result->assertStatus(302);
        $location = $result->response()->getHeaderLine('Location');
        $this->assertStringStartsWith('https://www.facebook.com/v25.0/dialog/oauth?', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('123', $query['client_id']);
        $this->assertSame('https://example.test/auth/facebook/callback', $query['redirect_uri']);
        $this->assertSame(session('facebook_oauth')['state'], $query['state']);
        $this->assertSame('/checkout', session('facebook_oauth')['redirect']);
        $this->assertStringNotContainsString('route-fixture-only', $location);
        $this->assertStringContainsString('no-store', $result->response()->getHeaderLine('Cache-Control'));
        $this->assertSame('no-referrer', $result->response()->getHeaderLine('Referrer-Policy'));
    }

    public function testDisabledStartRouteReturnsToLogin(): void
    {
        config(\Config\Facebook::class)->enabled = false;
        $result = $this->get('/auth/facebook?redirect=%2Fcheckout');
        $result->assertRedirectTo(site_url('login') . '?redirect=%2Fcheckout');
        $this->assertNull(session('facebook_oauth'));
    }

    public function testCallbackRouteRequiresStateFromInitiatedLogin(): void
    {
        $result = $this->get('/auth/facebook/callback?state=unrequested&code=fixture-code');
        $result->assertRedirectTo(site_url('login'));
        $this->assertFalse((bool)session('isLogin'));
        $this->assertSame('no-referrer', $result->response()->getHeaderLine('Referrer-Policy'));
    }
}
