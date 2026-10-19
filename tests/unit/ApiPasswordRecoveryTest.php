<?php

use App\Controllers\Api\V1\AuthController;
use App\Services\Auth\PasswordResetService;
use App\Services\Auth\RecaptchaVerifier;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiPasswordRecoveryTest extends CIUnitTestCase
{
    private function invoke(string $method, array $payload, PasswordResetService $service, ?string $captchaError = null)
    {
        $request = service('request');
        $request->setBody(json_encode($payload));
        $request->setHeader('Content-Type', 'application/json');
        $controller = new class($service, $captchaError) extends AuthController {
            private PasswordResetService $resetService;
            private ?string $captchaError;
            public function __construct(PasswordResetService $service, ?string $captchaError)
            {
                $this->resetService = $service;
                $this->captchaError = $captchaError;
                $this->request = service('request');
                $this->response = service('response');
            }
            protected function passwordResets(): PasswordResetService { return $this->resetService; }
            protected function captchaVerifier(): RecaptchaVerifier
            {
                $message = $this->captchaError;
                return new class($message) extends RecaptchaVerifier {
                    private ?string $message;
                    public function __construct(?string $message) { $this->message = $message; }
                    public function error($token, string $platform = 'web', string $expectedAction = ''): ?string { return $this->message; }
                };
            }
        };
        return $controller->{$method}();
    }

    public function testResetRequestUsesEnumerationSafeResponse(): void
    {
        $known = new class extends PasswordResetService {
            public array $requested = [];
            public function __construct() {}
            public function resolveCustomerEmail(string $identity, string $ip): ?string { return 'buyer@example.test'; }
            public function request(string $email, string $ip): bool { $this->requested[] = $email; return true; }
        };
        $unknown = new class extends PasswordResetService {
            public function __construct() {}
            public function resolveCustomerEmail(string $identity, string $ip): ?string { return null; }
        };
        $knownResponse = $this->invoke('requestPasswordReset', ['identity' => 'buyer@example.test'], $known);
        $unknownResponse = $this->invoke('requestPasswordReset', ['identity' => 'nobody@example.test'], $unknown);
        $this->assertSame(202, $knownResponse->getStatusCode());
        $this->assertSame(202, $unknownResponse->getStatusCode());
        $this->assertSame($knownResponse->getJSON(), $unknownResponse->getJSON());
        $this->assertSame(['buyer@example.test'], $known->requested);
        $this->assertStringNotContainsString('buyer@example.test', $knownResponse->getBody());
    }

    public function testResetRequestRequiresCaptchaWhenVerifierRejectsIt(): void
    {
        $service = new class extends PasswordResetService {
            public function __construct() {}
            public function resolveCustomerEmail(string $identity, string $ip): ?string { throw new RuntimeException('Must not resolve'); }
        };
        $response = $this->invoke('requestPasswordReset', ['identity' => 'buyer@example.test'], $service, 'Captcha tidak valid.');
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('CAPTCHA_REQUIRED', json_decode($response->getBody())->error->code);
    }

    public function testResetCompletionUsesOtpServiceAndReturnsNoAuthenticationToken(): void
    {
        $service = new class extends PasswordResetService {
            public array $resetArgs = [];
            public function __construct() {}
            public function resolveCustomerEmail(string $identity, string $ip): ?string { return 'buyer@example.test'; }
            public function reset(string $email, $code, $password, $confirmation, string $ip): void
            {
                $this->resetArgs = [$email, $code, $password, $confirmation];
            }
        };
        $response = $this->invoke('completePasswordReset', [
            'identity' => 'buyer@example.test', 'code' => '123456',
            'password' => 'newPass123', 'password_confirmation' => 'newPass123',
        ], $service);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['buyer@example.test', '123456', 'newPass123', 'newPass123'], $service->resetArgs);
        $this->assertArrayNotHasKey('tokens', (array)json_decode($response->getBody())->data);
    }

    public function testResetCompletionValidatesPasswordBeforeResolvingAccount(): void
    {
        $service = new class extends PasswordResetService {
            public function __construct() {}
            public function resolveCustomerEmail(string $identity, string $ip): ?string { throw new RuntimeException('Must not resolve'); }
        };
        $response = $this->invoke('completePasswordReset', [
            'identity' => 'buyer@example.test', 'code' => '123456',
            'password' => 'short', 'password_confirmation' => 'short',
        ], $service);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('VALIDATION_ERROR', json_decode($response->getBody())->error->code);
    }
}
