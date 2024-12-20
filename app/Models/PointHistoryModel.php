<?php

namespace App\Models;

use CodeIgniter\Model;

class PointHistoryModel extends Model
{
    protected $table = 'point_history';
    protected $allowedFields = [
        'id',
        'label',
        'nominal',
        'keterangan',
        'tanggal',
        'email_user'
    ];

    public function getHistoryCus($email = false)
    {
        return $this->where(['email_user' => $email])->findAll();
    }
}
