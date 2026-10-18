<?php

namespace App\Services\Api;

/** Builds a customer tracking snapshot without confusing payment state with shipment state. */
final class CustomerTrackingSnapshot
{
    public static function make(array $order, ?array $shipment): array
    {
        $trackingNumber = self::nullableReference($shipment['tracking_number'] ?? null)
            ?? self::nullableReference($order['resi'] ?? null);
        $courier = trim((string)($shipment['courier_name'] ?? ''));
        if ($courier === '' || self::isPlaceholder($courier)) $courier = trim((string)($order['kurir'] ?? ''));
        $status = trim((string)($shipment['shipping_status'] ?? ''));

        return [
            'order_id' => (string)($order['id_midtrans'] ?? ''),
            'courier' => $courier !== '' && !self::isPlaceholder($courier) ? $courier : null,
            'tracking_number' => $trackingNumber,
            'status' => $status !== '' && !self::isPlaceholder($status) ? $status : null,
            'order_status' => (string)($order['status'] ?? ''),
            'updated_at' => $shipment['updated_at'] ?? null,
        ];
    }

    private static function nullableReference($value): ?string
    {
        $value = trim((string)$value);
        return $value === '' || self::isPlaceholder($value) ? null : $value;
    }

    private static function isPlaceholder(string $value): bool
    {
        $value = mb_strtolower(trim($value));
        return $value === '' || $value === '-' || $value === 'kosong'
            || str_starts_with($value, 'menunggu pengiriman')
            || in_array($value, ['belum tersedia', 'belum ada', 'belum diisi'], true);
    }
}
