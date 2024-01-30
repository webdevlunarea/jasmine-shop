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
        'berat',
        'stok',
        'dimensi',
        'deskripsi',
        'kategori',
        'subkategori',
        'diskon',
        'varian',
        'jml_varian'
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
        return $this->findAll(10, 0);
    }
    public function getBarangPage($page)
    {
        $hitungPag = floor($page / 20);
        return $this->findAll(20, $hitungPag);
    }
}
