<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

/** Metadata-only checks: never fetch customer rows, credentials, or provider tokens. */
final class FacebookSchemaAudit
{
    public function findings(BaseConnection $db): array
    {
        $findings = [];
        $tables = [
            'facebook_identities' => ['facebook_id', 'email_user'],
            'user' => ['email', 'sandi', 'role', 'active', 'otp', 'waktu_otp', 'auth_provider'],
            'pembeli' => ['email_user', 'nama', 'nohp', 'alamat', 'wishlist', 'keranjang', 'transaksi', 'poin', 'tier', 'foto', 'tgl_lahir'],
        ];
        try {
            foreach ($tables as $table => $columns) {
                if (!$db->tableExists($table)) {
                    $findings[] = 'missing_table:' . $table;
                    continue;
                }
                foreach ($columns as $column) {
                    if (!$db->fieldExists($column, $table)) $findings[] = 'missing_column:' . $table . '.' . $column;
                }
            }
            if ($db->DBDriver === 'MySQLi') {
                if ($db->tableExists('user') && $db->fieldExists('auth_provider', 'user')) {
                    $column = $db->query('SHOW COLUMNS FROM ' . $db->protectIdentifiers($db->prefixTable('user')) . ' WHERE Field = ?', ['auth_provider'])->getRowArray();
                    $type = strtolower((string)($column['Type'] ?? ''));
                    if (strpos($type, 'enum(') === 0 && strpos($type, "'facebook'") === false) {
                        $findings[] = 'unsupported_value:user.auth_provider.facebook';
                    }
                }
                foreach (array_keys($tables) as $table) {
                    $row = $db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$db->prefixTable($table)])->getRowArray();
                    if ($row && strcasecmp((string)$row['ENGINE'], 'InnoDB') !== 0) $findings[] = 'nontransactional_table:' . $table;
                }
            }
        } catch (\Throwable $e) {
            $findings[] = 'database_metadata_unavailable';
        }
        return $findings;
    }
}
