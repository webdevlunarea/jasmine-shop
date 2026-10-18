<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Compare-and-swap persistence prevents concurrent devices from losing wishlist changes. */
final class CustomerWishlistStore
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function saveIfUnchanged(string $email, ?string $expectedJson, array $items): bool
    {
        $encoded = json_encode(array_values($items), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $predicate = $expectedJson === null ? 'wishlist IS NULL' : match ($this->db->DBDriver) {
            'MySQLi' => 'BINARY wishlist = BINARY ?',
            'SQLite3' => 'wishlist = ? COLLATE BINARY',
            default => 'wishlist = ?',
        };
        $matchValues = $expectedJson === null ? [] : [$expectedJson];
        if ($encoded === $expectedJson) {
            return $this->db->query('SELECT 1 FROM pembeli WHERE email_user = ? AND ' . $predicate . ' LIMIT 1', array_merge([$email], $matchValues))->getRowArray() !== null;
        }
        $updated = $this->db->query('UPDATE pembeli SET wishlist = ? WHERE email_user = ? AND ' . $predicate, array_merge([$encoded, $email], $matchValues));
        if (!$updated) throw new RuntimeException('Wishlist persistence failed.');
        return $this->db->affectedRows() === 1;
    }
}
