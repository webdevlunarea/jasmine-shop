<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Compare-and-swap persistence prevents concurrent clients from overwriting a newer cart. */
final class CustomerCartStore
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** Returns false only when the stored cart no longer matches the caller's snapshot. */
    public function saveIfUnchanged(string $email, string $expectedJson, array $cart): bool
    {
        $encoded = json_encode(array_values($cart), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $match = $this->snapshotPredicate();

        // A no-op quantity update is successful only while the original snapshot is still current.
        if ($encoded === $expectedJson) {
            return $this->db->query(
                'SELECT 1 FROM pembeli WHERE email_user = ? AND ' . $match . ' LIMIT 1',
                [$email, $expectedJson]
            )->getRowArray() !== null;
        }

        $updated = $this->db->query(
            'UPDATE pembeli SET keranjang = ? WHERE email_user = ? AND ' . $match,
            [$encoded, $email, $expectedJson]
        );
        if (!$updated) throw new RuntimeException('Cart persistence failed.');
        return $this->db->affectedRows() === 1;
    }

    /** Compare JSON bytes exactly, independent of a MySQL case-insensitive column collation. */
    private function snapshotPredicate(): string
    {
        return match ($this->db->DBDriver) {
            'MySQLi' => 'BINARY keranjang = BINARY ?',
            'SQLite3' => 'keranjang = ? COLLATE BINARY',
            default => 'keranjang = ?',
        };
    }
}
