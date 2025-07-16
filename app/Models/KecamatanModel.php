<?php

namespace App\Models;

use CodeIgniter\Model;

class KecamatanModel extends Model
{
    protected $table = 'kecamatan';
    protected $allowedFields = [
        'id',
        'provinsi_id',
        'kabupaten_id',
        'label',
    ];

    public function getKecamatanByKabupaten($kabupatenId)
    {
        return $this->where('kabupaten_id', $kabupatenId)->findAll();
    }
}