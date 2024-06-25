<?php

namespace App\Models;

use CodeIgniter\Model;

class SubmitEmailModel extends Model
{
    protected $table = 'submit_email';
    protected $allowedFields = [
        'id',
        'email',
    ];

    public function getEmail($email = false)
    {
        if ($email == false) {
            return $this->findAll();
        }
        return $this->where(['email' => $email])->first();
    }
}
