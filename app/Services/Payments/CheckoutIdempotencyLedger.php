<?php

namespace App\Services\Payments;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;

/** Durable claim/replay guard for native checkout; does not create or mutate an order. */
final class CheckoutIdempotencyLedger
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /** Stable hash of client intent, independent of JSON object key ordering. */
    public static function fingerprint(array $request): string
    {
        $canonical = self::canonicalize($request);
        $json = json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        return hash('sha256', $json);
    }

    /**
     * Claim a caller key before any payment-provider request.
     * Return `claimed` for a new owner or `replay` with the stored state/order id.
     */
    public function claim(string $email, string $idempotencyKey, string $requestHash, string $candidateOrderId): array
    {
        $email = strtolower(trim($email));
        $idempotencyKey = trim($idempotencyKey);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($idempotencyKey) < 16 || strlen($idempotencyKey) > 200
            || !preg_match('/^[a-f0-9]{64}$/D', $requestHash)
            || !preg_match('/^[A-Za-z0-9_-]{1,50}$/D', $candidateOrderId)) {
            throw new DomainException('Kunci permintaan checkout tidak valid.');
        }
        if (!$this->db->tableExists('customer_checkout_requests')) {
            throw new RuntimeException('Penyimpanan idempotensi checkout belum tersedia.');
        }

        $keyHash = hash('sha256', $idempotencyKey);
        $now = gmdate('Y-m-d H:i:s');
        if (!$this->db->transBegin()) throw new RuntimeException('Klaim checkout tidak dapat dimulai.');
        try {
            $this->db->table('customer_checkout_requests')->insert([
                'email_user' => $email,
                'idempotency_key_hash' => $keyHash,
                'request_hash' => $requestHash,
                'order_id' => $candidateOrderId,
                'state' => 'processing',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            if ($this->db->transStatus() === false || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('Klaim checkout tidak tersimpan.');
            }
            if (!$this->db->transCommit()) throw new RuntimeException('Klaim checkout tidak berhasil dikomit.');
            return ['result' => 'claimed', 'state' => 'processing', 'order_id' => $candidateOrderId];
        } catch (\Throwable $e) {
            $this->rollbackClaimTransaction();
            // A unique-key race can mean a concurrent request won. Read its
            // durable record only after rollback, then distinguish safe replay
            // from accidental reuse of the same key for a different purchase.
            $existing = $this->db->table('customer_checkout_requests')->where([
                'email_user' => $email,
                'idempotency_key_hash' => $keyHash,
            ])->get()->getRowArray();
            if ($existing) {
                if (!hash_equals((string)$existing['request_hash'], $requestHash)) {
                    throw new DomainException('Kunci checkout sudah digunakan untuk permintaan yang berbeda.');
                }
                if (($existing['state'] ?? '') === 'processing' && $this->isStaleOrphan((string)$existing['order_id'], (string)$existing['updated_at'])) {
                    $this->db->table('customer_checkout_requests')->where('id', $existing['id'])->where('state', 'processing')
                        ->update(['state' => 'failed', 'updated_at' => gmdate('Y-m-d H:i:s')]);
                    $existing['state'] = 'failed';
                }
                return [
                    'result' => 'replay',
                    'state' => (string)$existing['state'],
                    'order_id' => (string)$existing['order_id'],
                ];
            }
            throw $e;
        }
    }

    private function rollbackClaimTransaction(): void
    {
        if ($this->db->transStatus() === false) {
            $strict = $this->db->transStrict;
            $this->db->transStrict(false)->transComplete();
            $this->db->transStrict($strict);
            return;
        }
        $this->db->transRollback();
    }

    /** A provider request is never sent until an order row exists, so a stale claim without that row is safe to retry with a fresh key. */
    private function isStaleOrphan(string $orderId, string $updatedAt): bool
    {
        $timestamp = strtotime($updatedAt . ' UTC');
        if ($timestamp === false || time() - $timestamp < 900 || !$this->db->tableExists('pemesanan')) return false;
        return !$this->db->table('pemesanan')->where('id_midtrans', $orderId)->countAllResults();
    }

    /** Mark an already-persisted and provider-accepted order as complete. */
    public function complete(string $email, string $idempotencyKey, string $orderId): bool
    {
        return $this->transition($email, $idempotencyKey, $orderId, 'completed');
    }

    /** Mark failed only when the caller has proven no gateway charge was created. */
    public function failBeforeCharge(string $email, string $idempotencyKey, string $orderId): bool
    {
        return $this->transition($email, $idempotencyKey, $orderId, 'failed');
    }

    private function transition(string $email, string $key, string $orderId, string $state): bool
    {
        if (!$this->db->tableExists('customer_checkout_requests')) return false;
        $ok = $this->db->table('customer_checkout_requests')
            ->where([
                'email_user' => strtolower(trim($email)),
                'idempotency_key_hash' => hash('sha256', trim($key)),
                'order_id' => $orderId,
                'state' => 'processing',
            ])
            ->update(['state' => $state, 'updated_at' => gmdate('Y-m-d H:i:s')]);
        return $ok && $this->db->affectedRows() === 1;
    }

    private static function canonicalize(array $value): array
    {
        if (array_is_list($value)) {
            foreach ($value as $index => $item) if (is_array($item)) $value[$index] = self::canonicalize($item);
            return $value;
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) if (is_array($item)) $value[$key] = self::canonicalize($item);
        return $value;
    }
}
