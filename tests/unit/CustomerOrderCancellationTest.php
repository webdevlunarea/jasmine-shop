<?php

use App\Controllers\Pages;
use PHPUnit\Framework\TestCase;

final class CustomerOrderCancellationTest extends TestCase
{
    private bool $hadProductionKey;
    private bool $hadServerProductionKey;
    private bool $hadEnvProductionKey;
    private $oldProductionKey;
    private $oldServerProductionKey;
    private $oldEnvProductionKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hadProductionKey = array_key_exists('MIDTRANS_PRODUCTION_KEY', $_ENV);
        $this->hadServerProductionKey = array_key_exists('MIDTRANS_PRODUCTION_KEY', $_SERVER);
        $this->oldProductionKey = $_ENV['MIDTRANS_PRODUCTION_KEY'] ?? null;
        $this->oldServerProductionKey = $_SERVER['MIDTRANS_PRODUCTION_KEY'] ?? null;
        $envKey = getenv('MIDTRANS_PRODUCTION_KEY');
        $this->hadEnvProductionKey = $envKey !== false;
        $this->oldEnvProductionKey = $envKey;
        $_ENV['MIDTRANS_PRODUCTION_KEY'] = $_SERVER['MIDTRANS_PRODUCTION_KEY'] = 'unit-test-payment-key';
        putenv('MIDTRANS_PRODUCTION_KEY=unit-test-payment-key');
    }

    protected function tearDown(): void
    {
        if ($this->hadProductionKey) $_ENV['MIDTRANS_PRODUCTION_KEY'] = $this->oldProductionKey;
        else unset($_ENV['MIDTRANS_PRODUCTION_KEY']);
        if ($this->hadServerProductionKey) $_SERVER['MIDTRANS_PRODUCTION_KEY'] = $this->oldServerProductionKey;
        else unset($_SERVER['MIDTRANS_PRODUCTION_KEY']);
        putenv($this->hadEnvProductionKey ? 'MIDTRANS_PRODUCTION_KEY=' . $this->oldEnvProductionKey : 'MIDTRANS_PRODUCTION_KEY');
        parent::tearDown();
    }

    public function testOnlyUnpaidOrdersCanBeCancelled(): void
    {
        $controller = $this->controller();
        $result = $controller->cancelFixture(['status' => 'Proses', 'data_mid' => '{}']);
        $this->assertFalse($result['success']);
        $this->assertSame('ORDER_NOT_CANCELLABLE', $result['code']);
        $this->assertSame(0, $controller->applyCount);
    }

    public function testManualTransferWithProofRequiresHumanReview(): void
    {
        $controller = $this->controller();
        $result = $controller->cancelFixture([
            'id_midtrans' => 'order-1', 'status' => 'Menunggu Pembayaran Rekening',
            'data_mid' => json_encode(['payment_type' => 'rekening']), 'bukti_bayar' => 'proof',
        ]);
        $this->assertFalse($result['success']);
        $this->assertSame('PAYMENT_REVIEW_PENDING', $result['code']);
        $this->assertSame(0, $controller->applyCount);
    }

    public function testManualUnpaidTransferUsesSharedPaymentEffectsAndConfirmsSavedStatus(): void
    {
        $controller = $this->controller();
        $result = $controller->cancelFixture([
            'id_midtrans' => 'order-1', 'status' => 'Menunggu Pembayaran Rekening',
            'data_mid' => json_encode(['payment_type' => 'rekening']), 'bukti_bayar' => null,
        ]);
        $this->assertTrue($result['success']);
        $this->assertSame('CANCELLED', $result['code']);
        $this->assertSame('Dibatalkan', $result['status']);
        $this->assertSame(1, $controller->applyCount);
        $this->assertSame('Dibatalkan', $controller->pemesananModel->row['status']);
    }

    public function testMidtransCancellationRequiresPendingStatusAndSecondProviderRead(): void
    {
        $controller = $this->controller();
        $controller->providerStatus = 'pending';
        $order = [
            'id_midtrans' => 'order-2', 'email_cus' => 'buyer@example.test', 'status' => 'Menunggu Pembayaran',
            'data_mid' => json_encode(['payment_type' => 'bank_transfer', 'gross_amount' => '1000']),
        ];
        $result = $controller->cancelFixture($order);
        $this->assertTrue($result['success']);
        $this->assertSame('Dibatalkan', $result['status']);
        $this->assertSame(1, $controller->cancelRequests);
        $this->assertSame(2, $controller->statusRequests, 'Status must be read before and after cancellation.');
    }

    public function testMidtransPaidOrderIsReconciledButNeverCancelled(): void
    {
        $controller = $this->controller();
        $controller->providerStatus = 'settlement';
        $order = [
            'id_midtrans' => 'order-3', 'email_cus' => 'buyer@example.test', 'status' => 'Menunggu Pembayaran',
            'data_mid' => json_encode(['payment_type' => 'bank_transfer', 'gross_amount' => '1000']),
        ];
        $result = $controller->cancelFixture($order);
        $this->assertFalse($result['success']);
        $this->assertSame('ORDER_NOT_CANCELLABLE', $result['code']);
        $this->assertSame('Proses', $result['status']);
        $this->assertSame(0, $controller->cancelRequests);
    }

    public function testInvalidMidtransSignatureNeverTriggersCancellation(): void
    {
        $controller = $this->controller();
        $controller->tamperSignature = true;
        $order = [
            'id_midtrans' => 'order-4', 'email_cus' => 'buyer@example.test', 'status' => 'Menunggu Pembayaran',
            'data_mid' => json_encode(['payment_type' => 'bank_transfer', 'gross_amount' => '1000']),
        ];
        $result = $controller->cancelFixture($order);
        $this->assertFalse($result['success']);
        $this->assertSame('CANCELLATION_UNAVAILABLE', $result['code']);
        $this->assertSame(0, $controller->cancelRequests);
        $this->assertSame(0, $controller->applyCount);
        $this->assertSame('Menunggu Pembayaran', $controller->pemesananModel->row['status']);
    }

    private function controller(): object
    {
        return new class extends Pages {
            public $pemesananModel;
            public int $applyCount = 0;
            public int $statusRequests = 0;
            public int $cancelRequests = 0;
            public string $providerStatus = 'pending';
            public bool $tamperSignature = false;

            public function __construct()
            {
                $this->emailUjiCoba = [];
                $this->pemesananModel = new class {
                    public array $row = [];
                    public function getPemesanan(string $id): array { return $this->row; }
                };
            }

            public function cancelFixture(array $order): array
            {
                $this->pemesananModel->row = $order;
                return $this->performCustomerOrderCancellation($order);
            }

            protected function isSandboxOrder(array $order): bool { return false; }

            protected function applyVerifiedPaymentUpdate(array $order, array $body, bool $sandbox): array
            {
                $this->applyCount++;
                $status = \App\Services\Payments\PaymentSafety::notificationStatus($body);
                if ($status !== 'Menunggu Pembayaran') $this->pemesananModel->row['status'] = $status;
                return ['success' => true];
            }

            protected function paymentRemoteRequest(array $order, string $operation): array
            {
                if ($operation === 'cancel') {
                    $this->cancelRequests++;
                    return [];
                }
                $this->statusRequests++;
                $body = [
                    'order_id' => $order['id_midtrans'], 'status_code' => $this->providerStatus === 'settlement' ? '200' : '201',
                    'gross_amount' => '1000.00', 'transaction_status' => $this->providerStatus,
                    'fraud_status' => 'accept',
                ];
                $body['signature_key'] = hash('sha512', $body['order_id'] . $body['status_code'] . $body['gross_amount'] . 'unit-test-payment-key');
                if ($this->tamperSignature) $body['signature_key'] = str_repeat('0', 128);
                return $body;
            }

            protected function reconcilePendingOrder(array $order, bool $force = false): array
            {
                $this->statusRequests++;
                $this->pemesananModel->row['status'] = 'Dibatalkan';
                return $this->pemesananModel->row;
            }
        };
    }
}
