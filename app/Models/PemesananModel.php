<?php

namespace App\Models;

use CodeIgniter\Model;

class PemesananModel extends Model
{
    protected $table = 'pemesanan';
    protected $allowedFields = [
        'nama_cus',
        'email_cus',
        'hp_cus',
        'nama_pen',
        'hp_pen',
        'alamat_pen',
        'resi',
        'id_midtrans',
        'items',
        'status',
        'kurir',
        'data_mid',
    ];

    public function getPemesanan($id_midtrans = false)
    {
        if (!$id_midtrans) {
            return $this->orderBy('id', 'desc')->findAll();
        }
        return $this->where(['id_midtrans' => $id_midtrans])->first();
    }
    public function getPemesananCus($emailCus)
    {
        return $this->where(['email_cus' => $emailCus])->orderBy('id', 'desc')->findAll();
    }
}
