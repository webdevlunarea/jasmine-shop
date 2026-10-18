<?php

use App\Services\Payments\NativeCheckoutOrderService;
use CodeIgniter\Test\CIUnitTestCase;

final class NativeCheckoutOrderServiceGateTest extends CIUnitTestCase
{
    private bool $hadEnv = false;
    private $oldEnv = null;
    private bool $hadServer = false;
    private $oldServer = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hadEnv = array_key_exists('LUNAREA_ANDROID_CHECKOUT_ENABLED', $_ENV);
        $this->oldEnv = $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] ?? null;
        $this->hadServer = array_key_exists('LUNAREA_ANDROID_CHECKOUT_ENABLED', $_SERVER);
        $this->oldServer = $_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED'] ?? null;
        $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = 'false';
        $_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = 'false';
    }

    protected function tearDown(): void
    {
        if ($this->hadEnv) $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = $this->oldEnv;
        else unset($_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED']);
        if ($this->hadServer) $_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = $this->oldServer;
        else unset($_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED']);
        parent::tearDown();
    }

    public function testCheckoutFeatureFlagStopsBeforeDatabaseOrPaymentSideEffects(): void
    {
        $chargeCalls = 0;
        $service = new NativeCheckoutOrderService(null, null, static function () use (&$chargeCalls) {
            $chargeCalls++;
            throw new RuntimeException('Charge factory must not be called while disabled.');
        });

        try {
            $service->create('buyer@example.test', [], 'idempotency-key-12345');
            $this->fail('Disabled checkout must stop.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Checkout aplikasi belum diaktifkan.', $exception->getMessage());
        }

        $this->assertSame(0, $chargeCalls);
    }

    public function testInvalidCustomerEmailIsRejectedBeforeCheckoutGate(): void
    {
        $service = new NativeCheckoutOrderService();

        try {
            $service->create('not-an-email', [], 'idempotency-key-12345');
            $this->fail('Invalid account email must be rejected.');
        } catch (DomainException $exception) {
            $this->assertSame('Akun pelanggan tidak valid.', $exception->getMessage());
        }
    }
}
