<?php

namespace App\Services\Payments;

final class PaymentExpiry
{
    public static function timestamp(array $payment): ?int
    {
        $text = $payment['expiry_time'] ?? null;
        if (!is_string($text) || trim($text) === '') return null;
        // Midtrans Core API and the local bank-transfer snapshot use Jakarta wall time.
        $zone = new \DateTimeZone('Asia/Jakarta');
        foreach (['!Y-m-d H:i:s', '!Y-m-d\TH:i:sP', '!Y-m-d\TH:i:s.uP'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $text, $zone);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) return $date->getTimestamp();
        }
        return null;
    }

    public static function isDue(array $payment, ?int $now = null): bool
    {
        $expiry = self::timestamp($payment);
        return $expiry !== null && $expiry <= ($now ?? time());
    }

    public static function label(array $payment): string
    {
        $timestamp = self::timestamp($payment);
        if ($timestamp === null) return 'Batas waktu belum tersedia';
        $date = (new \DateTimeImmutable('@' . $timestamp))->setTimezone(new \DateTimeZone('Asia/Jakarta'));
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return $date->format('d') . ' ' . $months[(int)$date->format('n')-1] . $date->format(' Y H:i:s') . ' WIB';
    }

    public static function countdown(array $payment, ?int $now = null): string
    {
        $seconds = max(0, (self::timestamp($payment) ?? ($now ?? time())) - ($now ?? time()));
        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }
}
