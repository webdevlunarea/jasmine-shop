<?php

namespace App\Services\Payments;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;

/** Serializes authenticated checkouts for one buyer across website and native clients. */
final class CustomerPendingOrderGuard
{
    public static function lockAndAssertNoPending(BaseConnection $db, string $email): void
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new DomainException('Akun pelanggan tidak valid.');
        if (!$db->tableExists('pembeli') || !$db->tableExists('pemesanan')) {
            throw new RuntimeException('Penyimpanan checkout pelanggan belum tersedia.');
        }

        $buyerSql = $db->table('pembeli')->where('email_user', $email)->getCompiledSelect();
        $buyer = $db->query($buyerSql . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
        if (!$buyer) throw new DomainException('Data akun pelanggan tidak tersedia.');

        $pendingSql = $db->table('pemesanan')->select('id_midtrans')
            ->where('email_cus', $email)
            ->whereIn('status', ['Menunggu Pembayaran', 'Menunggu Pembayaran Rekening'])
            ->limit(1)->getCompiledSelect();
        // The buyer row is the cross-request mutex; this read need not lock
        // every matching order row (which could otherwise widen lock scope).
        $pending = $db->query($pendingSql)->getRowArray();
        if ($pending) {
            throw new DomainException('Masih ada pesanan menunggu pembayaran. Selesaikan atau tunggu pesanan tersebut diperbarui.');
        }
    }
}
