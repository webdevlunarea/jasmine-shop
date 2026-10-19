<?php

use App\Controllers\Api\V1\AuthController;
use App\Services\Auth\CustomerRegistrationService;
use App\Services\Auth\RecaptchaVerifier;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiCustomerRegistrationTest extends CIUnitTestCase
{
    private function invoke(string $method, array $payload, ?string $captchaError = null)
    {
        $request = service('request');
        $request->setBody(json_encode($payload));
        $request->setHeader('Content-Type', 'application/json');
        service('response')->setStatusCode(200);
        $controller = new class($captchaError) extends AuthController {
            private ?string $captchaError;
            public array $calls = [];
            public function __construct(?string $error)
            {
                $this->captchaError = $error;
                $this->request = service('request');
                $this->response = service('response');
            }
            protected function allowAuthAttempt(string $scope, string $identity = '', int $limit = 8, int $seconds = 900): bool { return true; }
            protected function captchaVerifier(): RecaptchaVerifier
            {
                $error = $this->captchaError;
                return new class($error) extends RecaptchaVerifier {
                    public function __construct(private ?string $error) {}
                    public function error($token, string $platform = 'web', string $expectedAction = ''): ?string { return $this->error; }
                };
            }
            protected function customerRegistrations(): CustomerRegistrationService
            {
                $controller = $this;
                return new class($controller) extends CustomerRegistrationService {
                    public function __construct(private $owner) {}
                    public function register(string $name, string $email, string $password, string $confirmation, string $phone): array
                    {
                        $this->owner->calls[] = ['register', $name, $email, $password, $confirmation, $phone];
                        return ['created' => true, 'email_sent' => true];
                    }
                    public function verify(string $email, $code): bool
                    {
                        $this->owner->calls[] = ['verify', $email, $code];
                        if ($code !== '123456') throw new DomainException('Kode tidak valid.');
                        return true;
                    }
                    public function resend(string $email): bool { $this->owner->calls[] = ['resend', $email]; return true; }
                };
            }
        };
        $response = $controller->{$method}();
        return [$response, $controller->calls];
    }

    public function testRegistrationRequiresConsentAndValidIndonesianPhone(): void
    {
        [$response, $calls] = $this->invoke('register', ['name' => 'Buyer', 'email' => 'buyer@example.test', 'phone' => '081299873465']);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([], $calls);
    }

    public function testRegistrationCreatesPendingAccountWithoutReturningTokens(): void
    {
        [$response, $calls] = $this->invoke('register', [
            'name' => 'Buyer', 'email' => 'BUYER@example.test', 'phone' => '081299873465',
            'password' => 'StrongPass123', 'password_confirmation' => 'StrongPass123', 'consent' => true,
        ]);
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('buyer@example.test', $calls[0][2]);
        $this->assertArrayNotHasKey('tokens', (array)json_decode($response->getBody())->data);
    }

    public function testRegistrationStopsBeforeCreatingAccountWhenCaptchaFails(): void
    {
        [$response, $calls] = $this->invoke('register', [
            'name' => 'Buyer', 'email' => 'buyer@example.test', 'phone' => '081299873465',
            'password' => 'StrongPass123', 'password_confirmation' => 'StrongPass123', 'consent' => true,
        ], 'CAPTCHA tidak valid.');
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([], $calls);
    }

    public function testVerificationAndResendDoNotIssueTokens(): void
    {
        [$verified, $verifyCalls] = $this->invoke('verifyEmail', ['email' => 'buyer@example.test', 'code' => '123456']);
        $this->assertSame(200, $verified->getStatusCode());
        $this->assertArrayNotHasKey('tokens', (array)json_decode($verified->getBody())->data);
        $this->assertSame('verify', $verifyCalls[0][0]);

        [$resent, $resendCalls] = $this->invoke('resendVerification', ['email' => 'buyer@example.test']);
        $this->assertSame(202, $resent->getStatusCode());
        $this->assertSame('resend', $resendCalls[0][0]);
    }
}
