<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $allowedFields = [
        'email',
        'sandi',
        'role',
        'otp',
        'active',
        'waktu_otp',
        'google_id',
        'auth_provider'
    ];

    public function getUser($email = false)
    {
        return $this->where(['email' => $email])->first();
    }

    public function getUserByUsername(string $value)
    {
        $username = \App\Services\Auth\UsernamePolicy::normalize($value);
        if ($username === null || !$this->db->tableExists('customer_usernames')) return null;
        $mapping = $this->db->table('customer_usernames')->where('username', $username)->get()->getRowArray();
        if (!$mapping) return null;
        $user = $this->getUser($mapping['email_user']);
        // Admins retain email login; customer aliases never change the canonical account email.
        return $user && (string)$user['role'] === '0' ? $user : null;
    }
}
