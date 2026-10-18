<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Transactional mutations for a customer's saved addresses. */
class CustomerAddressBook
{
    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * Delete an owned address and promote the next address if the default was removed.
     * Returns false when the address does not belong to the customer.
     */
    public function delete(string $email, int $id): bool
    {
        return $this->mutate($email, function (array $rows) use ($email, $id): bool {
            $target = null;
            foreach ($rows as $row) {
                if ((int) $row['id'] === $id) {
                    $target = $row;
                    break;
                }
            }
            if ($target === null) return false;

            $deleted = $this->db->table('customer_addresses')
                ->where(['email_user' => $email, 'id' => $id])->delete();
            if ($deleted !== true || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('Address delete did not affect exactly one row.');
            }

            if ((int) $target['is_default'] === 1) {
                foreach ($rows as $row) {
                    if ((int) $row['id'] === $id) continue;
                    $updated = $this->db->table('customer_addresses')
                        ->where(['email_user' => $email, 'id' => (int) $row['id']])
                        ->update(['is_default' => 1]);
                    if ($updated !== true || $this->db->affectedRows() !== 1) {
                        throw new RuntimeException('Replacement default address could not be set.');
                    }
                    break;
                }
            }
            return true;
        });
    }

    /** Returns false when the address does not belong to the customer. */
    public function setDefault(string $email, int $id): bool
    {
        return $this->mutate($email, function (array $rows) use ($email, $id): bool {
            $ownsTarget = false;
            foreach ($rows as $row) {
                if ((int) $row['id'] === $id) $ownsTarget = true;
            }
            if (!$ownsTarget) return false;

            $clear = $this->db->table('customer_addresses')->where('email_user', $email)
                ->update(['is_default' => 0]);
            if ($clear !== true) throw new RuntimeException('Existing default address could not be cleared.');

            $set = $this->db->table('customer_addresses')->where(['email_user' => $email, 'id' => $id])
                ->update(['is_default' => 1]);
            if ($set !== true || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('Selected default address could not be set.');
            }
            return true;
        });
    }

    private function mutate(string $email, callable $operation): bool
    {
        if (!$this->db->transBegin()) throw new RuntimeException('Address transaction could not start.');
        try {
            $sql = $this->db->table('customer_addresses')->where('email_user', $email)
                ->orderBy('id', 'ASC')->getCompiledSelect();
            // Serialize default/delete decisions for this owner on databases supporting row locks.
            if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre'], true)) $sql .= ' FOR UPDATE';
            $rows = $this->db->query($sql)->getResultArray();
            $result = $operation($rows);
            if ($this->db->transStatus() === false) throw new RuntimeException('Address transaction failed.');
            if (!$this->db->transCommit()) throw new RuntimeException('Address transaction commit failed.');
            return $result;
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
    }
}
