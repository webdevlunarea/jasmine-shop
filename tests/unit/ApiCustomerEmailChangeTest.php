<?php

use App\Controllers\Api\V1\AuthController;
use App\Services\Auth\CustomerEmailChanges;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiCustomerEmailChangeTest extends CIUnitTestCase
{
    private function controller(array $payload, ?string $failure = null): AuthController
    {
        $request = service('request');
        $request->setBody(json_encode($payload));
        $request->setHeader('Content-Type', 'application/json');
        $request->setHeader('X-Lunarea-Authenticated-Customer', 'old@example.test');
        service('response')->setStatusCode(200);

        return new class($failure) extends AuthController {
            public array $calls = [];
            public function __construct(public ?string $failure)
            {
                $this->request = service('request');
                $this->response = service('response');
            }
            protected function allowAuthAttempt(string $scope, string $identity = '', int $limit = 8, int $seconds = 900): bool { return true; }
            protected function customerEmailChanges(): CustomerEmailChanges
            {
                $controller = $this;
                return new class($controller) extends CustomerEmailChanges {
                    public function __construct(private $owner) {}
                    public function request(string $email, string $password, string $newEmail): void
                    {
                        $this->owner->calls[] = ['request', $email, $password, $newEmail];
                        if ($this->owner->failure) throw new DomainException($this->owner->failure);
                    }
                    public function confirm(string $email, string $password, string $code): string
                    {
                        $this->owner->calls[] = ['confirm', $email, $password, $code];
                        if ($this->owner->failure) throw new DomainException($this->owner->failure);
                        return 'new@example.test';
                    }
                };
            }
        };
    }

    public function testRequestRequiresCurrentPasswordAndReturnsVerificationState(): void
    {
        $controller = $this->controller(['current_password' => 'CurrentPass123', 'new_email' => 'New@Example.test']);
        $response = $controller->requestEmailChange();

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame(['request', 'old@example.test', 'CurrentPass123', 'New@Example.test'], $controller->calls[0]);
        $this->assertTrue(json_decode($response->getBody(), true)['data']['verification_required']);
    }

    public function testConfirmReturnsNewIdentityAndRequiresFreshLogin(): void
    {
        $controller = $this->controller(['current_password' => 'CurrentPass123', 'verification_code' => '123456']);
        $response = $controller->confirmEmailChange();
        $body = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['confirm', 'old@example.test', 'CurrentPass123', '123456'], $controller->calls[0]);
        $this->assertSame('new@example.test', $body['data']['email']);
        $this->assertTrue($body['data']['login_required']);
    }

    public function testInvalidCodeFormatStopsBeforeEmailChangeService(): void
    {
        $controller = $this->controller(['current_password' => 'CurrentPass123', 'verification_code' => '12345']);
        $response = $controller->confirmEmailChange();

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([], $controller->calls);
    }

    public function testDomainRejectionIsReturnedAsValidationError(): void
    {
        $controller = $this->controller(['current_password' => 'wrong', 'new_email' => 'new@example.test'], 'Kata sandi saat ini tidak sesuai.');
        $response = $controller->requestEmailChange();

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('EMAIL_CHANGE_REJECTED', json_decode($response->getBody(), true)['error']['code']);
    }
}
