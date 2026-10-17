<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

class PasswordSessionVersions
{
    private BaseConnection $db;
    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    public function current(string $email): string
    {
        if (!$this->db->tableExists('customer_password_sessions')) return '';
        $row = $this->db->table('customer_password_sessions')->where('email_user', $email)->get()->getRowArray();
        return (string)($row['version'] ?? '');
    }

    public function matches(string $email, $version): bool
    {
        return ($version === null || is_string($version)) && hash_equals($this->current($email), $version ?? '');
    }
}
