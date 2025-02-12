<?php

namespace App\Models;

use CodeIgniter\Model;

class StokModel extends Model
{
    protected $table = 'stok';
    protected $allowedFields = [
        'id',
        'id_barang',
        'nama',
        'varian',
        'jumlah',
        'email_admin',
        'keterangan',
        'stok_akhir',
        'tanggal'
    ];
}
