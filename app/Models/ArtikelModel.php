<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtikelModel extends Model
{
    protected $table = 'artikel';
    protected $allowedFields = [
        'id',
        'judul',
        'penulis',
        'waktu',
        'isi',
    ];

    public function getArtikel($id = false)
    {
        if ($id == false) {
            return $this->orderBy('id', 'asc')->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
}