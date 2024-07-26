<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtikelModel extends Model
{
    protected $table = 'artikel';
    protected $allowedFields = [
        'id',
        'judul',
        'path',
        'penulis',
        'waktu',
        'isi',
        'kategori',
        'header',
        'suka',
        'komen',
        'bagikan',
        'keywords',
    ];

    public function getArtikel($id = false)
    {
        if ($id == false) {
            return $this->orderBy('id', 'asc')->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
    public function getArtikelJudul($judul = false)
    {
        if ($judul == false) {
            return $this->orderBy('id', 'asc')->findAll();
        }
        return $this->where(['path' => $judul])->first();
    }
    public function getArtikelKategori($kategori = false)
    {
        if ($kategori == false) {
            return $this->orderBy('id', 'asc')->findAll();
        }
        return $this->like("kategori", $kategori, "after")->orderBy('id', 'asc')->findAll();
    }
}
