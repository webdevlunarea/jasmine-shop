<?php

namespace App\Models;

use CodeIgniter\Model;

class PembeliModel extends Model
{
    protected $table = 'pembeli';
    protected $allowedFields = [
        'email_user',
        'nama',
        'nohp',
        'alamat',
        'wishlist',
        'keranjang',
        'transaksi',
        'poin',
        'tier',
        'tgl_lahir',
        'foto'
    ];

    public function getPembeli($email = false)
    {
        return $this->where(['email_user' => $email])->first();
    }
}
