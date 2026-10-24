<?php

use App\Services\Auth\RecaptchaVerifier;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Recaptcha;

final class RecaptchaVerifierTest extends CIUnitTestCase
{
    private function settings(): Recaptcha
    {
        $config = new Recaptcha();
        $config->enabled = true;
        $config->siteKey = 'unit-public-key';
        $config->secretKey = 'unit-private-key';
        $config->allowedHostnames = 'lunareafurniture.com,www.lunareafurniture.com';
        return $config;
    }

    private function client($body, int $status = 200)
    {
        return new class($body, $status) {
            public int $calls = 0;
            private $body;
            private int $status;
            public function __construct($body, int $status) { $this->body = $body; $this->status = $status; }
            public function post($url, array $options) {
                ++$this->calls;
                \PHPUnit\Framework\Assert::assertSame('https://www.google.com/recaptcha/api/siteverify', $url);
                \PHPUnit\Framework\Assert::assertSame('unit-private-key', $options['form_params']['secret']);
                \PHPUnit\Framework\Assert::assertSame(true, $options['verify']);
                \PHPUnit\Framework\Assert::assertSame(false, $options['allow_redirects']);
                return new class($this->body, $this->status) {
                    private $body; private int $status;
                    public function __construct($body, int $status) { $this->body=$body; $this->status=$status; }
                    public function getStatusCode() { return $this->status; }
                    public function getBody() { return is_string($this->body) ? $this->body : json_encode($this->body); }
                };
            }
        };
    }

    public function testDisabledPerformsNoNetworkCall(): void
    {
        $config = $this->settings(); $config->enabled = false;
        $client = $this->client([]);
        $this->assertNull((new RecaptchaVerifier($config, $client))->error(null));
        $this->assertSame(0, $client->calls);
    }

    /** @dataProvider invalidTokens */
    public function testMissingAndMalformedTokensAreRejectedBeforeNetwork($token): void
    {
        $client = $this->client([]);
        $this->assertNotNull((new RecaptchaVerifier($this->settings(), $client))->error($token));
        $this->assertSame(0, $client->calls);
    }
    public static function invalidTokens(): array { return [[null], [''], ['  '], [['token']], [true], [str_repeat('x', 8193)]]; }

    public function testIncompleteConfigurationFailsClosed(): void
    {
        foreach (['siteKey', 'secretKey', 'allowedHostnames'] as $field) {
            $config = $this->settings(); $config->$field = '';
            $client = $this->client([]);
            $this->assertNotNull((new RecaptchaVerifier($config, $client))->error('token'));
            $this->assertSame(0, $client->calls);
        }
    }

    public function testValidResponseAcceptsOnlyExpectedHost(): void
    {
        foreach (['lunareafurniture.com', 'www.lunareafurniture.com'] as $host) {
            $client=$this->client(['success'=>true, 'hostname'=>$host, 'challenge_ts'=>gmdate('c')]);
            $this->assertNull((new RecaptchaVerifier($this->settings(), $client))->error('unit-token'));
            $this->assertSame(1, $client->calls);
        }
    }

    /** @dataProvider rejectedResponses */
    public function testInvalidProviderResponsesAreRejected(array $overrides): void
    {
        $body=array_merge(['success'=>true, 'hostname'=>'lunareafurniture.com', 'challenge_ts'=>gmdate('c')], $overrides);
        $this->assertNotNull((new RecaptchaVerifier($this->settings(), $this->client($body)))->error('unit-token'));
    }
    public static function rejectedResponses(): array
    {
        return [[['success'=>false]], [['success'=>'true']], [['hostname'=>'evil.test']], [['hostname'=>'lunareafurniture.com.evil.test']],
            [['hostname'=>['lunareafurniture.com']]], [['challenge_ts'=>null]], [['challenge_ts'=>'bad date']],
            [['challenge_ts'=>gmdate('c', time()-180)]], [['challenge_ts'=>gmdate('c', time()+300)]],
            [['error-codes'=>['timeout-or-duplicate']]], [['error-codes'=>['over-quota']]], [['error'=>'Over free quota.']]];
    }

