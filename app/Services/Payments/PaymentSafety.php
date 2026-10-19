<?php

namespace App\Services\Payments;

/** Provider environment and notification rules. No credentials or database schema changes. */
final class PaymentSafety
{
    /** Accounts used for isolated sandbox orders; keep website and native checkout routing aligned. */
    public static function testAccountEmails(): array
    {
        return [
            'galihsuks123@gmail.com',
            'lunareafurniture@gmail.com',
            'galih8.4.2001@gmail.com',
            'uuua5021@gmail.com',
        ];
    }

    public static function normalizeEmail(string $email): string { return strtolower(trim($email)); }

    public static function isTestEmail(string $email, array $allowlist): bool
    {
        return in_array(self::normalizeEmail($email), array_map([self::class, 'normalizeEmail'], $allowlist), true);
    }

    /** Keep checkout closed to customers while allowing explicitly allowlisted accounts to exercise sandbox. */
    public static function nativeCheckoutEnabledFor(string $email): bool
    {
        return filter_var(env('LUNAREA_ANDROID_CHECKOUT_ENABLED', false), FILTER_VALIDATE_BOOLEAN) === true
            || self::isTestEmail($email, self::testAccountEmails());
    }

    public static function environment(array $order, array $allowlist, string $emailField): string
    {
        $mid = $order['data_mid'] ?? [];
        if (is_string($mid)) $mid = json_decode($mid, true);
        $saved = is_array($mid) ? ($mid['_app_payment_environment'] ?? null) : null;
        if (in_array($saved, ['sandbox', 'production'], true)) return $saved;
        // Legacy records keep their historical email-based routing until explicitly reconciled.
        return self::isTestEmail((string)($order[$emailField] ?? ''), $allowlist) ? 'sandbox' : 'production';
    }

    private static function amount($value): ?string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) return null;
        $text = (string)$value;
        if (!preg_match('/^([0-9]+)(?:\.([0-9]{1,2}))?$/D', $text, $match)) return null;
        return (ltrim($match[1], '0') ?: '0') . '.' . str_pad($match[2] ?? '', 2, '0');
    }

    public static function validNotification(array $order, array $body, string $serverKey): bool
    {
        if ($serverKey === '' || $serverKey === 'DefaultValue') return false;
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'] as $field) {
            if (!isset($body[$field]) || !is_scalar($body[$field]) || (string)$body[$field] === '') return false;
        }
        if ((string)($order['id_midtrans'] ?? '') !== (string)$body['order_id']) return false;
        $mid = $order['data_mid'] ?? [];
        if (is_string($mid)) $mid = json_decode($mid, true);
        if (!is_array($mid) || ($mid['payment_type'] ?? '') === 'rekening') return false;
        $expectedAmount = self::amount($mid['gross_amount'] ?? null);
        if ($expectedAmount === null || $expectedAmount !== self::amount($body['gross_amount'])) return false;
        if (!empty($mid['transaction_id']) && !empty($body['transaction_id']) && (string)$mid['transaction_id'] !== (string)$body['transaction_id']) return false;
        if (in_array(strtolower((string)$body['transaction_status']), ['capture','settlement'], true) && (string)$body['status_code'] !== '200') return false;
        $expected = hash('sha512', (string)$body['order_id'] . (string)$body['status_code'] . (string)$body['gross_amount'] . $serverKey);
        return hash_equals($expected, (string)$body['signature_key']);
    }

    public static function notificationStatus(array $body): ?string
    {
        $raw = strtolower((string)($body['transaction_status'] ?? ''));
        if ($raw === 'capture' && ($body['fraud_status'] ?? '') !== 'accept') return null;
        if ($raw === 'settlement' && !in_array($body['fraud_status'] ?? 'accept', ['', 'accept'], true)) return null;
        return ['settlement'=>'Proses', 'capture'=>'Proses', 'pending'=>'Menunggu Pembayaran', 'expire'=>'Kadaluarsa',
            'deny'=>'Ditolak', 'failure'=>'Gagal', 'refund'=>'Refund', 'partial_refund'=>'Partial Refund', 'cancel'=>'Dibatalkan'][$raw] ?? null;
    }

    public static function canTransition(string $old, ?string $new): bool
    {
        if ($new === null || $new === $old) return false;
        if ($old === 'Menunggu Pembayaran Rekening') return in_array($new, ['Kadaluarsa','Dibatalkan'], true);
        if (in_array($old, ['Menunggu Pembayaran', 'No Status'], true)) {
            return in_array($new, ['Proses','Kadaluarsa','Ditolak','Gagal','Dibatalkan'], true);
        }
        if (in_array($old, ['Proses','Dikirim','Selesai'], true)) return in_array($new, ['Refund','Partial Refund'], true);
        return $old === 'Partial Refund' && $new === 'Refund';
    }

    public static function needsFirstPaidEffects(array $order, ?string $new): bool
    {
        $mid = $order['data_mid'] ?? [];
        if (is_string($mid)) $mid = json_decode($mid, true);
        return $new === 'Proses' && ($order['status'] ?? '') === 'Proses'
            && is_array($mid) && ($mid['_app_payment_effects_applied'] ?? null) === false;
    }

    /** The caller must begin a transaction first. No schema-specific JSON expressions. */
    public static function lockedOrder($db, string $orderId): ?array
    {
        if (!in_array($db->DBDriver, ['SQLite3','MySQLi','Postgre'], true)) throw new \RuntimeException('Unsupported payment locking driver');
        $sql = $db->table('pemesanan')->where('id_midtrans', $orderId)->getCompiledSelect();
        if ($db->DBDriver !== 'SQLite3') $sql .= ' FOR UPDATE';
        return $db->query($sql)->getRowArray();
    }

    /** Call inside the same transaction as the business effects. Concurrent callbacks serialize here. */
    public static function claim($db, string $orderId, string $old, string $new, array $mid): bool
    {
        $ok = $db->table('pemesanan')->where('id_midtrans', $orderId)->where('status', $old)
            ->update(['status'=>$new, 'data_mid'=>json_encode($mid)]);
        if (!$ok) throw new \RuntimeException('Payment status update failed');
        return $db->affectedRows() === 1;
    }
}
