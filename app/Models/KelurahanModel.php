<?php

namespace App\Models;

use CodeIgniter\Model;

class KelurahanModel extends Model
{
    protected $table = 'kelurahan';
    protected $allowedFields = [
        'id',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kodepos',
        'label',
    ];

    public function getKelurahanByKecamatan($kecamatanId)
    {
        return $this->where('kecamatan_id', $kecamatanId)->findAll();
    }
}