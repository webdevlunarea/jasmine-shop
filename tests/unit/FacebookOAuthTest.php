<?php

use App\Services\Auth\FacebookOAuth;
use CodeIgniter\Test\CIUnitTestCase;

final class FacebookOAuthTest extends CIUnitTestCase
{
    private function settings(): \Config\Facebook
    {
        $c = new \Config\Facebook();
        $c->enabled = true; $c->appId = '123'; $c->appSecret = 'fixture-only-secret';
        $c->redirectUri = 'https://example.test/auth/facebook/callback'; $c->graphVersion = 'v25.0';
        return $c;
    }

    public function testDialogContainsStateAndNoSecret(): void
    {
        $oauth = new FacebookOAuth($this->settings());
        $this->assertTrue($oauth->ready());
        $url = $oauth->authorizationUrl('random-state');
        parse_str(parse_url($url, PHP_URL_QUERY), $q);
        $this->assertSame('random-state', $q['state']);
        $this->assertSame('code', $q['response_type']);
        $this->assertSame('public_profile,email', $q['scope']);
        $this->assertSame('https://example.test/auth/facebook/callback', $q['redirect_uri']);
        $this->assertStringNotContainsString('fixture-only-secret', $url);
        $this->assertArrayNotHasKey('client_secret', $q);
        $c = $this->settings(); $c->enabled = false;
        $this->assertFalse((new FacebookOAuth($c))->ready());
        $c->enabled = true; $c->appSecret = '';
        $this->assertFalse((new FacebookOAuth($c))->ready());
    }

    /** @dataProvider callbackUris */
    public function testConfigurationValidatesCallbackUri(string $uri, bool $valid): void
    {
        $c = $this->settings();
        $c->redirectUri = $uri;
        $this->assertSame($valid, $c->ready());
    }

    public static function callbackUris(): array
    {
        return [
            ['https://example.test/auth/facebook/callback', true],
            ['https://example.test/jasmine-shop/public/auth/facebook/callback', true],
            ['http://localhost:8080/auth/facebook/callback', true],
            ['http://127.0.0.1:8080/auth/facebook/callback', true],
            ['http://example.test/auth/facebook/callback', false],
            ['https://user:pass@example.test/auth/facebook/callback', false],
            ['https://example.test/auth/facebook/callback?token=x', false],
            ['https://example.test/auth/facebook/callback#token', false],
            ['https://example.test/not-a-callback', false],
            ['not-a-url', false],
        ];
    }

    public function testPlaceholderAppIdDoesNotEnableButton(): void
    {
        $c = $this->settings(); $c->appId = 'YOUR_APP_ID';
        $this->assertFalse($c->ready());
    }

    public function testDocumentedEnvironmentKeysLoadIntoConfig(): void
    {
        $values = [
            'facebook.enabled' => 'true', 'facebook.appId' => '123', 'facebook.appSecret' => 'environment-fixture-only',
            'facebook.redirectUri' => 'https://example.test/auth/facebook/callback', 'facebook.graphVersion' => 'v25.0',
        ];
        $original = [];
        foreach ($values as $key => $value) {
            $original[$key] = [array_key_exists($key, $_ENV), $_ENV[$key] ?? null];
            $_ENV[$key] = $value;
        }
        try {
            $c = new \Config\Facebook();
            $this->assertTrue($c->ready());
            $this->assertSame('123', $c->appId);
            $this->assertSame('https://example.test/auth/facebook/callback', $c->redirectUri);
            $_ENV['facebook.enabled'] = 'false';
            $this->assertFalse((new \Config\Facebook())->ready());
        } finally {
            foreach ($original as $key => [$exists, $value]) {
                if ($exists) $_ENV[$key] = $value; else unset($_ENV[$key]);
            }
        }
    }

    /** @dataProvider targets */
    public function testOnlyLocalRedirects($value, string $expected): void
    {
        $this->assertSame($expected, FacebookOAuth::redirectTarget($value));
    }
    public static function targets(): array
    {
        return [['/checkout?from=cart', '/checkout?from=cart'], ['https://evil.test', ''], ['//evil.test', ''],
            ['/\\evil.test', ''], ['/%2fevil.test', ''], ['/%5cevil.test', ''], ["/\r\nLocation:evil", ''],
            ['/%0aevil', ''], [[], ''], [null, '']];
    }

