<?php

namespace App\Models;

use CodeIgniter\Model;

class PreorderBarangModel extends Model
{
    protected $table = 'preorder_barang';
    protected $allowedFields = [
        'id_barang',
        'harga',
        'email_customer',
    ];
}
