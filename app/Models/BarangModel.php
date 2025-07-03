<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $table = 'barang';
    protected $allowedFields = [
        'id',
        'nama',
        'path',
        'pencarian',
        'gambar',
        'harga',
        'berat',
        'stok',
        'dimensi',
        'deskripsi',
        'deskripsi_nonhtml',
        'kategori',
        'subkategori',
        'diskon',
        'varian',
        'jml_varian',
        'shopee',
        'tokped',
        'tiktok',
        'youtube',
        'tracking_pop',
        'active',
        'kaca'
    ];

    public function getBarang($id = false)
    {
        if ($id == false) {
            return $this->where(['active' => '1'])->orderBy('nama', 'asc')->findAll();
        }
        return $this->where(['id' => $id, 'active' => '1'])->first();
    }
    public function getBarangAdmin($id = false)
    {
        if ($id == false) {
            return $this->orderBy('nama', 'asc')->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
    public function getBarangNama($nama = false)
    {
        if ($nama == false) {
            return $this->orderBy('nama', 'asc')->findAll();
        }
        return $this->where(['path' => $nama])->first();
    }
    public function getBarangLimit()
    {
        return $this->where(['active' => '1'])->orderBy('nama', 'asc')->findAll(10, 0);
    }
    public function getBarangBaru()
    {
        return $this->where(['active' => '1'])->orderBy('id', 'desc')->findAll(10, 0);
    }
    public function getBarangPopuler()
    {
        return $this->where(['active' => '1'])->orderBy('tracking_pop', 'desc')->findAll(10, 0);
    }
    public function getBarangPage($page)
    {
        // $hitungPag = floor($page / 20);
        $hitungPag = 20 * ($page - 1);
        if ($page > 1) {
            return $this->where(['active' => '1'])->orderBy('nama', 'asc')->findAll(20, $hitungPag);
        } else {
            return $this->where(['active' => '1'])->orderBy('nama', 'asc')->findAll(20, 0);
        }
    }
    public function getBarangPageAdmin($page)
    {
        // $hitungPag = floor($page / 20);
        $hitungPag = 20 * ($page - 1);
        if ($page > 1) {
            return $this->orderBy('nama', 'asc')->findAll(20, $hitungPag);
        } else {
            return $this->orderBy('nama', 'asc')->findAll(20, 0);
        }
    }
}