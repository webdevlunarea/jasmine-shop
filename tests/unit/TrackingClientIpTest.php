<?php

use App\Controllers\TrackingController;
use CodeIgniter\Test\CIUnitTestCase;

final class TrackingClientIpTest extends CIUnitTestCase
{
    public function testForwardedHeadersAreNotReadDirectlyForVisitorIp(): void
    {
        $original = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.99';
        try {
            $controller = new class extends TrackingController {
                public function __construct()
                {
                    $this->request = new class {
                        public function getIPAddress(): string { return '198.51.100.12'; }
                    };
                }
                public function resolvedIp(): string { return $this->getClientIp(); }
            };
            $this->assertSame('198.51.100.12', $controller->resolvedIp());
        } finally {
            if ($original === null) unset($_SERVER['HTTP_X_FORWARDED_FOR']);
            else $_SERVER['HTTP_X_FORWARDED_FOR'] = $original;
        }
        $this->assertSame([], config(\Config\App::class)->proxyIPs);
    }
}
