<?php

namespace App\Services\Payments;

use DomainException;

/** Creates a validated Midtrans Core API payload from a server-calculated checkout preview. */
final class MidtransCheckoutRequest
{
    private const BANKS = ['bca', 'bri', 'bni', 'cimb'];
    private const EXPIRING_BANKS = ['bca', 'bri', 'bni', 'cimb', 'permata', 'mandiri'];

    /** Methods with a verified Core API payload; excludes legacy static VA and unverified DANA. */
    public static function supportedMethods(): array
    {
        return ['bca', 'bri', 'bni', 'cimb', 'permata', 'mandiri', 'qris', 'gopay', 'shopeepay', 'card'];
    }

    public function build(array $checkout, string $orderId, string $email, string $name, string $phone, ?string $cardToken = null): array
    {
        $orderId = trim($orderId);
        $email = strtolower(trim($email));
        $method = strtolower(trim((string)($checkout['payment_method'] ?? '')));
        $items = $checkout['midtrans_item_details'] ?? null;
        $amount = $checkout['total'] ?? null;
        if (!preg_match('/^[A-Za-z0-9_-]{1,50}$/D', $orderId)
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || trim($name) === '' || trim($phone) === ''
            || !is_array($items) || !$items || count($items) > 100
            || !is_numeric($amount) || (float)$amount !== (float)(int)$amount || (int)$amount < 1) {
            throw new DomainException('Data pembayaran tidak valid.');
        }
        $items = $this->normalizeItems($items);
        $itemsTotal = (new CheckoutAmountCalculator())->totalFromItems($items);
        if ($itemsTotal !== (int)$amount) throw new DomainException('Rincian pembayaran tidak sama dengan total checkout.');

        $payload = [
            'transaction_details' => ['order_id' => $orderId, 'gross_amount' => (int)$amount],
            'customer_details' => [
                'email' => $email,
                'phone' => mb_substr(trim($phone), 0, 20),
                'first_name' => mb_substr(trim($name), 0, 255),
            ],
            'item_details' => $items,
        ];

        if (in_array($method, self::EXPIRING_BANKS, true)) {
            $payload['custom_expiry'] = ['expiry_duration' => 60, 'unit' => 'minute'];
            if (in_array($method, self::BANKS, true)) {
                $payload['payment_type'] = 'bank_transfer';
                $payload['bank_transfer'] = ['bank' => $method];
            } elseif ($method === 'permata') {
                $payload['payment_type'] = 'permata';
            } else {
                $payload['payment_type'] = 'echannel';
                $payload['echannel'] = ['bill_info1' => 'Payment:', 'bill_info2' => 'Online purchase'];
            }
        } elseif ($method === 'qris') {
            $payload['payment_type'] = 'qris';
            $payload['qris'] = ['acquirer' => 'gopay'];
            $payload['custom_expiry'] = ['expiry_duration' => 15, 'unit' => 'minute'];
        } elseif ($method === 'gopay') {
            $payload['payment_type'] = 'gopay';
            $payload['gopay'] = ['enable_callback' => true, 'callback_url' => site_url('order/' . rawurlencode($orderId))];
            $payload['custom_expiry'] = ['expiry_duration' => 15, 'unit' => 'minute'];
        } elseif ($method === 'shopeepay') {
            $payload['payment_type'] = 'shopeepay';
            $payload['shopeepay'] = ['callback_url' => site_url('order/' . rawurlencode($orderId))];
            $payload['custom_expiry'] = ['expiry_duration' => 15, 'unit' => 'minute'];
        } elseif ($method === 'dana') {
            // DANA is shown in checkout as a supported fee option, but the current
            // web checkout has no verified Core API payment payload for it.
            throw new DomainException('Metode pembayaran ini belum tersedia di aplikasi.');
        } elseif ($method === 'card') {
            if (!$cardToken || strlen($cardToken) > 4096) throw new DomainException('Token kartu belum tersedia.');
            $payload['payment_type'] = 'credit_card';
            $payload['credit_card'] = ['token_id' => $cardToken];
        } else {
            // `rekening` in the legacy web handler contains a static VA snapshot;
            // never carry that placeholder into a native checkout transaction.
            throw new DomainException('Metode pembayaran ini belum tersedia di aplikasi.');
        }

        return $payload;
    }

    private function normalizeItems(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['id'], $item['name'], $item['price'], $item['quantity'])
                || !is_scalar($item['id']) || !is_scalar($item['name']) || !is_numeric($item['price'])
                || !is_numeric($item['quantity'])) {
                throw new DomainException('Rincian pembayaran tidak valid.');
            }
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            if ($quantity < 1 || $quantity > 99 || $price !== (float)(int)$price
                || trim((string)$item['id']) === '' || trim((string)$item['name']) === '') {
                throw new DomainException('Rincian pembayaran tidak valid.');
            }
            $normalized[] = [
                'id' => mb_substr(trim((string)$item['id']), 0, 64),
                'name' => mb_substr(trim((string)$item['name']), 0, 50),
                'price' => (int)$price,
                'quantity' => $quantity,
            ];
        }
        return $normalized;
    }
}
