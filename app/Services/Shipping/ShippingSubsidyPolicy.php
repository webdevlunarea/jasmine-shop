<?php

namespace App\Services\Shipping;

use DateTimeImmutable;
use DateTimeZone;

class ShippingSubsidyPolicy
{
    public static function fromInput(array $input): array
    {
        $percentage = trim((string)($input['percentage'] ?? '0'));
        if (!preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $percentage)) throw new \DomainException('Persentase harus angka 0–100, maksimal dua desimal.');
        $parts = explode('.', $percentage);
        $bps = (int)$parts[0] * 100 + (int)str_pad($parts[1] ?? '', 2, '0');
        if ($bps > 10000) throw new \DomainException('Persentase subsidi maksimal 100%.');
        $cap = trim((string)($input['max_amount'] ?? '0'));
        if (!ctype_digit($cap) || strlen($cap) > 10 || (int)$cap > 2000000000) throw new \DomainException('Batas subsidi harus nominal rupiah bulat antara 0 dan 2.000.000.000.');
        $couriers = $input['couriers'] ?? [];
        if (!is_array($couriers)) throw new \DomainException('Pilihan ekspedisi tidak valid.');
        $couriers = array_values(array_unique(array_map(static fn($code) => strtolower(trim((string)$code)), $couriers)));
        foreach ($couriers as $code) {
            if (!preg_match('/^[a-z0-9_]{1,30}$/', $code) || $code === 'internal') throw new \DomainException('Kode ekspedisi subsidi tidak valid.');
        }
        sort($couriers);
        $policy = [
            'enabled' => !empty($input['enabled']), 'percentage_bps' => $bps, 'max_amount' => (int)$cap,
            'couriers' => $couriers, 'starts_at' => self::dateValue($input['starts_at'] ?? ''),
            'ends_at' => self::dateValue($input['ends_at'] ?? ''), 'timezone' => 'Asia/Jakarta',
        ];
        if ($policy['starts_at'] && $policy['ends_at'] && $policy['ends_at'] <= $policy['starts_at']) throw new \DomainException('Waktu selesai harus setelah waktu mulai.');
        if ($policy['enabled'] && $bps === 0) throw new \DomainException('Isi persentase lebih dari 0 sebelum mengaktifkan subsidi.');
        return $policy;
    }

    private static function dateValue($value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') return null;
        $value = str_replace('T', ' ', $value);
        if (strlen($value) === 16) $value .= ':00';
        $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, new DateTimeZone('Asia/Jakarta'));
        if (!$date || $date->format('Y-m-d H:i:s') !== $value) throw new \DomainException('Tanggal periode subsidi tidak valid.');
        return $value;
    }

    public static function normalize(array $stored): array
    {
        $bps = $stored['percentage_bps'] ?? 0;
        if (!is_int($bps) || $bps < 0 || $bps > 10000) throw new \DomainException('Konfigurasi persentase subsidi tidak valid.');
        return self::fromInput([
            'enabled' => $stored['enabled'] ?? false,
            'percentage' => intdiv($bps, 100) . '.' . str_pad((string)($bps % 100), 2, '0', STR_PAD_LEFT),
            'max_amount' => $stored['max_amount'] ?? 0, 'couriers' => $stored['couriers'] ?? [],
            'starts_at' => $stored['starts_at'] ?? '', 'ends_at' => $stored['ends_at'] ?? '',
        ]);
    }

    public static function calculate(int $original, array $policy, string $courier, bool $internal = false, ?DateTimeImmutable $now = null): array
    {
        if ($original < 0 || $original > 2000000000) throw new \DomainException('Tarif pengiriman di luar batas yang didukung.');
        $policy = self::normalize($policy);
        $time = ($now ?? new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s');
        $eligible = !$internal && $original > 0 && $policy['enabled']
            && (!$policy['couriers'] || in_array(strtolower($courier), $policy['couriers'], true))
            && (!$policy['starts_at'] || $time >= $policy['starts_at'])
            && (!$policy['ends_at'] || $time < $policy['ends_at']);
        $discount = $eligible ? intdiv($original * $policy['percentage_bps'], 10000) : 0;
        if ($policy['max_amount'] > 0) $discount = min($discount, $policy['max_amount']);
        return [
            'version' => 1, 'currency' => 'IDR', 'original_cost' => $original, 'subsidy_amount' => $discount,
            'payable_cost' => $original - $discount, 'policy' => $policy,
            'policy_hash' => hash('sha256', json_encode($policy)),
        ];
    }

    public static function storageReady($db): bool
    {
        return $db->tableExists('order_shipments') && $db->fieldExists('shipping_pricing', 'order_shipments');
    }

    public static function snapshot(array $quote): ?array
    {
        $raw = json_decode((string)($quote['raw_response'] ?? ''), true);
        return is_array($raw['shipping_pricing'] ?? null) ? $raw['shipping_pricing'] : null;
    }
}
