<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingModel extends Model
{
    protected $table = 'tracking';
    protected $allowedFields = [
        'waktu',
        'ip',
        'path',
        'durasi',
    ];

    public function getTracking($waktu = false)
    {
        if (!$waktu) {
            return $this->orderBy('waktu', 'desc')->findAll();
        }
        return $this->where(['waktu' => $waktu])->first();
    }
}
