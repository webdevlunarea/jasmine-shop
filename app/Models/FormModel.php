<?php

namespace App\Models;

use CodeIgniter\Model;

class FormModel extends Model
{
    protected $table = 'form';
    protected $allowedFields = [
        'nama',
        'nohp',
        'alamat',
        'pesan',
    ];

    public function getForm($id = false)
    {
        if ($id == false) {
            return $this->orderBy('id', 'asc')->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
}