    public function testServiceFailuresNeverGrantAccess(): void
    {
        foreach ([$this->client('invalid-json'), $this->client([], 500), new class { public function post($url, $options) { throw new RuntimeException('network error'); } }] as $client) {
            $this->assertNotNull((new RecaptchaVerifier($this->settings(), $client))->error('token'));
        }
    }

    public function testWidgetDoesNotExposeSecretAndIsOmittedWhenOff(): void
    {
        $config=$this->settings();
        $html=view('partials/auth_recaptcha', ['captchaConfig'=>$config], ['saveData'=>false]);
        $this->assertStringContainsString('data-sitekey="unit-public-key"', $html);
        $this->assertStringNotContainsString($config->secretKey, $html);
        $this->assertStringContainsString("slot.clientWidth >= 304 ? 'normal' : 'compact'", $html);
        $this->assertStringContainsString("'expired-callback': window.lunareaCaptchaExpired", $html);
        $this->assertStringContainsString('render=explicit', $html);
        $this->assertStringNotContainsString('transform: scale', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $config->enabled=false;
        $this->assertStringNotContainsString('api.js', view('partials/auth_recaptcha', ['captchaConfig'=>$config], ['saveData'=>false]));
    }

    public function testDirectAuthPostsWithoutTokenStopBeforeModelsOrOtp(): void
    {
        $config=config(Recaptcha::class);
        $saved=[$config->enabled,$config->siteKey,$config->secretKey,$config->allowedHostnames];
        $config->enabled=true; $config->siteKey='unit-public-key'; $config->secretKey='unit-private-key';
        $config->allowedHostnames='lunareafurniture.com';
        try {
            foreach (['actionLogin'=>'/login','actionSignup'=>'/signup'] as $method=>$target) {
                $controller=new class extends \App\Controllers\Pages {
                    public function __construct() {
                        // Models intentionally absent: reaching account logic would fail the test.
                        $this->request=new class {
                            public function getPost($name) { return $name==='redirect' ? '/checkout' : null; }
                            public function getIPAddress() { return '192.0.2.10'; }
                        };
                        $this->response=\Config\Services::response(null,false);
                    }
                };
                $response=$controller->$method();
                $this->assertSame(302,$response->getStatusCode());
                $this->assertStringEndsWith($target,$response->getHeaderLine('Location'));
                $this->assertSame('/checkout',session()->getFlashdata('redirect'));
                $this->assertNotEmpty(session()->getFlashdata('msg'));
            }
        } finally {
            [$config->enabled,$config->siteKey,$config->secretKey,$config->allowedHostnames]=$saved;
        }
    }

    public function testWebsiteRegistrationRateLimitRunsBeforeCaptchaAndAccountWork(): void
    {
        $config = config(Recaptcha::class);
        $savedEnabled = $config->enabled;
        $config->enabled = true;
        $ip = '198.51.100.' . random_int(1, 250);
        try {
            $controller = new class($ip) extends \App\Controllers\Pages {
                public function __construct(string $ip)
                {
                    $this->request = new class($ip) {
                        public function __construct(private string $ip) {}
                        public function getPost($name) { return $name === 'email' ? 'audit-' . $this->ip . '@example.test' : null; }
                        public function getIPAddress() { return $this->ip; }
                    };
                    $this->response = \Config\Services::response(null, false);
                }
            };

            for ($attempt = 0; $attempt < 5; ++$attempt) $controller->actionSignup();
            $response = $controller->actionSignup();
            $this->assertSame(302, $response->getStatusCode());
            $this->assertStringEndsWith('/signup', $response->getHeaderLine('Location'));
            $this->assertSame('Terlalu banyak percobaan membuat akun. Coba lagi nanti.', session()->getFlashdata('msg'));
        } finally {
            $config->enabled = $savedEnabled;
        }
    }

    public function testGuardsPrecedeAccountValidationAndOnlyAuthRoutesGainCsrf(): void
    {
        $source=file_get_contents(APPPATH.'Controllers/Pages.php');
        foreach (['actionLogin','actionSignup'] as $method) {
            $start=strpos($source, 'public function '.$method.'()');
            $guard=strpos($source, 'RecaptchaVerifier', $start);
            $validation=strpos($source, $method === 'actionLogin' ? '$this->validateData(' : '$this->validate(', $start);
            $this->assertLessThan($validation, $guard);
        }
        $routes=file_get_contents(APPPATH.'Config/Routes.php');
        $this->assertStringContainsString("'Pages::actionLogin', ['filter' => ['harusLogout', 'csrf']]", $routes);
        $this->assertStringContainsString("'Pages::actionSignup', ['filter' => ['harusLogout', 'csrf']]", $routes);
        $this->assertStringContainsString("$"."routes->post('/updatetransaction', 'Pages::updateTransaction');", $routes);
    }

    public function testAndroidAssessmentValidatesTokenActionPackageAndScore(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'lunarea-recaptcha-');
        file_put_contents($path, '{}');
        try {
            $config = new Recaptcha();
            $config->androidEnabled = true;
            $config->androidProjectId = 'test-project';
            $config->androidSiteKey = 'test-key';
            $config->androidPackageName = 'com.lunareafurniture.lunarea';
            $config->androidServiceAccountPath = $path;
            $config->androidMinimumScore = 0.5;
            $google = new class {
                public function setAuthConfig(string $path): void {}
                public function setScopes(array $scopes): void {}
                public function fetchAccessTokenWithAssertion(): array { return ['access_token' => 'test-access']; }
            };
            $http = new class {
                public array $options = [];
                public function post(string $url, array $options)
                {
                    $this->options = $options;
                    return new class {
                        public function getStatusCode(): int { return 200; }
                        public function getBody(): string
                        {
                            return json_encode(['tokenProperties' => ['valid' => true, 'action' => 'signup', 'androidPackageName' => 'com.lunareafurniture.lunarea'], 'riskAnalysis' => ['score' => 0.9]]);
                        }
                    };
                }
            };
            $verifier = new RecaptchaVerifier($config, $http, static fn () => $google);
            $this->assertNull($verifier->error('opaque-token', 'android', 'signup'));
            $this->assertSame('signup', $http->options['json']['event']['expectedAction']);
            $this->assertSame('test-key', $http->options['json']['event']['siteKey']);
        } finally {
            @unlink($path);
        }
    }

