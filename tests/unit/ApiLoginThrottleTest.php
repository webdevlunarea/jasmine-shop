<?php

use App\Controllers\Api\V1\AuthController;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiLoginThrottleTest extends CIUnitTestCase
{
    private function controller(array $payload, bool $allowed): AuthController
    {
        $request = service('request');
        $request->setBody(json_encode($payload));
        $request->setHeader('Content-Type', 'application/json');
        service('response')->setStatusCode(200);

        return new class($allowed) extends AuthController {
            public array $attempts = [];
            public function __construct(private bool $allowed)
            {
                $this->request = service('request');
                $this->response = service('response');
            }
            protected function allowAuthAttempt(string $scope, string $identity = '', int $limit = 8, int $seconds = 900): bool
            {
                $this->attempts[] = [$scope, $identity, $limit, $seconds];
                return $this->allowed;
            }
        };
    }

    public function testLoginThrottlesByBothIpAndNormalizedAccountIdentity(): void
    {
        $controller = $this->controller(['identifier' => 'BUYER@example.test', 'password' => 'wrong'], false);
        $response = $controller->login();
        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame([['login', 'BUYER@example.test', 8, 300]], $controller->attempts);
    }

    public function testMalformedLoginDoesNotConsumeAuthenticationAttempt(): void
    {
        $controller = $this->controller(['identifier' => '', 'password' => ''], false);
        $response = $controller->login();
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([], $controller->attempts);
    }
}
