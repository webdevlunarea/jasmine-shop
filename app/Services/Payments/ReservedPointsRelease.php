<?php

namespace App\Services\Payments;

final class ReservedPointsRelease
{
    public static function restore(array $points, int $amount): array
    {
        $remaining = max(0, $amount);
        foreach ($points as &$point) {
            if ($remaining === 0) break;
            $nominal = max(0, (int)($point['nominal'] ?? 0));
            if (empty($point['active'])) {
                // Fully reserved buckets retain nominal; the final exhausted bucket stores zero.
                $credit = $nominal > 0 ? min($nominal, $remaining) : $remaining;
                $point['nominal'] = $credit;
                $point['active'] = true;
                $remaining -= $credit;
            } else {
                $point['nominal'] = $nominal + $remaining;
                $remaining = 0;
            }
        }
        unset($point);
        if ($remaining > 0) throw new \RuntimeException('Reserved points need review');
        return $points;
    }

    public static function release($db, array $order): void
    {
        $sql = $db->table('pembeli')->where('email_user', $order['email_cus'])->getCompiledSelect();
        $buyer = $db->query($sql . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
        $points = $buyer ? json_decode($buyer['poin'] ?? '', true) : null;
        if (!is_array($points)) throw new \RuntimeException('Reserved points snapshot missing');
        $restored = self::restore($points, (int)$order['pakai_poin']);
        $db->table('pembeli')->where('email_user', $order['email_cus'])->update(['poin'=>json_encode($restored)]);
    }
}
