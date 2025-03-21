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
        'all_user',
        'keterangan',
        'auto_claimed',
        'poster',
        'private',
        'kuota',
        'poster_email',
        'isi_email',
        'jadwal',
        'syarat_ketentuan',
    ];

    public function getVoucher($id = false)
    {
        if ($id == false) {
            return $this->where(['active' => '1'])->orderBy('id', 'desc')->findAll();
        }
        return $this->where(['active' => '1', 'id' => $id])->first();
    }
}
