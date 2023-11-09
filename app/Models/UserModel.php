<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $allowedFields = [
        'email',
        'sandi',
        'role'
    ];

    public function getUser($email = false)
    {
        return $this->where(['email' => $email])->first();
    }
}
