<?php

namespace App\Services\Payments;

use RuntimeException;

/** Read-only provider reconciliation for an authenticated customer's existing order. */
final class CustomerOrderPaymentStatus
{
    private $statusRequest;
    private ?string $serverKey;

    public function __construct(?callable $statusRequest = null, ?string $serverKey = null)
    {
        $this->statusRequest = $statusRequest;
        $this->serverKey = $serverKey;
    }

    public function get(array $order): array
    {
        $payment = json_decode((string)($order['data_mid'] ?? ''), true) ?: [];
        $savedStatus = (string)($order['status'] ?? '');
        $savedType = strtolower((string)($payment['payment_type'] ?? ''));
        $environment = (string)($payment['_app_payment_environment'] ?? '');

        // Manual transfers and terminal orders have no Midtrans status to poll.
        if ($savedType === 'rekening' || !in_array($savedStatus, ['Menunggu Pembayaran'], true)) {
            return $this->snapshot($order, $payment, 'database');
        }
        // Do not guess the gateway account for historical records without an environment marker.
        if (!in_array($environment, ['sandbox', 'production'], true)) {
            return $this->snapshot($order, $payment, 'database');
        }

        $key = trim((string)($this->serverKey ?? env($environment === 'sandbox' ? 'MIDTRANS_SANDBOX_KEY' : 'MIDTRANS_PRODUCTION_KEY', '')));
        if ($key === '' || $key === 'DefaultValue') throw new RuntimeException('Payment provider is not configured');
        $remote = $this->statusRequest
            ? ($this->statusRequest)($order, $environment, $key)
            : (new MidtransOrderClient($environment === 'sandbox', $key))->request((string)$order['id_midtrans'], 'status');
        if (!is_array($remote)) throw new RuntimeException('Payment provider response invalid');
        if (!PaymentSafety::validNotification($order, $remote, $key)) throw new RuntimeException('Payment status verification failed');

        $status = PaymentSafety::notificationStatus($remote);
        if ($status === null) throw new RuntimeException('Payment status is not recognized');
        $payment = array_merge($payment, array_intersect_key($remote, array_flip([
            'transaction_status', 'transaction_time', 'transaction_id', 'payment_type', 'gross_amount',
            'expiry_time', 'va_numbers', 'permata_va_number', 'payment_code', 'bill_key', 'biller_code',
        ])));
        return $this->snapshot($order, $payment, 'gateway', $status);
    }

    private function snapshot(array $order, array $payment, string $source, ?string $status = null): array
    {
        $vas = [];
        foreach (($payment['va_numbers'] ?? []) as $entry) {
            if (is_array($entry) && !empty($entry['va_number'])) {
                $vas[] = ['bank' => (string)($entry['bank'] ?? ''), 'number' => (string)$entry['va_number']];
            }
        }
        if (!empty($payment['permata_va_number'])) $vas[] = ['bank' => 'permata', 'number' => (string)$payment['permata_va_number']];
        return [
            'order_id' => (string)($order['id_midtrans'] ?? ''),
            'status' => $status ?? (string)($order['status'] ?? ''),
            'payment_status' => (string)($payment['transaction_status'] ?? ''),
            'payment_type' => (string)($payment['payment_type'] ?? ''),
            'amount' => max(0, (int)round((float)($payment['gross_amount'] ?? 0))),
            'expiry_time' => $payment['expiry_time'] ?? null,
            'va_numbers' => $vas,
            'payment_code' => $payment['payment_code'] ?? null,
            'bill_key' => $payment['bill_key'] ?? null,
            'biller_code' => $payment['biller_code'] ?? null,
            'source' => $source,
        ];
    }
}
