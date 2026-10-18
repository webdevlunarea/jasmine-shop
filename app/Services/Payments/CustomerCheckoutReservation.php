<?php

namespace App\Services\Payments;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;

/** Reserves loyalty points and a claimed voucher inside the order transaction. */
final class CustomerCheckoutReservation
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /** Caller owns an active transaction and persists the order before commit. */
    public function reserve(string $email, int $voucherClaimId, int $pointsToUse, ?string $expectedCartFingerprint = null, bool $clearCart = true): array
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $voucherClaimId < 0 || $pointsToUse < 0) {
            throw new DomainException('Penggunaan voucher atau poin tidak valid.');
        }
        if ((int)$this->db->transDepth < 1) throw new RuntimeException('Reservasi pelanggan harus berada dalam transaksi order.');
        $buyer = $this->lockedRow('pembeli', 'email_user', $email);
        if (!$buyer) throw new DomainException('Data akun pelanggan tidak tersedia.');
        $cart = json_decode((string)($buyer['keranjang'] ?? '[]'), true);
        if (!is_array($cart)) throw new DomainException('Data keranjang pelanggan perlu diperiksa.');
        if ($expectedCartFingerprint !== null
            && (!preg_match('/^[a-f0-9]{64}$/D', $expectedCartFingerprint)
                || !hash_equals($expectedCartFingerprint, CheckoutIdempotencyLedger::fingerprint($cart)))) {
            throw new DomainException('Keranjang berubah. Perbarui ringkasan checkout sebelum membayar.');
        }
        $points = json_decode((string)($buyer['poin'] ?? '[]'), true);
        if (!is_array($points)) throw new DomainException('Data poin pelanggan perlu diperiksa.');
        $updatedPoints = self::consumePoints($points, $pointsToUse);
        if ($pointsToUse > 0 || ($expectedCartFingerprint !== null && $clearCart)) {
            $changes = [];
            if ($pointsToUse > 0) $changes['poin'] = json_encode($updatedPoints);
            if ($expectedCartFingerprint !== null && $clearCart) $changes['keranjang'] = '[]';
            $ok = $this->db->table('pembeli')->where('email_user', $email)->update($changes);
            if (!$ok) throw new RuntimeException('Poin pelanggan tidak dapat dicadangkan.');
        }

        $voucher = null;
        if ($voucherClaimId > 0) {
            if (!$this->db->tableExists('voucher_claimed') || !$this->db->tableExists('voucher')) {
                throw new RuntimeException('Penyimpanan voucher belum tersedia.');
            }
            $query = $this->db->table('voucher_claimed vc')
                ->select('vc.id,vc.id_voucher,vc.email_user,vc.active,vc.kadaluarsa,v.active AS voucher_active,v.jadwal')
                ->join('voucher v', 'v.id = vc.id_voucher')
                ->where(['vc.id' => $voucherClaimId, 'vc.email_user' => $email]);
            $sql = $query->getCompiledSelect();
            $voucher = $this->db->query($sql . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$voucher || (string)$voucher['active'] !== '1' || (string)$voucher['voucher_active'] !== '1' || !$this->voucherCurrent($voucher)) {
                throw new DomainException('Voucher tidak tersedia atau sudah kedaluwarsa.');
            }
            $ok = $this->db->table('voucher_claimed')->where([
                'id' => $voucherClaimId, 'email_user' => $email, 'active' => 1,
            ])->update(['active' => 0]);
            if (!$ok || $this->db->affectedRows() !== 1) throw new DomainException('Voucher sudah digunakan oleh pesanan lain.');
        }

        return [
            'points_used' => $pointsToUse,
            'voucher_claim_id' => $voucher ? (int)$voucher['id'] : 0,
            'voucher_id' => $voucher ? (int)$voucher['id_voucher'] : 0,
            'points_after_reservation' => $updatedPoints,
        ];
    }

    /** Consume earliest active batches in their existing order, matching web checkout semantics. */
    public static function consumePoints(array $batches, int $amount): array
    {
        if ($amount < 0) throw new DomainException('Penggunaan poin tidak valid.');
        $today = new \DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));
        $available = 0;
        foreach ($batches as $batch) {
            if (!is_array($batch) || empty($batch['active'])) continue;
            $expiry = trim((string)($batch['kadaluarsa'] ?? ''));
            if ($expiry === '' || strtotime($expiry) === false || $today->getTimestamp() <= strtotime($expiry)) {
                $available += max(0, (int)($batch['nominal'] ?? 0));
            }
        }
        if ($amount > $available) throw new DomainException('Poin yang tersedia berubah. Perbarui checkout.');
        $remaining = $amount;
        foreach ($batches as &$batch) {
            if (!is_array($batch) || empty($batch['active']) || $remaining <= 0) continue;
            $expiry = trim((string)($batch['kadaluarsa'] ?? ''));
            if ($expiry !== '' && strtotime($expiry) !== false && $today->getTimestamp() > strtotime($expiry)) continue;
            $nominal = max(0, (int)($batch['nominal'] ?? 0));
            $used = min($nominal, $remaining);
            $batch['nominal'] = $nominal - $used;
            $remaining -= $used;
            if ($batch['nominal'] === 0) $batch['active'] = false;
        }
        unset($batch);
        return $batches;
    }

    private function lockedRow(string $table, string $key, string $value): ?array
    {
        if (!$this->db->tableExists($table)) throw new RuntimeException('Data checkout pelanggan belum tersedia.');
        $sql = $this->db->table($table)->where($key, $value)->getCompiledSelect();
        return $this->db->query($sql . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
    }

    private function voucherCurrent(array $voucher): bool
    {
        $today = new \DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));
        if (!empty($voucher['kadaluarsa'])) {
            $expiry = strtotime((string)$voucher['kadaluarsa']);
            if ($expiry === false || $today->getTimestamp() > $expiry) return false;
        }
        $schedule = explode('@', (string)($voucher['jadwal'] ?? ''));
        if (count($schedule) < 2 || !$schedule[0] || !$schedule[1]) return true;
        $start = strtotime($schedule[0]); $end = strtotime($schedule[1]);
        return $start !== false && $end !== false && $today->getTimestamp() >= $start && $today->getTimestamp() <= $end;
    }
}