    public function testAndroidAssessmentRejectsActionMismatchAndLowScore(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'lunarea-recaptcha-');
        file_put_contents($path, '{}');
        try {
            $config = new Recaptcha();
            $config->androidEnabled = true;
            $config->androidProjectId = 'test-project';
            $config->androidSiteKey = 'test-key';
            $config->androidPackageName = 'com.lunareafurniture.lunarea';
            $config->androidServiceAccountPath = $path;
            $google = new class {
                public function setAuthConfig(string $path): void {}
                public function setScopes(array $scopes): void {}
                public function fetchAccessTokenWithAssertion(): array { return ['access_token' => 'test-access']; }
            };
            $http = new class {
                public function post(string $url, array $options)
                {
                    return new class {
                        public function getStatusCode(): int { return 200; }
                        public function getBody(): string
                        {
                            return json_encode(['tokenProperties' => ['valid' => true, 'action' => 'other_action', 'androidPackageName' => 'com.lunareafurniture.lunarea'], 'riskAnalysis' => ['score' => 0.1]]);
                        }
                    };
                }
            };
            $verifier = new RecaptchaVerifier($config, $http, static fn () => $google);
            $this->assertNotNull($verifier->error('opaque-token', 'android', 'signup'));
        } finally {
            @unlink($path);
        }
    }
}
