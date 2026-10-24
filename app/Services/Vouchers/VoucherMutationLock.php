<?php

namespace App\Services\Vouchers;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Serializes legacy JSON-backed voucher mutations across PHP workers. */
final class VoucherMutationLock
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function run(string $key, callable $operation)
    {
        $lockName = null;
        if (stripos($this->db->DBDriver, 'mysql') !== false) {
            $lockName = 'lunarea-voucher-' . substr(hash('sha256', $key), 0, 48);
            $row = $this->db->query('SELECT GET_LOCK(?, 5) AS acquired', [$lockName])->getRowArray();
            if ((int) ($row['acquired'] ?? 0) !== 1) {
                throw new RuntimeException('Voucher operation is busy.');
            }
        }

        try {
            if (!$this->db->transBegin()) throw new RuntimeException('Voucher transaction unavailable.');
            $result = $operation();
            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new RuntimeException('Voucher transaction failed.');
            }
            return $result;
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        } finally {
            if ($lockName !== null) $this->db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }
}
