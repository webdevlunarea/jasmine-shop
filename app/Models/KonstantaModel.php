<?php

namespace App\Models;

use CodeIgniter\Model;

class KonstantaModel extends Model
{
    protected $table = 'konstanta';
    protected $allowedFields = [
        'label',
        'value',
    ];

    public function getKonstantaById($id)
    {
        return $this->where(['id' => $id])->first();
    }
    public function getKonstantaByLabel($label)
    {
        return $this->where(['label' => $label])->first();
    }
}
