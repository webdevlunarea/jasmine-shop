<?php

namespace App\Models;

use CodeIgniter\Model;

class GambarBarangModel extends Model
{
    protected $table = 'gambar_barang';
    protected $allowedFields = [
        'id',
        'gambar1',
        'gambar2',
        'gambar3',
        'gambar4',
        'gambar5',
    ];

    public function getGambar($id = false)
    {
        if ($id == false) {
            return $this->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
    public function getGambarLimit()
    {
        return $this->findAll(10, 0);
    }
}
