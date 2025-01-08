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
        'durasi',
        'durasi_poin',
        'jenis',
        'active',
        'code',
        'keterangan',
        'auto_claimed',
        'poster',
        'private',
        'kuota'
    ];

    public function getVoucher($id = false)
    {
        if ($id == false) {
            return $this->where(['active' => '1'])->findAll();
        }
        return $this->where(['active' => '1', 'id' => $id])->first();
    }
}
