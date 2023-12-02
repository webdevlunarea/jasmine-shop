<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $table = 'barang';
    protected $allowedFields = [
        'id',
        'nama',
        'gambar',
        'harga',
        'stok',
        'deskripsi',
        'kategori',
        'subkategori',
        'diskon'
    ];

    public function getBarang($id = false)
    {
        if ($id == false) {
            return $this->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
    public function getBarangLimit()
    {
        return $this->findAll(10,0);
    }
}