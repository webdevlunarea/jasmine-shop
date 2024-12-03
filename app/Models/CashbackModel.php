<?php

namespace App\Models;

use CodeIgniter\Model;

class CashbackModel extends Model
{
    protected $table = 'cashback';
    protected $allowedFields = [
        'id',
        'nama',
        'satuan',
        'nominal',
        'berakhir',
        'list_email',
        'jenis',
        'active'
    ];

    public function getVoucher($id = false)
    {
        if ($id == false) {
            return $this->where(['active' => '1'])->findAll();
        }
        return $this->where(['active' => '1', 'id' => $id])->first();
    }
}
