<?php

namespace Tests\Unit;

use App\Services\Payments\CustomerOrderPaymentStatus;
use PHPUnit\Framework\TestCase;

final class CustomerOrderPaymentStatusTest extends TestCase
{
    public function testLegacyAndManualOrdersUseSavedSnapshotWithoutGatewayRequest(): void
    {
        $called = false;
        $service = new CustomerOrderPaymentStatus(static function () use (&$called) {
            $called = true;
            return [];
        });
        $legacy = $service->get($this->order(['payment_type' => 'bank_transfer', 'gross_amount' => '5000.00']));
        $manual = $service->get($this->order(['payment_type' => 'rekening', 'gross_amount' => '7000.00']));

        self::assertFalse($called);
        self::assertSame('database', $legacy['source']);
        self::assertSame('Menunggu Pembayaran', $legacy['status']);
        self::assertSame(7000, $manual['amount']);
    }

    public function testGatewayStatusMustMatchOrderAmountAndSignature(): void
    {
        $key = 'unit-test-server-key';
        $body = [
            'order_id' => 'L00000123', 'status_code' => '200', 'gross_amount' => '150000.00',
            'transaction_status' => 'settlement', 'fraud_status' => 'accept', 'transaction_id' => 'txn-1',
            'payment_type' => 'bank_transfer', 'va_numbers' => [['bank' => 'bca', 'va_number' => '1234567890']],
        ];
        $body['signature_key'] = hash('sha512', $body['order_id'] . $body['status_code'] . $body['gross_amount'] . $key);
        $order = $this->order(['_app_payment_environment' => 'sandbox', 'gross_amount' => '150000.00']);
        $service = new CustomerOrderPaymentStatus(static fn() => $body, $key);
        $result = $service->get($order);

        self::assertSame('Proses', $result['status']);
        self::assertSame('settlement', $result['payment_status']);
        self::assertSame('gateway', $result['source']);
        self::assertSame([['bank' => 'bca', 'number' => '1234567890']], $result['va_numbers']);
    }

    public function testInvalidGatewaySignatureIsRejected(): void
    {
        $body = [
            'order_id' => 'L00000123', 'status_code' => '200', 'gross_amount' => '150000.00',
            'transaction_status' => 'settlement', 'signature_key' => str_repeat('0', 128),
        ];
        $service = new CustomerOrderPaymentStatus(static fn() => $body, 'unit-test-server-key');
        $this->expectException(\RuntimeException::class);
        $service->get($this->order(['_app_payment_environment' => 'production', 'gross_amount' => '150000.00']));
    }

    private function order(array $payment): array
    {
        $payment += ['payment_type' => 'bank_transfer', 'gross_amount' => '150000.00'];
        return [
            'id_midtrans' => 'L00000123', 'email_cus' => 'customer@example.test', 'status' => 'Menunggu Pembayaran',
            'data_mid' => json_encode($payment),
        ];
    }
}
