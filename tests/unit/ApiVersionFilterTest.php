<?php

use App\Filters\ApiVersionFilter;
use CodeIgniter\Test\CIUnitTestCase;

final class ApiVersionFilterTest extends CIUnitTestCase
{
    private bool $hadEnv = false;
    private $oldEnv = false;
    private bool $hadServer = false;
    private $oldServer = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hadEnv = array_key_exists('LUNAREA_ANDROID_API_ENABLED', $_ENV);
        $this->oldEnv = $_ENV['LUNAREA_ANDROID_API_ENABLED'] ?? false;
        $this->hadServer = array_key_exists('LUNAREA_ANDROID_API_ENABLED', $_SERVER);
        $this->oldServer = $_SERVER['LUNAREA_ANDROID_API_ENABLED'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->hadEnv) $_ENV['LUNAREA_ANDROID_API_ENABLED'] = $this->oldEnv;
        else unset($_ENV['LUNAREA_ANDROID_API_ENABLED']);
        if ($this->hadServer) $_SERVER['LUNAREA_ANDROID_API_ENABLED'] = $this->oldServer;
        else unset($_SERVER['LUNAREA_ANDROID_API_ENABLED']);
        parent::tearDown();
    }

    public function testDisabledApiStopsBeforeControllerAndReturnsJson503(): void
    {
        $_ENV['LUNAREA_ANDROID_API_ENABLED'] = 'false';
        $_SERVER['LUNAREA_ANDROID_API_ENABLED'] = 'false';
        $result = (new ApiVersionFilter())->before(service('request'));
        $body = json_decode($result->getBody(), true);
        $this->assertSame(503, $result->getStatusCode());
        $this->assertSame('API_NOT_ENABLED', $body['error']['code']);
    }

    public function testEnabledApiPassesThroughTheVersionGate(): void
    {
        $_ENV['LUNAREA_ANDROID_API_ENABLED'] = 'true';
        $_SERVER['LUNAREA_ANDROID_API_ENABLED'] = 'true';
        $this->assertNull((new ApiVersionFilter())->before(service('request')));
    }
}