    /** @dataProvider applicationTargets */
    public function testRedirectsRespectApplicationBasePath(string $target, string $baseURL, string $expected): void
    {
        $this->assertSame($expected, FacebookOAuth::applicationPath($target, $baseURL));
    }

    public static function applicationTargets(): array
    {
        return [
            ['', 'https://example.test/', '/'],
            ['/checkout', 'https://example.test/', '/checkout'],
            ['', 'http://localhost/jasmine-shop/public/', '/jasmine-shop/public/'],
            ['/checkout?from=cart', 'http://localhost/jasmine-shop/public/', '/jasmine-shop/public/checkout?from=cart'],
            ['/jasmine-shop/public/checkout', 'http://localhost/jasmine-shop/public/', '/jasmine-shop/public/checkout'],
            ['/jasmine-shop/public?from=login', 'http://localhost/jasmine-shop/public/', '/jasmine-shop/public?from=login'],
            ['//evil.test', 'http://localhost/jasmine-shop/public/', '/jasmine-shop/public/'],
        ];
    }

    public function testExchangeUsesPostAndBearerAndProofWithoutPersistingToken(): void
    {
        $oauth = new class($this->settings()) extends FacebookOAuth {
            public array $calls = [];
            protected function requestJson(string $url, array $options): array {
                $this->calls[] = [$url, $options];
                return count($this->calls) === 1 ? ['access_token' => 'fixture-token'] : ['id' => '987', 'name' => 'Buyer'];
            }
        };
        $this->assertSame('987', $oauth->profile('fixture-code')['id']);
        [$url, $options] = $oauth->calls[0];
        $this->assertStringEndsWith('/oauth/access_token', $url);
        $this->assertStringNotContainsString('fixture-only-secret', $url);
        $this->assertTrue($options[CURLOPT_POST]);
        parse_str($options[CURLOPT_POSTFIELDS], $body);
        $this->assertSame('fixture-only-secret', $body['client_secret']);
        $this->assertSame('fixture-code', $body['code']);
        [$url, $options] = $oauth->calls[1];
        $this->assertSame(['Authorization: Bearer fixture-token'], $options[CURLOPT_HTTPHEADER]);
        $this->assertStringContainsString(hash_hmac('sha256', 'fixture-token', 'fixture-only-secret'), $url);
        $this->assertStringNotContainsString('fixture-token', $url);
    }

    public function testMissingTokenStopsProfileLookup(): void
    {
        $oauth = new class($this->settings()) extends FacebookOAuth {
            protected function requestJson(string $url, array $options): array { return []; }
        };
        $this->expectException(\RuntimeException::class);
        $oauth->profile('fixture-code');
    }

    public function testButtonIsConditionalAndPreservesCheckoutRedirect(): void
    {
        $c = config(\Config\Facebook::class);
        $original = clone $c;
        try {
            foreach (['enabled', 'appId', 'appSecret', 'redirectUri', 'graphVersion'] as $key) $c->$key = $this->settings()->$key;
            $html = view('partials/auth_facebook', ['redirect' => '/checkout?from=cart'], ['saveData' => false]);
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $this->assertStringContainsString('/auth/facebook?redirect=%2Fcheckout%3Ffrom%3Dcart', $dom->getElementsByTagName('a')->item(0)->getAttribute('href'));
            $this->assertStringContainsString('Masuk dengan Facebook', $html);
            $this->assertStringContainsString('aria-hidden="true"', $html);
            $this->assertStringNotContainsString('fixture-only-secret', $html);
            $c->enabled = false;
            $this->assertStringNotContainsString('auth-facebook-btn', view('partials/auth_facebook', [], ['saveData' => false]));
        } finally {
            foreach (['enabled', 'appId', 'appSecret', 'redirectUri', 'graphVersion'] as $key) $c->$key = $original->$key;
        }
    }
}
