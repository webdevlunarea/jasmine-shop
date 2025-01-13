<?php

namespace App\Models;

use CodeIgniter\Model;

class GambarUserModel extends Model
{
    protected $table = 'gambar_user';
    protected $allowedFields = [
        'email_user',
        'gambar',
    ];

    public function getGambar($email)
    {
        return $this->where(['email_user' => $email])->first();
    }
}
