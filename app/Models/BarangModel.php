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
        'kaca',
        'terjual',
        'terjual_custom'
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
    public function getBarangFlashSale(int $limit = 12, array $productIds = [], bool $manual = false)
    {
        $limit = min(24, max(4, $limit));
        $productIds = array_values(array_unique(array_filter(array_map('trim', $productIds))));

        if ($manual && !empty($productIds)) {
            $rows = $this->where(['active' => '1'])
                ->where('diskon >', 0)
                ->whereIn('id', $productIds)
                ->findAll($limit, 0);

            $positions = array_flip($productIds);
            usort($rows, static function ($a, $b) use ($positions) {
                return ($positions[$a['id']] ?? 9999) <=> ($positions[$b['id']] ?? 9999);
            });

            return $rows;
        }

        return $this->where(['active' => '1'])
            ->where('diskon >', 0)
            ->orderBy('diskon', 'desc')
            ->orderBy('tracking_pop', 'desc')
            ->findAll($limit, 0);
    }
    public function getBarangFlashSaleCandidates(int $limit = 200)
    {
        return $this->where(['active' => '1'])
            ->where('diskon >', 0)
            ->orderBy('diskon', 'desc')
            ->orderBy('nama', 'asc')
            ->findAll($limit, 0);
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
