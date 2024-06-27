<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherModel extends Model
{
    protected $table = 'voucher';
    protected $allowedFields = [
        'id',
        'nama',
        'satuan',
        'nominal',
        'berakhir',
        'list_email',
        'jenis',
    ];

    public function getVoucher($id = false)
    {
        if ($id == false) {
            return $this->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
}
