<?php

namespace App\Models;

use CodeIgniter\Model;

class KabupatenModel extends Model
{
    protected $table = 'kabupaten';
    protected $allowedFields = [
        'id',
        'provinsi_id',
        'label',
    ];

    public function getKabupatenByProvinsi($provinsiId)
    {
        return $this->where('provinsi_id', $provinsiId)->findAll();
    }
}