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
            return $this->orderBy('id', 'desc')->findAll();
        }
        $cur = $this->where(['path' => $judul])->first();
        $prev = $this->where('id_count <', $cur['id_count'])->orderBy('id_count', 'desc')->first();
        $next = $this->where('id_count >', $cur['id_count'])->first();
        return [
            'cur' => $cur,
            'prev' => $prev,
            'next' => $next,
        ];
    }
    public function getArtikelKategori($kategori = false)
    {
        if ($kategori == false) {
            return $this->orderBy('id', 'asc')->findAll();
        }
        return $this->where("kategori", $kategori)->orderBy('id', 'asc')->findAll();
    }
}
